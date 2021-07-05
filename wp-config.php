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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '{~1h=r:LaQ$)?<Ubp0vkBL^2eZrIq+<V?}RkzQHZ2Ep;5uV_Q%`Zs=b(HR*,s#&X' );
define( 'SECURE_AUTH_KEY',  ';}~3haGCIk&DYklf,d/W7edw_(1,B#u(,L(BH4B)I:x#`L4HUlwx:A~COF@&!8&H' );
define( 'LOGGED_IN_KEY',    '7Ih6zY[vNhU! m<7bhDNiB%G@=YEM)#43SWSy*?2>b_$:t bd2U-t|BZ;:Vy:qx-' );
define( 'NONCE_KEY',        'b{mmb5tPrVo6v:h;(h7jB=8^Ja%_ uq)=W!qkW#+PYmzZq,F<q1~N?0yiPrKXXyk' );
define( 'AUTH_SALT',        'f2En/6/b#;^s!(wS],nh2)9`(+s`UYP>,l6yjd&S74b)@X4Ff#CG6Z}J3x/j^;_5' );
define( 'SECURE_AUTH_SALT', '}k6-70SJAatOAo<B-$@|[CHfm84gmJN/O/0Q!I[M#rqP^D9XY?d;,?uiv}UGQ9h/' );
define( 'LOGGED_IN_SALT',   'o(CSHfD<nM!Ck4-WOWU([BU~R<6FrOnb1?ppFEoT7KMLpt]6)d>zGR$:Aaah,E}O' );
define( 'NONCE_SALT',       'eBBMne@ayrNXd^T<R.ay9x%-BZev><R*[1n+ld6oADK)Q^,aP;KOwo9%-w3CvSN;' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
