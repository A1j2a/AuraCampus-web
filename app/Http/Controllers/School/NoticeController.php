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
            'event_date' => 'required_if:type,event|nullable|date',
            'event_time' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:5120',
        ]);

        $schoolId = auth()->user()->school_id;

        $publishNow = $request->boolean('publish_now');
        $publishedAt = $publishNow || !$request->published_at
            ? now() 
            : $request->published_at;

        $attachmentPath = null;
        $fileType = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('events', 'public');
            $mime = $file->getClientMimeType();
            $fileType = str_starts_with($mime, 'image/') ? 'image' : 'document';
        }

        // If category is event, create a SchoolEvent first
        if ($request->type === 'event') {
            \App\Models\SchoolEvent::create([
                'school_id' => $schoolId,
                'title' => $request->title,
                'description' => $request->content,
                'type' => 'general',
                'event_date' => $request->event_date,
                'event_time' => $request->event_time,
                'banner_image_url' => $attachmentPath,
            ]);
        }

        // Create the Notice record
        Notice::create([
            'school_id' => $schoolId,
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'attachment_path' => $attachmentPath,
            'attachment_type' => $fileType,
            'published_at' => $publishedAt,
        ]);

        // Send notifications to all students of the school
        try {
            $students = \App\Models\User::where('school_id', $schoolId)->where('user_type', 3)->get();
            foreach ($students as $student) {
                $notificationData = [
                    'user_id' => $student->id,
                    'title'   => ($request->type === 'event' ? "Event: " : "Notice: ") . $request->title,
                    'body'    => $request->content,
                    'type'    => 'general',
                ];

                if ($attachmentPath) {
                    if ($fileType === 'image') {
                        $notificationData['image_url'] = $attachmentPath;
                    } else {
                        $notificationData['document_url'] = $attachmentPath;
                    }
                }

                \App\Models\Notification::create($notificationData);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to create notifications for published notice/event: " . $e->getMessage());
        }

        return redirect()->route('school.notices')
            ->with('success', $request->type === 'event' ? 'Event & Notice published successfully!' : 'Notice published successfully!');
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
