<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Define a new migration using an anonymous class
return new class () extends Migration {
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Disable foreign key constraints during migration rollback
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');

        // Re-enable foreign key constraints after migration rollback
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Check if the necessary configuration exists
        $tableNames = config('permission.table_names');
        $teams = config('permission.teams');
        $columnNames = config('permission.column_names');

        if (empty($tableNames)) {
            throw new Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['permissions'], function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames): void {
            $table->id();

            // Add team foreign key if necessary
            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }

            $table->string('name');
            $table->string('guard_name');
            $table->timestampsTz();
            $table->softDeletesTz();

            // Add unique constraint if team is enabled
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teams): void {
            $table->unsignedBigInteger($columnNames['permission_morph_key']);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            // Define foreign key relationship
            $table->foreign($columnNames['permission_morph_key'])
                ->references('id')
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();

            // Add team foreign key if necessary
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary(
                    [$columnNames['team_foreign_key'], $columnNames['permission_morph_key'], $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            } else {
                $table->primary(
                    [$columnNames['permission_morph_key'], $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            }
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teams): void {
            $table->unsignedBigInteger($columnNames['role_morph_key']);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            // Define foreign key relationship
            $table->foreign($columnNames['role_morph_key'])
                ->references('id')
                ->on($tableNames['roles'])
                ->cascadeOnDelete();

            // Add team foreign key if necessary
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary(
                    [$columnNames['team_foreign_key'], $columnNames['role_morph_key'], $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            } else {
                $table->primary(
                    [$columnNames['role_morph_key'], $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            }
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames): void {
            $table->unsignedBigInteger($columnNames['permission_morph_key']);
            $table->unsignedBigInteger($columnNames['role_morph_key']);

            // Define foreign key relationships
            $table->foreign($columnNames['permission_morph_key'])
                ->references('id')
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();

            $table->foreign($columnNames['role_morph_key'])
                ->references('id')
                ->on($tableNames['roles'])
                ->cascadeOnDelete();

            $table->primary([$columnNames['permission_morph_key'], $columnNames['role_morph_key']], 'role_has_permissions_permission_id_role_id_primary');
        });

        // Clear cache after migrations
        app('cache')
            ->store('default' !== config('permission.cache.store') ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
