<?php

declare(strict_types=1);

namespace Syde\UsersTable\Ajax;

use Syde\UsersTable\Api\UsersClient;

/**
 * Handles AJAX requests for one user's details.
 */
final class UserDetailsAction
{
    /**
     * Register AJAX actions for logged-in and logged-out visitors.
     */
    public function __construct(
    private readonly UsersClient $client
) {

    add_action('wp_ajax_syde_user_details', [$this, 'handle']);
    add_action('wp_ajax_nopriv_syde_user_details', [$this, 'handle']);
}

    /**
     * Validate the request, load the user, and return HTML as JSON.
     */
    public function handle(): void
    {
        check_ajax_referer(
            'syde_users_table_details',
            'nonce'
        );

        $userId = isset($_POST['user_id'])
            ? absint(wp_unslash($_POST['user_id']))
            : 0;

        $user = $this->client->user($userId);

        if (is_wp_error($user)) {
            wp_send_json_error(
                [
                    'message' => $user->get_error_message(),
                ],
                502
            );
        }

        wp_send_json_success([
            'html' => $this->render($user),
        ]);
    }

    /**
     * Build the HTML shown in the user details area.
     *
     * @param array<string, mixed> $user User data returned by the API.
     */
    private function render(array $user): string
    {
        $company = is_array($user['company'] ?? null)
            ? $user['company']
            : [];

        $address = is_array($user['address'] ?? null)
            ? $user['address']
            : [];

        ob_start();
        ?>
        <article class="syde-user-card">
            <h2><?= esc_html((string) ($user['name'] ?? '')) ?></h2>

            <dl>
                <dt><?php esc_html_e('Username', 'syde-users-table') ?></dt>
                <dd><?= esc_html((string) ($user['username'] ?? '')) ?></dd>

                <dt><?php esc_html_e('Email', 'syde-users-table') ?></dt>
                <dd><?= esc_html((string) ($user['email'] ?? '')) ?></dd>

                <dt><?php esc_html_e('Phone', 'syde-users-table') ?></dt>
                <dd><?= esc_html((string) ($user['phone'] ?? '')) ?></dd>

                <dt><?php esc_html_e('Website', 'syde-users-table') ?></dt>
                <dd><?= esc_html((string) ($user['website'] ?? '')) ?></dd>

                <dt><?php esc_html_e('Company', 'syde-users-table') ?></dt>
                <dd><?= esc_html((string) ($company['name'] ?? '')) ?></dd>

                <dt><?php esc_html_e('City', 'syde-users-table') ?></dt>
                <dd><?= esc_html((string) ($address['city'] ?? '')) ?></dd>
            </dl>
        </article>
        <?php

        return (string) ob_get_clean();
    }
}