<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TempAdminCreate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tempadmin:create';

    /**
     * The console command description.
     */
    protected $description = 'Create temporary admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = env('TEMP_ADMIN_EMAIL');
        $password = env('TEMP_ADMIN_PASSWORD');

        if (!$email || !$password) {
            $this->error('TEMP_ADMIN_EMAIL or PASSWORD not set');
            return;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $this->info('Admin already exists');
            return;
        }

        User::create([
            'name' => 'Temp Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => 1,
        ]);

        $this->info('Temporary admin created successfully');
    }
}