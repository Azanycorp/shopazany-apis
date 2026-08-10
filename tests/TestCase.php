<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Shared Auth service instance (set by Pest's beforeEach in unit tests).
     */
    public mixed $auth = null;
}
