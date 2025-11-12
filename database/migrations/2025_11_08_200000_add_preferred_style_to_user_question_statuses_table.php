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
        Schema::table('user_question_statuses', function (Blueprint $table) {
            // adiciona o campo preferred_style para guardar preferências de estilo do usuário
            $table->string('preferred_style')->nullable()->after('image_generated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_question_statuses', function (Blueprint $table) {
            $table->dropColumn('preferred_style');
        });
    }
};
