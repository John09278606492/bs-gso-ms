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
        Schema::create('scolleges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->index('scolleges_student_id_foreign');
            $table->unsignedBigInteger('college_id')->index('scolleges_college_id_foreign');
            $table->unsignedBigInteger('program_id')->index('scolleges_program_id_foreign');
            $table->unsignedBigInteger('yearlevel_id')->index('scolleges_yearlevel_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scolleges');
    }
};
