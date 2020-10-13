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

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'itcgroup_8ugu_nvflfjkgnqabdilfba' );

/** MySQL database username */
define( 'DB_USER', 'rxdbywhkxsoboxrp' );

/** MySQL database password */
define( 'DB_PASSWORD', 'e6MFuKdj0r4QFkY' );

/** MySQL hostname */
define( 'DB_HOST', 'itcgroup.ipagemysql.com' );

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
define('AUTH_KEY',         'S.LXK_k}mNp=lc0:,#B|R}&^DjHW-@Fsz}xOhr|.#+x`,^`#-BoqB)$cK|7SW@DI');
define('SECURE_AUTH_KEY',  '2lA#Ww*R,8FNcoVnu0F#Et]Rr>NLj<2bK7Y}?5({-]Q/f1u|]oy-t.Np>!P=+C~O');
define('LOGGED_IN_KEY',    'E++{ ~$k@#tv-tNDQiZYKByy<qqZ6V.^R67YTdsPYSnsU&hJ=coE@cGKiK3!?/^P');
define('NONCE_KEY',        'wtV3|?tz=b nZ)8~Vy){|M##!%eAz~tttMOW5$g;e^/f%gkErbn~]sJi`1-},_1b');
define('AUTH_SALT',        '^Ud~n`++URIPTu6SCV~<1!ucd$j0UZL!b.]bHy@W+_e0i:6bGV71F`I%GVdp-zQ ');
define('SECURE_AUTH_SALT', 'DA+Rn|%9G]t5g~*CE[:=NpB}+Uxn0LY.7rI;!~SKIecL.a2N l>U`+e&^`+;O0Dh');
define('LOGGED_IN_SALT',   'Zmq<F& ~aM,pXDy&2&ul|,dni..--M|4CJ-TQ^n2]&ilRJ*q[U[y+5EZef-,1-{W');
define('NONCE_SALT',       'TWh4^.]Qs(E;-T-M4LbvA {/n)mp Xq(Syg.@-.y<|+:F~{-US>`&V7S$A7uD&#l');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = '8ugu_';




/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
define('FS_METHOD', 'direct');
define( 'WP_MEMORY_LIMIT', '256M' );

define('CODE_SNIPPETS_SAFE_MODE', true);
