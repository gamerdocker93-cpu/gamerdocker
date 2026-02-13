<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_providers', function (Blueprint $table) {
            $table->id();

            // código interno: fivers, worldslot, ever, venix, playgaming, games2api...
            $table->string('code', 50)->unique();

            // nome amigável
            $table->string('name', 120);

            // liga/desliga no admin
            $table->boolean('enabled')->default(false);

            // base url opcional (alguns providers usam)
            $table->string('base_url')->nullable();

            // credenciais (criptografado via cast no Model)
            $table->longText('credentials_json')->nullable();

            // metadados/flags
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_providers');
    }
};
