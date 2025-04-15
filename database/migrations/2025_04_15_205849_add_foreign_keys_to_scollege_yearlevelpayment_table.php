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
        Schema::table('scollege_yearlevelpayment', function (Blueprint $table) {
            $table->foreign(['scollege_id'])->references(['id'])->on('scolleges')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['yearlevelpayment_id'])->references(['id'])->on('yearlevelpayments')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scollege_yearlevelpayment', function (Blueprint $table) {
            $table->dropForeign('scollege_yearlevelpayment_scollege_id_foreign');
            $table->dropForeign('scollege_yearlevelpayment_yearlevelpayment_id_foreign');
        });
    }
};
