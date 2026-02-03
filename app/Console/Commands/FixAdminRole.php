<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixAdminRole extends Command
{
    protected $signature = 'fix:admin-role';

    protected $description = 'Attach admin role to temp admin';

    public function handle()
    {
        $user = User::where('email', env('TEMP_ADMIN_EMAIL'))->first();

        if (!$user) {
            $this->error('User not found');
            return;
        }

        if ($user->hasRole('admin')) {
            $this->info('User already has admin role');
            return;
        }

        $user->assignRole('admin');

        $this->info('Admin role attached successfully');
    }
}
