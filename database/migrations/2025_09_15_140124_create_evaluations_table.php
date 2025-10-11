<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id('eval_id'); // primary key
            $table->unsignedBigInteger('student_id');
            $table->string('level')->nullable();
            $table->date('date')->nullable();
            $table->time('start')->nullable();
            $table->time('end')->nullable();
            $table->string('position')->nullable();
            $table->unsignedBigInteger('examiner_id')->nullable();
            $table->unsignedBigInteger('training_id')->nullable();
            $table->string('sessionPerformed')->nullable();
            $table->string('complexity')->nullable();
            $table->string('workload')->nullable();
            $table->string('trafficLoad')->nullable();
            $table->string('trainingPhase')->nullable();
            $table->text('finalReview')->nullable();
            // optional: foreign keys
            $table->foreign('student_id')->references('id')->on('users');
            $table->foreign('examiner_id')->references('id')->on('users');
            $table->foreign('training_id')->references('id')->on('trainings');
            $table->timestamps(); // Adds created_at and updated_at columns

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
