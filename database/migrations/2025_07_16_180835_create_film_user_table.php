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
        Schema::dropIfExists('film_user');
        Schema::create('film_user', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Film::class)->constrained();
            $table->foreignIdFor(\App\Models\User::class)->constrained();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('film_user');
    }
};
