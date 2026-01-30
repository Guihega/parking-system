<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique(); // tariffs.create, tariffs.update, cash.close, etc.
            $table->string('name', 120);
            $table->string('module', 50)->nullable(); // tariffs, cash_sessions, tickets, reports
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('permissions');
    }
};
