<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Evita crash se a tabela ainda não existir (ambiente desalinhado)
        if (!Schema::hasTable('users')) {
            return;
        }

        // Calcula o que precisa adicionar (idempotente)
        $addOauthId               = !Schema::hasColumn('users', 'oauth_id');
        $addOauthType             = !Schema::hasColumn('users', 'oauth_type');
        $addAvatar                = !Schema::hasColumn('users', 'avatar');
        $addLastName              = !Schema::hasColumn('users', 'last_name');
        $addCpf                   = !Schema::hasColumn('users', 'cpf');
        $addPhone                 = !Schema::hasColumn('users', 'phone');
        $addLoggedIn              = !Schema::hasColumn('users', 'logged_in');
        $addBanned                = !Schema::hasColumn('users', 'banned');
        $addInviter               = !Schema::hasColumn('users', 'inviter');
        $addInviterCode           = !Schema::hasColumn('users', 'inviter_code');
        $addAffiliateRevenueShare = !Schema::hasColumn('users', 'affiliate_revenue_share');
        $addAffiliateCpa          = !Schema::hasColumn('users', 'affiliate_cpa');
        $addAffiliateBaseline     = !Schema::hasColumn('users', 'affiliate_baseline');
        $addIsDemoAgent           = !Schema::hasColumn('users', 'is_demo_agent');
        $addStatus                = !Schema::hasColumn('users', 'status');

        if (
            !$addOauthId && !$addOauthType && !$addAvatar && !$addLastName && !$addCpf && !$addPhone &&
            !$addLoggedIn && !$addBanned && !$addInviter && !$addInviterCode &&
            !$addAffiliateRevenueShare && !$addAffiliateCpa && !$addAffiliateBaseline &&
            !$addIsDemoAgent && !$addStatus
        ) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use (
            $addOauthId,
            $addOauthType,
            $addAvatar,
            $addLastName,
            $addCpf,
            $addPhone,
            $addLoggedIn,
            $addBanned,
            $addInviter,
            $addInviterCode,
            $addAffiliateRevenueShare,
            $addAffiliateCpa,
            $addAffiliateBaseline,
            $addIsDemoAgent,
            $addStatus
        ) {
            if ($addOauthId) {
                $table->string('oauth_id')->nullable();
            }
            if ($addOauthType) {
                $table->string('oauth_type')->nullable();
            }
            if ($addAvatar) {
                $table->string('avatar')->nullable();
            }
            if ($addLastName) {
                $table->string('last_name')->nullable();
            }
            if ($addCpf) {
                $table->string('cpf', 20)->nullable();
            }
            if ($addPhone) {
                $table->string('phone', 30)->nullable();
            }
            if ($addLoggedIn) {
                $table->tinyInteger('logged_in')->default(0);
            }
            if ($addBanned) {
                $table->tinyInteger('banned')->default(0);
            }

            // IMPORTANTÍSSIMO: precisa bater com users.id (unsignedBigInteger)
            if ($addInviter) {
                $table->unsignedBigInteger('inviter')->nullable()->index();
            }

            if ($addInviterCode) {
                $table->string('inviter_code', 25)->nullable();
            }
            if ($addAffiliateRevenueShare) {
                $table->bigInteger('affiliate_revenue_share')->default(2);
            }
            if ($addAffiliateCpa) {
                $table->decimal('affiliate_cpa', 20, 2)->default(10);
            }
            if ($addAffiliateBaseline) {
                $table->decimal('affiliate_baseline', 20, 2)->default(40);
            }
            if ($addIsDemoAgent) {
                $table->tinyInteger('is_demo_agent')->default(0);
            }
            if ($addStatus) {
                $table->string('status', 50)->default('active');
            }
        });

        // FK do inviter separado (mais seguro em ambientes já existentes)
        if ($addInviter) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('inviter', 'users_inviter_foreign')
                    ->references('id')->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        // Remove FK antes, se existir (evita erro ao dropar coluna)
        if (Schema::hasColumn('users', 'inviter')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign('users_inviter_foreign');
                });
            } catch (\Throwable $e) {
                // Se não existir/for outro nome, seguimos sem quebrar rollback
            }
        }

        $cols = [];

        if (Schema::hasColumn('users', 'oauth_id')) $cols[] = 'oauth_id';
        if (Schema::hasColumn('users', 'oauth_type')) $cols[] = 'oauth_type';
        if (Schema::hasColumn('users', 'avatar')) $cols[] = 'avatar';
        if (Schema::hasColumn('users', 'last_name')) $cols[] = 'last_name';
        if (Schema::hasColumn('users', 'cpf')) $cols[] = 'cpf';
        if (Schema::hasColumn('users', 'phone')) $cols[] = 'phone';
        if (Schema::hasColumn('users', 'logged_in')) $cols[] = 'logged_in';
        if (Schema::hasColumn('users', 'banned')) $cols[] = 'banned';
        if (Schema::hasColumn('users', 'inviter')) $cols[] = 'inviter';
        if (Schema::hasColumn('users', 'inviter_code')) $cols[] = 'inviter_code';
        if (Schema::hasColumn('users', 'affiliate_revenue_share')) $cols[] = 'affiliate_revenue_share';
        if (Schema::hasColumn('users', 'affiliate_cpa')) $cols[] = 'affiliate_cpa';
        if (Schema::hasColumn('users', 'affiliate_baseline')) $cols[] = 'affiliate_baseline';
        if (Schema::hasColumn('users', 'is_demo_agent')) $cols[] = 'is_demo_agent';
        if (Schema::hasColumn('users', 'status')) $cols[] = 'status';

        if (empty($cols)) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($cols) {
            $table->dropColumn($cols);
        });
    }
};