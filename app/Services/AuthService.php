<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use App\Models\User;
use Illuminate\Support\Facades\Gate;


class AuthService
{
    public static function login(string $email, string $password): array
    {
        // 1️⃣ Login vía SP
        $result = DB::selectOne(
            'CALL sp_auth_login(?)',
            [$email]
        );

        if (!$result) {
            throw new \Exception('AUTH.USER_NOT_FOUND');
        }

        if (!$result->is_active) {
            throw new \Exception('AUTH.USER_DISABLED');
        }

        // 2️⃣ Validar password
        if (!Hash::check($password, $result->password)) {
            throw new \Exception('AUTH.INVALID_CREDENTIALS');
        }

        // 3️⃣ Permisos efectivos
        $permissions = DB::select(
            'CALL sp_auth_get_effective_permissions(?)',
            [$result->user_id]
        );

        $permissionCodes = collect($permissions)
            ->pluck('code')
            ->values()
            ->toArray();

        // 4️⃣ User model (solo para Laravel session)
        $user = User::findOrFail($result->user_id);

        // 5️⃣ Payload JWT
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 8),
            'token_version' => $user->token_version
        ];

        $jwt = JWT::encode(
            $payload,
            config('jwt.secret'),
            'HS256'
        );

        // 6️⃣ Payload final
        return [
            'token' => $jwt,
            'raw_user' => $user,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'roles' => [$result->role_code],
                'permissions' => $permissionCodes,
                'is_superadmin' => (bool) $user->is_superadmin,
            ]
        ];
    }
}
