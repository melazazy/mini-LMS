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
        Schema::create('moderation_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type'); // 'course' or 'lesson'
            $table->unsignedBigInteger('subject_id');
            $table->enum('state', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->foreignId('reviewer_id')->nullable()->constrained('users');
            $table->foreignId('submitted_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['subject_type', 'subject_id']);
            $table->index(['state', 'created_at']);
            $table->index('reviewer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderation_reviews');
    }
};
