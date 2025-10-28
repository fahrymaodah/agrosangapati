<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // ==========================================
        // SUPERADMIN: Full Access to Everything
        // ==========================================
        Gate::before(function (User $user, string $ability) {
            if ($user->role === 'superadmin') {
                return true; // Superadmin can do anything
            }
        });

        // ==========================================
        // FINANCIAL MANAGEMENT PERMISSIONS
        // ==========================================
        
        // View financial transactions
        Gate::define('view-transactions', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan',
                'ketua_poktan',
                'pengurus_poktan',
                'anggota_poktan'
            ]);
        });

        // Create/manage transactions (poktan level)
        Gate::define('manage-transactions', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_poktan',
                'pengurus_poktan',
                'anggota_poktan' // Anggota can submit, but needs approval
            ]);
        });

        // Approve transactions (ketua poktan only)
        Gate::define('approve-transactions', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_poktan'
            ]);
        });

        // View gapoktan-level financial reports
        Gate::define('view-gapoktan-reports', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // Manage transaction categories
        Gate::define('manage-categories', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_poktan',
                'pengurus_poktan'
            ]);
        });

        // View cash balance
        Gate::define('view-cash-balance', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan',
                'ketua_poktan',
                'pengurus_poktan'
            ]);
        });

        // ==========================================
        // PRODUCTION MANAGEMENT PERMISSIONS
        // ==========================================

        // View commodities & grades (master data)
        Gate::define('view-commodities', function (User $user) {
            return true; // All authenticated users can view
        });

        // Manage commodities & grades (admin only)
        Gate::define('manage-commodities', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // Record harvests
        Gate::define('manage-harvests', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_poktan',
                'pengurus_poktan',
                'anggota_poktan'
            ]);
        });

        // View production reports
        Gate::define('view-production-reports', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan',
                'ketua_poktan',
                'pengurus_poktan',
                'anggota_poktan'
            ]);
        });

        // View gapoktan production summary
        Gate::define('view-gapoktan-production', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // ==========================================
        // STOCK MANAGEMENT PERMISSIONS
        // ==========================================

        // View stocks
        Gate::define('view-stocks', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan',
                'ketua_poktan',
                'pengurus_poktan'
            ]);
        });

        // Manage stocks (poktan level)
        Gate::define('manage-stocks', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_poktan',
                'pengurus_poktan'
            ]);
        });

        // Transfer stock to gapoktan
        Gate::define('transfer-to-gapoktan', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_poktan',
                'pengurus_poktan'
            ]);
        });

        // View gapoktan stocks
        Gate::define('view-gapoktan-stocks', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // ==========================================
        // SALES & MARKETING PERMISSIONS
        // ==========================================

        // Manage products (gapoktan level)
        Gate::define('manage-products', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // View product catalog (public - no need for gate, but listed for reference)
        Gate::define('view-products', function (User $user) {
            return true; // All users can view
        });

        // Manage orders
        Gate::define('manage-orders', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // Confirm/process orders
        Gate::define('process-orders', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // Manage shipments
        Gate::define('manage-shipments', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // Manage sales distributions (poktan payments)
        Gate::define('manage-distributions', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // ==========================================
        // REPORTING PERMISSIONS
        // ==========================================

        // View consolidated reports (all poktans)
        Gate::define('view-consolidated-reports', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // View poktan-specific reports
        Gate::define('view-poktan-reports', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan',
                'ketua_poktan',
                'pengurus_poktan'
            ]);
        });

        // Export reports
        Gate::define('export-reports', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan',
                'ketua_poktan'
            ]);
        });

        // ==========================================
        // DASHBOARD ACCESS PERMISSIONS
        // ==========================================

        // View gapoktan dashboard
        Gate::define('view-gapoktan-dashboard', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // View poktan dashboard
        Gate::define('view-poktan-dashboard', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan',
                'ketua_poktan',
                'pengurus_poktan'
            ]);
        });

        // View member dashboard
        Gate::define('view-member-dashboard', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_poktan',
                'pengurus_poktan',
                'anggota_poktan'
            ]);
        });

        // ==========================================
        // USER MANAGEMENT PERMISSIONS
        // ==========================================

        // Manage users (create, edit, deactivate)
        Gate::define('manage-users', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'ketua_poktan' // Ketua poktan can manage their own poktan members
            ]);
        });

        // View all users
        Gate::define('view-users', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan',
                'ketua_poktan',
                'pengurus_poktan'
            ]);
        });

        // ==========================================
        // POKTAN & GAPOKTAN MANAGEMENT
        // ==========================================

        // Manage poktans (CRUD)
        Gate::define('manage-poktans', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan',
                'pengurus_gapoktan'
            ]);
        });

        // View poktans list
        Gate::define('view-poktans', function (User $user) {
            return true; // All authenticated users
        });

        // Manage gapoktan info
        Gate::define('manage-gapoktan', function (User $user) {
            return in_array($user->role, [
                'superadmin',
                'ketua_gapoktan'
            ]);
        });
    }
}
