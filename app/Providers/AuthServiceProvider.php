<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Policies futuras aquí
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, string $ability) {
            $permissions = session('permissions', []);

            return in_array($ability, $permissions) ? true : null;
        });
    }
}
