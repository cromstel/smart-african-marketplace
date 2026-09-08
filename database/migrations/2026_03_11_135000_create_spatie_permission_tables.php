<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the spatie/laravel-permission tables that were never published.
 *
 * The package is required in composer.json and config/permission.php is
 * present, but its migration was never published, so `permissions`,
 * `model_has_permissions` and `model_has_roles` did not exist. The knock-on
 * effect was that 2026_03_11_140000_create_role_has_permissions_table failed
 * on a foreign key to a `permissions` table that was never created -- which
 * meant `php artisan migrate` failed on every fresh database.
 *
 * Only the missing tables are created here. `roles` already exists
 * (2026_02_23_080000_create_roles_tables) and `role_has_permissions` is
 * created by the migration immediately after this one, so both are left alone.
 *
 * Every step is guarded: on a database where these were created by hand the
 * migration is a no-op rather than an error.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $morphKey = $columnNames['model_morph_key'] ?? 'model_id';

        throw_if(
            empty($tableNames),
            Exception::class,
            'config/permission.php not loaded. Run [php artisan config:clear] and try again.'
        );

        if (! Schema::hasTable($tableNames['permissions'])) {
            Schema::create($tableNames['permissions'], static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();

                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable($tableNames['model_has_permissions'])) {
            Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotPermission, $morphKey) {
                $table->unsignedBigInteger($pivotPermission);
                $table->string('model_type');
                $table->unsignedBigInteger($morphKey);

                $table->index([$morphKey, 'model_type'], 'model_has_permissions_model_id_model_type_index');

                $table->foreign($pivotPermission)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->primary(
                    [$pivotPermission, $morphKey, 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            });
        }

        if (! Schema::hasTable($tableNames['model_has_roles'])) {
            Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $pivotRole, $morphKey) {
                $table->unsignedBigInteger($pivotRole);
                $table->string('model_type');
                $table->unsignedBigInteger($morphKey);

                $table->index([$morphKey, 'model_type'], 'model_has_roles_model_id_model_type_index');

                $table->foreign($pivotRole)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary(
                    [$pivotRole, $morphKey, 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            });
        }

        app('cache')->forget(config('permission.cache.key', 'spatie.permission.cache'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            return;
        }

        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};
