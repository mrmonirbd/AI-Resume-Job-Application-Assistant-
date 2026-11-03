<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\LoginController;
use App\Services\CmpApiFactory;
use App\Services\LegacyLoginCompatibility;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as RoutingController;

abstract class Controller extends RoutingController
{
    use AuthorizesRequests;

    public function callAction(mixed $method, mixed $parameters): mixed
    {
        if ($this instanceof LoginController) {
            $this->cmp = app(CmpApiFactory::class)->make($this->concept);

            return app(LegacyLoginCompatibility::class)->handle($method, fn (): mixed => parent::callAction($method, $parameters));
        }

        return parent::callAction($method, $parameters);
    }
}
