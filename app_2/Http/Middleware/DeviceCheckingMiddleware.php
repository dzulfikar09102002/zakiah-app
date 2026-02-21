<?php

namespace App\Http\Middleware;

use App\Http\Responses\BaseJsonResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceCheckingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $device = $request->employee->devices()->where('code', $request->header('x-device-code'))->first();
        if (!$device) {
            # TEJA check error message
            $response = new BaseJsonResponse(['relogin' => true], __('auth.invalid_device'));
            return $response->response(422);
        }

        return $next($request->merge([
            'device' => $device,
        ]));
    }
}
