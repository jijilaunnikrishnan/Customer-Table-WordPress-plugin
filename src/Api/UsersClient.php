<?php

declare(strict_types=1);

namespace Syde\UsersTable\Api;

use Syde\UsersTable\Admin\Settings;
use WP_Error;

/**
 * Fetches users from the configured API and caches successful responses.
 *
 * If the configured API fails, the client retries once with the default
 * JSONPlaceholder API.
 */
final class UsersClient
{
    /**
     * Keep successful API responses for 15 minutes.
     */
    private const CACHE_TTL = 15 * MINUTE_IN_SECONDS;

    /**
     * Return the configured number of users.
     *
     * Cached data is returned when available. Otherwise, the users are fetched
     * from the API, limited according to the plugin setting, and cached.
     *
     * @return array<int, array<string, mixed>>|WP_Error
     */
    public function users(): array|WP_Error
    {
        $cacheKey = $this->usersCacheKey();
        $cachedUsers = get_transient($cacheKey);

        if (is_array($cachedUsers)) {
            return $cachedUsers;
        }

        $users = $this->request('/users');

        if (is_wp_error($users)) {
            return $users;
        }

        $users = array_slice(
            $users,
            0,
            $this->userLimit()
        );

        set_transient(
            $cacheKey,
            $users,
            self::CACHE_TTL
        );

        return $users;
    }

    /**
     * Return one user by ID.
     *
     * Cached data is returned when available. Otherwise, the user is fetched
     * from the API and cached.
     *
     * @return array<string, mixed>|WP_Error
     */
    public function user(int $userId): array|WP_Error
    {
        if ($userId < 1) {
            return new WP_Error(
                'invalid_user_id',
                __('The user ID is invalid.', 'syde-users-table')
            );
        }

        $cacheKey = $this->userCacheKey($userId);
        $cachedUser = get_transient($cacheKey);

        if (is_array($cachedUser)) {
            return $cachedUser;
        }

        $user = $this->request('/users/' . $userId);

        if (is_wp_error($user)) {
            return $user;
        }

        set_transient(
            $cacheKey,
            $user,
            self::CACHE_TTL
        );

        return $user;
    }

    /**
     * Request data from the configured API.
     *
     * If the configured API fails, retry once with the default API.
     *
     * @return array<mixed>|WP_Error
     */
    private function request(string $endpoint): array|WP_Error
    {
        $configuredUrl = $this->baseUrl();

        $result = $this->requestFromUrl(
            $configuredUrl,
            $endpoint
        );

        if (!is_wp_error($result)) {
            return $result;
        }

        if ($configuredUrl === Settings::DEFAULT_API_URL) {
            return $result;
        }

        return $this->requestFromUrl(
            Settings::DEFAULT_API_URL,
            $endpoint
        );
    }

    /**
     * Send one HTTP request and validate the response.
     *
     * @return array<mixed>|WP_Error
     */
    private function requestFromUrl(
        string $baseUrl,
        string $endpoint
    ): array|WP_Error {

        $response = wp_remote_get(
            untrailingslashit($baseUrl) . $endpoint,
            [
                'timeout' => 10,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $statusCode = wp_remote_retrieve_response_code($response);

        if ($statusCode !== 200) {
            return new WP_Error(
                'external_api_error',
                sprintf(
                    /* translators: %d is the HTTP response status code. */
                    __(
                        'The users API returned HTTP status %d.',
                        'syde-users-table'
                    ),
                    $statusCode
                )
            );
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (
            json_last_error() !== JSON_ERROR_NONE
            || !is_array($data)
        ) {
            return new WP_Error(
                'invalid_api_response',
                __(
                    'The users API returned an invalid JSON response.',
                    'syde-users-table'
                )
            );
        }

        return $data;
    }

    /**
     * Return the configured API URL or the default URL.
     */
    private function baseUrl(): string
    {
        $url = (string) get_option(
            Settings::OPTION_API_URL,
            Settings::DEFAULT_API_URL
        );

        if ($url === '') {
            return Settings::DEFAULT_API_URL;
        }

        return untrailingslashit($url);
    }

    /**
     * Return the configured user limit within the allowed range.
     */
    private function userLimit(): int
    {
        $limit = absint(
            get_option(
                Settings::OPTION_USER_LIMIT,
                Settings::DEFAULT_USER_LIMIT
            )
        );

        if ($limit < 1) {
            return Settings::DEFAULT_USER_LIMIT;
        }

        return min($limit, 100);
    }

    /**
     * Build a cache key that changes when the API URL or user limit changes.
     */
    private function usersCacheKey(): string
    {
        return 'syde_users_table_all_' . md5(
            $this->baseUrl() . '|' . (string) $this->userLimit()
        );
    }

    /**
     * Build a cache key for one user and the current API URL.
     */
    private function userCacheKey(int $userId): string
    {
        return 'syde_users_table_user_' . md5(
            $this->baseUrl() . '|' . (string) $userId
        );
    }
}