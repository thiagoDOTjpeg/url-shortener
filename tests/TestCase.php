<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\WithAuthentication;

abstract class TestCase extends BaseTestCase
{
    use WithAuthentication;
}
