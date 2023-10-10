<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!function_exists('wcabe_transform_variation_to_simple_product')) {
	function wcabe_transform_variation_to_simple_product( $variation_id, $parent_custom_fields_to_override_with ) {
		$product = wc_get_product($variation_id);
		
		$parentProduct = wc_get_product($product->get_parent_id());
		
		$duplicate = new WC_Product_Simple;
		$duplicate->set_id( 0 );
		
		//$duplicate->set_name( $product->get_name() );
		$duplicate->set_regular_price( $product->get_regular_price() );
		$duplicate->set_sale_price( $product->get_sale_price() );
		$duplicate->set_date_on_sale_from( $product->get_date_on_sale_from() );
		$duplicate->set_date_on_sale_to( $product->get_date_on_sale_to() );
		$duplicate->set_catalog_visibility($product->get_catalog_visibility());
		$duplicate->set_stock_status($product->get_stock_status());
		$duplicate->set_stock_quantity($product->get_stock_quantity());
		$duplicate->set_manage_stock($product->get_manage_stock());
		$duplicate->set_backorders($product->get_backorders());
		$duplicate->set_height($product->get_height());
		$duplicate->set_width($product->get_width());
		$duplicate->set_length($product->get_length());
		$duplicate->set_weight($product->get_weight());
		$duplicate->set_downloadable($product->get_downloadable());
		$duplicate->set_download_expiry($product->get_download_expiry());
		$duplicate->set_download_limit($product->get_download_limit());
		$duplicate->set_downloads($product->get_downloads());
		
		
		$duplicate->set_image_id($product->get_image_id());
		$duplicate->set_low_stock_amount($product->get_low_stock_amount());
		$duplicate->set_menu_order($product->get_menu_order());
		$duplicate->set_shipping_class_id($product->get_shipping_class_id());
		$duplicate->set_tax_class($product->get_tax_class());
		$duplicate->set_virtual($product->get_virtual());
		
		try {
			$duplicate->set_tax_status( $parentProduct->get_tax_status() );
		}
		catch ( WC_Data_Exception $e ) {
		}
		$duplicate->set_tag_ids($parentProduct->get_tag_ids());
		$duplicate->set_sold_individually($parentProduct->get_sold_individually());
		$duplicate->set_slug($parentProduct->get_slug());
		$duplicate->set_short_description($parentProduct->get_short_description());
		$duplicate->set_gallery_image_ids($parentProduct->get_gallery_image_ids());
		$duplicate->set_featured($parentProduct->get_featured());
		$duplicate->set_cross_sell_ids($parentProduct->get_cross_sell_ids());
		$duplicate->set_upsell_ids($parentProduct->get_upsell_ids());
		$duplicate->set_category_ids($parentProduct->get_category_ids());
		$duplicate->set_description($parentProduct->get_description());
		//$duplicate->set_description($product->get_variation_description());
		
		// $duplicate->set_($product->get_());
		// $duplicate->set_purchase_note($product->get_purchase_note());
		// $duplicate->set_title($product->get_title());
		
		$parentProductAttributes = $parentProduct->get_attributes();
		$variationAttributes = $product->get_attributes();
		$variationAttributesIds = [];
		$variationAttributesNames = [];
		foreach ($variationAttributes as $attName => $attValue) {
			if ($term = get_term_by('slug', $attValue, $attName)) {
				$variationAttributesIds[$attName] = $term->term_id;
			}
			if ($term = get_term_by('slug', $attValue, $attName)) {
				if (!empty($term->name)) {
					$variationAttributesNames[$attName] = $term->name;
				}
			}
		}
		$titlePartWithAttributes = '('.implode(')(', $variationAttributesNames).')';
		if (file_exists( WCABE_PLUGIN_PATH.'integrations/split-variations-customizations-for-anders.php')) {
			require_once(WCABE_PLUGIN_PATH.'integrations/split-variations-customizations-for-anders.php');
			$titlePartWithAttributes = W3ExABulkEdit_Integ_SplitVariationsCustomizationsForAnders::reorder_attribute_values_in_title($variationAttributesNames);
		}
		$duplicate->set_name($parentProduct->get_name() . ' ' . $titlePartWithAttributes);
		foreach ($parentProductAttributes as $attName => $attObject) {
			if (in_array($attName, array_keys($variationAttributesIds))) {
				$attObject->set_options([$variationAttributesIds[$attName]]);
				$parentProductAttributes[$attName] = $attObject;
			} else {
				$attObject->set_options([]);
				$parentProductAttributes[$attName] = $attObject;
			}
		}
		$duplicate->set_attributes($parentProductAttributes);
		
		$duplicate->set_status( 'draft' );
		$duplicate->set_total_sales( 0 );
		if ( '' !== $product->get_sku( 'edit' ) ) {
			try {
				$duplicate->set_sku( wc_product_generate_unique_sku( 0, $product->get_sku( 'edit' ) ) );
			}
			catch ( WC_Data_Exception $e ) {
			}
		}
		$duplicate->set_date_created( null );
		$duplicate->set_slug( '' );
		$duplicate->set_rating_counts( 0 );
		$duplicate->set_average_rating( 0 );
		$duplicate->set_review_count( 0 );
		
		$duplicate->save();
		
		foreach ($product->get_meta_data() as $meta_data) {
			error_log("copying custom field: {$meta_data->key} / {$meta_data->value}");
			update_post_meta($duplicate->get_id(), $meta_data->key, $meta_data->value);
		}
		
		foreach ($parentProduct->get_meta_data() as $meta_data) {
			foreach ($parent_custom_fields_to_override_with as $field) {
				if ($meta_data->key == $field) {
				    error_log("Updating custom field $field from parent with value {$meta_data->value}");
					update_post_meta($duplicate->get_id(), $meta_data->key, $meta_data->value);
				}
			}
		
		}
		
		//wp_trash_post( $product_id );
		//do_action( 'woocommerce_trash_product_variation', $product_id );
		//delete_transient( 'wc_product_children_' . $parentProduct->get_id() );
		do_action( 'woocommerce_before_delete_product_variation', $variation_id );
		wp_delete_post( $variation_id, true );
		do_action( 'woocommerce_delete_product_variation', $variation_id );
		
		delete_transient( 'wc_product_children_' . $parentProduct->get_id() );
		
		return $duplicate;
	}
}

