<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')
                ->after('id');

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropColumn('tenant_id');
        });
    }
};
