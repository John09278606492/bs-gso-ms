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
        Schema::table('enrollment_semester', function (Blueprint $table) {
            $table->foreign(['enrollment_id'])->references(['id'])->on('enrollments')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['semester_id'])->references(['id'])->on('semesters')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_semester', function (Blueprint $table) {
            $table->dropForeign('enrollment_semester_enrollment_id_foreign');
            $table->dropForeign('enrollment_semester_semester_id_foreign');
        });
    }
};
