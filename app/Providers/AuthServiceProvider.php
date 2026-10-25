<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Owner & Admin Office: user management, financials, leave approvals
        Gate::define('manage-system', fn (User $user) => $user->canManageSystem());

        // Owner & Admin Office: apply for leave (staff excluded)
        Gate::define('apply-leave', fn (User $user) => $user->canApplyLeave());
    }
}
