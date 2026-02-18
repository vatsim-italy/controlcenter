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
        Schema::create('training_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('month'); // e.g., 2
            $table->unsignedInteger('year');  // e.g., 2024
            $table->decimal('hours', 8, 2);
            $table->boolean('requirement_met')->nullable()->default(null);

            $table->timestamps();

            // Ensure we only have one record per training per month
            $table->unique(['training_id', 'month', 'year']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_activity_logs');
    }
};
