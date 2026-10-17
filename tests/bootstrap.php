<?php

declare(strict_types=1);

/**
 * Load Composer autoloading for the test suite.
 */
require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Define the WordPress time constant used by the tests.
 */
if (!defined('MINUTE_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
}

/**
 * Provide the minimal WP_Error behavior needed by isolated unit tests.
 */
if (!class_exists('WP_Error')) {
    final class WP_Error
    {
        public function __construct(
            private readonly string $code = '',
            private readonly string $message = ''
        ) {
        }
    }
}
/*
if (!class_exists('WP_Error')) {
    final class WP_Error
    {
        /**
         * @param mixed $data
         */
/*
        public function __construct(
            private readonly string $code = '',
            private readonly string $message = '',
            private readonly mixed $data = null
        ) {
        }

        public function get_error_code(): string
        {
            return $this->code;
        }

        public function get_error_message(): string
        {
            return $this->message;
        }

        public function get_error_data(): mixed
        {
            return $this->data;
        }
    }
}*/