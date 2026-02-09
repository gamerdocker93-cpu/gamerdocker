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
        Schema::table('settings', function (Blueprint $table) {

            // Saque automático para jogadores
            if (!Schema::hasColumn('settings', 'auto_withdraw_players')) {
                $table->boolean('auto_withdraw_players')
                    ->default(false)
                    ->after('max_withdrawal');
            }

            // Saque automático para afiliados
            if (!Schema::hasColumn('settings', 'auto_withdraw_affiliates')) {
                $table->boolean('auto_withdraw_affiliates')
                    ->default(false)
                    ->after('auto_withdraw_players');
            }

            // Gateway preferencial para saque automático
            if (!Schema::hasColumn('settings', 'auto_withdraw_gateway')) {
                $table->string('auto_withdraw_gateway', 50)
                    ->default('auto') // auto | sharkpay | digitopay
                    ->after('auto_withdraw_affiliates');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {

            if (Schema::hasColumn('settings', 'auto_withdraw_players')) {
                $table->dropColumn('auto_withdraw_players');
            }

            if (Schema::hasColumn('settings', 'auto_withdraw_affiliates')) {
                $table->dropColumn('auto_withdraw_affiliates');
            }

            if (Schema::hasColumn('settings', 'auto_withdraw_gateway')) {
                $table->dropColumn('auto_withdraw_gateway');
            }

        });
    }
};
