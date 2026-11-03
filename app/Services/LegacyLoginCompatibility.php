<?php

namespace App\Services;

use App\Models\User;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LegacyLoginCompatibility
{
    public function handle(string $method, Closure $next): mixed
    {
        $request = request();
        if ($method === 'login') {
            $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
            $user = User::where('email', $credentials['email'])->first();
            if ($user && filled($user->domain_name) && strtolower($user->domain_name) !== strtolower($request->getHost())) {
                throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
            }
            if ($user && blank($user->domain_name) && Auth::validate($credentials)) {
                $user->update(['domain_name' => strtolower($request->getHost())]);
            }
        }

        try {
            $response = DB::transaction($next);
        } catch (ConnectionException) {
            $this->clearSession($request);
            throw ValidationException::withMessages(['email' => 'We could not reach the account service. Please try again.']);
        } catch (ValidationException $exception) {
            if ($method === 'login') {
                $this->clearSession($request);
            }
            throw $exception;
        }

        if ($method === 'logout') {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } elseif ($method === 'login' && Auth::check()) {
            $request->session()->regenerate();
            if ($response instanceof RedirectResponse) {
                $response->setTargetUrl(route('dashboard'));
            }
        }

        return $response;
    }

    private function clearSession(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
