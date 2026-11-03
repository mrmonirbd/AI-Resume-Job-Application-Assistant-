<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__.'/CmpCurlFake.php';

        $this->withoutVite();
    }
}
