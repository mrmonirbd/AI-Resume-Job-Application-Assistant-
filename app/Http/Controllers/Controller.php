<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

abstract class Controller extends RoutingController
{
    use AuthorizesRequests;

    public function callAction(mixed $method, mixed $parameters): mixed
    {
        $response = $this instanceof LoginController
            ? DB::transaction(fn () => parent::callAction($method, $parameters))
            : parent::callAction($method, $parameters);
        if ($this instanceof LoginController) {
            if ($method === 'logout') {
                request()->session()->invalidate();
                request()->session()->regenerateToken();
            } elseif ($method === 'login' && Auth::check()) {
                request()->session()->regenerate();
                if ($response instanceof RedirectResponse) {
                    $response->setTargetUrl(route('dashboard'));
                }
            }
        }

        return $response;
    }
}
