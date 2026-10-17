<?php
/**
 * Plugin Name: Syde Users Table
 * Description: Displays JSONPlaceholder users at a custom frontend endpoint.
 * Version: 1.0.0
 * Requires at least: 7.0
 * Requires PHP: 8.1
 * Author: Jijila
 * License: GPL-2.0-or-later
 * Text Domain: syde-users-table
 */

declare(strict_types=1);

namespace Syde\UsersTable;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load the Composer autoloader generated during installation.
 *
 * The plugin cannot boot without it because all production classes use
 * PSR-4 autoloading.
 */
$autoload = __DIR__ . '/vendor/autoload.php';

if (!is_readable($autoload)) {
    return;
}

require_once $autoload;

/**
 * Stop gracefully when dependencies are missing instead of causing a fatal
 * error in WordPress.
 */
if (!class_exists(Plugin::class)) {
    return;
}

/**
 * Create and boot the plugin's main composition root.
 */
$plugin = new Plugin(__FILE__);
$plugin->boot();

/**
 * Register and flush the custom rewrite rule during activation.
 */
register_activation_hook(__FILE__, [$plugin, 'activate']);

/**
 * Flush persisted rewrite rules during deactivation.
 */
register_deactivation_hook(__FILE__, [$plugin, 'deactivate']);