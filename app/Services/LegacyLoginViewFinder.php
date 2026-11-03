<?php

namespace App\Services;

use Illuminate\View\FileViewFinder;

class LegacyLoginViewFinder extends FileViewFinder
{
    public function find(mixed $name): string
    {
        if (is_string($name) && str_starts_with($name, 'templates.') && str_ends_with($name, '.auth.login')) {
            $name = 'auth.login';
        }

        return parent::find($name);
    }
}
