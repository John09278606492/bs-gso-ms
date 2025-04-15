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
        Schema::table('syear_semester', function (Blueprint $table) {
            $table->foreign(['semester_id'])->references(['id'])->on('semesters')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['syear_id'])->references(['id'])->on('syears')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syear_semester', function (Blueprint $table) {
            $table->dropForeign('syear_semester_semester_id_foreign');
            $table->dropForeign('syear_semester_syear_id_foreign');
        });
    }
};
