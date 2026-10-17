<?php

declare(strict_types=1);

namespace Syde\UsersTable\Tests;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Base test case that prepares the Brain Monkey test environment.
 */
abstract class MonkeyTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Set up Brain Monkey before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    /**
     * Clean up Brain Monkey after each test.
     */
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
}