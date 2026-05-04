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
            ]);

            if ($result) {
                $this->info("\nSynced: {$user->email}");
            } else {
                // Check if user already exists
                $this->error("\nFailed to sync: {$user->email} (Maybe already exists?)");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nSync complete!");
    }
}
