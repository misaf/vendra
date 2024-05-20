<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->dropBlogPostTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->createBlogPostTables();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Create blog post categories table.
     *
     * @return void
     */
    private function createBlogPostCategoriesTable(): void
    {
        Schema::create('blog_post_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->longText('name');
            $table->longText('description')
                ->nullable();
            $table->longText('slug');
            $table->unsignedBigInteger('position')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create blog posts table.
     *
     * @return void
     */
    private function createBlogPostsTable(): void
    {
        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('blog_post_category_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->longText('name');
            $table->longText('description')
                ->nullable();
            $table->longText('slug');
            $table->unsignedBigInteger('position')
                ->index();
            $table->boolean('status')
                ->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Create blog post tables.
     *
     * @return void
     */
    private function createBlogPostTables(): void
    {
        $this->createBlogPostCategoriesTable();
        $this->createBlogPostsTable();
    }

    /**
     * Drop blog post tables.
     *
     * @return void
     */
    private function dropBlogPostTables(): void
    {
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_post_categories');
    }
};
