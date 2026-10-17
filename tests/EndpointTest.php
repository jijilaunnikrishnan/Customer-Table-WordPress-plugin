<?php

declare(strict_types=1);

namespace Syde\UsersTable\Tests;

use Syde\UsersTable\Api\UsersClient;
use Syde\UsersTable\Frontend\Endpoint;

/**
 * Tests the custom frontend endpoint.
 */
final class EndpointTest extends MonkeyTestCase
{
    /**
     * Verify that the custom query variable is registered.
     */
    public function testRegistersQueryVariable(): void
    {
        $endpoint = new Endpoint(new UsersClient());

        $result = $endpoint->registerQueryVar(['existing_var']);

        self::assertSame(
            ['existing_var', 'syde_users_table'],
            $result
        );
    }
}