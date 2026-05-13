<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->foreignId('request_id')->nullable()->after('service_request_id')->constrained('requests')->onDelete('cascade');
        });

        Schema::table('request_documents', function (Blueprint $table) {
            $table->foreignId('request_id')->nullable()->after('service_request_id')->constrained('requests')->onDelete('cascade');
            $table->string('file_name')->nullable()->after('file_path');
            $table->dropForeign(['service_request_id']);
            $table->dropColumn('service_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropForeign(['request_id']);
            $table->dropColumn('request_id');
        });

        Schema::table('request_documents', function (Blueprint $table) {
            $table->dropForeign(['request_id']);
            $table->dropColumn(['request_id', 'file_name']);
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
        });
    }
};
