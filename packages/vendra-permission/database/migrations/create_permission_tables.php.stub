<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Tenancy\TenantSchema;

return new class extends Migration
{
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = Arr::get($columnNames, 'role_pivot_key', 'role_id');
        $pivotPermission = Arr::get($columnNames, 'permission_pivot_key', 'permission_id');
        $teamForeignKey = Arr::get($columnNames, 'team_foreign_key', 'team_id');

        throw_if(blank($tableNames), 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        throw_if($teams && blank(Arr::get($columnNames, 'team_foreign_key', null)), 'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        /**
         * See `docs/prerequisites.md` for suggested lengths on 'name' and 'guard_name' if "1071 Specified key was too long" errors are encountered.
         */
        Schema::create(Arr::get($tableNames, 'permissions'), static function (Blueprint $table): void {
            $table->id(); // permission id
            TenantSchema::addTenantColumn($table);
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(TenantSchema::tenantIndex(['name', 'guard_name']));
        });

        /**
         * See `docs/prerequisites.md` for suggested lengths on 'name' and 'guard_name' if "1071 Specified key was too long" errors are encountered.
         */
        Schema::create(Arr::get($tableNames, 'roles'), static function (Blueprint $table) use ($teams, $teamForeignKey): void {
            $table->id(); // role id
            TenantSchema::addTenantColumn($table);
            if (($teams || config('permission.testing')) && (! TenantSchema::enabled() || TenantSchema::column() !== $teamForeignKey)) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($teamForeignKey)->nullable();
            }

            if ($teams || config('permission.testing')) {
                $table->index($teamForeignKey, 'roles_team_foreign_key_index');
            }

            $table->string('name');
            $table->string('description')->nullable();
            $table->string('guard_name');
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique(TenantSchema::tenantIndex([$teamForeignKey, 'name', 'guard_name']));
            } else {
                $table->unique(TenantSchema::tenantIndex(['name', 'guard_name']));
            }
        });

        Schema::create(Arr::get($tableNames, 'model_has_permissions'), static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams): void {
            $table->unsignedBigInteger($pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger(Arr::get($columnNames, 'model_morph_key'));
            $table->index([Arr::get($columnNames, 'model_morph_key'), 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on(Arr::get($tableNames, 'permissions'))
                ->cascadeOnDelete();
            if ($teams) {
                $table->unsignedBigInteger(Arr::get($columnNames, 'team_foreign_key'));
                $table->index(Arr::get($columnNames, 'team_foreign_key'), 'model_has_permissions_team_foreign_key_index');

                $table->primary(
                    [Arr::get($columnNames, 'team_foreign_key'), $pivotPermission, Arr::get($columnNames, 'model_morph_key'), 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotPermission, Arr::get($columnNames, 'model_morph_key'), 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            }
        });

        Schema::create(Arr::get($tableNames, 'model_has_roles'), static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams): void {
            $table->unsignedBigInteger($pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger(Arr::get($columnNames, 'model_morph_key'));
            $table->index([Arr::get($columnNames, 'model_morph_key'), 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on(Arr::get($tableNames, 'roles'))
                ->cascadeOnDelete();
            if ($teams) {
                $table->unsignedBigInteger(Arr::get($columnNames, 'team_foreign_key'));
                $table->index(Arr::get($columnNames, 'team_foreign_key'), 'model_has_roles_team_foreign_key_index');

                $table->primary(
                    [Arr::get($columnNames, 'team_foreign_key'), $pivotRole, Arr::get($columnNames, 'model_morph_key'), 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotRole, Arr::get($columnNames, 'model_morph_key'), 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            }
        });

        Schema::create(Arr::get($tableNames, 'role_has_permissions'), static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission): void {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on(Arr::get($tableNames, 'permissions'))
                ->cascadeOnDelete();

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on(Arr::get($tableNames, 'roles'))
                ->cascadeOnDelete();

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        resolve(Factory::class)
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        throw_if(blank($tableNames), 'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');

        Schema::dropIfExists(Arr::get($tableNames, 'role_has_permissions'));
        Schema::dropIfExists(Arr::get($tableNames, 'model_has_roles'));
        Schema::dropIfExists(Arr::get($tableNames, 'model_has_permissions'));
        Schema::dropIfExists(Arr::get($tableNames, 'roles'));
        Schema::dropIfExists(Arr::get($tableNames, 'permissions'));
    }
};
