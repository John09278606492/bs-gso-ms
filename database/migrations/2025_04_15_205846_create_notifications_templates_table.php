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
        Schema::create('notifications_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('key')->unique();
            $table->json('title');
            $table->json('body')->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable()->default('heroicon-o-check-circle');
            $table->string('type')->nullable()->default('success');
            $table->json('providers')->nullable();
            $table->string('action')->nullable()->default('manual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_templates');
    }
};
