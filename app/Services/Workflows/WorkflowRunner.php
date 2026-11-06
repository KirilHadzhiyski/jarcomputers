<?php

namespace App\Services\Workflows;

use App\Models\UsageRecord;
use App\Models\WorkflowRecord;
use App\Models\WorkflowRun;
use Illuminate\Support\Facades\DB;

class WorkflowRunner
{
    public function __construct(
        private readonly WorkflowSourceClient $source,
        private readonly WorkflowQuotaService $quota,
        private readonly WorkflowAlertService $alerts,
    ) {}

    public function execute(WorkflowRun $run): void
    {
        $run->load('workflow.client.currentSubscription.plan', 'workflow.steps');
        $workflow = $run->workflow;
        if (! $workflow || $workflow->status !== 'active') {
            throw new WorkflowExecutionException('Работният процес не е активен.');
        }

        $this->quota->assertCanStart($workflow);
        $records = collect($this->applyCategoryFilters($workflow->category_filters ?? [], $this->source->collect($workflow)))
            ->unique('fingerprint')
            ->values()
            ->all();
        $this->quota->assertRecordsFit($workflow, count($records));

        $matched = collect($records)->filter(fn (array $record) => filled($record['sku'] ?? null)
            || filled($record['ean'] ?? null)
            || filled($record['gtin'] ?? null)
            || filled($record['mpn'] ?? null))->count();
        $publishStep = $workflow->steps->first(fn ($step) => $step->is_enabled && $step->type === 'publish');

        DB::transaction(function () use ($run, $workflow, $records, $matched, $publishStep): void {
            foreach ($records as $record) {
                WorkflowRecord::query()->updateOrCreate(
                    ['workflow_run_id' => $run->id, 'fingerprint' => $record['fingerprint']],
                    [...$record, 'workflow_id' => $workflow->id, 'status' => 'collected']
                );
            }

            if ($workflow->client_user_id && $records !== []) {
                UsageRecord::query()->create([
                    'user_id' => $workflow->client_user_id,
                    'workflow_id' => $workflow->id,
                    'workflow_run_id' => $run->id,
                    'service_type' => 'workflow',
                    'provider' => parse_url((string) $workflow->source_url, PHP_URL_HOST) ?: 'workflow-source',
                    'quantity' => count($records),
                    'unit' => 'record',
                    'cost' => 0,
                    'currency_code' => $workflow->client?->currentSubscription?->plan?->currency_code ?? 'EUR',
                    'occurred_at' => now(),
                    'metadata' => ['source_type' => $workflow->source_type],
                ]);
            }

            $run->update([
                'status' => 'completed',
                'records_collected' => count($records),
                'records_matched' => $matched,
                'records_exported' => 0,
                'error_count' => 0,
                'metrics' => [
                    'source_type' => $workflow->source_type,
                    'steps_enabled' => $workflow->steps->where('is_enabled', true)->count(),
                    'publish_blocked' => (bool) $publishStep,
                ],
                'summary' => $publishStep
                    ? 'Данните са събрани и нормализирани. Публикуването е задържано за преглед и активен live конектор.'
                    : 'Данните са събрани и нормализирани успешно.',
                'error_message' => null,
                'finished_at' => now(),
            ]);

            $workflow->update([
                'last_run_at' => now(),
                'next_run_at' => $workflow->trigger_type === 'schedule'
                    ? now()->addMinutes((int) $workflow->frequency_minutes)
                    : null,
            ]);
        });

        if ($publishStep) {
            $this->alerts->create(
                $workflow,
                $run,
                'approval_required',
                'Нужно е одобрение за публикуване',
                'Процесът подготви данните, но не извърши външна промяна. Проверете резултата и активирайте конкретен live конектор.',
                'info',
                ['records' => count($records)]
            );
        }
        $this->alerts->resolvePreviousFailures($workflow);
    }

    private function applyCategoryFilters(array $filters, array $records): array
    {
        if ($filters === []) {
            return $records;
        }
        $needles = array_map(fn (string $value) => mb_strtolower($value), $filters);

        return array_values(array_filter($records, function (array $record) use ($needles): bool {
            $category = mb_strtolower((string) ($record['category'] ?? ''));

            return $category !== '' && collect($needles)->contains(fn (string $needle) => str_contains($category, $needle));
        }));
    }
}
