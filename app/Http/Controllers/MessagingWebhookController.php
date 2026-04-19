<?php

namespace App\Http\Controllers;

use App\Services\Communications\InboundMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MessagingWebhookController extends Controller
{
    public function __construct(
        private readonly InboundMessageService $inboundMessageService,
    ) {
    }

    public function verify(string $channel, Request $request): Response
    {
        return $this->inboundMessageService->verify($channel, $request);
    }

    public function receive(string $channel, Request $request): JsonResponse
    {
        $messages = $this->inboundMessageService->receive($channel, $request);

        return response()->json([
            'received' => $messages->count(),
        ]);
    }
}
