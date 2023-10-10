
<h2><?php _e( "WCABE Settings", "woocommerce-advbulkedit" ); ?></h2>

<a href="<?php echo admin_url( 'edit.php?post_type=product&page=advanced_bulk_edit' ); ?>"><?php _e( "< back to WCABE", "woocommerce-advbulkedit" ); ?></a>

<?php
if (!wcabe_is_current_user_admin()) {
?>
	<p><?php _e( "Only admins can access this page.", "woocommerce-advbulkedit" ); ?></p>
<?php
	return;
}

$settings = get_option('w3exabe_settings');
$wcabe_license_key = $settings['license_key'] ?? '';
?>

<div class="wrap">
	<form method="post" action="<?php echo admin_url( 'edit.php' ); ?>">
		<table class="form-table">
			<tr>
				<th scope="row"><label for="wcabe_license_key"><?php _e( "License Key (Purchase Code)", "woocommerce-advbulkedit" ); ?></label></th>
				<td>
					<input type="text" id="wcabe_license_key" name="wcabe_license_key" value="<?php echo $wcabe_license_key; ?>" class="regular-text" />
					<p class="description"><?php _e( "You can find the purchase code in your CodeCanyon account in Downloads section. Click on the Download button next to WooCommerce Advanced Bulk Edit and from the drop-down menu select the text version of the purchase code doc.", "woocommerce-advbulkedit" ); ?></p>
					<p class="description"><?php _e( "The purchase code will allow you to use plugin auto-updates only if your support subscription is active. If it's not, you can download the plugin from CodeCanyon and install it manually.", "woocommerce-advbulkedit" ); ?></p>
				</td>
			</tr>
		</table>
		<input type="submit" name="wcabe-submit-settings" id="wcabe-submit-settings" class="button button-primary" value="Save">
	</form>
</div>
