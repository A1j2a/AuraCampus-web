<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\ParentDetail;
use App\Models\SchoolClass;
use App\Models\StudentDetail;
use App\Models\User;
use App\Models\UserCredential;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ParentController extends Controller
{
    public function index(): View
    {
        $schoolId = auth()->user()->school_id;

        $parents = User::where('school_id', $schoolId)
            ->role('parent')
            ->with(['students.studentDetail.class', 'parentDetail'])
            ->latest()
            ->get();

        $classes = SchoolClass::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('section')
            ->get();

        return view('school.parents.index', compact('parents', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                          => 'required|string|max:255',
            'email'                         => 'required|email|unique:users,email',
            'mobile'                        => 'required|string|max:15',
            'relation'                      => 'required|string|max:50',
            'occupation'                    => 'nullable|string|max:100',
            'emergency_contact'             => 'nullable|string|max:15',
            'address'                       => 'nullable|string|max:500',
            'children_count'                => 'required|integer|min:1|max:4',
            'children.*.name'               => 'required|string|max:255',
            'children.*.admission_number'   => 'required|string|max:50',
            'children.*.roll_number'        => 'nullable|string|max:20',
            'children.*.class_id'           => 'required|exists:classes,id',
            'children.*.dob'                => 'nullable|date',
            'children.*.gender'             => 'nullable|in:male,female,other',
            'children.*.blood_group'        => 'nullable|string|max:5',
        ]);

        DB::transaction(function () use ($request) {
            $schoolId = auth()->user()->school_id;

            // Auto generate parent credentials
            $username   = $this->generateUsername($request->name);
            $password   = $this->generatePassword($request->name);

            // Create parent user
            $parent = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'phone'     => $request->mobile,
                'password'  => Hash::make($password),
                'school_id' => $schoolId,
            ]);
            $parent->assignRole('parent');

            // Save parent details
            ParentDetail::create([
                'user_id'           => $parent->id,
                'relation'          => $request->relation,
                'occupation'        => $request->occupation,
                'emergency_contact' => $request->emergency_contact,
            ]);

            // Save credentials
            UserCredential::create([
                'user_id'        => $parent->id,
                'username'       => $username,
                'plain_password' => $password,
            ]);

            // Create each child
            foreach ($request->children as $childData) {
                $childPassword  = $this->generatePassword($childData['name']);
                $childUsername  = $this->generateUsername($childData['name']);

                $student = User::create([
                    'name'      => $childData['name'],
                    'email'     => $childUsername . '@student.' . $schoolId . '.auracampus.in',
                    'password'  => Hash::make($childPassword),
                    'school_id' => $schoolId,
                ]);
                $student->assignRole('student');

                StudentDetail::create([
                    'user_id'          => $student->id,
                    'school_id'        => $schoolId,
                    'class_id'         => $childData['class_id'],
                    'admission_number' => $childData['admission_number'],
                    'roll_number'      => $childData['roll_number'] ?? null,
                    'date_of_birth'    => $childData['dob'] ?? null,
                    'gender'           => $childData['gender'] ?? null,
                    'blood_group'      => $childData['blood_group'] ?? null,
                    'status'           => 'active',
                ]);

                UserCredential::create([
                    'user_id'        => $student->id,
                    'username'       => $childUsername,
                    'plain_password' => $childPassword,
                ]);

                // Link parent to student
                $parent->students()->attach($student->id, [
                    'relationship' => $request->relation,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        });

        return redirect()->route('school.parents')
            ->with('success', 'Parent and ' . $request->children_count . ' child(ren) registered successfully!');
    }

    public function update(Request $request, User $parent): RedirectResponse
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email,' . $parent->id,
            'mobile'            => 'required|string|max:15',
            'relation'          => 'required|string|max:50',
            'occupation'        => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:15',
            'address'           => 'nullable|string|max:500',
        ]);

        $parent->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->mobile,
        ]);

        $parent->parentDetail()->updateOrCreate(
            ['user_id' => $parent->id],
            [
                'relation'          => $request->relation,
                'occupation'        => $request->occupation,
                'emergency_contact' => $request->emergency_contact,
            ]
        );

        return redirect()->route('school.parents')->with('success', $parent->name . ' updated successfully!');
    }

    public function link(Request $request): RedirectResponse
    {
        $request->validate([
            'parent_id'    => 'required|exists:users,id',
            'student_id'   => 'required|exists:users,id',
            'relationship' => 'required|string|max:50',
        ]);

        $schoolId = auth()->user()->school_id;

        $parent  = User::where('school_id', $schoolId)->role('parent')->findOrFail($request->parent_id);
        $student = User::where('school_id', $schoolId)->role('student')->findOrFail($request->student_id);

        $parent->students()->syncWithoutDetaching([
            $student->id => [
                'relationship' => $request->relationship,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]
        ]);

        return redirect()->route('school.parents')
            ->with('success', $student->name . ' linked to ' . $parent->name . ' successfully!');
    }

    private function generateUsername(string $name): string
    {
        $parts    = explode(' ', strtolower(trim($name)));
        $base     = isset($parts[1]) ? $parts[0] . '.' . $parts[1] : $parts[0];
        $base     = preg_replace('/[^a-z0-9.]/', '', $base);
        $username = $base;
        $counter  = 1;

        while (User::where('email', 'like', $username . '%')->exists() ||
               UserCredential::where('username', $username)->exists()) {
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
