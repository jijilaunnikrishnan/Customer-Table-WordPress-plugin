<?php

declare(strict_types=1);

namespace Syde\UsersTable\Tests;

use Brain\Monkey\Functions;
use Syde\UsersTable\Admin\Settings;
use Syde\UsersTable\Api\UsersClient;
use WP_Error;

/**
 * Tests API requests, caching, limits, and fallback behavior.
 */
final class UsersClientTest extends MonkeyTestCase
{
    /**
     * Verify that a cached user is returned without an HTTP request.
     */
    public function testUserReturnsCachedUserWithoutHttpRequest(): void
    {
        $this->mockDefaultOptions();

        $cachedUser = [
            'id' => 1,
            'name' => 'Leanne Graham',
        ];

        Functions\expect('get_transient')
            ->once()
            ->with($this->userCacheKey(1))
            ->andReturn($cachedUser);

        Functions\expect('wp_remote_get')->never();

        self::assertSame(
            $cachedUser,
            (new UsersClient())->user(1)
        );
    }

    /**
     * Verify that a successful user response is cached.
     */
    public function testUserCachesSuccessfulResponse(): void
    {
        $this->mockDefaultOptions();

        $user = [
            'id' => 1,
            'name' => 'Leanne Graham',
        ];

        Functions\expect('get_transient')
            ->once()
            ->with($this->userCacheKey(1))
            ->andReturn(false);

        $this->expectSuccessfulRequest(
            Settings::DEFAULT_API_URL . '/users/1',
            $user,
            3
        );

        Functions\expect('set_transient')
            ->once()
            ->with(
                $this->userCacheKey(1),
                $user,
                15 * MINUTE_IN_SECONDS
            )
            ->andReturn(true);

        self::assertSame(
            $user,
            (new UsersClient())->user(1)
        );
    }

    /**
     * Verify that the configured user limit is applied before caching.
     */
    public function testUsersAppliesConfiguredLimit(): void
    {
        $this->mockOptions(
            Settings::DEFAULT_API_URL,
            2
        );

        $users = [
            ['id' => 1, 'name' => 'User One'],
            ['id' => 2, 'name' => 'User Two'],
            ['id' => 3, 'name' => 'User Three'],
        ];

        $limitedUsers = array_slice($users, 0, 2);
        $cacheKey = $this->usersCacheKey(
            Settings::DEFAULT_API_URL,
            2
        );

        Functions\expect('get_transient')
            ->once()
            ->with($cacheKey)
            ->andReturn(false);

        $this->expectSuccessfulRequest(
            Settings::DEFAULT_API_URL . '/users',
            $users,
            3
        );

        Functions\expect('set_transient')
            ->once()
            ->with(
                $cacheKey,
                $limitedUsers,
                15 * MINUTE_IN_SECONDS
            )
            ->andReturn(true);

        self::assertSame(
            $limitedUsers,
            (new UsersClient())->users()
        );
    }

    /**
     * Verify that a failed custom API request uses the default API.
     */
    public function testUsersFallsBackToDefaultApi(): void
    {
        $customUrl = 'https://broken.example';
        $users = [
            ['id' => 1, 'name' => 'User One'],
        ];

        $this->mockOptions(
            $customUrl,
            Settings::DEFAULT_USER_LIMIT
        );

        $cacheKey = $this->usersCacheKey(
            $customUrl,
            Settings::DEFAULT_USER_LIMIT
        );

        Functions\expect('get_transient')
            ->once()
            ->with($cacheKey)
            ->andReturn(false);

        $this->expectFailedThenSuccessfulRequest(
            $customUrl,
            $users
        );

        Functions\expect('set_transient')
            ->once()
            ->with(
                $cacheKey,
                $users,
                15 * MINUTE_IN_SECONDS
            )
            ->andReturn(true);

        self::assertSame(
            $users,
            (new UsersClient())->users()
        );
    }

    /**
     * Mock the plugin settings with their default values.
     */
    private function mockDefaultOptions(): void
    {
        $this->mockOptions(
            Settings::DEFAULT_API_URL,
            Settings::DEFAULT_USER_LIMIT
        );
    }

    /**
     * Mock the configured API URL and user limit.
     */
    private function mockOptions(
        string $apiUrl,
        int $userLimit
    ): void {

        Functions\when('get_option')
            ->alias(
                static function (
                    string $option,
                    mixed $default
                ) use ($apiUrl, $userLimit): mixed {
                    return match ($option) {
                        Settings::OPTION_API_URL => $apiUrl,
                        Settings::OPTION_USER_LIMIT => $userLimit,
                        default => $default,
                    };
                }
            );
    }

    /**
     * Mock one successful API response.
     *
     * @param array<mixed> $data API response data.
     */
    private function expectSuccessfulRequest(
        string $url,
        array $data,
        int $errorCheckCount
    ): void {

        Functions\expect('wp_remote_get')
            ->once()
            ->with($url, $this->requestArguments())
            ->andReturn([
                'body' => json_encode($data),
            ]);

        Functions\expect('is_wp_error')
            ->times($errorCheckCount)
            ->andReturn(false);

        Functions\expect('wp_remote_retrieve_response_code')
            ->once()
            ->andReturn(200);

        Functions\expect('wp_remote_retrieve_body')
            ->once()
            ->andReturn(json_encode($data));
    }

    /**
     * Mock a failed custom request followed by a successful fallback.
     *
     * @param array<mixed> $users User data returned by the fallback API.
     */
    private function expectFailedThenSuccessfulRequest(
        string $customUrl,
        array $users
    ): void {

        $error = new WP_Error(
            'connection_failed',
            'The custom API is unavailable.'
        );

        Functions\expect('wp_remote_get')
            ->once()
            ->with(
                $customUrl . '/users',
                $this->requestArguments()
            )
            ->andReturn($error)
            ->ordered();

        Functions\expect('wp_remote_get')
            ->once()
            ->with(
                Settings::DEFAULT_API_URL . '/users',
                $this->requestArguments()
            )
            ->andReturn([
                'body' => json_encode($users),
            ])
            ->ordered();

        Functions\expect('is_wp_error')
            ->times(4)
            ->andReturn(true, true, false, false);

        Functions\expect('wp_remote_retrieve_response_code')
            ->once()
            ->andReturn(200);

        Functions\expect('wp_remote_retrieve_body')
            ->once()
            ->andReturn(json_encode($users));
    }

    /**
     * Return the HTTP arguments expected by the API client.
     *
     * @return array<string, mixed>
     */
    private function requestArguments(): array
    {
        return [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];
    }

    /**
     * Build the expected users-list cache key.
     */
    private function usersCacheKey(
        string $apiUrl,
        int $limit
    ): string {

        return 'syde_users_table_all_' . md5(
            $apiUrl . '|' . (string) $limit
        );
    }

    /**
     * Build the expected cache key for one user.
     */
    private function userCacheKey(int $userId): string
    {
        return 'syde_users_table_user_' . md5(
            Settings::DEFAULT_API_URL . '|' . (string) $userId
        );
    }
}