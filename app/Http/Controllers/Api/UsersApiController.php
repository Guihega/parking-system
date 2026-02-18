<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersApiController extends Controller
{
    /**
     * LISTADO DE USUARIOS
     * (se mantiene con query directa por ahora)
     */
    public function index()
    {
        $users = DB::table('users')
            ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.is_active',
                'roles.code as role'
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'users'  => $users
        ]);
    }

    /**
     * CREAR USUARIO (SP)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'  => 'required|string|max:100',
                'email' => 'required|email',
                'role'  => 'required|string'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $passwordPlain = Str::random(10);
            $passwordHash  = Hash::make($passwordPlain);

            DB::selectOne(
                'CALL sp_user_create(?, ?, ?, ?, ?, ?)',
                [
                    app('tenant_id'),
                    $validated['name'],
                    $validated['email'],
                    $passwordHash,
                    $validated['role'],
                    auth()->id()
                ]
            );

            return response()->json([
                'status'   => 'success',
                'message'  => 'Usuario creado correctamente',
                'password' => $passwordPlain
            ], 201);

        } catch (\Throwable $e) {

            if (str_contains($e->getMessage(), 'EMAIL_ALREADY_EXISTS')) {
                return response()->json([
                    'message' => 'El correo ya está registrado',
                    'errors'  => [
                        'email' => ['El correo ya está registrado']
                    ]
                ], 422);
            }

            return response()->json([
                'message' => 'Error al crear usuario'
            ], 500);
        }
    }

    /**
     * MOSTRAR USUARIO
     */
    public function show(int $id)
    {
        $user = DB::table('users')
            ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.is_active',
                'users.created_at',
                'roles.code as role'
            )
            ->where('users.id', $id)
            ->first();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'user'   => $user
        ]);
    }

    /**
     * ACTUALIZAR PERFIL (SOLO DATOS BÁSICOS)
     * 👉 se mantiene directo (no crítico)
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $id
        ]);

        $affected = DB::table('users')
            ->where('id', $id)
            ->update([
                'name'       => $request->name,
                'email'      => $request->email,
                'updated_at' => now()
            ]);

        if (!$affected) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Auditoría centralizada
        DB::select(
            'CALL sp_audit_log(?, ?, ?, ?)',
            [
                app('tenant_id'),
                auth()->id(),
                'USER_PROFILE_UPDATED',
                "Actualización de datos del usuario ID {$id}"
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Usuario actualizado correctamente'
        ]);
    }

    /**
     * CAMBIAR ROL (SP)
     */
    public function updateRole(Request $request, int $id)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,code'
        ]);

        try {
            $result = DB::selectOne(
                'CALL sp_user_assign_role(?, ?, ?)',
                [
                    $id,
                    $request->role,
                    auth()->id()
                ]
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Rol actualizado correctamente',
                'data'    => $result
            ]);

        } catch (\Throwable $e) {

            $message = match (true) {
                str_contains($e->getMessage(), 'USER_NOT_FOUND') =>
                    'Usuario no encontrado',

                str_contains($e->getMessage(), 'ROLE_NOT_FOUND') =>
                    'Rol inválido o inactivo',

                str_contains($e->getMessage(), 'CANNOT_CHANGE_OWN_ROLE') =>
                    'No puedes cambiar tu propio rol',

                default =>
                    'No se pudo actualizar el rol'
            };

            return response()->json([
                'status'  => 'error',
                'message' => $message
            ], 422);
        }
    }


    /**
     * ACTIVAR / DESACTIVAR USUARIO (SP)
     */
    public function updateStatus(int $id)
    {
        try {
            $result = DB::selectOne(
                'CALL sp_user_toggle_status(?, ?)',
                [$id, auth()->id()]
            );

            return response()->json([
                'status'  => 'success',
                'message' => $result->current_status
                    ? 'Usuario activado'
                    : 'Usuario desactivado'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al actualizar estado'
            ], 500);
        }
    }

    /**
     * RESET PASSWORD (SP)
     */
    public function resetPassword(int $id)
    {
        try {
            $newPassword = Str::random(10);
            $hash        = Hash::make($newPassword);

            DB::selectOne(
                'CALL sp_user_reset_password(?, ?, ?)',
                [$id, $hash, auth()->id()]
            );

            return response()->json([
                'status'   => 'success',
                'message'  => 'Contraseña restablecida',
                'password' => $newPassword
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al restablecer contraseña'
            ], 500);
        }
    }

    public function assignRole(Request $request, int $userId)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,code'
        ]);

        try {
            /**
             * El SP:
             *  - valida usuario
             *  - evita auto-cambio
             *  - valida rol activo
             *  - reemplaza rol
             *  - retorna info final
             */
            $result = DB::selectOne(
                'CALL sp_user_assign_role(?, ?, ?)',
                [
                    $userId,
                    $request->role,
                    auth()->id()
                ]
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Rol actualizado correctamente',
                'data'    => $result
            ]);

        } catch (\Throwable $e) {

            /**
             * Errores de negocio lanzados por SIGNAL
             */
            $message = match (true) {
                str_contains($e->getMessage(), 'USER_NOT_FOUND') =>
                    'Usuario no encontrado',

                str_contains($e->getMessage(), 'ROLE_NOT_FOUND') =>
                    'Rol inválido o inactivo',

                str_contains($e->getMessage(), 'CANNOT_CHANGE_OWN_ROLE') =>
                    'No puedes cambiar tu propio rol',

                default =>
                    'No se pudo actualizar el rol'
            };

            return response()->json([
                'status'  => 'error',
                'message' => $message
            ], 422);
        }
    }


}
