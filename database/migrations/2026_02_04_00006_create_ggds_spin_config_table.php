<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ggds_spin_config', function (Blueprint $table) {
            $table->id();

            // Campos "seguros" para não quebrar a tela
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ggds_spin_config');
    }
};

