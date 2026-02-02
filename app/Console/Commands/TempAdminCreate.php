<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TempAdminCreate extends Command
{
    protected $signature = 'temp:admin {--email=} {--password=}';
    protected $description = 'Create/update a temporary admin user using env vars or options';

    public function handle(): int
    {
        // Gate simples: precisa existir a variável (só pra evitar rodar sem querer)
        $gate = env('GAMERDOCKER_TEMP_ADMIN_2026_02_02_!@#_987654321', null);
        if (empty($gate)) {
            $this->warn('Gate ENV not found. Skipping temp admin creation.');
            return self::SUCCESS;
        }

        $email = $this->option('email') ?: env('GAMERDOCKER_TEMP_ADMIN_EMAIL', null);
        $password = $this->option('password') ?: env('GAMERDOCKER_TEMP_ADMIN_PASSWORD', null);

        if (empty($email) || empty($password)) {
            $this->error('Missing email/password. Set GAMERDOCKER_TEMP_ADMIN_EMAIL and GAMERDOCKER_TEMP_ADMIN_PASSWORD.');
            return self::FAILURE;
        }

        $name = env('GAMERDOCKER_TEMP_ADMIN_NAME', 'Temp Admin');

        // cria/atualiza
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = new User();
            $user->email = $email;
        }

        // Campos comuns
        if (Schema::hasColumn('users', 'name')) {
            $user->name = $user->name ?: $name;
        }

        if (Schema::hasColumn('users', 'password')) {
            $user->password = Hash::make($password);
        }

        // Evitar erro de NOT NULL em affiliate_cpa (se existir)
        if (Schema::hasColumn('users', 'affiliate_cpa') && ($user->affiliate_cpa === null)) {
            $user->affiliate_cpa = 0;
        }
        if (Schema::hasColumn('users', 'affiliate_revenue_share') && ($user->affiliate_revenue_share === null)) {
            $user->affiliate_revenue_share = 0;
        }
        if (Schema::hasColumn('users', 'affiliate_baseline') && ($user->affiliate_baseline === null)) {
            $user->affiliate_baseline = 0;
        }

        // Alguns projetos exigem phone NOT NULL
        if (Schema::hasColumn('users', 'phone') && empty($user->phone)) {
            $user->phone = '0000000000';
        }

        // Alguns exigem username
        if (Schema::hasColumn('users', 'username') && empty($user->username)) {
            $user->username = Str::before($email, '@');
        }

        // role_id (se existir)
        if (Schema::hasColumn('users', 'role_id')) {
            // tenta achar um role "admin" na tabela roles (se existir)
            $roleId = null;
            if (Schema::hasTable('roles')) {
                try {
                    $roleRow = \DB::table('roles')
                        ->where('name', 'admin')
                        ->orWhere('name', 'Admin')
                        ->orWhere('name', 'administrator')
                        ->orWhere('name', 'Administrador')
                        ->first();

                    if ($roleRow) {
                        $roleId = $roleRow->id;
                    }
                } catch (\Throwable $e) {
                    // ignora
                }
            }

            $user->role_id = $roleId ?: ($user->role_id ?: 1);
        }

        $user->save();

        // Spatie Permission (se estiver instalado)
        try {
            if (method_exists($user, 'assignRole')) {
                if (class_exists(\Spatie\Permission\Models\Role::class)) {
                    $role = \Spatie\Permission\Models\Role::firstOrCreate([
                        'name' => 'admin',
                        'guard_name' => 'web',
                    ]);
                    $user->assignRole($role);
                }
            }
        } catch (\Throwable $e) {
            // ignora — o importante é criar o user
        }

        $this->info('Temp admin ready: ' . $email);
        return self::SUCCESS;
    }
}
