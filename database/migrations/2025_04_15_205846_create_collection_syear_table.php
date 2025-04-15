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
        Schema::create('collection_syear', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('syear_id')->nullable()->index('collection_syear_syear_id_foreign');
            $table->unsignedBigInteger('collection_id')->nullable()->index('collection_syear_collection_id_foreign');
            $table->unsignedBigInteger('syear_semester_id')->nullable()->index('collection_syear_syear_semester_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_syear');
    }
};
