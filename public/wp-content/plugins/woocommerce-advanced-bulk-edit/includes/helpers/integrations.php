<?php

if ( ! defined( 'ABSPATH' ) ) exit;

define('WCABE_179_INTEG_SITE_WIDE_OPS', 'wcabe-179-site-wide-basic-operations');
define('WCABE_187_INTEG_LIMIT_SHOP_MANAGER_VIEW', 'wcabe-187-limit-shop-manager-view-and-actions');
define('WCABE_195_INTEG_SYNCING_3RD_PARTY_PLUGIN_MAC_WITH_WCABE', 'wcabe-195-integ-syncing-3rd-party-plugin-mac-with-wcabe');

$integrations = [
	WCABE_179_INTEG_SITE_WIDE_OPS => [
		'files_to_load' => [
			WCABE_PLUGIN_PATH.'/integrations/site-wide-ops.php',
		],
	],
	WCABE_187_INTEG_LIMIT_SHOP_MANAGER_VIEW => [
		'files_to_load' => [
			WCABE_PLUGIN_PATH.'/integrations/wcabe-187-limit-shop-manager-view-and-actions.php',
		],
	],
];

function wcabe_load_integration($integration_key): bool {
	global $integrations;
	if (isset($integrations[$integration_key])) {
		foreach ($integrations[$integration_key]['files_to_load'] as $file) {
			if (file_exists($file)) {
				require_once $file;
			} else {
				return false;
			}
		}
		return true;
	}
	return false;
}
