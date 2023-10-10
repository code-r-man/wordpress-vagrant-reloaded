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
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wordpress' );

/** Database password */
define( 'DB_PASSWORD', 'wordpress' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define( 'WP_DEBUG', true );

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
define( 'AUTH_KEY',          '@;y S yC~xLNh{lASosX$}OD25VlN$4N~5m_q[uR1 u~c!>yD=GhM5[8EyB9S# 5' );
define( 'SECURE_AUTH_KEY',   '=tY+$!VsWeA`6r&@%mMS)L#ol9?0cv5K?Km{ZwH4x u`7X2AS@GZ=P;,&p,2Y[Lx' );
define( 'LOGGED_IN_KEY',     '%@s_z<mBg)Ug/lrYxgCED]YW.ocplZMJD_P2CbryL~{qRBWqnKKH`9Kfm+I&*k%L' );
define( 'NONCE_KEY',         '`E~rvKH!`#mx0_@P{%pw<^|JA0WPIy:u=MwI1r3{lE?n1l+Off@Z@VMQxEKn)#lZ' );
define( 'AUTH_SALT',         'hsKERI3t8+[ M:kIKgFT<fPW91z##N_8et]nU?1.DekNoq@DBC`J!nBc Hk)K^.]' );
define( 'SECURE_AUTH_SALT',  '6S[5a!<.3 Jr!%fG%tRX;1#u[i,q/Oe(I7G_}~3BYvp${DD@uB32UQIg+*zNhK(Y' );
define( 'LOGGED_IN_SALT',    '64pg}kJZ@~HvB[o8,l2V457=jPMu#LJ}NJK<glN|mkq1ckzt8r)>%SnS^g>$p$z0' );
define( 'NONCE_SALT',        'SV:$8;Rbv5MH-9YG,R#aVbD6`I.$fG0Qs*_u+6y}wFJ:oKIlWlMIkXlqGACk+.6t' );
define( 'WP_CACHE_KEY_SALT', 'kgU?eFk~6!v CEo=Z?^tE&kZPNmgBebo5QFIsF+}G:sZX_bKUZE=:&JrGiVred8L' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

define( 'JETPACK_DEV_DEBUG', True );
define( 'WP_DEBUG', True );
define( 'FORCE_SSL_ADMIN', False );
define( 'SAVEQUERIES', False );

// Additional PHP code in the wp-config.php
// These lines are inserted by VCCW.
// You can place additional PHP code here!


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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
