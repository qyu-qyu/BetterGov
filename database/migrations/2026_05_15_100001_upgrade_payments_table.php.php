<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('status');
            $table->string('currency', 10)->default('USD')->after('transaction_id');
            $table->string('receipt_path')->nullable()->after('currency');
            $table->json('gateway_metadata')->nullable()->after('receipt_path');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'currency', 'receipt_path', 'gateway_metadata']);
        });
    }
};