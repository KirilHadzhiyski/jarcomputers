<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAccessBlocked()) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            abort(403, 'Профилът е временно спрян или деактивиран.');
        }

        return redirect()
            ->route('login')
            ->withErrors(['email' => 'Профилът е временно спрян или деактивиран. Свържете се с администратор.']);
    }
}
