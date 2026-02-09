<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Auto saque (jogadores)
            if (!Schema::hasColumn('settings', 'auto_withdraw_enabled')) {
                $table->boolean('auto_withdraw_enabled')->default(false)->after('max_withdrawal');
            }

            // Auto saque (afiliados) — já deixo pronto pra próxima etapa
            if (!Schema::hasColumn('settings', 'auto_withdraw_affiliates_enabled')) {
                $table->boolean('auto_withdraw_affiliates_enabled')->default(false)->after('auto_withdraw_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'auto_withdraw_enabled')) {
                $table->dropColumn('auto_withdraw_enabled');
            }
            if (Schema::hasColumn('settings', 'auto_withdraw_affiliates_enabled')) {
                $table->dropColumn('auto_withdraw_affiliates_enabled');
            }
        });
    }
};