<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminMfa
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (config('security.mfa.enforce_admins') && ! $request->user()?->hasTwoFactorEnabled()) {
            return redirect()
                ->route('security.show')
                ->with('status', 'Активирайте двуфакторната защита, за да използвате администраторския панел.');
        }

        return $next($request);
    }
}
