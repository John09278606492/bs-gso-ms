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
        Schema::create('bulk_action_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bulk_action_id')->index('bulk_action_records_bulk_action_id_foreign');
            $table->unsignedBigInteger('record_id');
            $table->string('record_type');
            $table->string('status')->default('queued');
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['record_id', 'record_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_action_records');
    }
};
