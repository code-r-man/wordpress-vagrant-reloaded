<?php

defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class WpMelonWCABENewAjaxHandler
{
	public static function ajax()
	{
		wcabe_verify_ajax_nonce_or_die();
		
		$type = $_POST['type'];
		
		switch($type){
			case 'split_variation':
			{
				$new_simple_product = wcabe_transform_variation_to_simple_product(
					$_POST['variationId'],
					$_POST['parentCustomFieldsToOverrideWith']
				);
				echo json_encode( [
					'success'  => 'Successfully split variation ID:'.$_POST['variationId'].
								  ' to Simple Product with ID:'.$new_simple_product->get_id(),
					'products' => []
				] );
				exit;
			} break;
			case 'split_variation_get_parent_custom_fields':
			{
				echo json_encode( [
					'success'  => 'Success getting products custom fields',
					'productCustomFields' => wcabe_get_variation_parent_custom_fields()
				] );
				die();
			} break;
			case 'site_wide_update':
			{
				if (file_exists( __DIR__.'/integrations/site-wide-ops.php')) {
					require_once('integrations/site-wide-ops.php');
					if (isset($_POST['fields']))
					W3ExABulkEdit_Integ_SiteWideOps::site_wide_ops_process($_POST['fields']);
				}
				
				echo json_encode( [
					//'success'  => $result
					'success'  => 'OK'
				] );
				die();
				
			} break;
			case 'autocomplete_product_selector':
			{
				wcabe_process_ajax_autocomplete_product_selector_search();
			} break;
		}
	}
}

//WpMelonWCABENewAjaxHandler::ajax();
