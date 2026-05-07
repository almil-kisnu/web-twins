<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Outlet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use App\Models\Fitur;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['outlet', 'operator'])->latest()->get();
        $outlets = Outlet::all();
        $fiturs = Fitur::all();
        $operators = \App\Models\Operator::whereRaw('LOWER(nama) != ?', ['owner'])->get();
        
        return view('users.index', compact('users', 'outlets', 'fiturs', 'operators'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'no_hp' => ['required', 'string', 'max:15', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
            'role' => ['required'],
            'outlet_id' => ['nullable'], 
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->no_hp = $request->no_hp;
        $user->password = Hash::make($request->password);
        $user->outlet_id = $request->outlet_id;
        $user->operator_id = $request->role;

        if (Auth::user() && Auth::user()->isOwner()) {
            $user->email_verified_at = now();
        }

        $user->save();

        // Sync to Supabase
        $supabase = new \App\Services\SupabaseService();
        $supabase->createUser([
            'id' => $user->uuid,
            'email' => $user->email,
            'password' => $request->password,
            'username' => $user->name,
            'no_hp' => $user->no_hp,
            'operator_id' => $user->operator_id,
            'store_id' => $user->store_id,
            'role' => $user->role,
        ]);

        if ($request->has('fitur')) {
            $operator = \App\Models\Operator::find($request->role);
            if ($operator) {
                $operator->fitur = $request->fitur;
                $operator->save();
            }
        }

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        // $id di sini adalah UUID karena primary key model adalah uuid
        $user = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->uuid.',uuid'],
            'no_hp' => ['required', 'string', 'max:15', 'unique:users,no_hp,'.$user->uuid.',uuid'],
            'role' => ['required'],
            'outlet_id' => ['nullable'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->no_hp = $request->no_hp;
        $user->outlet_id = $request->outlet_id;
        $user->operator_id = $request->role;

        if ($request->has('fitur')) {
            $operator = \App\Models\Operator::find($request->role);
            if ($operator) {
                $operator->fitur = $request->fitur;
                $operator->save();
            }
        }

        if ($request->filled('password')) {
            $request->validate(['password' => [Rules\Password::defaults()]]);
            $user->password = Hash::make($request->password);

            $supabase = new \App\Services\SupabaseService();
            $supabase->updateUser($user->uuid, [
                'password' => $request->password,
                'email' => $user->email,
                'username' => $user->name,
                'no_hp' => $user->no_hp,
                'operator_id' => $user->operator_id,
                'store_id' => $user->store_id,
                'role' => $user->role,
            ]);
        } else {
            // Update Supabase metadata even if password is not changed
            $supabase = new \App\Services\SupabaseService();
            $supabase->updateUser($user->uuid, [
                'email' => $user->email,
                'username' => $user->name,
                'no_hp' => $user->no_hp,
                'operator_id' => $user->operator_id,
                'store_id' => $user->store_id,
                'role' => $user->role,
            ]);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if(Auth::user()->uuid == $user->uuid) {
            return redirect()->route('users.index')->with('error', 'Tidak dapat menghapus akun sendiri!');
        }
        $supabaseUid = $user->uuid;
        $user->delete();

        // Sync deletion to Supabase
        $supabase = new \App\Services\SupabaseService();
        $supabase->deleteUser($supabaseUid);

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        if(Auth::user()->uuid == $user->uuid) {
            return redirect()->route('users.index')->with('error', 'Tidak dapat mengubah status akun sendiri!');
        }
        $user->status_aktif = !$user->status_aktif;
        $user->save();
        
        $status = $user->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('users.index')->with('success', "User berhasil $status");
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'password' => ['required', Rules\Password::defaults()],
        ]);
        
        $user->password = Hash::make($request->password);
        $user->save();

        // Sync password reset to Supabase
        $supabase = new \App\Services\SupabaseService();
        $supabase->updateUser($user->uuid, [
            'password' => $request->password,
        ]);
        
        return redirect()->route('users.index')->with('success', 'Password user berhasil direset');
    }

}
