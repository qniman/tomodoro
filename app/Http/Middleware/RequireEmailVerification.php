<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireEmailVerification
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->email_verified_at) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Email не подтверждён.'], 403);
            }

            return redirect()->route('verify.email');
        }

        return $next($request);
    }
}
