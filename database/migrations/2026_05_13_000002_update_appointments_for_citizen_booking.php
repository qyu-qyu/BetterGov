<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('office_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('office_time_slot_id')->nullable()->after('office_id')->constrained('office_time_slots')->onDelete('set null');
            $table->date('appointment_date_only')->nullable()->after('office_time_slot_id');
            $table->string('status')->default('pending')->after('appointment_date_only');
            $table->text('notes')->nullable()->after('status');
            $table->dropForeign(['service_request_id']);
            $table->dropColumn('service_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['office_time_slot_id']);
            $table->dropForeign(['office_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'office_id', 'office_time_slot_id', 'appointment_date_only', 'status', 'notes']);
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
        });
    }
};
