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

        // Drop blog post tables if they exist
        $this->dropBlogPostTables();

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
        // Disable foreign key constraints during migration execution
        Schema::disableForeignKeyConstraints();

        $this->createBlogPostTables();

        // Re-enable foreign key constraints after migration execution
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
            // Define columns
            $table->id();
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
            // Define columns
            $table->id();
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
