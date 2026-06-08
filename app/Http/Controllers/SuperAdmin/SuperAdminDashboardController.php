<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    /**
     * Display the Super Admin Dashboard.
     */
    public function index(): View
    {
        return view('superadmin.dashboard.index');
    }
}
