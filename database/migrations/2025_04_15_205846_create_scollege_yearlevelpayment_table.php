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
        Schema::create('scollege_yearlevelpayment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('scollege_id')->nullable()->index('scollege_yearlevelpayment_scollege_id_foreign');
            $table->unsignedBigInteger('yearlevelpayment_id')->nullable()->index('scollege_yearlevelpayment_yearlevelpayment_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scollege_yearlevelpayment');
    }
};
