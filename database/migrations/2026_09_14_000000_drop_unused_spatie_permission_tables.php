<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * spatie/laravel-permission is a composer dependency and its config is
 * published, but it was never actually wired in: App\Models\User does not
 * use the HasRoles trait, nothing in the codebase calls assignRole(),
 * hasPermissionTo(), Gate::, or ->can() against a Spatie permission, and
 * `Spatie\Permission` appears nowhere outside composer.lock. The app's real,
 * exercised role system is App\Models\User::hasRole()/hasAnyRole(), backed
 * by the `role` column and the (unrelated, hand-rolled) `roles`/`role_user`
 * tables from 2026_02_23_080000_create_roles_tables.
 *
 * `permissions`, `model_has_permissions`, `model_has_roles` and
 * `role_has_permissions` are Spatie-specific tables that exist only because
 * 2026_03_11_135000/140000 created them -- nothing ever reads or writes
 * them. Dropping them removes dead schema and the exact class of bug those
 * two migrations' own comments describe fixing (a partially-published
 * package migration breaking `php artisan migrate` on a fresh database).
 *
 * `roles`/`role_user` are NOT touched here -- they are the live, used
 * system. The `guard_name` column added to `roles` by
 * 2026_03_11_141000_add_guard_name_to_roles_table is left in place: it's
 * unused but harmless, and removing it isn't necessary to resolve the
 * dead-table risk this migration targets.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
    }

    public function down(): void
    {
        // Intentionally not restored: these tables were dead weight with no
        // application code reading or writing them. If spatie/laravel-permission
        // is ever actually adopted, republish its migration instead of
        // reversing this one.
    }
};
