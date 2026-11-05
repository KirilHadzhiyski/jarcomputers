<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowRun;
use Illuminate\Http\Request;
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
            'seo' => [
                'title' => 'Изпълнения и мониторинг',
                'description' => 'История, обеми, грешки и AI разходи на клиентските автоматизации.',
            ],
        ]);
    }
}
