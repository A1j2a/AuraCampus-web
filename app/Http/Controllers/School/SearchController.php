<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function query(Request $request): JsonResponse
    {
        $query = $request->query('query');
        if (strlen($query) < 2) {
            return response()->json([
                'students' => [],
                'teachers' => [],
                'classes' => [],
            ]);
        }

        $schoolId = auth()->user()->school_id;

        // Search students
        $students = User::where('school_id', $schoolId)
            ->role('student')
            ->where('name', 'like', "%{$query}%")
            ->take(5)
            ->get(['id', 'name', 'email']);

        // Search teachers
        $teachers = User::where('school_id', $schoolId)
            ->role('teacher')
            ->where('name', 'like', "%{$query}%")
            ->take(5)
            ->get(['id', 'name', 'email']);

        // Search classes (isolated to school scope by where)
        $classes = SchoolClass::where('school_id', $schoolId)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('section', 'like', "%{$query}%");
            })
            ->take(5)
            ->get(['id', 'name', 'section']);

        return response()->json([
            'students' => $students,
            'teachers' => $teachers,
            'classes' => $classes,
        ]);
    }
}
