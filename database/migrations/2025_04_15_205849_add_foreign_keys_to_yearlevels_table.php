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
        Schema::table('yearlevels', function (Blueprint $table) {
            $table->foreign(['program_id'])->references(['id'])->on('programs')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yearlevels', function (Blueprint $table) {
            $table->dropForeign('yearlevels_program_id_foreign');
        });
    }
};
