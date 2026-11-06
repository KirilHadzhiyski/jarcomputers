<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowAlert;
use App\Models\WorkflowRun;
use App\Services\Workflows\WorkflowDispatcher;
use App\Services\Workflows\WorkflowExecutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminWorkflowRunController extends Controller
{
    public function index(Request $request): View
    {
        $workflowId = $request->integer('workflow_id');

        return view('admin.workflow-runs.index', [
            'runs' => WorkflowRun::query()
                ->with(['workflow', 'actor'])
                ->when($workflowId, fn ($query) => $query->where('workflow_id', $workflowId))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'workflows' => Workflow::query()->orderBy('name')->get(['id', 'name']),
            'selectedWorkflowId' => $workflowId,
            'statusLabels' => WorkflowRun::STATUS_LABELS,
            'alerts' => WorkflowAlert::query()->with(['workflow', 'run'])->whereIn('status', ['open', 'acknowledged'])->latest('occurred_at')->take(20)->get(),
            'alertSeverityLabels' => WorkflowAlert::SEVERITY_LABELS,
            'alertStatusLabels' => WorkflowAlert::STATUS_LABELS,
            'monitoring' => [
                'queued' => WorkflowRun::query()->where('status', 'queued')->count(),
                'running' => WorkflowRun::query()->where('status', 'running')->count(),
                'failed_today' => WorkflowRun::query()->where('status', 'failed')->where('created_at', '>=', now()->startOfDay())->count(),
                'scheduler_heartbeat' => Cache::get('workflows:scheduler_heartbeat'),
            ],
            'seo' => [
                'title' => 'Изпълнения и мониторинг',
                'description' => 'История, обеми, грешки и AI разходи на клиентските автоматизации.',
            ],
        ]);
    }

    public function store(Request $request, Workflow $workflow, WorkflowDispatcher $dispatcher): RedirectResponse
    {
        try {
            $run = $dispatcher->enqueue($workflow, $request->user()->id, 'manual');
        } catch (WorkflowExecutionException $exception) {
            return back()->withErrors(['workflow' => $exception->getMessage()]);
        }

        if (! $run) {
            return back()->withErrors(['workflow' => 'Процесът вече е на опашка или се изпълнява.']);
        }

        return redirect()->route('admin.workflow-runs.show', $run)->with('status', 'Изпълнението е добавено в опашката.');
    }

    public function show(WorkflowRun $workflowRun): View
    {
        $workflowRun->load(['workflow.client', 'actor', 'alerts']);

        return view('admin.workflow-runs.show', [
            'run' => $workflowRun,
            'records' => $workflowRun->records()->latest()->paginate(50),
            'statusLabels' => WorkflowRun::STATUS_LABELS,
            'alertSeverityLabels' => WorkflowAlert::SEVERITY_LABELS,
            'seo' => ['title' => 'Изпълнение #'.$workflowRun->id],
        ]);
    }

    public function acknowledge(WorkflowAlert $workflowAlert): RedirectResponse
    {
        if ($workflowAlert->status === 'open') {
            $workflowAlert->update(['status' => 'acknowledged', 'acknowledged_at' => now()]);
        }

        return back()->with('status', 'Сигналът е отбелязан като прегледан.');
    }
}
