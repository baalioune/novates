<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'noovaa' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Zwp85P2NRaIkTX49w72Z5ihW9SYauNeWxqb5qsPDbW6KldWHfhvIH6ocKtIhTOCH' );
define( 'SECURE_AUTH_KEY',  'nhNXkh6HIwDetQz5YTGpW3CIG9zfRvnvVWVa9Vc8M8hzJZccS3xaPU5cRxT4f3Kb' );
define( 'LOGGED_IN_KEY',    'WtjYcUd0bNlE322LJ7Yc14EeTQRhrX0dzRnQzXytkZWxhSFA8IHhehPYBlYgLsPl' );
define( 'NONCE_KEY',        'oUnXfEYt9s4K0l1W6HKmax0HMn2wI5CdMmwY6ikcVScuubGjCb2GaPSGcgoCv9K7' );
define( 'AUTH_SALT',        'kMAZh6mYUXEMPWusdpP4x8u2aYTrMe77c9JQwIYKz8f2XYPRbn5dNNgHuG7OxEsg' );
define( 'SECURE_AUTH_SALT', 'VUiA6Lj6QTTVsm9Jf55T8WMaeyV7PQgWGBq5EOSWheL8Bzg2ngoctKMvbBrpF5Eg' );
define( 'LOGGED_IN_SALT',   'gEM3P3CMIWJPrOBi56K4LOspsETCOC9FTLjGxzoM8ZWE0fSN9SokiEbNmiRsyf8O' );
define( 'NONCE_SALT',       'mktb8QbwSJZ3VvGRHSH7UEfbH2B2Gf2N2z6FUSxw1kBSIhwrpRWQ1f7Brn9sUMQr' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
