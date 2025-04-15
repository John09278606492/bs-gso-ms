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
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable()->index('user_notifications_template_id_foreign');
            $table->text('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable()->default('heroicon-o-check-circle');
            $table->string('type')->nullable()->default('success');
            $table->string('privacy')->nullable()->default('public');
            $table->json('data')->nullable();
            $table->unsignedBigInteger('created_by')->index('user_notifications_created_by_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
