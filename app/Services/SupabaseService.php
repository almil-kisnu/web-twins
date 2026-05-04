<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
    protected $url;
    protected $serviceKey;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->serviceKey = config('services.supabase.service_role_key');
    }

    /**
     * Create a user in Supabase Authentication.
     */
    public function createUser(array $data)
    {
        try {
            $payload = [
                'email' => $data['email'],
                'password' => $data['password'],
                'email_confirm' => true,
                'user_metadata' => [
                    'username' => $data['username'],
                    'no_hp' => $data['no_hp'] ?? '',
                ]
            ];

            // If a specific UUID is provided, use it
            if (isset($data['id'])) {
                $payload['id'] = $data['id'];
            }

            $response = Http::withHeaders([
                'apikey' => $this->serviceKey,
                'Authorization' => 'Bearer ' . $this->serviceKey,
                'Content-Type' => 'application/json',
            ])->post($this->url . '/auth/v1/admin/users', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Supabase Auth Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Supabase Auth Exception: ' . $e->getMessage());
            return null;
        }
    }
}
