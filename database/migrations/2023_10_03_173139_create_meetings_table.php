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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Book::class)->constrained()->cascadeOnDelete();

            $table->unsignedInteger('order');
            $table->string('title');
            $table->date('date');
            $table->string('location');
            $table->longText('topics')->nullable();
            $table->longText('decisions')->nullable();

            $table->json('guests')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
