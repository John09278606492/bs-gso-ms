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
        Schema::table('enrollment_yearlevelpayments', function (Blueprint $table) {
            $table->foreign(['enrollment_id'])->references(['id'])->on('enrollments')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['yearlevelpayments_id'], 'enrollment_yearlevelpayments_yearlevelpayment_id_foreign')->references(['id'])->on('yearlevelpayments')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_yearlevelpayments', function (Blueprint $table) {
            $table->dropForeign('enrollment_yearlevelpayments_enrollment_id_foreign');
            $table->dropForeign('enrollment_yearlevelpayments_yearlevelpayment_id_foreign');
        });
    }
};
