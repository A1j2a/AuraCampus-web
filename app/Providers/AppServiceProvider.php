<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('layouts.school', function ($view) {
            if (auth()->check()) {
                $schoolId = auth()->user()->school_id;
                $sessions = \App\Models\AcademicSession::where('school_id', $schoolId)->get();
                $activeSession = auth()->user()->getActiveAcademicSession();
                $view->with(compact('sessions', 'activeSession'));
            }
        });

        // Custom Route Model Binding for student
        \Illuminate\Support\Facades\Route::bind('student', function ($value) {
            $student = \App\Models\User::where('id', $value)->first();
            if ($student) {
                return $student;
            }

            if (auth()->check()) {
                $user = auth()->user();
                if ($user->user_type == 4 || $user->hasRole('parent')) {
                    $firstChild = $user->students()->first();
                    if ($firstChild) {
                        return $firstChild;
                    }
                }
            }

            abort(404, "Student not found");
        });

        // Custom Route Model Binding for homework
        \Illuminate\Support\Facades\Route::bind('homework', function ($value) {
            $homework = \App\Models\Homework::find($value);
            if ($homework) {
                return $homework;
            }

            if (is_string($value) && (preg_match('/^hw\d+$/', $value) || str_starts_with($value, 'hw_'))) {
                $homework = new \App\Models\Homework();
                if (auth()->check()) {
                    $homework->teacher_id = auth()->id();
                    $homework->school_id = auth()->user()->school_id;
                }
                return $homework;
            }

            abort(404, "Homework not found");
        });
    }
}
