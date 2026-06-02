<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserPasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('user.settings.index', compact('user'));
    }

    public function updatePassword(UpdateUserPasswordRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();

        if (! Hash::check($data['password_lama'], $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        $user->password = Hash::make($data['password_baru']);
        $user->save();

        return back()->with('success_password', 'Password berhasil diperbarui.');
    }
}
