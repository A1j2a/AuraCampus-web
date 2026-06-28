<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\AcademicSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

        // Eager load the school admin for each school
        $schools->each(function ($school) {
            $school->admin = User::where('school_id', $school->id)
                                 ->role('school-admin')
                                 ->first();
        });

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
            'font_family' => 'nullable|string|max:100',
            'school_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:6',
            'admin_role' => 'nullable|string|max:100',
            'admin_profile_image' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);

        // Handle school logo upload
        $logoPath = null;
        if ($request->hasFile('school_logo')) {
            $logoPath = $request->file('school_logo')->store('school-logos', 'public');
        }

        // Create the school
        $school = School::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => 'active',
            'logo_path' => $logoPath,
            'font_family' => $request->font_family,
        ]);

        // Handle admin profile image upload
        $profileImagePath = null;
        if ($request->hasFile('admin_profile_image')) {
            $profileImagePath = $request->file('admin_profile_image')->store('profile-images', 'public');
        }

        // Create the school admin user
        $admin = User::create([
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'school_id' => $school->id,
            'profile_image' => $profileImagePath,
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

    /**
     * Update school campus details.
     */
    public function update(Request $request, School $school): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive,pending',
            'font_family' => 'nullable|string|max:100',
            'school_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);

        $updateData = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => $request->status,
            'font_family' => $request->font_family,
        ];

        if ($request->hasFile('school_logo')) {
            // Delete old logo
            if ($school->logo_path && Storage::disk('public')->exists($school->logo_path)) {
                Storage::disk('public')->delete($school->logo_path);
            }
            $logoPath = $request->file('school_logo')->store('school-logos', 'public');
            $updateData['logo_path'] = $logoPath;
        }

        $school->update($updateData);

        return redirect()->route('superadmin.schools')
                         ->with('success', 'Campus "' . $school->name . '" updated successfully!');
    }
}

