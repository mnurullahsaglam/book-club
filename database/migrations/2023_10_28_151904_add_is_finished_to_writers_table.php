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
        Schema::table('writers', function (Blueprint $table) {
            $table->boolean('is_finished')->default(false)->after('death_place');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('writers', function (Blueprint $table) {
            $table->dropColumn('is_finished');
        });
    }
};
