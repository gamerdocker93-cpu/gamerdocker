<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            if (!Schema::hasColumn('gateways', 'stripe_public_key')) {
                $table->string('stripe_public_key', 191)->nullable()->after('shark_private_key');
            }
            if (!Schema::hasColumn('gateways', 'stripe_secret_key')) {
                $table->string('stripe_secret_key', 191)->nullable()->after('stripe_public_key');
            }
            if (!Schema::hasColumn('gateways', 'stripe_webhook_secret')) {
                $table->string('stripe_webhook_secret', 191)->nullable()->after('stripe_secret_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gateways', function (Blueprint $table) {
            if (Schema::hasColumn('gateways', 'stripe_webhook_secret')) {
                $table->dropColumn('stripe_webhook_secret');
            }
            if (Schema::hasColumn('gateways', 'stripe_secret_key')) {
                $table->dropColumn('stripe_secret_key');
            }
            if (Schema::hasColumn('gateways', 'stripe_public_key')) {
                $table->dropColumn('stripe_public_key');
            }
        });
    }
};
