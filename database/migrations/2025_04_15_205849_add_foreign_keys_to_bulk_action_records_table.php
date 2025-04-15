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
        Schema::table('bulk_action_records', function (Blueprint $table) {
            $table->foreign(['bulk_action_id'])->references(['id'])->on('bulk_actions')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bulk_action_records', function (Blueprint $table) {
            $table->dropForeign('bulk_action_records_bulk_action_id_foreign');
        });
    }
};
