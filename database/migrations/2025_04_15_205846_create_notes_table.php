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
        Schema::create('notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('group')->nullable()->index();
            $table->string('status')->nullable()->default('pending')->index();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type')->nullable();
            $table->string('title')->nullable()->index();
            $table->text('body')->nullable();
            $table->string('background')->nullable()->default('#F4F39E');
            $table->string('border')->nullable()->default('#DEE184');
            $table->string('color')->nullable()->default('#47576B');
            $table->json('checklist')->nullable();
            $table->string('icon')->nullable();
            $table->string('font_size')->nullable()->default('1em');
            $table->string('font')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->boolean('is_public')->nullable()->default(false);
            $table->boolean('is_pined')->nullable()->default(false);
            $table->integer('order')->nullable()->default(0);
            $table->string('place_in')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
