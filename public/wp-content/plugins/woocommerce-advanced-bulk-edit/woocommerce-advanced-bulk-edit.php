<?php
/*
Plugin Name: WooCommerce Advanced Bulk Edit
Plugin URI: https://wpmelon.com
Description: Edit your products both individually or in bulk.
Author: George Iron & Yas G.
Author URI: https://codecanyon.net/user/georgeiron/portfolio
Version: 5.2
Text Domain: woocommerce-advbulkedit
WC requires at least: 5.0
WC tested up to: 7.6.0
*/

defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

define('WCABE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WCABE_VERSION', '5.2');
define('WCABE_SITE_URL', 'https://wpmelon.com');
define('WCABE_SUPPORT_URL', 'https://wpmelon.com/r/support');

require_once WCABE_PLUGIN_PATH . 'includes/notices/rate.php';
require_once WCABE_PLUGIN_PATH . 'includes/notices/getting-started.php';
require_once WCABE_PLUGIN_PATH . 'includes/helpers/general.php';
require_once WCABE_PLUGIN_PATH . 'includes/helpers/products.php';
require_once WCABE_PLUGIN_PATH . 'includes/helpers/integrations.php';

class W3ExAdvancedBulkEditMain {

	private static $ins = null;
	private static $idCounter = 0;
	public static $table_name = "";
	public static $cache_key;
	public static $cache_expire;
	public static $cache_allowed;
	const PLUGIN_SLUG = 'advanced_bulk_edit';

