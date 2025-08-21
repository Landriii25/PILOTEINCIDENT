<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kb_articles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kb_category_id')->nullable()
                ->constrained('kb_categories')->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();

            $table->text('summary')->nullable();
            $table->longText('content')->nullable();

            $table->json('tags')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->boolean('is_published')->default(true);

            // (optionnel) auteur de l’article
            $table->foreignId('author_id')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_articles');
    }
};
