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
        // Ajouter les champs login et password à la table ipbx
        Schema::table('ipbx', function (Blueprint $table) {
            $table->string('login')->nullable()->after('port');
            $table->string('password')->nullable()->after('login');
        });

        // Créer la table pivot client_ipbx
        Schema::create('client_ipbx', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('ipbx_id')->constrained('ipbx')->onDelete('cascade');
            $table->timestamps();

            // Empêcher les doublons : un client ne peut pas avoir 2x le même ipbx
            $table->unique(['client_id', 'ipbx_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_ipbx');

        Schema::table('ipbx', function (Blueprint $table) {
            $table->dropColumn(['login', 'password']);
        });
    }
};
