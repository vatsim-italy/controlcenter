<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_results', function (Blueprint $table) {
            $table->id('results_id'); // primary key
            $table->unsignedBigInteger('eval_id');
            $table->unsignedBigInteger('item_id');
            $table->string('vote')->nullable();
            $table->text('comment')->nullable();

            // optional: foreign keys
            $table->foreign('eval_id')->references('eval_id')->on('evaluations')->onDelete('cascade');
            $table->foreign('item_id')->references('item_id')->on('evaluation_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_results');
    }
};
