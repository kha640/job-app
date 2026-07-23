<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobVacancyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


// PUBLIC - Landing Page Route
Route::get('/', function () {
    return view('welcome');
});


Route::middleware('auth', 'role:job-seeker')->group(function () {
    // Landing Page Route
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Jobs Page Route
    Route::get('job-applications', [JobApplicationController::class, 'index'])->name('job-applications.index');

    // Job Vacancy - Show and Apply
    Route::get('job-vacancies/{id}', [JobVacancyController::class, 'show'])->name('job-vacancies.show');
    Route::get('job-vacancies/{id}/apply', [JobVacancyController::class, 'apply'])->name('job-vacancies.apply');
    Route::post('job-vacancies/{id}/apply', [JobVacancyController::class, 'processApplication'])->name('job-vacancies.processApplication');

    // TEST APIs
    Route::get('test-groq', [JobVacancyController::class, 'testGroq'])->name('test-groq');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/test', function () {
    return 'OK';
});

require __DIR__.'/auth.php';
