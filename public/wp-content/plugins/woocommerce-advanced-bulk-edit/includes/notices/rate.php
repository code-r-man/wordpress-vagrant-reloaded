<?php

class WCABE_Notice_Rate {
    
    protected static $tag = 'rate';
    
	public static function init() {
		//update_option( 'wcabe_notice_'.self::$tag, false );
		if( get_option( 'wcabe_notice_'.self::$tag ) != true ) {
			add_action( 'admin_notices',  [self::class, 'show'] );
			add_action( 'wp_ajax_wcabe_dismiss_notice_'.self::$tag, [self::class, 'dismiss'] );
		}
	}
	
	public static function show() {
		global $pagenow, $plugin_page;
		if ( $pagenow == 'edit.php' && $plugin_page == 'advanced_bulk_edit') {
			?>
		
			<div id="wcabe_notice_<?php echo self::$tag; ?>" class="notice notice-success is-dismissible" data-nonce="<?php echo json_encode(wp_create_nonce('wcabe_dismiss_notice_'.self::$tag)) ?>">
				<h3>Please, rate us!</h3>
				<p>This notice appears on the settings page.</p>
				<p>Can have multiple rows... and <a href="#">links</a></p>
			</div>
		
			<script>
				jQuery( document ).ready( function() {

					jQuery( document ).on( 'click', '#wcabe_notice_<?php echo self::$tag; ?>', function() {
						var data = {
							action: 'wcabe_dismiss_notice_<?php echo self::$tag; ?>',
							nonce: <?php echo json_encode(wp_create_nonce('wcabe_dismiss_notice_'.self::$tag)); ?>
						};

						jQuery.post( notice_params.ajaxurl, data, function() {});
					})
				});
			</script>
		
			<?php
		}
	}

	public static function dismiss() {
		check_ajax_referer('wcabe_dismiss_notice_'.self::$tag, 'nonce');
		update_option( 'wcabe_notice_'.self::$tag, true );
		exit;
	}
}
