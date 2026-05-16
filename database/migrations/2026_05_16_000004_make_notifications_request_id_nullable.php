<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Make request_id nullable so appointment notifications (which have no request)
            // can still be stored
            $table->foreignId('request_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('request_id')->nullable(false)->change();
        });
    }
};