<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $company = $currentUser->company; // Dapatkan syarikat pengguna yang sedang login

        // 1. Semak jika syarikat mempunyai pakej
        if (!$company || !$company->package) {
            return back()->withErrors(['error' => 'Syarikat anda tiada pakej langganan yang sah.']);
        }

        // 2. Kira jumlah pengguna yang telah berdaftar di bawah company_id ini
        $currentUsersCount = \App\Models\User::where('company_id', $company->id)->count();
        $maxUsersAllowed = $company->package->max_users; // Had daripada pakej (cth: 3 pengguna)

        // 3. Sekat jika sudah mencapai had maksimum pakej
        if ($currentUsersCount >= $maxUsersAllowed) {
            return back()->withErrors(['error' => "Had bilangan staf untuk pakej anda telah penuh ({$maxUsersAllowed} pengguna sahaja). Sila naik taraf pakej."]);
        }

        // 4. Teruskan proses daftar staf baru
        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'company_id' => $company->id,
            'role' => 'staff', // Tetapkan sebagai staf biasa
        ]);

        return redirect()->back()->with('success', 'Staf berjaya didaftarkan!');
    }
}
