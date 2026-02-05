<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajusta colunas de afiliado para não quebrarem cadastro quando vier NULL
        // (MySQL)
        DB::statement("ALTER TABLE `users` MODIFY `affiliate_cpa` DECIMAL(10,2) NULL DEFAULT 0");
        DB::statement("ALTER TABLE `users` MODIFY `affiliate_baseline` DECIMAL(10,2) NULL DEFAULT 0");

        // Se existir e estiver dando problema também, pode ajustar o revenue share:
        // (deixei como opcional porque o seu insert mostrou '20' já preenchido)
        // DB::statement(\"ALTER TABLE `users` MODIFY `affiliate_revenue_share` INT NULL DEFAULT 0\");
    }

    public function down(): void
    {
        // Volta para NOT NULL (se um dia você quiser reverter)
        DB::statement("ALTER TABLE `users` MODIFY `affiliate_cpa` DECIMAL(10,2) NOT NULL");
        DB::statement("ALTER TABLE `users` MODIFY `affiliate_baseline` DECIMAL(10,2) NOT NULL");
        // DB::statement(\"ALTER TABLE `users` MODIFY `affiliate_revenue_share` INT NOT NULL\");
    }
};