if (!function_exists( 'wcabe_get_variation_parent_custom_fields' )) {
	function wcabe_get_variation_parent_custom_fields() {
		$custom_fields = wcabe_find_custom_fields('', true);
		$custom_fields_names_only = [];
		foreach ($custom_fields as $custom_field) {
			if  (!in_array($custom_field->meta_key, $custom_fields_names_only)) {
				$custom_fields_names_only[] = $custom_field->meta_key;
			}
		}
		
		return $custom_fields_names_only;
	}
}

if (!function_exists('wcabe_find_custom_fields')) {
	/**
	 * Get all product custom (meta) fields
	 *
	 * @param $data string The ID of the product to search meta fields for
	 * @param false $auto
	 *
	 * @return array|object|null
	 */
	function wcabe_find_custom_fields($data,$auto = false)
	{
		global $wpdb;
		$meta = $wpdb->postmeta;
		$posts = $wpdb->posts;
		
		$query = "SELECT meta_key,meta_value from {$meta} WHERE post_id={$data} AND meta_key NOT IN ('_regular_price','_sale_price','_sku','_weight','_length','_width','_height','_stock','_stock_status','_visibility','_virtual','_download_type','_download_limit','_download_expiry','_downloadable_files','_downloadable','_sale_price_dates_from','_sale_price_dates_to','_tax_class','_tax_status','_backorders','_manage_stock','_featured','_purchase_note','_sold_individually','_product_url','_button_text','_thumbnail_id','_product_image_gallery','_upsell_ids','_crosssell_ids','_product_attributes','_default_attributes','_price','_edit_lock','_edit_last','_min_variation_price','_max_variation_price','_min_price_variation_id','_max_price_variation_id','_min_variation_regular_price','_max_variation_regular_price','_min_regular_price_variation_id','_max_regular_price_variation_id','_min_variation_sale_price','_max_variation_sale_price','_min_sale_price_variation_id','_max_sale_price_variation_id','_file_paths','_variation_description','_wc_rating_count','_product_permalink','_children','_wp_trash_meta_status','_wp_trash_meta_time','_wp_desired_post_slug','_wp_old_slug','_product_version') AND meta_key NOT LIKE 'attribute_%'";
		if($auto)
		{
			$query = "SELECT
				ID
				FROM {$posts}
				WHERE {$posts}.post_type='product' ORDER BY ID ASC";//" LIMIT 200";
			$metas =  $wpdb->get_results($query);
			$prodids = "";
			foreach($metas as $meta1)
			{
				if($prodids === "")
					$prodids = $meta1->ID;
				else
					$prodids = $prodids.','.$meta1->ID;
			}
			$query = "SELECT DISTINCT meta_key,meta_value from {$meta} WHERE post_id IN ({$prodids}) AND meta_key NOT IN ('_wp_attachment_image_alt','_regular_price','_sale_price','_sku','_weight','_length','_width','_height','_stock','_stock_status','_visibility','_virtual','_download_type','_download_limit','_download_expiry','_downloadable_files','_downloadable','_sale_price_dates_from','_sale_price_dates_to','_tax_class','_tax_status','_backorders','_manage_stock','_featured','_purchase_note','_sold_individually','_product_url','_button_text','_thumbnail_id','_product_image_gallery','_upsell_ids','_crosssell_ids','_product_attributes','_default_attributes','_price','_edit_lock','_edit_last','_min_variation_price','_max_variation_price','_min_price_variation_id','_max_price_variation_id','_min_variation_regular_price','_max_variation_regular_price','_min_regular_price_variation_id','_max_regular_price_variation_id','_min_variation_sale_price','_max_variation_sale_price','_min_sale_price_variation_id','_max_sale_price_variation_id','_file_paths','_variation_description','_wc_rating_count','_product_permalink','_children','_wp_trash_meta_status','_wp_trash_meta_time','_wp_desired_post_slug','_wp_old_slug','_product_version') AND meta_key NOT LIKE 'attribute_%'";
			$metas =  $wpdb->get_results($query);
			$query = "SELECT
				ID
				FROM {$posts}
				WHERE {$posts}.post_type='product' ORDER BY ID DESC";//" LIMIT 200";
			$metas1 =  $wpdb->get_results($query);
			$prodids = "";
			foreach($metas1 as $meta1)
			{
				if($prodids === "")
					$prodids = $meta1->ID;
				else
					$prodids = $prodids.','.$meta1->ID;
			}
			$query = "SELECT DISTINCT meta_key,meta_value from {$meta} WHERE post_id IN ({$prodids}) AND meta_key NOT IN ('_wp_attachment_image_alt','_regular_price','_sale_price','_sku','_weight','_length','_width','_height','_stock','_stock_status','_visibility','_virtual','_download_type','_download_limit','_download_expiry','_downloadable_files','_downloadable','_sale_price_dates_from','_sale_price_dates_to','_tax_class','_tax_status','_backorders','_manage_stock','_featured','_purchase_note','_sold_individually','_product_url','_button_text','_thumbnail_id','_product_image_gallery','_upsell_ids','_crosssell_ids','_product_attributes','_default_attributes','_price','_edit_lock','_edit_last','_min_variation_price','_max_variation_price','_min_price_variation_id','_max_price_variation_id','_min_variation_regular_price','_max_variation_regular_price','_min_regular_price_variation_id','_max_regular_price_variation_id','_min_variation_sale_price','_max_variation_sale_price','_min_sale_price_variation_id','_max_sale_price_variation_id','_file_paths','_variation_description','_wc_rating_count','_product_permalink','_children','_wp_trash_meta_status','_wp_trash_meta_time','_wp_desired_post_slug','_wp_old_slug','_product_version') AND meta_key NOT LIKE 'attribute_%'";
			$metas1 =  $wpdb->get_results($query);
			$metas = array_merge($metas,$metas1);
			$query = "SELECT
				ID
				FROM {$posts}
				WHERE {$posts}.post_type='product_variation' ORDER BY ID ASC";// LIMIT 200";
			$metas1 =  $wpdb->get_results($query);
			$prodids = "";
			foreach($metas1 as $meta1)
			{
				if($prodids === "")
					$prodids = $meta1->ID;
				else
					$prodids = $prodids.','.$meta1->ID;
			}
			$query = "SELECT DISTINCT meta_key,meta_value from {$meta} WHERE post_id IN ({$prodids}) AND meta_key NOT IN ('_wp_attachment_image_alt','_regular_price','_sale_price','_sku','_weight','_length','_width','_height','_stock','_stock_status','_visibility','_virtual','_download_type','_download_limit','_download_expiry','_downloadable_files','_downloadable','_sale_price_dates_from','_sale_price_dates_to','_tax_class','_tax_status','_backorders','_manage_stock','_featured','_purchase_note','_sold_individually','_product_url','_button_text','_thumbnail_id','_product_image_gallery','_upsell_ids','_crosssell_ids','_product_attributes','_default_attributes','_price','_edit_lock','_edit_last','_min_variation_price','_max_variation_price','_min_price_variation_id','_max_price_variation_id','_min_variation_regular_price','_max_variation_regular_price','_min_regular_price_variation_id','_max_regular_price_variation_id','_min_variation_sale_price','_max_variation_sale_price','_min_sale_price_variation_id','_max_sale_price_variation_id','_file_paths','_variation_description','_wc_rating_count','_product_permalink','_children','_wp_trash_meta_status','_wp_trash_meta_time','_wp_desired_post_slug','_wp_old_slug','_product_version') AND meta_key NOT LIKE 'attribute_%'";
			$metas1 =  $wpdb->get_results($query);
			$metas = array_filter($metas, function($item) {
				if (
					!is_array($item->meta_value) &&
					wcabe_starts_with($item->meta_key, '_') &&
					preg_match("/field_([a-z\d]+)/", $item->meta_value)
				) {
					return false;
				} else {
					return true;
				}
			});
			$metas = array_merge($metas,$metas1);
			return $metas;
			
		}
		$metas =  $wpdb->get_results($query);
		$metas = array_filter($metas, function($item) {
			if (
				!is_array($item->meta_value) &&
				wcabe_starts_with($item->meta_key, '_') &&
				preg_match("/field_([a-z\d]+)/", $item->meta_value)
			) {
				return false;
			} else {
				return true;
			}
		});
		$metas = array_merge($metas, []);
		
		return $metas;
	}
	
}

