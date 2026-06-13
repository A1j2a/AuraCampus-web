<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\StudentLeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * Approve a leave request.
     */
    public function approve(Request $request, StudentLeaveRequest $leaveRequest)
    {
        // Ensure the leave belongs to this school admin's school
        if ($leaveRequest->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $request->validate([
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        $leaveRequest->update([
            'status'        => 'approved',
            'admin_remarks' => $request->admin_remarks,
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);

        return back()->with('success', 'Leave request approved successfully.');
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, StudentLeaveRequest $leaveRequest)
    {
        // Ensure the leave belongs to this school admin's school
        if ($leaveRequest->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $request->validate([
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        $leaveRequest->update([
            'status'        => 'rejected',
            'admin_remarks' => $request->admin_remarks,
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);

        return back()->with('success', 'Leave request rejected.');
    }
}
