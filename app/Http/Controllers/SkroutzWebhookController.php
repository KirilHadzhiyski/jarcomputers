<?php

namespace App\Http\Controllers;

use App\Jobs\ImportSkroutzOrder;
use App\Models\IntegrationConnection;
use App\Services\Integrations\SkroutzCatalog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class SkroutzWebhookController extends Controller
{
    public function receive(Request $request)
    {
        $connection = IntegrationConnection::where('provider', 'skroutz')->first();
        abort_unless($connection?->enabled && filled($connection->credentials['token'] ?? null), 404);
        abort_unless(IpUtils::checkIp($request->ip(), config('skroutz.webhook_allowed_ips')), 403);
        $data = $request->validate(['event_type' => 'required|in:new_order,order_updated', 'order.code' => ['required', 'string', 'regex:/^[0-9]{6}-[0-9]+$/', 'max:80']]);
        ImportSkroutzOrder::dispatch($data['order']['code']);

        return response()->json(['received' => true]);
    }

    public function feed(Request $request, SkroutzCatalog $catalog)
    {
        abort_unless(config('skroutz.feed_enabled'), 404);
        abort_unless(IpUtils::checkIp($request->ip(), config('skroutz.feed_allowed_ips')), 403);

        return response($catalog->xml())->header('Content-Type', 'application/xml; charset=UTF-8')->header('Cache-Control', 'no-store');
    }
}
