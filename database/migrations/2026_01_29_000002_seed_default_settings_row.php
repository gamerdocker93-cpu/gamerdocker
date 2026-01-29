<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        $now = Carbon::now();

        $exists = DB::table('settings')->where('id', 1)->exists();

        if (!$exists) {
            DB::table('settings')->insert([
                'id'                   => 1,
                'software_name'         => 'Sistema',
                'software_description'  => 'Sistema',
                'currency_code'         => 'BRL',
                'decimal_format'        => 2,
                'currency_position'     => 'left',
                'prefix'                => 'R$',
                'storage'               => 'local',
                'min_deposit'           => 0,
                'max_deposit'           => 0,
                'min_withdrawal'        => 0,
                'max_withdrawal'        => 0,
                'initial_bonus'         => 0,
                'digitopay_is_enable'   => 0,
                'sharkpay_is_enable'    => 0,
                'created_at'            => $now,
                'updated_at'            => $now,
            ]);
        } else {
            DB::table('settings')->where('id', 1)->update([
                'software_name'         => 'Sistema',
                'software_description'  => 'Sistema',
                'currency_code'         => 'BRL',
                'decimal_format'        => 2,
                'currency_position'     => 'left',
                'prefix'                => 'R$',
                'storage'               => 'local',
                'min_deposit'           => 0,
                'max_deposit'           => 0,
                'min_withdrawal'        => 0,
                'max_withdrawal'        => 0,
                'initial_bonus'         => 0,
                'digitopay_is_enable'   => 0,
                'sharkpay_is_enable'    => 0,
                'updated_at'            => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('id', 1)->delete();
    }
};