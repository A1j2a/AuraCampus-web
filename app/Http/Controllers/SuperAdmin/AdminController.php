<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Update school admin details.
     */
    public function update(Request $request, User $admin): RedirectResponse
    {
        // Ensure user is indeed a school-admin
        if (!$admin->hasRole('school-admin')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'password' => 'nullable|string|min:6',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_image')) {
            // Delete old profile image
            if ($admin->profile_image && Storage::disk('public')->exists($admin->profile_image)) {
                Storage::disk('public')->delete($admin->profile_image);
            }
            $path = $request->file('profile_image')->store('profile-images', 'public');
            $updateData['profile_image'] = $path;
        }

        $admin->update($updateData);

        return redirect()->route('superadmin.admins')
            ->with('success', 'Admin details for "' . $admin->name . '" updated successfully!');
    }
}
