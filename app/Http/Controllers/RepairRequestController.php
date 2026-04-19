<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRepairRequestRequest;
use App\Jobs\DispatchRepairRequestNotifications;
use App\Models\RepairRequest;
use App\Services\Communications\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class RepairRequestController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversationService,
    ) {
    }

    public function store(StoreRepairRequestRequest $request): JsonResponse|RedirectResponse
    {
        $repairRequest = DB::transaction(function () use ($request) {
            $repairRequest = RepairRequest::create([
                'name' => $request->string('name')->toString(),
                'phone' => $request->string('phone')->toString(),
                'email' => $request->string('email')->toString() ?: null,
                'city' => $request->string('city')->toString(),
                'model' => $request->string('model')->toString(),
                'issue' => $request->string('issue')->toString(),
                'preferred_contact' => $request->string('preferred_contact')->toString() ?: 'phone',
                'source_page' => $request->string('source_page')->toString() ?: $request->path(),
                'source_channel' => 'website-form',
                'gdpr_consent' => $request->boolean('gdpr_consent'),
                'meta' => array_filter([
                    'form_fragment' => $request->string('form_fragment')->toString() ?: null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referer' => $request->headers->get('referer'),
                ]),
            ]);

            $this->conversationService->createFromRepairRequest($repairRequest);

            return $repairRequest;
        });

        DispatchRepairRequestNotifications::dispatch($repairRequest->id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Благодарим за заявката. Ще се свържем с вас в рамките на 1 час в работно време.',
            ], 201);
        }

        $fragment = $request->string('form_fragment')->toString() ?: 'repair-form';

        return redirect()
            ->back()
            ->withFragment($fragment)
            ->with('repair_request_success', true);
    }
}
