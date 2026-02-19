<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Support\Facades\Log;

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

            $code = $e->getMessage();

            switch ($code) {

                case 'AUTH.USER_NOT_FOUND':
                    return response()->json([
                        'status' => 'error',
                        'code' => 'USER_NOT_FOUND',
                        'message' => 'No existe una cuenta con ese correo',
                    ], 404);

                case 'AUTH.USER_DISABLED':
                    return response()->json([
                        'status' => 'error',
                        'code' => 'USER_DISABLED',
                        'message' => 'El usuario está desactivado',
                    ], 403);

                case 'AUTH.INVALID_CREDENTIALS':
                    return response()->json([
                        'status' => 'error',
                        'code' => 'INVALID_CREDENTIALS',
                        'message' => 'La contraseña es incorrecta',
                    ], 401);

                default:
                    \Log::error('Auth error', [
                        'error' => $e->getMessage()
                    ]);

                    return response()->json([
                        'status' => 'error',
                        'code' => 'SYSTEM_ERROR',
                        'message' => 'Error interno del servidor',
                    ], 500);
            }
        }

    }
}
