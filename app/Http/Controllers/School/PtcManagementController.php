<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\PtcBooking;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

class PtcManagementController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        // Fetch classes and teachers for filters
        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->orderBy('section')->get();
        $teachers = User::where('school_id', $schoolId)->role('teacher')->orderBy('name')->get();

        // Query active bookings (exclude deleted/optional statuses if needed, but display all)
        $bookingsQuery = PtcBooking::where('school_id', $schoolId)
            ->with([
                'student.studentDetail.class.teacher',
                'parent'
            ]);

        if ($request->filled('date')) {
            $bookingsQuery->whereDate('ptc_date', $request->date);
        }

        if ($request->filled('class_id')) {
            $bookingsQuery->whereHas('student.studentDetail', function ($query) use ($request) {
                $query->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('teacher_id')) {
            $bookingsQuery->whereHas('student.studentDetail.class', function ($query) use ($request) {
                $query->where('teacher_id', $request->teacher_id);
            });
        }

        $bookings = $bookingsQuery->latest('ptc_date')->paginate(15)->withQueryString();

        return view('school.ptc.index', compact('bookings', 'classes', 'teachers'));
    }

    public function cancel(PtcBooking $booking): RedirectResponse
    {
        if ($booking->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $booking->update(['status' => 'cancelled']);

        // Notify parent (via student feed)
        Notification::create([
            'user_id' => $booking->student_id,
            'title'   => "PTC Session Cancelled",
            'body'    => "The Parent-Teacher Conference booked for student " . ($booking->student?->name ?? 'your child') . " on " . $booking->ptc_date->format('d M Y') . " has been cancelled.",
            'type'    => 'general',
        ]);

        return back()->with('success', 'PTC Booking cancelled successfully.');
    }

    public function reschedule(Request $request, PtcBooking $booking): RedirectResponse
    {
        if ($booking->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $request->validate([
            'ptc_date'  => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string',
        ]);

        $booking->update([
            'ptc_date'  => $request->ptc_date,
            'time_slot' => $request->time_slot,
            'status'    => 'booked' // reset status if cancelled previously
        ]);

        $formattedDate = Carbon::parse($request->ptc_date)->format('d M Y');

        // Notify parent (via student feed)
        Notification::create([
            'user_id' => $booking->student_id,
            'title'   => "PTC Rescheduled",
            'body'    => "Your Parent-Teacher Conference booking for " . ($booking->student?->name ?? 'your child') . " has been rescheduled to " . $formattedDate . " at " . $request->time_slot . ".",
            'type'    => 'general',
        ]);

        return back()->with('success', 'PTC Booking rescheduled successfully.');
    }
}
