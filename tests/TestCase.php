<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (blank($this->app['config']->get('app.key'))) {
            $this->app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        }
    }
}
