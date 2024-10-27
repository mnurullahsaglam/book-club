<?php

use App\Models\Book;
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
        Schema::table('meetings', function (Blueprint $table) {
            $table->after('id', function (Blueprint $table) {
                $table->morphs('meetable');
            });
        });

        DB::table('meetings')
            ->whereNotNull('book_id')
            ->update([
                'meetable_type' => Book::class,
                'meetable_id' => DB::raw('book_id'),
            ]);

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign(['book_id']);
            $table->dropColumn('book_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropMorphs('meetable');

            $table->foreignId('book_id')->nullable()->after('id')->constrained();
        });
    }
};
