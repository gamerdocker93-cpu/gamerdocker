<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TempAdminCreate extends Command
{
    protected $signature = 'tempadmin:create';
    protected $description = 'Create or update temporary admin user';

    public function handle()
    {
        $email = env('TEMP_ADMIN_EMAIL');
        $password = env('TEMP_ADMIN_PASSWORD');

        if (!$email || !$password) {
            $this->error('TEMP_ADMIN_EMAIL or TEMP_ADMIN_PASSWORD not set');
            return Command::FAILURE;
        }

        $this->info("Using email: {$email}");

        // Cria se não existir, atualiza se existir (senha SEMPRE)
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Temp Admin',
                'password' => Hash::make($password),
                'role_id' => 1, // se seu projeto usa role_id, mantém
            ]
        );

        // Se o projeto usa Spatie Permission, tenta garantir role admin (sem quebrar se não existir)
        try {
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $guard = config('auth.defaults.guard', 'web');

                $role = \Spatie\Permission\Models\Role::firstOrCreate([
                    'name' => 'admin',
                    'guard_name' => $guard,
                ]);

                // Se quiser: dar todas as permissões (opcional)
                if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                    $all = \Spatie\Permission\Models\Permission::where('guard_name', $guard)->get();
                    if ($all->count() > 0) {
                        $role->syncPermissions($all);
                    }
                }

                // atribui role ao usuário
                if (method_exists($user, 'assignRole')) {
                    $user->assignRole($role);
                }
            }
        } catch (\Throwable $e) {
            // Não derruba deploy por causa de role/permission
            $this->warn('Role/permission step skipped: ' . $e->getMessage());
        }

        $this->info('Temporary admin created/updated successfully');
        return Command::SUCCESS;
    }
}