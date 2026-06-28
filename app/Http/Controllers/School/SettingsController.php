<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $school = auth()->user()->school;
        return view('school.settings.index', compact('school'));
    }

    public function update(Request $request): RedirectResponse
    {
        $school = auth()->user()->school;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'timezone' => 'required|string',
            'grading_system' => 'required|in:percentage,grade,cgpa',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $settings = $school->settings ?? [];
        $settings['timezone'] = $request->timezone;
        $settings['grading_system'] = $request->grading_system;

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'settings' => $settings,
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($school->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($school->logo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($school->logo_path);
            }
            // Store new logo
            $path = $request->file('logo')->store('logos', 'public');
            $updateData['logo_path'] = $path;
        }

        $school->update($updateData);

        return redirect()->route('school.settings')
            ->with('success', 'School settings updated successfully!');
    }
}
