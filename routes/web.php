<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\App\AiToolController;
use App\Http\Controllers\App\ApplicationController;
use App\Http\Controllers\App\AtsCheckerController;
use App\Http\Controllers\App\CoverLetterController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\JobController;
use App\Http\Controllers\App\OnboardingController;
use App\Http\Controllers\App\ResumeController;
use App\Http\Controllers\App\TemplateController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('email/verify', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');

    Route::get('onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('master-resume', [ResumeController::class, 'master'])->name('resumes.master');
    Route::get('resumes/{resume}/export', [ResumeController::class, 'export'])->name('resumes.export');
    Route::post('resumes/{resume}/duplicate', [ResumeController::class, 'duplicate'])->name('resumes.duplicate');
    Route::resource('resumes', ResumeController::class)->except(['show']);

    Route::get('templates', TemplateController::class)->name('templates.index');
    Route::get('cover-letters', [CoverLetterController::class, 'index'])->name('cover-letters.index');
    Route::get('cover-letters/{coverLetter}/export', [CoverLetterController::class, 'export'])->name('cover-letters.export');

    Route::post('jobs/{job}/analyze', [JobController::class, 'analyze'])->middleware('throttle:ai')->name('jobs.analyze');
    Route::post('jobs/{job}/match', [JobController::class, 'match'])->middleware('throttle:ai')->name('jobs.match');
    Route::post('jobs/{job}/tailor', [JobController::class, 'tailor'])->middleware('throttle:ai')->name('jobs.tailor');
    Route::post('jobs/{job}/cover-letter', [JobController::class, 'coverLetter'])->middleware('throttle:ai')->name('jobs.cover-letter');
    Route::resource('jobs', JobController::class)->except(['edit']);

    Route::get('applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::patch('applications/{application}/stage', [ApplicationController::class, 'stage'])->name('applications.stage');

    Route::get('ats-checker', [AtsCheckerController::class, 'create'])->name('ats.create');
    Route::post('ats-checker', [AtsCheckerController::class, 'store'])->middleware('throttle:ai')->name('ats.store');

    Route::get('ai-tools', [AiToolController::class, 'index'])->name('ai-tools.index');
    Route::post('ai-tools', [AiToolController::class, 'run'])->middleware('throttle:ai')->name('ai-tools.run');

    Route::get('admin', AdminDashboardController::class)->middleware('can:view-admin')->name('admin.dashboard');
});
