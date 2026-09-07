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

        // Paparkan pengguna mengikut syarikat yang sama
        if ($currentUser->role === 'admin') {
            $users = User::latest()->get(); // Super Admin nampak semua
        } else {
            $users = User::where('company_id', $currentUser->company_id)->latest()->get();
        }

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    // Simpan pengguna baharu (Menggunakan had max_users daripada pakej syarikat)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,company_admin,staff', // <-- Diperbetulkan kepada company_admin
        ]);

        $currentUser = Auth::user();

        if (!$currentUser->company_id && $currentUser->role !== 'admin') {
            return back()->with('error', 'Akaun anda tidak diikat pada sebarang syarikat.');
        }

        $company = $currentUser->company;

        // --- LOGIK KAWALAN HAD STAF (MERUJUK KEPADA PAKEJ SYARIKAT) ---
        if ($company && $company->package) {
            $currentStaffCount = User::where('company_id', $company->id)->count();
            $maxUsersAllowed = $company->package->max_users; // Had daripada pakej (cth: 3)

            if ($currentStaffCount >= $maxUsersAllowed) {
                return back()->with('error', "Had bilangan pengguna bagi pakej syarikat anda telah penuh ({$maxUsersAllowed} pengguna sahaja). Sila naik taraf pakej.");
            }
        }
        // -------------------------------------------------------------

        // Tentukan company_id: Jika Super Admin, ambil dari input borang. Jika Company Admin, guna company sendiri.
        $companyId = $currentUser->role === 'admin' 
            ? $request->company_id 
            : $currentUser->company_id;

        User::create([
            'company_id' => $companyId, // <-- Menyesuaikan ikut siapa yang login
            'name'       => $request->name,
            'username'   => $request->username,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna berjaya didaftarkan!');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    // Kemas kini data pengguna
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,company_admin,staff', // <-- Diperbetulkan kepada company_admin
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