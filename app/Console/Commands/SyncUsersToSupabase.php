<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Http;

class SyncUsersToSupabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supabase:sync-users {--password=twins123 : Default password for migrated users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync existing users from public.users table to Supabase Authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync to Supabase Auth...');
        
        $users = User::all();
        $supabase = new SupabaseService();
        $defaultPassword = $this->option('password');

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            // Skip if no email
            if (!$user->email) {
                $this->warn("\nSkipping user {$user->username} (no email)");
                $bar->advance();
                continue;
            }

            // Attempt to create in Supabase Auth
            $result = $supabase->createUser([
                'id' => $user->uuid, // Keep the same UUID!
                'email' => $user->email,
                'password' => $defaultPassword,
                'username' => $user->username,
                'no_hp' => $user->no_hp,
                'operator_id' => $user->operator_id,
                'store_id' => $user->store_id,
                'role' => $user->role,
            ]);

            if ($result) {
                $this->info("\nCreated & Synced: {$user->email}");
            } else {
                // If create fails, try to update metadata & password for existing users
                $updateResult = $supabase->updateUser($user->uuid, [
                    'email' => $user->email,
                    'password' => $defaultPassword,
                    'username' => $user->username,
                    'no_hp' => $user->no_hp,
                    'operator_id' => $user->operator_id,
                    'store_id' => $user->store_id,
                    'role' => $user->role,
                ]);

                if ($updateResult) {
                    $this->info("\nUpdated metadata for existing user: {$user->email}");
                } else {
                    $this->error("\nFailed to sync: {$user->email}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nSync complete!");
    }
}
