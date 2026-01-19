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
        Schema::create('client_centrex', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('centrex_id')->constrained('centrex')->onDelete('cascade');
            $table->timestamps();

            // Empêcher les doublons : un client ne peut pas avoir 2x le même centrex
            $table->unique(['client_id', 'centrex_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_centrex');
    }
};
