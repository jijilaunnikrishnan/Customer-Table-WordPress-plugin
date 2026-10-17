<?php

declare(strict_types=1);

/**
 * @var array<int, array<string, mixed>>|\WP_Error $users
 */
get_header();
?>
<!doctype html>
<html <?php language_attributes() ?>>
<head>
    <meta charset="<?php bloginfo('charset') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php esc_html_e('Syde Users Table', 'syde-users-table') ?></title>
    <?php wp_head() ?>
</head>
<body <?php body_class() ?>>
    <?php wp_body_open() ?>

    <main>
        <div class="syde-users-table-wrapper">
        <h2><?php esc_html_e('Syde Users Table', 'syde-users-table') ?></h2>

        <?php if (is_wp_error($users)) : ?>
            <p role="alert">
                <?php
                esc_html_e(
                    'Users could not be loaded. Please try again later.',
                    'syde-users-table'
                )
                ?>
            </p>
        <?php endif ?>
<div class="syde-users-table-scroll">
        <?php if (!is_wp_error($users)) : ?>
            <table class="syde-users-table">
                <thead>
                    <tr>
                        <th scope="col">
                            <?php esc_html_e('ID', 'syde-users-table') ?>
                        </th>
                        <th scope="col">
                            <?php esc_html_e('Name', 'syde-users-table') ?>
                        </th>
                        <th scope="col">
                            <?php esc_html_e('Username', 'syde-users-table') ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <?php $userId = absint($user['id'] ?? 0) ?>

                        <?php if ($userId < 1) : ?>
                            <?php continue ?>
                        <?php endif ?>

                        <tr>
                            <td>
                                <a
                                    href="#"
                                    data-user-id="<?= esc_attr((string) $userId) ?>"
                                >
                                    <?= esc_html((string) $userId) ?>
                                </a>
                            </td>
                            <td>
                                <a
                                    href="#"
                                    data-user-id="<?= esc_attr((string) $userId) ?>"
                                >
                                    <?= esc_html(
                                        (string) ($user['name'] ?? '')
                                    ) ?>
                                </a>
                            </td>
                            <td>
                                <a
                                    href="#"
                                    data-user-id="<?= esc_attr((string) $userId) ?>"
                                >
                                    <?= esc_html(
                                        (string) ($user['username'] ?? '')
                                    ) ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
                        </div>
            <section id="syde-user-details" aria-live="polite">
                <p>
                    <?php
                    esc_html_e(
                        'Select a user to view their details.',
                        'syde-users-table'
                    )
                    ?>
                </p>
            </section>
        <?php endif ?>
                        </div>
    </main>

    <?php wp_footer(); get_footer() ?>
</body>
</html>