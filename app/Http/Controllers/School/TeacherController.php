<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\TeacherClassSection;
use App\Models\TeacherDetail;
use App\Models\User;
use App\Models\UserCredential;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        $schoolId = auth()->user()->school_id;

        $teachers = User::where('school_id', $schoolId)
            ->role('teacher')
            ->with(['teacherDetail', 'credential', 'teacherClassSections', 'subjects'])
            ->latest()
            ->get();

        $classes = SchoolClass::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('section')
            ->get();

        $subjects = Subject::where('school_id', $schoolId)
            ->orderBy('name')
            ->get();

        return view('school.teachers.index', compact('teachers', 'classes', 'subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'mobile'         => 'required|string|max:15',
            'employee_id'    => 'nullable|string|max:50',
            'designation'    => 'required|string|max:255',
            'qualification'  => 'required|string|max:255',
            'experience'     => 'nullable|string|max:50',
            'joining_date'   => 'nullable|date',
            'address'        => 'nullable|string|max:500',
            'profile_image'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'class_ids'      => 'nullable|array',
            'class_ids.*'    => 'exists:classes,id',
            'class_teacher_of' => 'nullable|exists:classes,id',
            'subject_ids'    => 'nullable|array',
            'subject_ids.*'  => 'exists:subjects,id',
        ]);

        DB::transaction(function () use ($request) {
            $schoolId = auth()->user()->school_id;

            $username = $this->generateUsername($request->name);
            $password = $this->generatePassword($request->name);

            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profiles', 'public');
            }

            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'phone'         => $request->mobile,
                'password'      => Hash::make($password),
                'school_id'     => $schoolId,
                'profile_image' => $profileImagePath,
            ]);
            $user->assignRole('teacher');

            TeacherDetail::create([
                'user_id'       => $user->id,
                'employee_id'   => $request->employee_id,
                'designation'   => $request->designation,
                'qualification' => $request->qualification,
                'experience'    => $request->experience,
                'joining_date'  => $request->joining_date,
                'is_active'     => true,
            ]);

            UserCredential::create([
                'user_id'        => $user->id,
                'username'       => $username,
                'plain_password' => $password,
            ]);

            // Sync subjects
            if ($request->has('subject_ids')) {
                $user->subjects()->sync($request->subject_ids);
            }

            // Assign classes
            if ($request->class_ids) {
                foreach ($request->class_ids as $classId) {
                    TeacherClassSection::create([
                        'teacher_id'      => $user->id,
                        'class_id'        => $classId,
                        'is_class_teacher' => $request->class_teacher_of == $classId,
                    ]);
                }
            }

            // If class teacher of a class not in assigned classes
            if ($request->class_teacher_of && !in_array($request->class_teacher_of, $request->class_ids ?? [])) {
                TeacherClassSection::create([
                    'teacher_id'       => $user->id,
                    'class_id'         => $request->class_teacher_of,
                    'is_class_teacher' => true,
                ]);
            }

            // Sync classes.teacher_id
            if ($request->class_teacher_of) {
                SchoolClass::where('id', $request->class_teacher_of)->update(['teacher_id' => $user->id]);
            }
        });

        return redirect()->route('school.teachers')
            ->with('success', $request->name . ' has been onboarded successfully!');
    }

    public function update(Request $request, User $teacher): RedirectResponse
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $teacher->id,
            'mobile'           => 'required|string|max:15',
            'employee_id'      => 'nullable|string|max:50',
            'designation'      => 'required|string|max:255',
            'qualification'    => 'required|string|max:255',
            'experience'       => 'nullable|string|max:50',
            'joining_date'     => 'nullable|date',
            'address'          => 'nullable|string|max:500',
            'profile_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'class_ids'        => 'nullable|array',
            'class_ids.*'      => 'exists:classes,id',
            'class_teacher_of' => 'nullable|exists:classes,id',
            'subject_ids'      => 'nullable|array',
            'subject_ids.*'    => 'exists:subjects,id',
        ]);

        DB::transaction(function () use ($request, $teacher) {
            $updateData = [
                'name'  => $request->name,
                'email' => $request->email,
                'phone' => $request->mobile,
            ];

            if ($request->hasFile('profile_image')) {
                if ($teacher->profile_image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($teacher->profile_image);
                }
                $updateData['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
            }

            $teacher->update($updateData);

            $teacher->teacherDetail()->updateOrCreate(
                ['user_id' => $teacher->id],
                [
                    'employee_id'   => $request->employee_id,
                    'designation'   => $request->designation,
                    'qualification' => $request->qualification,
                    'experience'    => $request->experience,
                    'joining_date'  => $request->joining_date,
                ]
            );

            // Sync subjects
            $subjectIds = $request->input('subject_ids', []);
            $teacher->subjects()->sync($subjectIds);

            // Clean up class_subject and timetable_slots for subjects the teacher is no longer qualified to teach
            if (empty($subjectIds)) {
                DB::table('class_subject')
                    ->where('teacher_id', $teacher->id)
                    ->update(['teacher_id' => null]);

                DB::table('timetable_slots')
                    ->where('teacher_id', $teacher->id)
                    ->update(['teacher_id' => null]);
            } else {
                DB::table('class_subject')
                    ->where('teacher_id', $teacher->id)
                    ->whereNotIn('subject_id', $subjectIds)
                    ->update(['teacher_id' => null]);

                DB::table('timetable_slots')
                    ->where('teacher_id', $teacher->id)
                    ->whereNotIn('subject_id', $subjectIds)
                    ->update(['teacher_id' => null]);
            }

            // Update class assignments
            TeacherClassSection::where('teacher_id', $teacher->id)->delete();

            $classIds = $request->input('class_ids', []);
            if ($classIds) {
                foreach ($classIds as $classId) {
                    TeacherClassSection::create([
                        'teacher_id'       => $teacher->id,
                        'class_id'         => $classId,
                        'is_class_teacher' => $request->class_teacher_of == $classId,
                    ]);
                }
            }

            if ($request->class_teacher_of && !in_array($request->class_teacher_of, $classIds)) {
                TeacherClassSection::create([
                    'teacher_id'       => $teacher->id,
                    'class_id'         => $request->class_teacher_of,
                    'is_class_teacher' => true,
                ]);
                $classIds[] = $request->class_teacher_of;
            }

            // Clean up class_subject and timetable_slots for classes the teacher is no longer assigned to
            if (empty($classIds)) {
                DB::table('class_subject')
                    ->where('teacher_id', $teacher->id)
                    ->update(['teacher_id' => null]);

                DB::table('timetable_slots')
                    ->where('teacher_id', $teacher->id)
                    ->update(['teacher_id' => null]);
            } else {
                DB::table('class_subject')
                    ->where('teacher_id', $teacher->id)
                    ->whereNotIn('class_id', $classIds)
                    ->update(['teacher_id' => null]);

                DB::table('timetable_slots')
                    ->where('teacher_id', $teacher->id)
                    ->whereNotIn('class_id', $classIds)
                    ->update(['teacher_id' => null]);
            }

            // Sync classes.teacher_id
            SchoolClass::where('teacher_id', $teacher->id)->update(['teacher_id' => null]);
            if ($request->class_teacher_of) {
                SchoolClass::where('id', $request->class_teacher_of)->update(['teacher_id' => $teacher->id]);
            }
        });

        return redirect()->route('school.teachers')->with('success', $teacher->name . ' updated successfully!');
    }

    private function generateUsername(string $name): string
    {
        $parts    = explode(' ', strtolower(trim($name)));
        $base     = isset($parts[1]) ? $parts[0] . '.' . $parts[1] : $parts[0];
        $base     = preg_replace('/[^a-z0-9.]/', '', $base);
        $username = $base;
        $counter  = 1;

        while (UserCredential::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    private function generatePassword(string $name): string
    {
        $initials = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', trim($name)))));
        return $initials . '@' . rand(1000, 9999);
    }
}
