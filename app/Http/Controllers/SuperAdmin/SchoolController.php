<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\AcademicSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SchoolController extends Controller
{
    /**
     * Display a listing of all schools.
     */
    public function index(): View
    {
        $schools = School::withCount('users')
                         ->latest()
                         ->get();

        return view('superadmin.schools.index', compact('schools'));
    }

    /**
     * Store a newly provisioned school.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:6',
        ]);

        // Create the school
        $school = School::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => 'active',
        ]);

        // Create the school admin user
        $admin = User::create([
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'school_id' => $school->id,
        ]);
        $admin->assignRole('school-admin');

        // Create a default academic session
        AcademicSession::create([
            'school_id' => $school->id,
            'name' => date('Y') . '-' . (date('Y') + 1),
            'start_date' => date('Y') . '-04-01',
            'end_date' => (date('Y') + 1) . '-03-31',
            'is_active' => true,
        ]);

        return redirect()->route('superadmin.schools')
                         ->with('success', 'Campus "' . $school->name . '" provisioned successfully!');
    }
}
