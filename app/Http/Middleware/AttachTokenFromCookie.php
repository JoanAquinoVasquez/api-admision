<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AttachTokenFromCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request has the 'token' cookie and no Authorization header
        if ($request->hasCookie('token')) {
            $token = $request->cookie('token');
            if (!$request->headers->has('Authorization')) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
        }
        return $next($request);
    }
}
