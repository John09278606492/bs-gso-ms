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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('stud_id')->index('enrollments_stud_id_foreign');
            $table->unsignedBigInteger('college_id')->index('enrollments_college_id_foreign');
            $table->unsignedBigInteger('program_id')->index('enrollments_program_id_foreign');
            $table->unsignedBigInteger('yearlevel_id')->index('enrollments_yearlevel_id_foreign');
            $table->unsignedBigInteger('schoolyear_id')->index('enrollments_schoolyear_id_foreign');
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
