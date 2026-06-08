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
    }
}
