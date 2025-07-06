<?php

namespace App\Providers;

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
        \App\Models\TimeEntry::class => \App\Policies\TimeEntryPolicy::class,
        \App\Models\Project::class => \App\Policies\ProjectPolicy::class,
        \App\Models\Team::class => \App\Policies\TeamPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define gates for role-based access control
        Gate::define('manage-projects', function ($user) {
            return in_array($user->role, ['admin', 'manager']);
        });

        Gate::define('manage-users', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('view-all-time-entries', function ($user) {
            return in_array($user->role, ['admin', 'manager']);
        });

        Gate::define('export-reports', function ($user) {
            return in_array($user->role, ['admin', 'manager']);
        });
    }
}
