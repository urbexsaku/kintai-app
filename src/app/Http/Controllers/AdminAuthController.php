<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'admin_status' => 1,
        ])) {
            return redirect()->route('admin.attendance.index');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }
}
