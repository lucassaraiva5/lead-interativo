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
        Schema::create('user_question_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique('user_number');
            $table->string('name')->nullable();
            $table->text('image_sent')->nullable(true);
            $table->text('image_generated')->nullable(true);
            $table->integer('questionary_id');
            $table->integer('current_question')->default(0);
            $table->string('vocation')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_question_statuses');
    }
};
