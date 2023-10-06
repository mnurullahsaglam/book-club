<?php

use App\Models\Publisher;
use App\Models\Writer;
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
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Writer::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Publisher::class)->constrained()->cascadeOnDelete();

            $table->string('image')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('page_count')->nullable();
            $table->boolean('is_finished')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
