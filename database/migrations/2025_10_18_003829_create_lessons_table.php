<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->string('video_url')->nullable();
            $table->string('hls_manifest_url')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->boolean('is_published')->default(false);
            $table->integer('order')->default(0);
            $table->boolean('is_free_preview')->default(false);
            $table->json('resources')->nullable();
            $table->timestamps();
            
            $table->index(['course_id', 'order']);
            $table->index(['course_id', 'is_published']);
            $table->index('is_free_preview');
            $table->unique(['course_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
