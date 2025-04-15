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
        Schema::create('syear_semester', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('syear_id')->nullable()->index('syear_semester_syear_id_foreign');
            $table->unsignedBigInteger('semester_id')->nullable()->index('syear_semester_semester_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syear_semester');
    }
};
