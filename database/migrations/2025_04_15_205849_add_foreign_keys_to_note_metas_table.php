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
        Schema::table('note_metas', function (Blueprint $table) {
            $table->foreign(['note_id'])->references(['id'])->on('notes')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('note_metas', function (Blueprint $table) {
            $table->dropForeign('note_metas_note_id_foreign');
        });
    }
};
