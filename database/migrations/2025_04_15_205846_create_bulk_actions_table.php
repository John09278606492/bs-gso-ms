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
        Schema::create('bulk_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('type');
            $table->string('identifier');
            $table->string('status')->default('queued');
            $table->string('job');
            $table->unsignedBigInteger('user_id')->nullable()->index('bulk_actions_user_id_foreign');
            $table->unsignedBigInteger('total_records')->nullable();
            $table->json('data')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'identifier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_actions');
    }
};
