<?php

namespace App\Services\Communications;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class WebhookSignatureValidator
{
    public function validate(string $channel, Request $request): void
    {
        if (in_array($channel, ['facebook-messenger', 'whatsapp'], true)) {
            $this->validateFacebookStyleSignature($request, config("communications.channels.{$channel}.app_secret"));

            return;
        }

        if ($channel === 'viber') {
            $signature = (string) $request->header('X-Viber-Content-Signature');
            $secret = (string) config('communications.channels.viber.webhook_secret');

            if ($signature === '' || $secret === '') {
                return;
            }

            $expected = hash_hmac('sha256', $request->getContent(), $secret);

            if (! hash_equals($expected, $signature)) {
                throw new UnauthorizedHttpException('Viber', 'Invalid webhook signature.');
            }
        }
    }

    private function validateFacebookStyleSignature(Request $request, ?string $secret): void
    {
        $signature = (string) $request->header('X-Hub-Signature-256');

        if ($signature === '' || empty($secret)) {
            return;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, $signature)) {
            throw new UnauthorizedHttpException('Facebook', 'Invalid webhook signature.');
        }
    }
}
