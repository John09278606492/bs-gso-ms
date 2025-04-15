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
        Schema::table('collection_syear', function (Blueprint $table) {
            $table->foreign(['collection_id'])->references(['id'])->on('collections')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['syear_id'])->references(['id'])->on('syears')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['syear_semester_id'])->references(['id'])->on('syear_semester')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collection_syear', function (Blueprint $table) {
            $table->dropForeign('collection_syear_collection_id_foreign');
            $table->dropForeign('collection_syear_syear_id_foreign');
            $table->dropForeign('collection_syear_syear_semester_id_foreign');
        });
    }
};
