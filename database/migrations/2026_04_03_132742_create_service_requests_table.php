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
    Schema::create('service_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('service_id')->constrained()->onDelete('cascade');
        $table->foreignId('status_id')->constrained('request_statuses')->onDelete('cascade');
        $table->foreignId('priority_id')->constrained('request_priorities')->onDelete('cascade');
        $table->text('description')->nullable();
        $table->timestamp('submitted_at')->useCurrent();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
