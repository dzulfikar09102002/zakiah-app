<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\UniqueCodeGenerator;
use App\Http\Controllers\Controller;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Device;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request) {
        $request->validate([
            "email" => 'required',
            "device_id" => 'required',
            "device_name" => 'required',
            "password" => "required"
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            $response = new BaseJsonResponse(null, __('auth.password'));
            return $response->response();
        }

        $token = $user->createToken($user->email);

        # for now
        # find entities
        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            $response = new BaseJsonResponse(null, __('auth.missing_employee'));
            return $response->response();
        }

        $selected_entity = $employee->entity;
        if (!$selected_entity) {
            $response = new BaseJsonResponse(null, __('auth.missing_entity'));
            return $response->response();
        }

        $role = $employee->role;

        $response = new BaseJsonResponse([
            "user" => $user,
            "selected_entity" => $selected_entity,
            "employee_code" => $employee->code,
            "allow_pos" => $role->allow_pos,
            "allow_backoffice" => $role->allow_backoffice,
            "token" => $token->plainTextToken,
        ]);
        return $response->response();
    }

    public function logout(Request $request) {
        # start transcation
        DB::transaction(function () use ($request) {
            # do proccess
    
            // $request->user()->tokens()->delete();
            // $request->employee->devices()->delete();

            # end transaction
        });

        $response = new BaseJsonResponse(null, "Logout");
        return $response->response();
    }
}
