<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
// force WordPress to use the “dev” versions of core CSS and JavaScript files.
define( 'SCRIPT_DEBUG', false );
// database queries to an array and that array can be displayed to help analyze those queries
// The array is stored in the global $wpdb->queries.
define( 'SAVEQUERIES', false );

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'homestead' );

/** MySQL database username */
define( 'DB_USER', 'homestead' );

/** MySQL database password */
define( 'DB_PASSWORD', 'secret' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'ci-k-<2yO*JbAsr/0Q7wJH><Vx,n/|Jq=Um]FHN~i%[E<4uC,kux#!Y;C@`NB&<%' );
define( 'SECURE_AUTH_KEY',   '!B.YmYpkoA,N~]<71p/{Pv`XtF<4yj.`K>.Lw3J||9eD>,{l`mXZ3z0w|u}j~X;Q' );
define( 'LOGGED_IN_KEY',     'prD#t..]5Y~AsP*>j.J@%]UeKEh0nYwC],My)>96e-czfF+Q1gt`jHr4S~.(5RK~' );
define( 'NONCE_KEY',         '67J`sN;//A+@4oRXYb1L).U!x_^=mH;%b)xtfxFwEhznO Ojp!?JAdzq>~2s*U?v' );
define( 'AUTH_SALT',         'tY%u8z(J%@:7(^)ZnrYBF`xG$Nl9D1v5(=W?x!Z#D31qUay(+N3FfqNb ._+Ow8X' );
define( 'SECURE_AUTH_SALT',  ']W*Pj=6|N)$XSdhkp?*tL$xy..zN`d:*.J%Us1G8*U:S@xy2q8w7#-k+>!&}Wv|y' );
define( 'LOGGED_IN_SALT',    '1G-}`Qk/P8#?$?,$3osyI~eLH^vu,j5R5m8a-MT+T%NJM=C(wXx##*aX45cl#cE_' );
define( 'NONCE_SALT',        ']j4$|#Qva%g[OpE.GQ0.Z*qv>2VNWA;c&aYdc>$cIW@nR0/adZ_^@ijhgQ.Y~t e' );
define( 'WP_CACHE_KEY_SALT', 'u@:`Zx<cxc-byM$-lx+SV,X<U$q!P#Fh9AQQ`KC i7G{<KyZu~SPgi#C8t6tlPUn' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
