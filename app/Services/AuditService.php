<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AuditService
{
    /**
     * Método base de auditoría
     */
    public static function log(array $data): void
    {
        $tenantId = app()->bound('tenant_id')
            ? app('tenant_id')
            : ($data['tenant_id'] ?? null);

        DB::table('audit_logs')->insert([
            'tenant_id'   => $tenantId,
            'user_id'     => $data['actor_user_id'] ?? null,
            'action'      => $data['action'],
            'description' => $data['description'],
            'created_at'  => now(),
        ]);
    }



    /* =========================
       Helpers semánticos
       ========================= */

    public static function loginSuccess(int $userId, string $email): void
    {
        self::log([
            'tenant_id'     => DB::table('users')->where('id',$userId)->value('tenant_id'),
            'action'        => 'LOGIN_SUCCESS',
            'actor_user_id' => $userId,
            'description'   => 'Inicio de sesión exitoso',
        ]);
    }

    public static function loginFailure(
    ?int $userId,
    ?string $identifier,
    string $reason
    ): void {
        DB::table('auth_login_failures')->insert([
            'user_id'    => $userId,
            'identifier' => $identifier,
            'reason'     => $reason,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }



    public static function createUser(int $actorId, int $userId, array $createdData): void
    {
        self::log([
            'action'        => 'CREATE_USER',
            'actor_user_id' => $actorId,
            'target_type'   => 'USER',
            'target_id'     => $userId,
            'description'   => 'Creación de usuario',
            'meta'          => [
                'created' => $createdData
            ],
        ]);
    }

    public static function updateUserStatus(
        int $actorId,
        int $userId,
        bool $before,
        bool $after
    ): void {
        self::log([
            'action'        => 'UPDATE_USER_STATUS',
            'actor_user_id' => $actorId,
            'target_type'   => 'USER',
            'target_id'     => $userId,
            'description'   => 'Cambio de estado de usuario',
            'meta'          => [
                'before' => ['is_active' => $before],
                'after'  => ['is_active' => $after],
            ],
        ]);
    }

    public static function updateUserRole(
        int $actorId,
        int $userId,
        string $beforeRole,
        string $afterRole
    ): void {
        self::log([
            'action'        => 'UPDATE_USER_ROLE',
            'actor_user_id' => $actorId,
            'target_type'   => 'USER',
            'target_id'     => $userId,
            'description'   => 'Cambio de rol de usuario',
            'meta'          => [
                'before' => ['role' => $beforeRole],
                'after'  => ['role' => $afterRole],
            ],
        ]);
    }

    public static function resetPassword(int $actorId, int $userId): void
    {
        self::log([
            'action'        => 'RESET_PASSWORD',
            'actor_user_id' => $actorId,
            'target_type'   => 'USER',
            'target_id'     => $userId,
            'description'   => 'Restablecimiento de contraseña',
        ]);
    }

    public static function updateRole(int $actorId, int $roleId, array $before, array $after): void
    {
        self::log([
            'action'        => 'UPDATE_ROLE',
            'actor_user_id' => $actorId,
            'target_type'   => 'ROLE',
            'target_id'     => $roleId,
            'description'   => 'Actualización de rol',
            'meta'          => [
                'before' => $before,
                'after'  => $after,
            ],
        ]);
    }
}
