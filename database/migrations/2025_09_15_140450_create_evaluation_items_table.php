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
        Schema::create('evaluation_items', function (Blueprint $table) {
            $table->id('item_id'); // Primary key named 'item_id'
            $table->text('rating'); // Integer column for rating
            $table->string('category'); // String column for category
            $table->string('key_name'); // String column for key_name
            $table->text('description')->nullable(); // Text column for description, nullable
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_items');
    }
};
