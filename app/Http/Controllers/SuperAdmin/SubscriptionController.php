<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\SchoolSubscription;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::latest()->get();
        
        $subscriptions = SchoolSubscription::with(['school', 'plan'])
            ->latest()
            ->get();

        $schools = School::orderBy('name')->get();

        return view('superadmin.subscriptions.index', compact('plans', 'subscriptions', 'schools'));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'max_students' => 'required|integer|min:-1',
            'max_teachers' => 'required|integer|min:-1',
            'features' => 'nullable|array',
        ]);

        SubscriptionPlan::create([
            'name' => $request->name,
            'price' => $request->price,
            'max_students' => $request->max_students,
            'max_teachers' => $request->max_teachers,
            'features' => $request->features,
        ]);

        return redirect()->route('superadmin.subscriptions')
            ->with('success', 'Subscription plan created successfully!');
    }

    public function storeSchoolSubscription(Request $request): RedirectResponse
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Cancel previous active subscriptions for this school
        SchoolSubscription::where('school_id', $request->school_id)
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        SchoolSubscription::create([
            'school_id' => $request->school_id,
            'subscription_plan_id' => $request->subscription_plan_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'active',
        ]);

        return redirect()->route('superadmin.subscriptions')
            ->with('success', 'Plan subscribed to school successfully!');
    }
}
