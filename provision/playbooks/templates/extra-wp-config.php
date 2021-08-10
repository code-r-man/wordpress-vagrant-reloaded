define( 'JETPACK_DEV_DEBUG', {{ settings.wp_debug }} );
define( 'WP_DEBUG', {{ settings.wp_debug }} );
define( 'FORCE_SSL_ADMIN', {{ settings.force_ssl_admin }} );
define( 'SAVEQUERIES', {{ settings.savequeries }} );

{{ settings.extra_wp_config }}
