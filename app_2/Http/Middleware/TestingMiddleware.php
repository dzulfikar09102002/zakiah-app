<?php

namespace App\Http\Middleware;

use App\Models\entity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TestingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $entity = entity::where('id', 1)->first();

        return $next($request->merge(['key' => $entity]));
    }
}
