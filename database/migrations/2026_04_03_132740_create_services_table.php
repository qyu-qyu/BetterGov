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
    Schema::create('services', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->foreignId('service_category_id')->constrained()->onDelete('cascade');
        $table->foreignId('service_type_id')->constrained()->onDelete('cascade');
        $table->foreignId('office_id')->constrained()->onDelete('cascade');
        $table->decimal('fee', 8, 2)->default(0);
        $table->string('estimated_time')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
