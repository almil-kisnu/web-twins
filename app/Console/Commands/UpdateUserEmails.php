<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUserEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user emails from @twins.com to @gmail.com and specific aca account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting full email sync to Supabase Authentications...');
        $supabase = new \App\Services\SupabaseService();

        // 1. First, make sure local database is updated as requested
        
        // Handle specific aca account with UID 0447b6d3-dafb-4ce7-acc2-fdc1b7e25afa
        $specificUser = User::where('uuid', '0447b6d3-dafb-4ce7-acc2-fdc1b7e25afa')->first();
        if ($specificUser && $specificUser->email !== 'acaa@gmail.com') {
            $oldEmail = $specificUser->email;
            $specificUser->email = 'acaa@gmail.com';
            $specificUser->save();
            $this->info("Local: Updated UID 0447b6d3... to acaa@gmail.com");
        }

        // Update any remaining @twins.com to @gmail.com locally
        $twinsUsers = User::where('email', 'like', '%@twins.com')->get();
        foreach ($twinsUsers as $user) {
            $oldEmail = $user->email;
            $newEmail = str_replace('@twins.com', '@gmail.com', $oldEmail);
            
            if (!User::where('email', $newEmail)->where('uuid', '!=', $user->uuid)->exists()) {
                $user->email = $newEmail;
                $user->save();
                $this->info("Local: Updated {$oldEmail} -> {$newEmail}");
            } else {
                $this->error("Local: Cannot update {$oldEmail} -> {$newEmail} (Email already exists)");
            }
        }

        // 2. Now sync ALL local emails to Supabase Authentication
        
        // SYNC SPECIFIC USER FIRST to avoid collision (e.g. freeing up aca@gmail.com)
        if ($specificUser) {
            $this->info("Priority Sync: {$specificUser->email} (UID: {$specificUser->uuid})...");
            $result = $supabase->updateUser($specificUser->uuid, [
                'email' => $specificUser->email,
                'email_confirm' => true
            ]);
            if ($result) $this->info("  Supabase: Success");
            else $this->error("  Supabase: Failed");
        }

        $allUsers = User::where('uuid', '!=', '0447b6d3-dafb-4ce7-acc2-fdc1b7e25afa')->get();
        $this->info('Syncing remaining ' . $allUsers->count() . ' users to Supabase Auth...');

        foreach ($allUsers as $user) {
            if (!$user->email) continue;

            $this->info("Syncing {$user->email} (UID: {$user->uuid})...");
            
            $result = $supabase->updateUser($user->uuid, [
                'email' => $user->email,
                'email_confirm' => true // Ensure it's confirmed
            ]);

            if ($result) {
                $this->info("  Supabase: Success");
            } else {
                $this->error("  Supabase: Failed (Check logs for details)");
            }
        }

        $this->info('Full sync completed!');
    }
}
