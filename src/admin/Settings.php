<?php

declare(strict_types=1);

namespace Syde\UsersTable\Admin;

/**
 * Registers and renders the plugin settings page.
 */
final class Settings
{
    /**
     * Option name used to store the API base URL.
     */
    public const OPTION_API_URL = 'syde_users_table_api_url';

    /**
     * Option name used to store the maximum number of users.
     */
    public const OPTION_USER_LIMIT = 'syde_users_table_user_limit';

    /**
     * Default API used when no valid custom URL is available.
     */
    public const DEFAULT_API_URL = 'https://jsonplaceholder.typicode.com';

    /**
     * Default number of users shown on the frontend.
     */
    public const DEFAULT_USER_LIMIT = 10;

    /**
     * Settings group used by the WordPress Settings API.
     */
    private const SETTINGS_GROUP = 'syde_users_table';

    /**
     * Slug used for the settings page.
     */
    private const PAGE_SLUG = 'syde-users-table';

    /**
     * Identifier for the settings section.
     */
    private const SECTION_ID = 'syde_users_table_api_settings';

    /**
     * Register the settings page and settings fields with WordPress.
     */
    public function register(): void
    {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    /**
     * Add the plugin page under the WordPress Settings menu.
     */
    public function addSettingsPage(): void
    {
        add_options_page(
            __('Syde Users Table', 'syde-users-table'),
            __('Syde Users Table', 'syde-users-table'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderPage']
        );
    }

    /**
     * Register the plugin options, section, and fields.
     */
    public function registerSettings(): void
    {
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_API_URL,
            [
                'type' => 'string',
                'default' => self::DEFAULT_API_URL,
                'sanitize_callback' => [$this, 'sanitizeApiUrl'],
            ]
        );

        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_USER_LIMIT,
            [
                'type' => 'integer',
                'default' => self::DEFAULT_USER_LIMIT,
                'sanitize_callback' => [$this, 'sanitizeUserLimit'],
            ]
        );

        add_settings_section(
            self::SECTION_ID,
            __('API settings', 'syde-users-table'),
            [$this, 'renderSection'],
            self::PAGE_SLUG
        );

        add_settings_field(
            self::OPTION_API_URL,
            __('API base URL', 'syde-users-table'),
            [$this, 'renderApiUrlField'],
            self::PAGE_SLUG,
            self::SECTION_ID
        );

        add_settings_field(
            self::OPTION_USER_LIMIT,
            __('User limit', 'syde-users-table'),
            [$this, 'renderUserLimitField'],
            self::PAGE_SLUG,
            self::SECTION_ID
        );
    }

    /**
     * Return a valid API URL or fall back to the default URL.
     */
    public function sanitizeApiUrl(mixed $value): string
    {
        $url = untrailingslashit(
            esc_url_raw((string) $value)
        );

        if ($url === '') {
            return self::DEFAULT_API_URL;
        }

        return $url;
    }

    /**
     * Return a user limit between 1 and 100.
     */
    public function sanitizeUserLimit(mixed $value): int
    {
        $limit = absint($value);

        if ($limit < 1) {
            return self::DEFAULT_USER_LIMIT;
        }

        return min($limit, 100);
    }

    /**
     * Show a short explanation above the settings fields.
     */
    public function renderSection(): void
    {
        ?>
        <p>
            <?php
            esc_html_e(
                'Configure the users API and the maximum number of users shown.',
                'syde-users-table'
            )
            ?>
        </p>
        <?php
    }

    /**
     * Render the API URL field.
     */
    public function renderApiUrlField(): void
    {
        $value = get_option(
            self::OPTION_API_URL,
            self::DEFAULT_API_URL
        );

        ?>
        <input
            type="url"
            class="regular-text"
            name="<?= esc_attr(self::OPTION_API_URL) ?>"
            value="<?= esc_attr((string) $value) ?>"
            placeholder="<?= esc_attr(self::DEFAULT_API_URL) ?>"
        >

        <p class="description">
            <?php
            esc_html_e(
                'If this URL is empty, unavailable, returns an error, or returns invalid JSON, the default JSONPlaceholder API will be used.',
                'syde-users-table'
            )
            ?>
        </p>
        <?php
    }

    /**
     * Render the user limit field.
     */
    public function renderUserLimitField(): void
    {
        $value = get_option(
            self::OPTION_USER_LIMIT,
            self::DEFAULT_USER_LIMIT
        );

        ?>
        <input
            type="number"
            class="small-text"
            name="<?= esc_attr(self::OPTION_USER_LIMIT) ?>"
            value="<?= esc_attr((string) $value) ?>"
            min="1"
            max="100"
            step="1"
        >

        <p class="description">
            <?php
            esc_html_e(
                'Choose how many users should be displayed. Maximum: 100.',
                'syde-users-table'
            )
            ?>
        </p>
        <?php
    }

    /**
     * Render the complete plugin settings page.
     */
    public function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Syde Users Table Settings', 'syde-users-table') ?>
            </h1>

            <form action="options.php" method="post">
                <?php
                settings_fields(self::SETTINGS_GROUP);
                do_settings_sections(self::PAGE_SLUG);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}