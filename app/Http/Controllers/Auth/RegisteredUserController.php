<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users,username'], 
            'no_hp' => ['required', 'string', 'max:15', 'unique:users,no_hp'], 
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.unique' => 'Nama/Username ini sudah terdaftar.',
            'no_hp.unique' => 'Nomor HP ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // 1. Get default operator ID
        $operator = DB::table('operator')->where('nama', 'User')->first();
        $operatorId = $operator ? $operator->uuid : null;

        // 2. Create User in Supabase Auth
        $supabase = new \App\Services\SupabaseService();
        $supabaseUser = $supabase->createUser([
            'email' => $request->email,
            'password' => $request->password,
            'username' => $request->name,
            'no_hp' => $request->no_hp,
            'operator_id' => $operatorId,
            'role' => 'user',
        ]);

        if (!$supabaseUser) {
            return back()->with('error', 'Gagal mendaftarkan akun ke sistem autentikasi. Silakan coba lagi.');
        }

        $supabaseUid = $supabaseUser['id'];

        // 3. Create User in Local Database (linked by UUID)
        return DB::transaction(function () use ($request, $supabaseUid, $operatorId) {
            $user = User::create([
                'uuid' => $supabaseUid, // Use the UID from Supabase Auth
                'username' => $request->name, 
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'email_verified_at' => now(), // Mark as verified since Supabase Auth is confirmed
                'password' => Hash::make($request->password), // Keep hash for local fallback if needed
                'operator_id' => $operatorId, 
                'store_id' => null, 
                'status_aktif' => true, 
            ]);

            event(new Registered($user));

            Auth::login($user);

            return redirect()->route('dashboard')
                ->with('success', 'Registrasi berhasil! Akun Anda telah aktif.');
        });
    }

}