<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('parking_spaces', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();

            $table->string('code', 20);           // A-01
            $table->string('floor', 20)->nullable();   // PB, N1
            $table->string('section', 20)->nullable(); // A, B

            $table->foreignId('vehicle_type_id')->constrained('vehicle_types');
            $table->foreignId('status_id')->constrained('parking_space_statuses');

            $table->timestamps();

            $table->unique(['branch_id', 'code']);
            $table->index(['branch_id', 'status_id']);
            $table->index(['branch_id', 'vehicle_type_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_spaces');
    }
};
