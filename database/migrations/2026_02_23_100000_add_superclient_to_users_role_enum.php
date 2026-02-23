<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'client', 'superclient') NOT NULL DEFAULT 'client'");
    }

    public function down(): void
    {
        // Reconvertir les superclient en client avant de réduire l'enum
        DB::statement("UPDATE users SET role = 'client' WHERE role = 'superclient'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'client') NOT NULL DEFAULT 'client'");
    }
};
