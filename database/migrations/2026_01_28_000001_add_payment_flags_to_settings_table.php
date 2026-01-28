<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            // evita erro se já existir (ambientes diferentes)
            if (!Schema::hasColumn('settings', 'digitopay_is_enable')) {
                $table->boolean('digitopay_is_enable')->default(false)->after('initial_bonus');
            }

            if (!Schema::hasColumn('settings', 'sharkpay_is_enable')) {
                $table->boolean('sharkpay_is_enable')->default(false)->after('digitopay_is_enable');
            }

        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            if (Schema::hasColumn('settings', 'digitopay_is_enable')) {
                $table->dropColumn('digitopay_is_enable');
            }

            if (Schema::hasColumn('settings', 'sharkpay_is_enable')) {
                $table->dropColumn('sharkpay_is_enable');
            }

        });
    }
};
0

