<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(): View
    {
        $schoolId = auth()->user()->school_id;

        // Fetch all notices for this school, newest first
        $notices = Notice::where('school_id', $schoolId)
            ->latest('published_at')
            ->get();

        return view('school.notices.index', compact('notices'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:academic,event,holiday,general',
            'publish_now' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $schoolId = auth()->user()->school_id;

        $publishedAt = $request->has('publish_now') || !$request->published_at
            ? now() 
            : $request->published_at;

        Notice::create([
            'school_id' => $schoolId,
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'published_at' => $publishedAt,
        ]);

        return redirect()->route('school.notices')
            ->with('success', 'Notice published successfully!');
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        // Ensure tenant isolation
        if ($notice->school_id !== auth()->user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $notice->delete();

        return redirect()->route('school.notices')
            ->with('success', 'Notice deleted successfully.');
    }
}
