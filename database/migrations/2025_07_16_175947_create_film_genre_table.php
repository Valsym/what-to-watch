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
        Schema::dropIfExists('film_genre');
        Schema::create('film_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Film::class)->constrained();
            $table->foreignIdFor(\App\Models\Genre::class)->constrained();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('film_genre');
    }
};
