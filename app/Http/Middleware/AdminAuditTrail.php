<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuditTrail
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        AuditLog::query()->create([
            'user_id' => $request->user()?->id,
            'event' => 'admin.'.strtolower($request->method()),
            'route_name' => $request->route()?->getName(),
            'method' => $request->method(),
            'path' => '/'.$request->path(),
            'response_status' => $response->getStatusCode(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => [
                'changed_fields' => collect($request->except([
                    '_token', '_method', 'password', 'password_confirmation', 'token',
                    'client_secret', 'refresh_token', 'products_token',
                ]))->keys()->values()->all(),
            ],
            'created_at' => now(),
        ]);

        return $response;
    }
}
