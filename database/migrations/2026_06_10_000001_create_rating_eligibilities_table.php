<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating_eligibilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('rating_id');
            $table->boolean('eligible')->default(false);
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'rating_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rating_id')->references('id')->on('ratings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_eligibilities');
    }
};