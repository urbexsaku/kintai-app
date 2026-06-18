<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    /**
     * ログイン画面を表示する
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理を行う
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'admin_status' => 1,
        ])) {

            // ログアウト遷移用のセッションID再生成
            $request->session()->regenerate();

            return redirect()->route('admin.attendance.index');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
