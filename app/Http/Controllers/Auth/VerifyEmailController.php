<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return $request->user()->onboarding_completed_at
            ? to_route('dashboard')->with('status', 'Email verified.')
            : to_route('onboarding.show')->with('status', 'Email verified.');
    }
}
