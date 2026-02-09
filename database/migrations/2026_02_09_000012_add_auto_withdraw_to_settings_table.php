<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'auto_withdraw_enabled')) {
                $table->boolean('auto_withdraw_enabled')->default(false)->after('max_withdrawal');
            }
            if (!Schema::hasColumn('settings', 'auto_withdraw_affiliate_enabled')) {
                $table->boolean('auto_withdraw_affiliate_enabled')->default(false)->after('auto_withdraw_enabled');
            }
            if (!Schema::hasColumn('settings', 'auto_withdraw_gateway')) {
                // 'sharkpay' | 'digitopay' | 'auto'
                $table->string('auto_withdraw_gateway', 20)->default('auto')->after('auto_withdraw_affiliate_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'auto_withdraw_gateway')) {
                $table->dropColumn('auto_withdraw_gateway');
            }
            if (Schema::hasColumn('settings', 'auto_withdraw_affiliate_enabled')) {
                $table->dropColumn('auto_withdraw_affiliate_enabled');
            }
            if (Schema::hasColumn('settings', 'auto_withdraw_enabled')) {
                $table->dropColumn('auto_withdraw_enabled');
            }
        });
    }
};
