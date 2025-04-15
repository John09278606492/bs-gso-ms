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
        Schema::create('yearlevelpayments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('yearlevel_id1')->index('yearlevelpayments_yearlevel_id_foreign');
            $table->double('amount', 10, 2);
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yearlevelpayments');
    }
};
