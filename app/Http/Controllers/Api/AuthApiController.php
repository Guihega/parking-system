<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $data = AuthService::login(
                $request->email,
                $request->password
            );

            return response()->json([
                'status' => 'success',
                'token'  => $data['token'],
                'user'   => $data['user'],
            ]);

        } catch (\Exception $e) {

            if ($e->getMessage() === 'USER_DISABLED') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario desactivado',
                ], 403);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Credenciales inválidas',
            ], 401);
        }
    }
}
