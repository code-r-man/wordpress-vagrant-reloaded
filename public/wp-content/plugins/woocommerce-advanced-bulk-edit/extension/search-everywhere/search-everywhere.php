<?php
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class W3ExABulkEdit_Ext_SearchEverywhere
{
	public function init()
	{
		add_filter('wcabe_filter_selection_manager_before_content', array($this, 'search_everywhere_block'));
		add_action( 'admin_enqueue_scripts', function () {
			$url_path = plugin_dir_url(__FILE__);
			wp_enqueue_script('w3exabe-ext-search-everywhere-main-js',$url_path.'js/main.js' );
		} );
	}

	public function search_everywhere_block($content)
	{
		return '
			<strong>'. __( "Search Everywhere", "woocommerce-advbulkedit") . ':</strong>
			<input type="search" id="text-search-everywhere"> <button id="btn-search-everywhere" class="button-wcabe">Search</button> <input type="checkbox" id="check-search-everywhere-ignore-case"> Ignore case
		';
	}
}

(new W3ExABulkEdit_Ext_SearchEverywhere())->init();
