<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class ChangeRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rossa:role {email} {role} {--remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change a user\'s role by providing their email and the role code. Use --remove to remove a role. Example: rossa:role test@example.com admin --remove';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $role_name = $this->argument('role');
        $remove = $this->option('remove');

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("User under '{$email}' not found");

            return Command::FAILURE;
        }

        $role = Role::where('name', $role_name)->first();
        if (! $role) {
            $this->error("Role '{$role_name}' not found");

            return Command::FAILURE;
        }

        if ($role->is_custom) {
            $this->error('Cannot assign a custom role using this command.');

            return Command::FAILURE;
        }

        $currentRole = $user->role;
        if (! $remove && $currentRole && $currentRole->id === $role->id) {
            $this->info("User '{$user->name}' already has the role '{$role->name}'");

            return Command::SUCCESS;
        }

        if ($remove) {
            $user->role_id = null;
            $user->save();
            $this->info("Role '{$role->name}' removed from user '{$user->email}'");
        } else {
            $user->role_id = $role->id;
            $user->save();
            $this->info("Role '{$role->name}' set for user '{$user->email}'");
        }

        return 0;
    }
}
