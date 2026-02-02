<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TempAdminCreate extends Command
{
    protected $signature = 'tempadmin:create';

    protected $description = 'Create temporary admin user';

    public function handle()
    {
        $this->info('=== TEMP ADMIN CREATION START ===');

        $email = env('TEMP_ADMIN_EMAIL');
        $password = env('TEMP_ADMIN_PASSWORD');

        if (!$email || !$password) {
            $this->error('TEMP_ADMIN_EMAIL or TEMP_ADMIN_PASSWORD not set');
            return Command::FAILURE;
        }

        $this->info("Using email: {$email}");

        $user = User::where('email', $email)->first();

        if ($user) {
            $this->info('Admin already exists');
            return Command::SUCCESS;
        }

        try {

            User::create([
                'name'              => 'Temp Admin',
                'username'          => 'admin_' . Str::random(6),
                'email'             => $email,
                'password'          => Hash::make($password),

                // Campos comuns nesse tipo de sistema
                'role_id'           => 1,
                'status'            => 1,
                'email_verified_at' => now(),

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $this->info('Temporary admin created successfully');

        } catch (\Throwable $e) {

            $this->error('Failed to create admin');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}