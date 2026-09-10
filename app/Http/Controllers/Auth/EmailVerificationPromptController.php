<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View
    {
        if (! $request->user()->hasVerifiedEmail()) {
            return view('auth.verify-email');
        }

        return $request->user()->onboarding_completed_at
            ? redirect()->intended(route('dashboard', absolute: false))
            : redirect()->intended(route('onboarding.show', absolute: false));
    }
}