if (!function_exists('wcabe_process_ajax_autocomplete_product_selector_search')) {
	/**
	 * Search for product using the Title, SKU or ID
	 */
	function wcabe_process_ajax_autocomplete_product_selector_search()
	{



		wcabe_verify_ajax_nonce_or_die();

		$type = $_POST['type'];

		if ($type != 'autocomplete_product_selector') {
			echo json_encode( [
				'error'  => 'Invalid autocomplete search request',
				'products' => []
			] );
			die();
		}

		global $wpdb;

		$term = '';
		if ( isset( $_POST['term'] ) ) {
			$term = $_POST['term'];
		}

		$searchQuery = $wpdb->prepare("
                    select id, id as val from {$wpdb->posts} where id like '%s'
                    union
                    select id, post_title as val from {$wpdb->posts} where post_title like '%s' AND post_type='product'
                    union
                    select post_id as id, meta_value as val from {$wpdb->postmeta} where meta_key='_sku' AND meta_value like '%s'
                ",
			$wpdb->esc_like($term).'%',
			'%'.$wpdb->esc_like($term).'%',
			'%'.$wpdb->esc_like($term).'%'
		);
		$searchResults= $wpdb->get_results($searchQuery);
		$searchResultsTransformValues = [];
		$searchResultsTransformKeys = [];
		foreach ($searchResults as $result) {
			$value = str_replace(',', '', $result->val);
			$searchResultsTransformValues[$value] = $result->id;
			$searchResultsTransformKeys[] = $value;
		}

		echo json_encode(
			[
				'keys' => $searchResultsTransformKeys,
				'values' => $searchResultsTransformValues
			]
		);
		exit;
	}
}



