<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->createBlogPostCategoriesTable();
        $this->createBlogPostsTable();
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_post_categories');
        Schema::enableForeignKeyConstraints();
    }

    private function createBlogPostCategoriesTable(): void
    {
        Schema::create('blog_post_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->json('slug');
            $table->unsignedBigInteger('position');
            $table->boolean('status')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tenant_id', 'position']);
            $table->index(['tenant_id', 'status']);
        });
    }

    private function createBlogPostsTable(): void
    {
        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('blog_post_category_id');
            $table->json('name');
            $table->json('description')
                ->nullable();
            $table->json('slug');
            $table->unsignedBigInteger('position');
            $table->boolean('status')
                ->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tenant_id', 'blog_post_category_id']);
            $table->index(['tenant_id', 'position']);
            $table->index(['tenant_id', 'status']);
        });
    }
};
