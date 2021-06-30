<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\phongban;
use App\Models\roles;
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
            return $user->role_id == 1; //admin
            dd($user);
        });
        Gate::define('view/id', function (User $user) {
            return $user->role_id == 2; // admin,hr,user
        });
        Gate::define('create', function (User $user) {
            return in_array( $user->role_id, [2]) || (auth()->check() && $user->role_id == auth()->id()); 
             // hr
        });
        Gate::define('update', function (User $user) {
            return in_array( $user->role_id, [1,2]) || (auth()->check() &&$user->role_id == auth()->id());
            //admin, hr
        });
        Gate::define('delete', function (User $user) {
            return in_array( $user->role_id, [1]) || (auth()->check() &&$user->role_id == auth()->id());
            //admin
        });
    }
}
