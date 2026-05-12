<?php

namespace App\Observers;

use App\Models\PaymentOrder;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class PaymentOrderObserver
{
    /**
     * Handle the PaymentOrder "saved" event.
     */
    public function saved(PaymentOrder $order): void
    {
        /* 
        // Fitur sinkronisasi kontak otomatis dimatikan karena menyebabkan error duplikasi di database
        if ($order->user_id) {
            $user = User::where('uuid', $order->user_id)->first();
            if ($user) {
                // Gunakan pengecekan kolom untuk keamanan
                $hasUserId = Schema::hasColumn('contacts', 'user_id');
                
                $contact = null;
                if ($hasUserId) {
                    $contact = Contact::where('user_id', $user->uuid)->first();
                }
                
                if (!$contact && $user->no_hp) {
                    $contact = Contact::where('no_hp', $user->no_hp)->first();
                }

                $data = [
                    'nama' => $user->username,
                    'no_hp' => $user->no_hp ?? ($contact ? $contact->no_hp : null),
                    'store_id' => $user->store_id ?? ($contact ? $contact->store_id : null),
                ];

                if ($hasUserId) {
                    $data['user_id'] = $user->uuid;
                }

                if ($contact) {
                    $contact->update($data);
                } else {
                    $data['tipe'] = 'customer';
                    Contact::create($data);
                }
            }
        }
        */
    }
}
