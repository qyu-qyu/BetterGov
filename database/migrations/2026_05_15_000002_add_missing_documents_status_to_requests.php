<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // SQLite stores enum as TEXT and has no MODIFY COLUMN — validation is enforced at the app layer.
        // For MySQL/MariaDB, extend the enum to include 'missing_documents'.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE requests MODIFY COLUMN status ENUM('pending','processing','approved','rejected','completed','missing_documents') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE requests MODIFY COLUMN status ENUM('pending','processing','approved','rejected','completed') DEFAULT 'pending'");
        }
    }
};
