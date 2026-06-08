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
        ]);

        $settings = $school->settings ?? [];
        $settings['timezone'] = $request->timezone;
        $settings['grading_system'] = $request->grading_system;

        $school->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'settings' => $settings,
        ]);

        return redirect()->route('school.settings')
            ->with('success', 'School settings updated successfully!');
    }
}
