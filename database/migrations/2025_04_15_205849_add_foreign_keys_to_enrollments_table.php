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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreign(['college_id'])->references(['id'])->on('colleges')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['program_id'])->references(['id'])->on('programs')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['schoolyear_id'])->references(['id'])->on('schoolyears')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['stud_id'])->references(['id'])->on('studs')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['yearlevel_id'])->references(['id'])->on('yearlevels')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign('enrollments_college_id_foreign');
            $table->dropForeign('enrollments_program_id_foreign');
            $table->dropForeign('enrollments_schoolyear_id_foreign');
            $table->dropForeign('enrollments_stud_id_foreign');
            $table->dropForeign('enrollments_yearlevel_id_foreign');
        });
    }
};
