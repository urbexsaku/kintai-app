<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminStaffController extends Controller
{
    /**
     * スタッフ一覧を表示する
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = User::where('admin_status', false)->get();

        return view('admin.staff', compact('users'));
    }
}
