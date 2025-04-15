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
        Schema::table('collection_enrollment', function (Blueprint $table) {
            $table->foreign(['collection_id'])->references(['id'])->on('collections')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['enrollment_id'])->references(['id'])->on('enrollments')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collection_enrollment', function (Blueprint $table) {
            $table->dropForeign('collection_enrollment_collection_id_foreign');
            $table->dropForeign('collection_enrollment_enrollment_id_foreign');
        });
    }
};
