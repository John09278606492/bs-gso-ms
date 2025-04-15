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
        Schema::table('scolleges', function (Blueprint $table) {
            $table->foreign(['college_id'])->references(['id'])->on('colleges')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['program_id'])->references(['id'])->on('programs')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['student_id'])->references(['id'])->on('students')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['yearlevel_id'])->references(['id'])->on('yearlevels')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scolleges', function (Blueprint $table) {
            $table->dropForeign('scolleges_college_id_foreign');
            $table->dropForeign('scolleges_program_id_foreign');
            $table->dropForeign('scolleges_student_id_foreign');
            $table->dropForeign('scolleges_yearlevel_id_foreign');
        });
    }
};
