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
        Schema::create('city_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->boolean('precipitation_enabled')->default(true);
            $table->boolean('uv_enabled')->default(true);
            $table->float('precipitation_threshold')->default(5.0);
            $table->float('uv_threshold')->default(6.0);
            $table->timestamps();

            $table->unique(['user_id', 'city_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_user');
    }
};
