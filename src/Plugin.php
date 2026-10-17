<?php

declare(strict_types=1);

namespace Syde\UsersTable;

use Syde\UsersTable\Admin\Settings;
use Syde\UsersTable\Ajax\UserDetailsAction;
use Syde\UsersTable\Api\UsersClient;
use Syde\UsersTable\Frontend\Endpoint;

/**
 * Starts and connects all parts of the plugin.
 */
final class Plugin
{
    /**
     * Register the plugin features with WordPress.
     */
    public function boot(): void
    {
        // Register the plugin settings page.
        $settings = new Settings();
        $settings->register();

        // Create one API client to be shared across the plugin.
        $usersClient = new UsersClient();

        // Register the custom frontend endpoint.
        (new Endpoint($usersClient))->register();

        // Register the AJAX action for loading user details.
        new UserDetailsAction($usersClient);
    }

    /**
     * Register the custom rewrite rule and save it.
     */
    public function activate(): void
    {
        $endpoint = new Endpoint(
            new UsersClient()
        );

        $endpoint->registerRewriteRule();

        flush_rewrite_rules();
    }

    /**
     * Remove the stored rewrite rules when the plugin is disabled.
     */
    public function deactivate(): void
    {
        flush_rewrite_rules();
    }
}