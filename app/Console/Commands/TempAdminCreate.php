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
        // Lê primeiro TEMP_ADMIN_*
        $email = env('TEMP_ADMIN_EMAIL');
        $password = env('TEMP_ADMIN_PASSWORD');

        // Fallback: se o Railway estiver usando prefixo GAMERDOCKER_*
        if (!$email) {
            $email = env('GAMERDOCKER_TEMP_ADMIN_EMAIL');
        }
        if (!$password) {
            $password = env('GAMERDOCKER_TEMP_ADMIN_PASSWORD');
        }

        if (!$email || !$password) {
            $this->error('TEMP_ADMIN_EMAIL or TEMP_ADMIN_PASSWORD not set');
            return Command::FAILURE;
        }

        $this->info("Using email: {$email}");

        $user = User::where('email', $email)->first();

        if ($user) {
            // Atualiza senha e garante role_id
            $user->password = Hash::make($password);
            if (isset($user->role_id)) {
                $user->role_id = 1;
            }
            $user->save();

            $this->info('Admin already exists - password updated successfully');
            return Command::SUCCESS;
        }

        User::create([
            'name' => 'Temp Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => 1,
        ]);

        $this->info('Temporary admin created successfully');
        return Command::SUCCESS;
    }
}