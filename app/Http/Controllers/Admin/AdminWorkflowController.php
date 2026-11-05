<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminWorkflowController extends Controller
{
    public function index(): View
    {
        return view('admin.workflows.index', [
            'workflows' => Workflow::query()
                ->with(['client', 'steps'])
                ->withCount('runs')
                ->latest()
                ->paginate(12),
            'statusLabels' => Workflow::STATUS_LABELS,
            'stats' => [
                'total' => Workflow::query()->count(),
                'active' => Workflow::query()->where('status', 'active')->count(),
                'scheduled' => Workflow::query()->where('trigger_type', 'schedule')->count(),
                'needs_review' => Workflow::query()->where('approval_mode', 'review')->count(),
            ],
            'seo' => [
                'title' => 'Работни процеси',
                'description' => 'Управление на автоматизациите за събиране, анализ и подготовка на продуктови данни.',
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.workflows.create', $this->formData(null));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        $workflow = Workflow::query()->create($validated);

        return redirect()
            ->route('admin.workflows.edit', $workflow)
            ->with('status', 'Работният процес е създаден. Добавете стъпките му в редактора.');
    }

    public function edit(Workflow $workflow): View
    {
        $workflow->load(['steps', 'client', 'runs' => fn ($query) => $query->limit(10)]);

        return view('admin.workflows.edit', $this->formData($workflow));
    }

    public function update(Request $request, Workflow $workflow): RedirectResponse
    {
        $validated = $this->validated($request, $workflow);
        $validated['updated_by'] = $request->user()->id;
        $workflow->update($validated);

        return redirect()
            ->route('admin.workflows.edit', $workflow)
            ->with('status', 'Настройките на процеса са запазени.');
    }

    public function destroy(Workflow $workflow): RedirectResponse
    {
        $workflow->delete();

        return redirect()
            ->route('admin.workflows.index')
            ->with('status', 'Работният процес и неговата история са изтрити.');
    }

    private function formData(?Workflow $workflow): array
    {
        return [
            'workflow' => $workflow,
            'clients' => User::query()->where('role', 'user')->orderBy('name')->get(['id', 'name', 'email']),
            'statusLabels' => Workflow::STATUS_LABELS,
            'triggerLabels' => Workflow::TRIGGER_LABELS,
            'sourceLabels' => Workflow::SOURCE_LABELS,
            'approvalLabels' => Workflow::APPROVAL_LABELS,
            'stepTypeLabels' => WorkflowStep::TYPE_LABELS,
            'seo' => [
                'title' => $workflow ? "Редакция: {$workflow->name}" : 'Нов работен процес',
                'description' => 'Конфигуриране на източник, график, правила за одобрение и последователност от стъпки.',
            ],
        ];
    }

    private function validated(Request $request, ?Workflow $workflow = null): array
    {
        $validated = $request->validate([
            'client_user_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('workflows', 'slug')->ignore($workflow?->id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(array_keys(Workflow::STATUS_LABELS))],
            'trigger_type' => ['required', Rule::in(array_keys(Workflow::TRIGGER_LABELS))],
            'frequency_minutes' => ['nullable', 'required_if:trigger_type,schedule', 'integer', 'min:15', 'max:43200'],
            'source_type' => ['required', Rule::in(array_keys(Workflow::SOURCE_LABELS))],
            'source_url' => ['nullable', 'url:https', 'max:2000'],
            'category_filters' => ['nullable', 'string', 'max:3000'],
            'approval_mode' => ['required', Rule::in(array_keys(Workflow::APPROVAL_LABELS))],
        ]);

        $validated['slug'] = ($validated['slug'] ?? null) ?: $this->uniqueSlug($validated['name'], $workflow);
        $validated['frequency_minutes'] = $validated['trigger_type'] === 'schedule'
            ? $validated['frequency_minutes']
            : null;
        $validated['category_filters'] = collect(preg_split('/[\r\n,]+/', $validated['category_filters'] ?? ''))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $validated;
    }

    private function uniqueSlug(string $name, ?Workflow $workflow): string
    {
        $base = Str::slug($name) ?: 'workflow';
        $slug = $base;
        $suffix = 2;

        while (Workflow::query()->where('slug', $slug)->when($workflow, fn ($query) => $query->whereKeyNot($workflow->id))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
