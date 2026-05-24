<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function index ()
    {
        $users = User::where('admin_status', false)->get();

        return view('admin.staff', compact('users'));
    }
    
}
