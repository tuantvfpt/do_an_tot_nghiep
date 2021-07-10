<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\phongban;
use App\Models\roles;
use App\Models\chucvu;
use App\Models\lichChamcong;
use App\Models\Calendar_leave;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('view', function (User $user) {
            return in_array($user->role_id, [1, 2]); //admin,hr
            // dd($user);
        });
        Gate::define('view/id', function (User $user) {
            return in_array($user->role_id, [1, 2]); // admin,hr
        });
        Gate::define('create', function (User $user) {
            return in_array($user->role_id, [1, 2]); //admin
            // hr
        });
        Gate::define('update', function (User $user) {
            return in_array($user->role_id, [1, 2]); //admin
            //admin, hr
        });
        Gate::define('delete', function (User $user) {
            return in_array($user->role_id, [1, 2]); //admin
            //admin
        });
        Gate::define('attendanceCheck', function (User $user) {
            return in_array($user->role_id, [1, 2]); //admin,hr
            //admin
        });
        Gate::define('confirmLeave', function (User $user) {
            return in_array($user->role_id, [1, 2]); //admin, hr
        });
    }
}
