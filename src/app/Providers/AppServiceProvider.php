<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define authorization gates
        
        // Superadmin can do everything
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperadmin()) {
                return true;
            }
        });

        // Manage Gapoktan - only ketua & pengurus gapoktan
        Gate::define('manage-gapoktan', function (User $user) {
            return $user->hasGapoktanAccess();
        });

        // Manage Poktan - ketua & pengurus gapoktan can manage all, ketua & pengurus poktan can manage their own
        Gate::define('manage-poktan', function (User $user, ?int $poktanId = null) {
            if ($user->hasGapoktanAccess()) {
                return true; // Can manage all poktans
            }
            
            if ($user->hasPoktanAccess() && $poktanId) {
                return $user->poktan_id === $poktanId; // Can only manage their own poktan
            }
            
            return false;
        });

        // View financial data
        Gate::define('view-finances', function (User $user, ?int $poktanId = null) {
            if ($user->hasGapoktanAccess()) {
                return true; // Can view all finances
            }
            
            if ($user->hasPoktanAccess() && $poktanId) {
                return $user->poktan_id === $poktanId; // Can only view their poktan's finances
            }
            
            return false;
        });

        // Manage transactions (create, update, delete)
        Gate::define('manage-transactions', function (User $user, ?int $poktanId = null) {
            // Anggota cannot manage transactions
            if ($user->isAnggotaPoktan()) {
                return false;
            }

            if ($user->hasGapoktanAccess()) {
                return true;
            }
            
            if ($user->hasPoktanAccess() && $poktanId) {
                return $user->poktan_id === $poktanId;
            }
            
            return false;
        });

        // Approve transactions - only ketua can approve
        Gate::define('approve-transactions', function (User $user, ?int $poktanId = null) {
            if ($user->isKetuaGapoktan()) {
                return true; // Can approve all
            }
            
            if ($user->isKetuaPoktan() && $poktanId) {
                return $user->poktan_id === $poktanId; // Can only approve their poktan's transactions
            }
            
            return false;
        });

        // Manage harvests & stocks
        Gate::define('manage-harvests', function (User $user, ?int $poktanId = null) {
            if ($user->isAnggotaPoktan()) {
                return false;
            }

            if ($user->hasGapoktanAccess()) {
                return true;
            }
            
            if ($user->hasPoktanAccess() && $poktanId) {
                return $user->poktan_id === $poktanId;
            }
            
            return false;
        });

        // Manage products & orders
        Gate::define('manage-products', function (User $user) {
            return $user->hasGapoktanAccess();
        });

        // Manage orders
        Gate::define('manage-orders', function (User $user) {
            return $user->hasGapoktanAccess();
        });

        // View reports - everyone can view, but scope differs
        Gate::define('view-reports', function (User $user) {
            return true;
        });

        // Manage users
        Gate::define('manage-users', function (User $user) {
            if ($user->hasGapoktanAccess()) {
                return true; // Can manage all users
            }
            
            if ($user->isKetuaPoktan()) {
                return true; // Can manage users in their poktan
            }
            
            return false;
        });
    }
}
