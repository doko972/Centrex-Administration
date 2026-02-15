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
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('has_4g5g_backup')->default(false)->after('is_active');
            $table->string('backup_operator')->nullable()->after('has_4g5g_backup');
            $table->string('backup_sim_number')->nullable()->after('backup_operator');
            $table->string('backup_phone_number')->nullable()->after('backup_sim_number');
            $table->text('backup_notes')->nullable()->after('backup_phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'has_4g5g_backup',
                'backup_operator',
                'backup_sim_number',
                'backup_phone_number',
                'backup_notes',
            ]);
        });
    }
};
