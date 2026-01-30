<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Services\AuditService;


class UsersApiController extends Controller
{
    /**
     * LISTADO DE USUARIOS
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
            'users' => $users
        ]);
    }

    /**
     * CREAR USUARIO
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'role'  => 'required|exists:roles,code'
        ]);

        DB::beginTransaction();

        try {
            // Password automático
            $password = Str::random(10);

            // Crear usuario
            $userId = DB::table('users')->insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Asignar rol único
            $roleId = DB::table('roles')
                ->where('code', $request->role)
                ->value('id');

            DB::table('role_user')->insert([
                'user_id' => $userId,
                'role_id' => $roleId
            ]);

            $actorId = auth()->id();

            AuditService::createUser(
                $actorId,
                $userId,
                [
                    'name'  => $request->name,
                    'email' => $request->email,
                    'role'  => $request->role,
                ]
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario creado correctamente',
                'password' => $password
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear usuario'
            ], 500);
        }
    }

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
                'status' => 'error',
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'user' => $user
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $id
        ]);

        $before = DB::table('users')
            ->select('name', 'email')
            ->where('id', $id)
            ->first();

        if (!$before) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        DB::table('users')
            ->where('id', $id)
            ->update([
                'name'       => $request->name,
                'email'      => $request->email,
                'updated_at' => now()
            ]);

        $after = [
            'name'  => $request->name,
            'email' => $request->email
        ];

        // Auditoría
        AuditService::log([
            'action'        => 'UPDATE_USER_PROFILE',
            'actor_user_id' => auth()->id(),
            'target_type'   => 'USER',
            'target_id'     => $id,
            'description'   => 'Actualización de datos del usuario',
            'meta'          => [
                'before' => $before,
                'after'  => $after
            ]
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario actualizado correctamente'
        ]);
    }


    public function updateRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|exists:roles,code'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $role = DB::table('roles')
            ->where('code', $request->role)
            ->first();

        // 👇 ROL ANTERIOR
        $beforeRole = DB::table('roles')
            ->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $id)
            ->value('roles.code');

        DB::table('role_user')->where('user_id', $id)->delete();

        DB::table('role_user')->insert([
            'user_id' => $id,
            'role_id' => $role->id
        ]);

        // 👇 AUDITORÍA
        AuditService::updateUserRole(
            auth()->id(),
            $id,
            $beforeRole,
            $request->role
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Rol actualizado correctamente'
        ]);
    }

    public function updateStatus($id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $before = (bool) $user->is_active;

        DB::table('users')
            ->where('id', $id)
            ->update([
                'is_active' => !$before
            ]);

        DB::table('users')
            ->where('id', $id)
            ->increment('token_version');

        $after = !$before;

        AuditService::updateUserStatus(
            auth()->id(),
            $id,
            $before,
            $after
        );

        return response()->json([
            'status' => 'success',
            'message' => $before
                ? 'Usuario desactivado'
                : 'Usuario activado'
        ]);
    }

    public function resetPassword($id, Request $request)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token no proporcionado'
            ], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            //$decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));

            $adminId = $decoded->sub; // 👈 ID DEL USUARIO AUTENTICADO
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido'
            ], 401);
        }

        // Generar nuevo password
        $newPassword = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 10);

        DB::table('users')
            ->where('id', $id)
            ->update([
                'password' => Hash::make($newPassword)
            ]);

        DB::table('users')
            ->where('id', $id)
            ->increment('token_version');

        AuditService::resetPassword($adminId, $id);

        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña restablecida',
            'password' => $newPassword
        ]);
    }
}
