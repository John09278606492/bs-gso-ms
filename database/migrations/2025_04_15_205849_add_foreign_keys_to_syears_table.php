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
        Schema::table('syears', function (Blueprint $table) {
            $table->foreign(['schoolyear_id'])->references(['id'])->on('schoolyears')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['student_id'])->references(['id'])->on('students')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syears', function (Blueprint $table) {
            $table->dropForeign('syears_schoolyear_id_foreign');
            $table->dropForeign('syears_student_id_foreign');
        });
    }
};
