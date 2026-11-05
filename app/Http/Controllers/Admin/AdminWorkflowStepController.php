<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminWorkflowStepController extends Controller
{
    public function store(Request $request, Workflow $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::in(array_keys(WorkflowStep::TYPE_LABELS))],
            'configuration' => ['nullable', 'json', 'max:10000'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $workflow->steps()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'position' => ((int) $workflow->steps()->max('position')) + 1,
            'configuration' => filled($validated['configuration'] ?? null) ? json_decode($validated['configuration'], true, flags: JSON_THROW_ON_ERROR) : null,
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return back()->with('status', 'Стъпката е добавена към процеса.');
    }

    public function move(Request $request, Workflow $workflow, WorkflowStep $step): RedirectResponse
    {
        abort_unless($step->workflow_id === $workflow->id, 404);
        $validated = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);
        $operator = $validated['direction'] === 'up' ? '<' : '>';
        $order = $validated['direction'] === 'up' ? 'desc' : 'asc';

        $swap = $workflow->steps()
            ->where('position', $operator, $step->position)
            ->orderBy('position', $order)
            ->first();

        if ($swap) {
            DB::transaction(function () use ($step, $swap): void {
                $position = $step->position;
                $step->update(['position' => $swap->position]);
                $swap->update(['position' => $position]);
            });
        }

        return back()->with('status', 'Подредбата на стъпките е обновена.');
    }

    public function destroy(Workflow $workflow, WorkflowStep $step): RedirectResponse
    {
        abort_unless($step->workflow_id === $workflow->id, 404);
        $step->delete();

        $workflow->steps()->orderBy('position')->get()->each(function (WorkflowStep $item, int $index): void {
            $item->update(['position' => $index + 1]);
        });

        return back()->with('status', 'Стъпката е премахната.');
    }
}
