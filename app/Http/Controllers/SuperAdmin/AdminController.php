<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $admins = User::role('school-admin')
            ->with('school')
            ->latest()
            ->get();

        return view('superadmin.admins.index', compact('admins'));
    }
}
