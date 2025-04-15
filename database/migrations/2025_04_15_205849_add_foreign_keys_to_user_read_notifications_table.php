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
        Schema::table('user_read_notifications', function (Blueprint $table) {
            $table->foreign(['notification_id'])->references(['id'])->on('user_notifications')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_read_notifications', function (Blueprint $table) {
            $table->dropForeign('user_read_notifications_notification_id_foreign');
        });
    }
};
