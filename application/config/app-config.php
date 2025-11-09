<?php

defined('BASEPATH') or exit('No direct script access allowed');

$envFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';

if (is_readable($envFile)) {
    if (!isset($_ENV) || !is_array($_ENV)) {
        $_ENV = [];
    }

    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($envLines as $envLine) {
        $envLine = trim($envLine);

        if ($envLine === '' || strpos($envLine, '#') === 0 || strpos($envLine, '=') === false) {
            continue;
        }

        list($name, $value) = array_map('trim', explode('=', $envLine, 2));

        if ($name === '') {
            continue;
        }

        $valueLength = strlen($value);

        if ($valueLength >= 2) {
            $firstChar = $value[0];
            $lastChar = $value[$valueLength - 1];

            if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

if (!function_exists('app_env')) {
    /**
     * Fetch environment variable value with sane fallbacks.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    function app_env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }

        if ($value === false && isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        }

        if ($value === false) {
            return $default;
        }

        return $value;
    }
}

/*
* --------------------------------------------------------------------------
* Base Site URL
* --------------------------------------------------------------------------
*
* URL to your CodeIgniter root. Typically this will be your base URL,
* WITH a trailing slash:
*
*   http://example.com/
*
* If this is not set then CodeIgniter will try guess the protocol, domain
* and path to your installation. However, you should always configure this
* explicitly and never rely on auto-guessing, especially in production
* environments.
*
*/
define('APP_BASE_URL', app_env('APP_BASE_URL', 'https://pcs.amrautism.com/'));

/*
* --------------------------------------------------------------------------
* Encryption Key
* IMPORTANT: Do not change this ever!
* --------------------------------------------------------------------------
*
* If you use the Encryption class, you must set an encryption key.
* See the user guide for more info.
*
* http://codeigniter.com/user_guide/libraries/encryption.html
*
* Auto added on install
*/
define('APP_ENC_KEY', app_env('APP_ENC_KEY', ''));

/**
 * Database Credentials
 * The hostname of your database server
 */
define('APP_DB_HOSTNAME', app_env('APP_DB_HOSTNAME', 'localhost'));

/**
 * The username used to connect to the database
 */
define('APP_DB_USERNAME', app_env('APP_DB_USERNAME', ''));

/**
 * The password used to connect to the database
 */
define('APP_DB_PASSWORD', app_env('APP_DB_PASSWORD', ''));

/**
 * The name of the database you want to connect to
 */
define('APP_DB_NAME', app_env('APP_DB_NAME', ''));

/**
 * @since  2.3.0
 * Database charset
 */
define('APP_DB_CHARSET', app_env('APP_DB_CHARSET', 'utf8mb4'));

/**
 * @since  2.3.0
 * Database collation
 */
define('APP_DB_COLLATION', app_env('APP_DB_COLLATION', 'utf8mb4_unicode_ci'));

/**
 *
 * Session handler driver
 * By default the database driver will be used.
 *
 * For files session use this config:
 * define('SESS_DRIVER', 'files');
 * define('SESS_SAVE_PATH', NULL);
 * In case you are having problem with the SESS_SAVE_PATH consult with your hosting provider to set "session.save_path" value to php.ini
 *
 */
define('SESS_DRIVER', 'database');
define('SESS_SAVE_PATH', 'sessions');
define('APP_SESSION_COOKIE_SAME_SITE', 'Lax');

/**
 * Enables CSRF Protection
 */
define('APP_CSRF_PROTECTION', true);
