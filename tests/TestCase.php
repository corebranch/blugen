<?php

namespace Blugen\Tests;

use Blugen\Container;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Container::reset();
    }
}
