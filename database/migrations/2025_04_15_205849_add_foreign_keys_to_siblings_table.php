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
        Schema::table('siblings', function (Blueprint $table) {
            $table->foreign(['sibling_id'])->references(['id'])->on('studs')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['stud_id'])->references(['id'])->on('studs')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siblings', function (Blueprint $table) {
            $table->dropForeign('siblings_sibling_id_foreign');
            $table->dropForeign('siblings_stud_id_foreign');
        });
    }
};