	public static function init()
	{
		$settings = get_option('w3exabe_settings');
		if (!defined('WP_ALLOW_MULTISITE') || !WP_ALLOW_MULTISITE) {
			require (ABSPATH . WPINC . '/pluggable.php');
			$roles = [];
			if( is_user_logged_in() ) { // check if there is a logged in user
				$user = wp_get_current_user(); // getting & setting the current user
				$roles = ( array ) $user->roles; // obtaining the role
			}
			if(!in_array('administrator', $roles)) {
				define('WCABE_CANT_ACCESS_ADMIN_PLUGIN_SETTINGS', true);
				if (isset($settings['setting_enable_admin_only_visible']) && $settings['setting_enable_admin_only_visible'] == 1) {
					return;
				}
			}
		}



		if (!defined('CONCATENATE_SCRIPTS')) {
			define('CONCATENATE_SCRIPTS', false);
		}

		//if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
		//{//don't take resources if woocommerce is not running(not working on some installations, so beat it)
		//global $wpdb;
		add_action('admin_menu', array(self::instance(), '_setup'));
		add_action('wp_ajax_wpmelon_adv_bulk_edit',  array(__CLASS__, 'ajax_request'));
		add_action('wp_ajax_wpmelon_wcabe',  array(__CLASS__, 'new_ajax_request'));
		//add action to load my plugin files
		add_action('plugins_loaded', array(self::instance(), '_load_translations'));
		add_action( 'admin_init', array(__CLASS__, 'wcabe_settings_form_submission') );
		//WCABE_Notice_GettingStarted::init();
		//WCABE_Notice_Rate::init();
		//}

		self::load_extensions();

		if (file_exists( __DIR__.'/integrations/acf-custom-fields-customizations-for-viktor.php')) {
			require_once('integrations/acf-custom-fields-customizations-for-viktor.php');
			W3ExABulkEdit_Integ_ACFCustomFieldsCustomizationsForViktor::init();
		}
		
		self::$cache_key = 'wcabe_custom_update';
		self::$cache_allowed = true;
		self::$cache_expire = 3*DAY_IN_SECONDS;
		if (wp_get_environment_type() == 'local') {
			self::$cache_expire = 5;
		}
		add_filter( 'plugins_api', array( __CLASS__, 'info' ), 20, 3 );
		add_filter( 'site_transient_update_plugins', array( __CLASS__, 'update' ) );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'purge' ), 10, 2 );
		add_action( 'in_plugin_update_message-woocommerce-advanced-bulk-edit/woocommerce-advanced-bulk-edit.php', array(__CLASS__, 'wcabe_update_message'), 10, 2 );
	}
	
	public static function request(){
		//delete_transient( self::$cache_key );
		$remote = get_transient( self::$cache_key );
		
		if( false === $remote || ! self::$cache_allowed ) {
			
			$settings = get_option('w3exabe_settings');
			$wcabe_license_key = $settings['license_key'] ?? '';
			
			$remote = wp_remote_get(
				add_query_arg(
					array(
						'license_key' => urlencode( $wcabe_license_key )
					),
					'https://wpmelon.com/r/wcabe-update-url',
				),
				array(
					'timeout' => 10,
					'headers' => array(
						'Accept' => 'application/json'
					)
				)
			);
			
			if(
				is_wp_error( $remote )
				|| 200 !== wp_remote_retrieve_response_code( $remote )
				|| empty( wp_remote_retrieve_body( $remote ) )
			) {
				return false;
			}
			
			set_transient( self::$cache_key, $remote, self::$cache_expire );
			
		}
		
		$remote = json_decode( wp_remote_retrieve_body( $remote ) );
		
		return $remote;
		
	}
	
	public static function info( $res, $action, $args ) {
		
		// print_r( $action );
		// print_r( $args );
		
		// do nothing if you're not getting plugin information right now
		if( 'plugin_information' !== $action ) {
			return $res;
		}
		
		// do nothing if it is not our plugin
		if( self::PLUGIN_SLUG !== $args->slug ) {
			return $res;
		}
		
		// get updates
		$remote = self::request();
		
		if( ! $remote ) {
			return $res;
		}
		
		$res = new stdClass();
		
		$res->name = $remote->name;
		$res->slug = $remote->slug;
		$res->version = $remote->version;
		$res->tested = $remote->tested;
		$res->requires = $remote->requires;
		$res->author = $remote->author;
		$res->author_profile = $remote->author_profile;
		$res->download_link = $remote->download_url;
		$res->trunk = $remote->download_url;
		$res->requires_php = $remote->requires_php;
		$res->last_updated = $remote->last_updated;
		$res->rating = $remote->rating;
		$res->num_ratings = $remote->num_ratings;
		
		$res->sections = [];
		foreach ($remote->sections as $section_title => $section_content ) {
			$res->sections[$section_title] = $section_content;
		}
		
		if( ! empty( $remote->banners ) ) {
			$res->banners = array(
				'low' => $remote->banners->low,
				'high' => $remote->banners->high
			);
		}
		
		return $res;
		
	}
	
	public static function update( $transient ) {
		
		if ( empty($transient->checked ) ) {
			return $transient;
		}
		
		$remote = self::request();
		
		if(
			$remote
			&& version_compare( WCABE_VERSION, $remote->version, '<' )
			&& version_compare( $remote->requires, get_bloginfo( 'version' ), '<=' )
			&& version_compare( $remote->requires_php, PHP_VERSION, '<' )
		) {
			$res = new stdClass();
			$res->slug = self::PLUGIN_SLUG;
			$res->plugin = plugin_basename( __FILE__ );
			$res->new_version = $remote->version;
			$res->tested = $remote->tested;
			$res->package = $remote->download_url;
			
			$transient->response[ $res->plugin ] = $res;
			
		}
		
		return $transient;
		
	}
	
	public static function purge( $upgrader, $options ){
		
		if (
			self::$cache_allowed
			&& 'update' === $options['action']
			&& 'plugin' === $options[ 'type' ]
		) {
			// just clean the cache when new plugin version is installed
			delete_transient( self::$cache_key );
		}
		
	}
	
	public static function wcabe_update_message( $plugin_info_array, $plugin_info_object ) {
		if( empty( $plugin_info_array[ 'package' ] ) ) {
			$renew_msg = ' Please renew your support subscription to use automatic update or download the update from '.
			             'CodeCanyon site. You can update your license key <a href="'.
			             admin_url( 'edit.php?post_type=product&page=advanced_bulk_edit&section=wcabe_settings' ).
			             '">here</a>';
			echo $renew_msg;
		}
	}

	public function _load_translations()
	{
		 load_plugin_textdomain('woocommerce-advbulkedit', false,  dirname(plugin_basename(__FILE__)) .'/languages');
	}

	public static function instance()
	{
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}

	public function _setup()
	{
		add_submenu_page(
			'edit.php?post_type=product',
			'WooCommerce Advanced Bulk Edit',
			'WooCommerce Advanced Bulk Edit',
			'manage_woocommerce',
			self::PLUGIN_SLUG,
			array(self::instance(), 'showpage')
		);
		add_action( 'admin_enqueue_scripts', array(self::instance(), 'admin_scripts') );


		$settings = get_option('w3exabe_settings');
		if (!isset($settings['setting_display_top_bar_link_bulkedit']) || $settings['setting_display_top_bar_link_bulkedit'] == "0") {
			add_action('admin_bar_menu', function($wp_admin_bar) {
				$args = array(
					'id' => 'btn-wcabe-admin-bar',
					'title' => __('Bulk Edit Products', 'woocommerce-advbulkedit'),
					'href' => admin_url('edit.php?post_type=product&page=advanced_bulk_edit'),
					'meta' => array(
						'class' => 'wp-admin-bar-btn-wcabe',
						'title' => 'Load WooCommerce Advanced Bulk Edit'
					)
				);
				$wp_admin_bar->add_node($args);

			}, 200);
		}

	}

	protected static function load_extensions()
	{
		$extensions_base_path = WCABE_PLUGIN_PATH.'extension/';
		$extension_paths = array_map('basename', glob($extensions_base_path.'*', GLOB_ONLYDIR));
		foreach ($extension_paths as $extension) {
			$extension_main_file = $extensions_base_path.$extension.'/'.$extension.'.php';
			if (file_exists($extension_main_file)) {
				require_once ($extension_main_file);
			}
		}
	}

	public static function ajax_request()
	{
		require_once(dirname(__FILE__).'/ajax_handler.php');
		W3ExABulkEditAjaxHandler::ajax();
		// IMPORTANT: don't forget to "exit"
		die();
	}

	public static function new_ajax_request()
	{
		require_once(dirname(__FILE__).'/new_ajax_handler.php');
		WpMelonWCABENewAjaxHandler::ajax();
		// IMPORTANT: don't forget to "exit"
		die();
	}

	function admin_scripts($hook)
	{
		// Load libraries ONLY if WCABE plugin is loaded
		$ibegin = strpos($hook,'advanced_bulk_edit',0);
		if( $ibegin === FALSE)
			return;
		$purl = plugin_dir_url(__FILE__);

		$ver = WCABE_VERSION;
		$settings = get_option('w3exabe_settings');

//		$settings = get_option('w3exabe_settings');
//		if (isset($settings['usefixedjquery']) && $settings['usefixedjquery'] == 1) {
//			wp_deregister_script('jquery');
//			wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', array(), null, true);
//		} else {
//			wp_enqueue_script('jquery');
//		}

		// A fix for conflict with the Porto theme. It will only execute when our admin page is loaded,
		// so it won't interfiere on Porto's work in any way.
		if (wp_script_is('jquery-magnific-popup'))
		{
			wp_deregister_script( 'jquery-magnific-popup' );
		}
		if (wp_script_is('porto-builder-admin'))
		{
			wp_deregister_script( 'porto-builder-admin' );
		}
		if (wp_script_is('tidio-chat-admin'))
		{
			wp_deregister_script( 'tidio-chat-admin' );
		}
		if (wp_script_is('bm-admin'))
		{
			wp_deregister_script( 'bm-admin' );
		}
		if (wp_script_is('wbm-admin'))
		{
			wp_deregister_script( 'wbm-admin' );
		}
		if (wp_script_is('lpc_admin_notices'))
		{
			wp_deregister_script( 'lpc_admin_notices' );
		}
		if (wp_script_is('paypal-for-woocommerce-multi-account-management'))
		{
			wp_deregister_script( 'paypal-for-woocommerce-multi-account-management' );
		}
		if (wp_script_is('soisy-pagamento-rateale'))
		{
			wp_deregister_script( 'soisy-pagamento-rateale' );
		}
		/*
		//wp_enqueue_script('jquery');
		wp_deregister_script('jquery');
		wp_deregister_script('jquery-ui');
//        wp_deregister_script('jquery-ui-dialog');
//        wp_deregister_script('jquery-ui-tabs');
//        wp_deregister_script('jquery-ui-sortable');
//        wp_deregister_script('jquery-ui-draggable');
//        wp_deregister_script('jquery-ui-datepicker');

		//wp_register_script('jquery', $purl.'lib/jquery-1.12.4.min.js', false, '1.12.4');
		//wp_register_script('jquery-ui', $purl.'lib/jquery-ui-1.12.1.min.js', false, '1.12.1');
		wp_register_script('jquery-all', $purl.'lib/jquery-all.min.js', false, '1.12.4');
//		wp_enqueue_script('jquery-ui-core');
		//wp_enqueue_script('jquery');
		//wp_enqueue_script('jquery-ui');
		wp_enqueue_script('jquery-all');
		wp_enqueue_script('jquery-ui-dialog', array(), $ver, true);
		wp_enqueue_script('jquery-ui-tabs', array(), $ver, true);
		wp_enqueue_script('jquery-ui-sortable', array(), $ver, true);
//		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-draggable', array(), $ver, true);
		wp_enqueue_script('jquery-ui-datepicker', array(), $ver, true);
		wp_enqueue_script('jquery-ui-accordion', array(), $ver, true);
		if(function_exists( 'wp_enqueue_media' )){
			wp_enqueue_media();
		}else{
			wp_enqueue_style('thickbox', array(), $ver, true);
			wp_enqueue_script('media-upload', array(), $ver, true);
			wp_enqueue_script('thickbox', array(), $ver, true);
		}
		*/
		//wp_enqueue_script('jquery');
		wp_deregister_script('jquery');
		wp_deregister_script('jquery-ui');
//        wp_deregister_script('jquery-ui-dialog');
//        wp_deregister_script('jquery-ui-tabs');
//        wp_deregister_script('jquery-ui-sortable');
//        wp_deregister_script('jquery-ui-draggable');
//        wp_deregister_script('jquery-ui-datepicker');

		wp_register_script('jquery', $purl.'lib/jquery-1.12.4.min.js', false, '1.12.4');
		wp_register_script('jquery-ui', $purl.'lib/jquery-ui-1.12.1.min.js', false, '1.12.1');
//    wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-accordion');
		if(function_exists( 'wp_enqueue_media' )){
			wp_enqueue_media();
		}else{
			wp_enqueue_style('thickbox');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
		}

		wp_enqueue_style('w3exabe-slickgrid',$purl.'css/slick.grid.css',false, $ver, 'all' );
		//wp_enqueue_style('w3exabe-jqueryui',$purl.'css/smoothness/jquery-ui-1.8.16.custom.css',false, $ver, 'all' );
//		wp_enqueue_style('w3exabe-jqueryui-new',$purl.'css/smoothness/jquery-ui-1.12.1.css',false, $ver, 'all' );
		wp_enqueue_style('w3exabe-jqueryui-new',$purl.'css/jq-ui-themes/base/jquery-ui.min.css',false, $ver, 'all' );
//		wp_enqueue_style('w3exabe-jqueryui-new-theme',$purl.'css/jq-ui-themes/base/jquery-ui.theme.min.css',false, $ver, 'all' );
//		wp_enqueue_style('w3exabe-jqueryui-new-structure',$purl.'css/jq-ui-themes/base/jquery-ui.structure.min.css',false, $ver, 'all' );
//        wp_enqueue_script('jquery-ui-core');
//        wp_enqueue_script('jquery-ui-dialog');
//        wp_enqueue_script('jquery-ui-tabs');
//        wp_enqueue_script('jquery-ui-sortable');
//        wp_enqueue_script('jquery-ui-draggable');
//        wp_enqueue_script('jquery-ui-datepicker');

		wp_enqueue_style('w3exabe-main',$purl.'css/main.css',false, $ver, 'all' );
		wp_enqueue_style('w3exabe-chosencss',$purl.'chosen/chosen.min.css',false, $ver, 'all' );
		wp_enqueue_style('w3exabe-colpicker',$purl.'controls/slick.columnpicker.css',false, $ver, 'all' );

		wp_enqueue_style('w3exabe-main-extend',$purl.'css/main-extend.css',false, $ver, 'all' );
		wp_enqueue_style('w3exabe-dyn-css',$purl.'main-dyn-css.php',false, $ver, 'all' );


		if (!isset($settings['setting_disable_hints']) || $settings['setting_disable_hints'] != 1) {
			wp_enqueue_style('w3exabe-tippy-light',$purl.'css/tippy/light.css',false, $ver, 'all' );
			wp_enqueue_style('w3exabe-tippy-light-border',$purl.'css/tippy/light-border.css',false, $ver, 'all' );
			wp_enqueue_style('w3exabe-tippy-google',$purl.'css/tippy/google.css',false, $ver, 'all' );
			wp_enqueue_style('w3exabe-tippy-translucent',$purl.'css/tippy/translucent.css',false, $ver, 'all' );
		}


		wp_enqueue_script('w3exabe-sjdrag',$purl.'lib/jquery.event.drag-2.2.js', array(), $ver, true );

		wp_enqueue_script('w3exabe-score',$purl.'js/slick.core.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-schecks',$purl.'plugins/slick.checkboxselectcolumn.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-sautot',$purl.'plugins/slick.autotooltips.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-scellrd',$purl.'plugins/slick.cellrangedecorator.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-sranges',$purl.'plugins/slick.cellrangeselector.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-scopym',$purl.'plugins/slick.cellcopymanager.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-scells',$purl.'plugins/slick.cellselectionmodel.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-srowsel',$purl.'plugins/slick.rowselectionmodel.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-scolpicker',$purl.'controls/slick.columnpicker.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-sfor',$purl.'js/slick.formatters.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-seditor',$purl.'js/slick.editors.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-slgrid',$purl.'js/slick.grid.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-chosen',$purl.'chosen/chosen.jquery.min.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-adminjs',$purl.'js/admin.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-adminjsext',$purl.'js/admin-ext.js', array(), $ver, true );
		wp_enqueue_script('w3exabe-adminhelpersjs',$purl.'js/admin-helpers.js', array(), $ver, true );

		if (!isset($settings['setting_disable_hints']) || $settings['setting_disable_hints'] != 1) {
			wp_enqueue_script('w3exabe-tippyjs-popper',$purl.'js/tippy/popper.min.js', array(), $ver, true );
			wp_enqueue_script('w3exabe-tippyjs-tippy',$purl.'js/tippy/index.all.min.js', array(), $ver, true );
		}

		wp_localize_script('w3exabe-adminjs', 'W3ExABE', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'w3ex-advbedit-nonce' ),
			)
		);

	}

	public function showpage()
	{
		if (isset($_GET['section']) && $_GET['section'] == 'site_wide_ops') {
			if (wcabe_load_integration(WCABE_179_INTEG_SITE_WIDE_OPS)) {
				W3ExABulkEdit_Integ_SiteWideOps::site_wide_ops_admin_page();
			}
		}
		else if (isset($_GET['section']) && $_GET['section'] == 'wcabe_settings') {
			require_once(dirname(__FILE__).'/page-settings.php');
		}
		else {
			require_once(dirname(__FILE__).'/bulkedit.php');
		}
	}
	
	public static function wcabe_settings_form_submission()
	{
		if ( isset( $_POST['wcabe-submit-settings'] ) && ! empty( $_POST['wcabe_license_key'] ) ) {
			$wcabe_license_key = sanitize_text_field( $_POST['wcabe_license_key'] );
			
			$settings = get_option('w3exabe_settings');
			$settings['license_key'] = $wcabe_license_key;
			update_option('w3exabe_settings',$settings);
			delete_transient( self::$cache_key );
			
			$redirect_url = admin_url( 'edit.php?post_type=product&page=advanced_bulk_edit&section=wcabe_settings' );
			wp_redirect( $redirect_url );
			exit;
		}
	}
}

W3ExAdvancedBulkEditMain::init();
