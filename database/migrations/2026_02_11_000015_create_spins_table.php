<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('spins', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('provider')->nullable();
            $table->string('game_code')->nullable();

            $table->string('status')->default('queued'); // queued|processing|done|failed
            $table->string('request_id')->unique(); // token do spin

            $table->json('request')->nullable();  // payload do spin
            $table->json('result')->nullable();   // resultado final
            $table->text('error')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spins');
    }
};
