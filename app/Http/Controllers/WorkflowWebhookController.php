<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Services\Workflows\WorkflowDispatcher;
use App\Services\Workflows\WorkflowExecutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowWebhookController extends Controller
{
    public function receive(Request $request, Workflow $workflow, WorkflowDispatcher $dispatcher): JsonResponse
    {
        if ($workflow->status !== 'active' || $workflow->trigger_type !== 'webhook' || blank($workflow->webhook_secret)) {
            abort(404);
        }

        $timestamp = (string) $request->header('X-JAR-Timestamp');
        if (! ctype_digit($timestamp) || abs(now()->timestamp - (int) $timestamp) > 300) {
            return response()->json(['message' => 'Expired webhook timestamp.'], 401);
        }
        $provided = (string) $request->header('X-JAR-Signature');
        $expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $workflow->webhook_secret);
        if ($provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        try {
            $run = $dispatcher->enqueue($workflow, null, 'webhook');
        } catch (WorkflowExecutionException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'status' => $run ? 'queued' : 'already_queued',
            'run_id' => $run?->id,
        ], 202);
    }
}
