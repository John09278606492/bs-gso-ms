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
        Schema::table('yearlevelpayments', function (Blueprint $table) {
            $table->foreign(['yearlevel_id1'], 'yearlevelpayments_yearlevel_id_foreign')->references(['id'])->on('yearlevels')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yearlevelpayments', function (Blueprint $table) {
            $table->dropForeign('yearlevelpayments_yearlevel_id_foreign');
        });
    }
};
