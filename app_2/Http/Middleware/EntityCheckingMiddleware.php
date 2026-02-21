<?php

namespace App\Http\Middleware;

use App\Http\Responses\BaseJsonResponse;
use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityCheckingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $employee = Employee::where('code', $request->header('x-employee-code'))->first();
        if (!$employee) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('auth.invalid_employee'));
            return $response->response(422);
        }

        $entity = $employee->entity;
        if (!$entity) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('auth.invalid_entity'));
            return $response->response(422);
        }

        return $next($request->merge([
            'employee' => $employee,
            'entity' => $entity,
        ]));
    }
}
