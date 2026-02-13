<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scheduler_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // ex: process-auto-withdrawal
            $table->timestamp('last_ran_at')->nullable();
            $table->unsignedInteger('runs')->default(0);
            $table->unsignedInteger('last_runtime_ms')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduler_heartbeats');
    }
};
