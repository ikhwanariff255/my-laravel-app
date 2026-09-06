<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();

        // Paparkan pengguna mengikut syarikat yang sama (Kecuali jika system admin global)
        $users = User::where('company_id', $currentUser->company_id)->latest()->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    // 3. Simpan pengguna baharu (Dilengkapi logik had staf & company_id)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,staff,owner',
        ]);

        $currentUser = Auth::user();

        if (!$currentUser->company_id) {
            return back()->with('error', 'Akaun anda tidak diikat pada sebarang syarikat.');
        }

        // --- LOGIK KAWALAN HAD STAF (STAFF LIMIT) ---
        $currentStaffCount = User::where('company_id', $currentUser->company_id)->count();

        // Had default mengikut pakej biasa (Contoh: Pakej asas = 2 staf)
        $allowedLimit = 2; 
        
        // Berikan kelonggaran / had tinggi untuk akaun legacy (Contoh: ID syarikat 2 dan 3)
        if (in_array($currentUser->company_id, [2, 3])) {
            $allowedLimit = 50; 
        } 
        // ---------------------------------------------

        if ($currentStaffCount >= $allowedLimit) {
            return back()->with('error', 'Had bilangan staf bagi pakej syarikat anda telah penuh. Sila naik taraf pakej.');
        }

        User::create([
            'company_id' => $currentUser->company_id, // Wajib ada untuk multi-tenancy
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna berjaya didaftarkan!');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    // 5. Kemas kini data pengguna
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,staff,owner',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // Jika ada isi password baru, baru kita update password
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Maklumat pengguna berjaya dikemaskini!');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Anda tidak boleh memadam akaun anda sendiri!');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berjaya dipadam!');
    }
}