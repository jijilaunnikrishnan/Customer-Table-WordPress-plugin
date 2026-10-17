<?php

declare(strict_types=1);

namespace Syde\UsersTable\Frontend;

use Syde\UsersTable\Api\UsersClient;

/**
 * Registers and renders the custom users endpoint.
 */
final class Endpoint
{
    /**
     * Query variable used to identify the custom endpoint request.
     */
    private const QUERY_VAR = 'syde_users_table';

    /**
     * Create the endpoint with the API client it needs.
     */
    public function __construct(
        private readonly UsersClient $usersClient
    ) {
    }

    /**
     * Register the endpoint hooks with WordPress.
     */
    public function register(): void
    {
        add_action('init', [$this, 'registerRewriteRule']);
        add_filter('query_vars', [$this, 'registerQueryVar']);
        add_action('template_redirect', [$this, 'render']);
    }

    /**
     * Register the custom frontend URL.
     */
    public function registerRewriteRule(): void
    {
        add_rewrite_rule(
            '^syde-users-table/?$',
            'index.php?' . self::QUERY_VAR . '=1',
            'top'
        );
    }

    /**
     * Allow WordPress to recognize the custom query variable.
     *
     * @param string[] $queryVars Existing public query variables.
     *
     * @return string[]
     */
    public function registerQueryVar(array $queryVars): array
    {
        $queryVars[] = self::QUERY_VAR;

        return $queryVars;
    }

    /**
     * Render the users page when the custom endpoint is requested.
     */
    public function render(): void
    {
        if ((string) get_query_var(self::QUERY_VAR) !== '1') {
            return;
        }

        $users = $this->usersClient->users();

        status_header(200);
        nocache_headers();

        $template = dirname(__DIR__, 2) . '/templates/users-page.php';

        if (!is_readable($template)) {
            status_header(500);

            return;
        }

        $this->enqueueAssets();

        require $template;

        exit;
    }

    /**
     * Load the frontend script and stylesheet for the endpoint page.
     */
    private function enqueueAssets(): void
    {
        $pluginFile = dirname(__DIR__, 2) . '/syde-users-table.php';

        wp_enqueue_script(
            'syde-users-table',
            plugins_url(
                'resources/js/users.js',
                $pluginFile
            ),
            [],
            '1.0.0',
            true
        );

        wp_enqueue_style(
            'syde-users-table',
            plugins_url(
                'resources/css/users.css',
                $pluginFile
            ),
            [],
            '1.0.0'
        );

        wp_localize_script(
            'syde-users-table',
            'sydeUsersTable',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(
                    'syde_users_table_details'
                ),
            ]
        );
    }
}