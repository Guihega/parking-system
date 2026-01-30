<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use App\Services\AuditService;
use Exception;

class AuthService
{
    public static function login(string $email, string $password): array
    {
        $user = DB::table('users')
            ->where('email', $email)
            ->first();

        if (!$user) {
            AuditService::loginFailure(null, $email, 'USER_NOT_FOUND');
            throw new Exception('INVALID_CREDENTIALS');
        }

        if (!$user->is_active) {
            AuditService::loginFailure($user->id, $user->email, 'USER_DISABLED');
            throw new Exception('USER_DISABLED');
        }

        if (!Hash::check($password, $user->password)) {
            AuditService::loginFailure($user->id, $user->email, 'INVALID_PASSWORD');
            throw new Exception('INVALID_CREDENTIALS');
        }

        // Roles
        $roles = DB::table('roles')
            ->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->pluck('roles.code');

        // Permisos
        $permissions = DB::table('permissions')
            ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
            ->join('role_user', 'permission_role.role_id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->pluck('permissions.code')
            ->unique()
            ->values();

        $payload = [
            'iss'           => 'parking-system',
            'sub'           => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'roles'         => $roles,
            'permissions'  => $permissions,
            'token_version'=> $user->token_version,
            'iat'           => time(),
            'exp'           => time() + (60 * 60 * 8),
        ];

        $token = JWT::encode(
            $payload,
            config('jwt.secret'),
            'HS256'
        );

        AuditService::loginSuccess($user->id, $user->email);

        return [
            'token' => $token,
            'user'  => $payload,
            'raw_user' => $user
        ];
    }
}
