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
    /**
     * Update a user in Supabase Authentication.
     */
    public function updateUser($uuid, array $data)
    {
        try {
            $payload = [];
            if (isset($data['email'])) $payload['email'] = $data['email'];
            if (isset($data['password'])) $payload['password'] = $data['password'];
            if (isset($data['email_confirm'])) $payload['email_confirm'] = $data['email_confirm'];
            
            // Handle metadata
            $metadata = [];
            if (isset($data['username'])) $metadata['username'] = $data['username'];
            if (isset($data['no_hp'])) $metadata['no_hp'] = $data['no_hp'];
            
            if (!empty($metadata)) {
                $payload['user_metadata'] = $metadata;
            }

            $fullUrl = $this->url . '/auth/v1/admin/users/' . $uuid;
            Log::info('Supabase Update Request', ['url' => $fullUrl, 'payload' => $payload]);

            $response = Http::withHeaders([
                'apikey' => $this->serviceKey,
                'Authorization' => 'Bearer ' . $this->serviceKey,
                'Content-Type' => 'application/json',
            ])->put($fullUrl, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Supabase Auth Update Error: ' . $response->status() . ' - ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Supabase Auth Update Exception: ' . $e->getMessage());
            return null;
        }
    }
}
