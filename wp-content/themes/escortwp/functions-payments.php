<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

// Default Payment Plans
$payment_plans = array(
						'indescreg' => array(
										'title' => array('Independent %s registration','taxonomy_profile_name'),
										'label' => array('Price to register as independent %s','taxonomy_profile_name'),
										'label_help' => array('keep empty for free registration'),
										'price' => '',
										'duration' => '',
										'woo_product_id' => '',
										'woo_product_name' => array('Independent %s registration','taxonomy_profile_name'),
										'exp' => '2',
										'exp_text' => array('What happens on profile expiration?'),
									),
						'agreg' => array(
										'title' => array('%s registration','taxonomy_agency_name'),
										'label' => array('Price to register as %s','taxonomy_agency_name'),
										'label_help' => array('keep empty for free registration'),
										'price' => '',
										'duration' => '',
										'woo_product_id' => '',
										'woo_product_name' => array('%s registration','taxonomy_agency_name'),
										'exp' => '2',
										'exp_text' => array('What happens on profile expiration?'),
									),
						'agescortreg' => array(
										'title' => array('%1$s adding %2$s','taxonomy_agency_name','taxonomy_profile_name'),
										'label' => array('Price for %1$s to add an %2$s','taxonomy_agency_name','taxonomy_profile_name'),
										'label_help' => array('keep empty for free registration'),
										'price' => '',
										'duration' => '',
										'woo_product_id' => '',
										'woo_product_name' => array('Add new %s profile','taxonomy_profile_name'),
										'exp' => '2',
										'exp_text' => array('What happens on profile expiration?'),
									),
						'premium' => array(
										'title' => array('Premium options'),
										'label' => array('Price to become a premium %s','taxonomy_profile_name'),
										'label_help' => array('keep empty to disable'),
										'price' => '',
										'duration' => '',
										'woo_product_id' => '',
										'woo_product_name' => array('Upgrade profile to premium','taxonomy_profile_name'),
									),
						'featured' => array(
										'title' => array('Featured options'),
										'label_help' => array('keep empty to disable'),
										'label' => array('Price to become a featured %s','taxonomy_profile_name'),
										'price' => '',
										'duration' => '',
										'woo_product_id' => '',
										'woo_product_name' => array('Upgrade profile to featured','taxonomy_profile_name'),
									),
						'tours' => array(
										'title' => array('Tour options'),
										'label' => array('Price to add a tour'),
										'label_help' => array('keep empty for free tours'),
										'price' => '',
										'woo_product_id' => '',
										'woo_product_name' => array('Add city tour','taxonomy_profile_name'),
									),
						'vip' => array(
										'title' => array('VIP Options'),
										'label' => array('Price to become a VIP member'),
										'label_help' => array('keep empty to disable'),
										'price' => '',
										'duration' => '',
										'woo_product_id' => '',
										'woo_product_name' => array('Upgrade to VIP','taxonomy_profile_name'),
										'extra' => array(
														'hide_photos' => '',
														'hide_contact_info' => '',
														'hide_review_form' => '',
													),
									),
					);
// update_option('payment_plans', $payment_plans);
// $payment_plans = get_option('payment_plans');

function generate_payment_buttons($product, $payment_for_id, $text="") {
	if(!is_woocommerce_active) return false;

	global $woocommerce;
	$submit_text = $text ? $text : __("Pay now",'escortwp');
	$woo_id = payment_plans($product, 'woo_product_id');
	$checkout_url = $woocommerce->cart->get_checkout_url();
    $button  = '<form action="'.esc_url($checkout_url).'" class="cart" method="post" enctype="multipart/form-data">';
    $button .= '<input type="hidden" name="add-to-cart" value="'.$woo_id.'" />';
    $button .= '<input type="hidden" name="order_for_id" value="'.$payment_for_id.'" />';
    $button .= '<button type="submit" class="greenbutton payment-button rad25">'.$submit_text.'</button>';
    $button .= '</form>';	
	return $button;
	// return $link;
}

// Add custom id to cart order
function woo_add_order_extra_data($cart_item_data, $product_id, $variation_id) {
    if(isset($_REQUEST['order_for_id'])) {
        $cart_item_data['order_for_id'] = sanitize_text_field($_REQUEST['order_for_id']);
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data','woo_add_order_extra_data',10,3);

// Show custom id information in checkout and cart page
function woo_show_custom_info_checkout($product_name, $values, $cart_item_key) {
    if(is_array($values['order_for_id']) && count($values['order_for_id']) > 0) {
    	$custom_id = $values['order_for_id'];
    	$payment_type = get_post_meta($values['product_id'], 'payment_type', true);
    	if($payment_type == "vip") {
    		$user_info = get_userdata($custom_id);
			$extra_info = $user_info->display_name;
    	} else {
			$extra_info = get_the_title($custom_id);
    	}

        $product_text = $product_name;
        $product_text .= $extra_info ? '<div class="extra-info">'.$extra_info.'</div>' : "";
        return $product_text;
    } else {
        return $product_name;
    }
}
add_filter('woocommerce_checkout_cart_item_quantity','woo_show_custom_info_checkout',1,3);  

// Save custom id to order
function wdm_add_custom_order_line_item_meta($item, $cart_item_key, $values, $order) {
    if(array_key_exists('order_for_id', $values)) {
        $item->add_meta_data('order_for_id', $values['order_for_id']);
    }
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wdm_add_custom_order_line_item_meta',10,4 );

// Get any payment plan fast
function payment_plans($payment_name="", $key="", $key2="") {
	$payment_plans = get_option('payment_plans');
	if(!$payment_name) return $payment_plans;
	if($key2) {
		return $payment_plans[$payment_name][$key][$key2];
	} else {
		return $payment_plans[$payment_name][$key];
	}
}


// add_filter('woocommerce_add_to_cart_redirect', 'direct_checkout_redirect');
// function direct_checkout_redirect() {
// 	global $woocommerce;
// 	$checkout_url = wc_get_checkout_url();
// 	return $checkout_url;
// }

/**
 * WooCommerce Remove Address Fields from checkout based on presence of virtual products in cart
 * @link https://www.skyverge.com/blog/checking-woocommerce-cart-contains-product-category/
 * @link https://docs.woothemes.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/
 * @link https://businessbloomer.com/woocommerce-hide-checkout-billing-fields-if-virtual-product-cart/
 */
function woo_remove_address_from_checkout( $fields ) {
	// set flag to be true until we find a product that isn't virtual
	$virtual_products = true;

	// loop through our cart
	foreach(WC()->cart && WC()->cart->get_cart() as $cart_item_key => $cart_item) {
		// Check if there are non-virtual products and if so make it false
		if (!$cart_item['data']->is_virtual()) $virtual_products = false; 
	}

	// only unset fields if virtual_products is true so we have no physical products in the cart
	if($virtual_products === true) {
		unset($fields['billing']['billing_company']);
		unset($fields['billing']['billing_address_1']);
		unset($fields['billing']['billing_address_2']);
		unset($fields['billing']['billing_city']);
		unset($fields['billing']['billing_postcode']);
		unset($fields['billing']['billing_country']);
		unset($fields['billing']['billing_state']);
		unset($fields['billing']['billing_phone']);
		// Removes Additional Info title and Order Notes
		add_filter('woocommerce_enable_order_notes_field', '__return_false',9999);
	}

	return $fields;
}
if(get_option('show_address_checkout') == "2") {
	add_filter('woocommerce_checkout_fields', 'woo_remove_address_from_checkout');
}

// Remove coupon field from checkout
function remove_checkout_coupon_form(){
    remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
}
if(get_option('show_coupon_checkout') == "2") {
	add_action( 'woocommerce_before_checkout_form', 'remove_checkout_coupon_form', 9 );
}


$payment_duration_a = array(
	/*
	[0] Name (translatable)
	[1] Number of days
	[2] Name (to be used in php functions)
	*/
	"1" => array(__('1 day','escortwp'), "1", '1 day'),
	"2" => array(__('1 week','escortwp'), "7", '1 week'),
	"3" => array(__('2 weeks','escortwp'), "14", '2 weeks'),
	"4" => array(__('3 weeks','escortwp'), "21", '3 weeks'),
	"5" => array(__('1 month','escortwp'), "30", '1 month'),
	"6" => array(__('2 months','escortwp'), "60", '2 months'),
	"7" => array(__('3 months','escortwp'), "90", '3 months'),
	"8" => array(__('4 months','escortwp'), "120", '4 months'),
	"9" => array(__('5 months','escortwp'), "150", '5 months'),
	"10" => array(__('6 months','escortwp'), "180", '6 months'),
	"11" => array(__('7 months','escortwp'), "210", '7 months'),
	"12" => array(__('8 months','escortwp'), "240", '8 months'),
	"13" => array(__('9 months','escortwp'), "270", '9 months'),
	"14" => array(__('10 months','escortwp'), "300", '10 months'),
	"15" => array(__('11 months','escortwp'), "330", '11 months'),
	"16" => array(__('1 year','escortwp'), "365", '1 year'),
	"17" => array(__('2 years','escortwp'), "730", '2 years')
);


function format_price($payment_plan,$size="long") {
	global $payment_duration_a, $woocommerce;
	$woo_product_id = payment_plans($payment_plan, 'woo_product_id');
	if(!$woo_product_id || !class_exists('WC_Product')) return false;
	$woo_product = new WC_Product($woo_product_id);
	$price = $woo_product->get_price_html();
	$duration = payment_plans($payment_plan, 'duration');
	if($size == "long") {;
		if(class_exists('WC_Subscriptions_Product') && get_post_meta($woo_product_id, '_subscription_price', true)) {
			$price = '<br />'.get_woocommerce_currency_symbol().WC_Subscriptions_Product::get_price_string($woo_product_id);
			$price .= '<br />'.__('recurring payment','escortwp');
		} else {
			if($duration) {
				$price .= ' '.__('for','escortwp').' '.$payment_duration_a[$duration][0];
			}
			$price .= '<br />'.__('one time payment','escortwp');
		}

	}
	return $price;
}


add_filter('woocommerce_cart_item_thumbnail','__return_false'); // remove product thumbnails from cart
add_filter('woocommerce_cart_item_permalink','__return_false'); // remove product link from cart
add_filter('woocommerce_order_item_permalink','__return_false'); // remove order link

// show product name in order list
function woo_add_new_column_to_order_list($columns) {
    $new_columns = array();
    foreach ($columns as $key => $name) {
        $new_columns[$key] = $name;
        if ('order-number' === $key) {
            $new_columns['order-payment-for'] = __('Payment for', 'escortwp');
        }
    }
    return $new_columns;
}
add_filter('woocommerce_my_account_my_orders_columns', 'woo_add_new_column_to_order_list');

function woo_order_list_column_payment_for($order) {
    $items = $order->get_items();
    foreach ($items as $item_id => $item) {
	    $item_name = $item->get_name();
	    $order_for_id = wc_get_order_item_meta($item_id, 'order_for_id', true);
	    $payment_type = get_post_meta($item->get_product_id(), 'payment_type', true);
	    echo $item_name."<br />";
	    if($payment_type == "vip") {
		    $vip_user = get_user_by('id', $order_for_id);
		    echo $vip_user->display_name;
	    } elseif($payment_type == "tours") {
		    echo get_the_title($order_for_id);
	    } else {
		    echo '<a href="'.get_permalink($order_for_id).'">'.get_the_title($order_for_id).'</a>';
	    }
    }
}
add_action('woocommerce_my_account_my_orders_column_order-payment-for', 'woo_order_list_column_payment_for' );



// Remove notification emails from WooCommerce
function woo_remove_extra_emails_from_woocommerce($email_class) {
		// New order emails
		remove_action('woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger'));
		remove_action('woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger'));
		remove_action('woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger'));
		remove_action('woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger'));
		remove_action('woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger'));
		remove_action('woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger'));
		
		// Processing order emails
		remove_action('woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger'));
		remove_action('woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger'));
		
		// Completed order emails
		remove_action('woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger'));
			
		// Note emails
		remove_action('woocommerce_new_customer_note_notification', array( $email_class->emails['WC_Email_Customer_Note'], 'trigger'));
}
add_action('woocommerce_email', 'woo_remove_extra_emails_from_woocommerce');

// when a new item is added to cart the old items are removed
function woo_only_one_item_in_cart($passed, $added_product_id) {
	wc_empty_cart();
	return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'woo_only_one_item_in_cart', 99, 2);

// remove quantity field from cart, for virtual items
function woo_remove_quantity_field($return, $product) {
	if($product->virtual == "yes") {
		return true;
	} else {
		// return false;
	}
}
add_filter('woocommerce_is_sold_individually', 'woo_remove_quantity_field', 10, 2);

// change shop page to homepage
function woo_change_shop_page_url($link) {
	return get_home_url();
}
add_filter('woocommerce_get_shop_page_permalink', 'woo_change_shop_page_url');

// redirect shop page to homepage
function woo_disable_shop_page() {
    global $post;
    if (is_woocommerce_active && function_exists("is_shop") && is_shop() && !current_user_can('level_10')):
    	wp_redirect(get_home_url(), "301"); die();
    endif;
}
add_action('wp', 'woo_disable_shop_page');


// Payment complete trigger
function woo_payment_complete_trigger($order_id) {
    $order = wc_get_order($order_id);
    $user = $order->get_user();
	foreach ($order->get_items() as $item_id => $item) {
	    $order_for_id = wc_get_order_item_meta($item_id, 'order_for_id', true);
	    $item_name = $item->get_name();
	    $payment_type = get_post_meta($item->get_product_id(), 'payment_type', true);
		woo_activate_user_payment($payment_type, $item_name, $order_for_id, $order_id, $item->get_product_id());
	}
}
add_action('woocommerce_order_status_completed', 'woo_payment_complete_trigger');

function woo_activate_user_payment($payment_type, $item_name, $custom, $woo_order_id, $woo_product_id) {
	if(!$payment_type) return false;
	global $taxonomy_profile_name, $taxonomy_agency_name, $taxonomy_agency_name_plural, $payment_duration_a;

	// send email to admin
	$body = __('Hello admin','escortwp').',<br /><br /><br />'.__('Someone made a payment of','escortwp').' '.format_price($payment_type,'small').' '.__('in your account','escortwp').'.<br />'.__('Type of payment','escortwp').': <b>'.$item_name."</b>";
	if ($payment_type == "vip") { // vip
		$body .= '<br />'.__('User who made the payment','escortwp').':<br /><a href="'.admin_url( 'user-edit.php?user_id='.$custom).'">'.admin_url( 'user-edit.php?user_id='.$custom).'</a>';
	} elseif($payment_type == "tours") { // tours
		$belongstoescortid = get_post_meta($custom, 'belongstoescortid', true);
		$body .= '<br />'.__('Payment for','escortwp').':<br /><a href="'.get_permalink($belongstoescortid).'">'.get_permalink($belongstoescortid).'</a>';
	} else { // all other payments
		$body .= '<br />'.__('Payment for','escortwp').':<br /><a href="'.get_permalink($custom).'">'.get_permalink($custom).'</a>';
	}
	if (get_option("ifemail7") == "1") {
		dolce_email(null, null, get_bloginfo("admin_email"), __('New payment on','escortwp')." ".get_option("email_sitename"), $body);
	}

	// send mail to escort
	$custom_field_payment_name = $payment_type;
	$body = __('Hello','escortwp').',<br /><br /><br />'.__('Your payment on','escortwp').' '.get_option("email_sitename").' '.__('has been accepted','escortwp').'.<br />';
	if ($payment_type == "premium") { // premium status
		$body .= sprintf(esc_html__('You are now a premium %s.','escortwp'),$taxonomy_profile_name);
		update_post_meta($custom, "premium", "1");
		update_post_meta($custom, "premium_since", time());
		update_post_meta($custom, "premium_txn_id", $woo_order_id);
		if(payment_plans("premium","duration")) { // is the status going to expire?
			$expiration = strtotime("+".$payment_duration_a[payment_plans("premium","duration")][2]);
			$available_time = get_post_meta($custom, 'premium_expire', true);
			if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
			update_post_meta($custom, 'premium_expire', $expiration); // when does the premium status expire or get auto renewed
		}
	} elseif ($payment_type == "featured") { // featured status
		$body .= sprintf(esc_html__('You are now a featured %s.','escortwp'),$taxonomy_profile_name);
		update_post_meta($custom, "featured", "1");
		update_post_meta($custom, "featured_txn_id", $woo_order_id);
		if(payment_plans("featured","duration")) { // is the status going to expire?
			$expiration = strtotime("+".$payment_duration_a[payment_plans("featured","duration")][2]);
			$available_time = get_post_meta($custom, 'featured_expire', true);
			if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
			update_post_meta($custom, 'featured_expire', $expiration); // when does the featured status expire or get auto renewed
		}
	} elseif ($payment_type == "tours") { // tour activation
		$custom_field_payment_name = 'tour';
		$body .= __('Your tour has been activated.','escortwp');
		$post_tour = array('ID' => $custom, 'post_status' => 'publish');
		wp_update_post($post_tour);
		update_post_meta($custom, "tour_txn_id", $woo_order_id);
		delete_post_meta($custom, 'needs_payment');
	} elseif ($payment_type == "vip") { // VIP status
		$body .= __('You are now a VIP member.','escortwp');
		update_user_meta($custom, 'vip', "1");
		if(get_post_meta($woo_product_id, '_subscription_price', true)) {
			// $expiration = strtotime("+".$payment_duration_a[payment_plans("vip","duration")][2]);
			// $available_time = get_user_meta($custom, 'vip_expire', true);
			// if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
			// update_user_meta($custom, 'vip_expire', $expiration); // when does the VIP status expire or get auto renewed
		} elseif (payment_plans("vip","duration")) {
			$expiration = strtotime("+".$payment_duration_a[payment_plans("vip","duration")][2]);
			$available_time = get_user_meta($custom, 'vip_expire', true);
			if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
			update_user_meta($custom, 'vip_expire', $expiration); // when does the VIP status expire or get auto renewed
		}
		update_user_meta($custom, 'vip_txn_id', $woo_order_id);
	} elseif ($payment_type == "agreg") { // agency registration
		$custom_field_payment_name = 'agency';
		$body .= sprintf(esc_html__('Your %s profile has been activated.','escortwp'),$taxonomy_agency_name);
		if(get_option('manactivagprof') != "1") {
			$post_agency = array('ID' => $custom, 'post_status' => 'publish');
			wp_update_post($post_agency);
		}

		$args = array(
			'post_type' => $taxonomy_profile_url,
			'posts_per_page' => -1,
			'author' => $custom,
			'meta_query' => array(
				array(
					'key'     => 'needs_ag_payment',
					'value'   => '1',
					'type'    => 'numeric',
					'compare' => '=',
				),
				array(
					'key' => 'needs_payment',
					'value'   => '1',
					'type'    => 'numeric',
					'compare' => '!=',
				)
			)
		);
		$profiles = new WP_Query($args);
		if ($profiles->have_posts()) :
		while ($profiles->have_posts()) : $profiles->the_post();
			wp_update_post(array('ID' => get_the_ID(), 'post_status' => 'publish'));
			delete_post_meta(get_the_ID(), 'needs_ag_payment');
		endwhile;
		endif;
		wp_reset_postdata();

		if(payment_plans("agreg","duration")) {
			$expiration = strtotime("+".$payment_duration_a[payment_plans("agreg","duration")][2]);
			$available_time = get_post_meta($custom, 'agency_expire', true);
			if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
			update_post_meta($custom, 'agency_expire', $expiration); // when does the agency profile expire or get auto renewed
		}
		update_post_meta($custom, 'agency_txn_id', $woo_order_id);
		delete_post_meta($custom, 'needs_payment');
	} elseif ($payment_type == "agescortreg") { // agency adding escort
		$custom_field_payment_name = 'escort';
		$body .= sprintf(esc_html__('The %s you added has been activated.','escortwp'),$taxonomy_profile_name);
		if(get_option('manactivagescprof') != "1") {
			$post_escort = array( 'ID' => $custom, 'post_status' => 'publish' );
			wp_update_post( $post_escort );
		}
		if(payment_plans("agescortreg","duration")) {
			$expiration = strtotime("+".$payment_duration_a[payment_plans("agescortreg","duration")][2]);
			$available_time = get_post_meta($custom, 'escort_expire', true);
			if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
			update_post_meta($custom, 'escort_expire', $expiration); // when does the escort profile expire or get auto renewed
		}
		update_post_meta($custom, 'profile_txn_id', $woo_order_id);
		delete_post_meta($custom, 'needs_payment');
	} elseif ($payment_type == "indescreg") { // independent escort registration
		$custom_field_payment_name = 'escort';
		$body .= sprintf(esc_html__('Your %s profile is now active.','escortwp'),$taxonomy_profile_name);
		if(get_option('manactivindescprof') != "1") {
			$post_escort = array( 'ID' => $custom, 'post_status' => 'publish' );
			wp_update_post( $post_escort );
		}
		if(payment_plans("indescreg","duration")) {
			$expiration = strtotime("+".$payment_duration_a[payment_plans("indescreg","duration")][2]);
			$available_time = get_post_meta($custom, 'escort_expire', true);
			if($available_time && $available_time > time()) { $expiration = $expiration + ($available_time - time()); }
			update_post_meta($custom, 'escort_expire', $expiration); // when does the escort profile expire or get auto renewed
		}
		update_post_meta($custom, 'profile_txn_id', $woo_order_id);
		delete_post_meta($custom, 'needs_payment');
	}

	if(class_exists('WC_Subscriptions') && get_post_meta($woo_product_id, '_subscription_price', true)) {
		if($payment_type == "vip") {
			update_user_meta($custom, $custom_field_payment_name.'_renew', '1');
			delete_user_meta($custom, $custom_field_payment_name.'_expire');
		} else {
			$post = get_post($custom);
			$customeremail = get_the_author_meta('user_email', $post->post_author);

		    update_post_meta($custom, $custom_field_payment_name.'_renew', "1");
		    delete_post_meta($custom, $custom_field_payment_name.'_expire');
		}
	}

	if($payment_type == "vip") {
		$customeremail = get_the_author_meta('user_email', $custom);
	} else {
		$post = get_post($custom);
		$customeremail = get_the_author_meta('user_email', $post->post_author);
	}
	dolce_email("", "", $customeremail, __('Payment accepted on','escortwp')." ".get_option("email_sitename"), $body);
} // function woo_activate_user_payment()


// If the order status changes from completed to something else, and is a subscription, then we cancel the subscription for the user
add_action( 'woocommerce_order_status_changed', 'check_subscription_order_status_change', 99, 4);
function check_subscription_order_status_change($order_id, $old_status, $new_status, $order) {
    $order = wc_get_order($order_id);
    $user = $order->get_user();
	foreach ($order->get_items() as $item_id => $item) {
	    $item_name = $item->get_name();
		woo_activate_user_payment($payment_type, $item_name, $order_for_id, $order_id, $item->get_product_id());
	}

    if($old_status == "completed" && $new_status != "completed") {
	    $order_for_id = wc_get_order_item_meta($item_id, 'order_for_id', true);
    	$payment_type = get_post_meta($item->get_product_id(), 'payment_type', true);

		if($payment_type == "vip") {
			$customeremail = get_the_author_meta('user_email', $order_for_id);
		} else {
			$post = get_post($order_for_id);
			$customeremail = get_the_author_meta('user_email', $post->post_author);
		}
        order_subscription_canceled($customeremail, $payment_type, $order_for_id, "1", $order_id, $item->get_product_id());
    }
}

function woo_show_sidebar_expiration_notice($payment_type) {
	// '1' => 'premium'
	// '2' => 'featured'
	// '3' => 'tour'
	// '5' => 'vip'
	// '6' => 'agency'
	// '7' => 'ag escort'
	// '8' => 'escort'
	$var_names_array = array(
							// '1' => 'premium',
							// '2' => 'featured',
							// '3' => 'tour',
							'5' => 'vip',
							// '6' => 'agency',
							// '7' => 'escort',
							// '8' => 'escort'
						);
	$payment_plan_names_array = array(
							// '1' => 'premium',
							// '2' => 'featured',
							// '3' => 'tour',
							'5' => 'vip',
							// '6' => 'agency',
							// '7' => 'escort',
							// '8' => 'escort'
						);

	$text_names_array = array('1' => __('premium', 'escortwp'), '2' => __('featured', 'escortwp'), '3' => __('tour', 'escortwp'), '5' => __('vip', 'escortwp'), '6' => __('agency', 'escortwp'), '7' => __('escort', 'escortwp'), '8' => __('escort', 'escortwp'));
	$current_user = wp_get_current_user();
	$userid = $current_user->ID;
	// $userstatus = get_option("escortid".$userid);
	// $orderid = get_user_meta($userid, $var_names_array[$payment_type].'_txn_id', true);
	if(get_user_meta($userid, $var_names_array[$payment_type].'_expire', true)) {
		// one time payment
		$expire_date = date("d M Y", get_user_meta($userid, $var_names_array[$payment_type].'_expire', true));
		$expire_text = sprintf(__('Your %s status is active until','escortwp'), strtoupper($text_names_array[$payment_type])).': <b>'.$expire_date.'</b>';
		// $expire_text_mobile = __('VIP expiration:','escortwp').' <b>'.$vip_expire_date.'</b>';
	} else {
		if(get_user_meta($userid, $var_names_array[$payment_type].'_renew', true) == "1") {
			// subscription
			$expire_text = sprintf(__('Your %s status subscription is ACTIVE','escortwp'), strtoupper($text_names_array[$payment_type]));
			// $expire_text_mobile = __('VIP status ACTIVE','escortwp');
		} else {
			// forever
			$expire_text = sprintf(__('Your %s status is active FOREVER','escortwp'), strtoupper($text_names_array[$payment_type]));
			// $expire_text_mobile = __('VIP status ACTIVE forever','escortwp');
		}
	}

	// echo '<div class="sidebar-expire-notice-mobile bluedegrade text-center" data-payment-plan="featured">';
	// 	echo '<div class="expiration-date">'.$expire_text_mobile.'</div>';
	// 	if (get_user_meta($userid, 'vip_expire', true) && payment_plans('vip','price')) {
	// 		echo '<div class="sidebar-expire-mobile-extent-button greenbutton rad25">'.__('Extend','escortwp').'</div>';
	// 	}
	// echo '</div>';

	// echo '<div class="sidebar-expire-notice sidebar-expire-notice-has-mobile pinkdegrade center">';
	echo '<div class="sidebar-expire-notice greendegrade center">';
		echo $expire_text;
		if(get_user_meta($userid, $var_names_array[$payment_type]."_renew", true)) {
			?>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$('.sidebar-expire-notice-cancel-subscription .fake-button1').on('click', function(event) {
							$(this).hide();
							$(this).siblings('.sidebar-expire-notice-cancel-subscription-form').show();
						});
					});
				</script>
			    <div class="sidebar-expire-notice-cancel-subscription text-center">
			    	<div class="fake-button fake-button1 redbutton rad25 center"><?=__('Cancel subscription', 'escortwp')?></div>
				    <form action="" method="post" class="sidebar-expire-notice-cancel-subscription-form">
				    	<input type="hidden" name="payment_type" value="<?=$payment_type?>" />
				    	<input type="hidden" name="action" value="cancel_subscription" />
				    	<div class="clear10"></div>
				    	<?=__('Are you sure?', 'escortwp')?>
				    	<div class="clear5"></div>
				    	<button class="fake-button fake-button2 redbutton rad25 center"><?=__('Cancel now', 'escortwp')?></button>
				    </form>
			    </div>
			<?php
		} elseif (get_user_meta($userid, $var_names_array[$payment_type]."_expire", true) && payment_plans($payment_plan_names_array[$payment_type],'price')) {
			echo '<div class="clear20"></div>';
			echo '<div class="text-center">'.generate_payment_buttons($payment_plan_names_array[$payment_type], $userid, sprintf(__('Extend %s status','escortwp'), strtoupper($text_names_array[$payment_type])))."</div> <!--center-->";
			echo '<div class="clear5"></div>';
			echo '<small>'.format_price($payment_plan_names_array[$payment_type]).'</small>';
		}
	echo '</div>';
}

function woo_cancel_subscription_form_process() {
	if(!is_user_logged_in()) return false;

	if (isset($_POST['action']) && $_POST['action'] == 'cancel_subscription') {
		$current_user = wp_get_current_user();
		$userid = $current_user->ID;
		$payment_type = (int)$_POST['payment_type'];
		if($payment_type < 1 || $payment_type > 8) return false;

    	// '1' => 'premium'
    	// '2' => 'featured'
    	// '3' => 'tour'
    	// '5' => 'vip'
    	// '6' => 'agency'
    	// '7' => 'escort'
    	// '8' => 'escort'
    	switch ($payment_type) {
    		case '1':
    			break;
    		
    		case '2':
    			break;
    		
    		case '3':
    			break;
    		
    		case '5': // vip
					$order_id = get_user_meta($userid, 'vip_txn_id', true);
					// $exp = get_next_payment_date($order_id);
					// prd($exp);
					// if($exp) {
					// 	update_user_meta($userid, 'vip_expire', $exp);
					// }
					// $payment_name = __('VIP', 'escortwp');
    			break;

    		case '6': // agency
		    		// $agency_profile_id = get_option('agencypostid'.$userid);
					// $order_id = get_user_meta($agency_profile_id , 'agency_tax_id', true);
    			break;
    	}

		// global $woocommerce; // in case of need…
		$order = new WC_Order($order_id);
		if (!empty($order)) $order->update_status('cancelled');

		// include_once(ABSPATH."wp-admin/includes/user.php");
		// wp_delete_user($userid);
		wp_redirect(get_bloginfo("url")); die();
	} // if admin
}
add_action('init', 'woo_cancel_subscription_form_process');

function order_subscription_canceled($customeremail, $paymenttype, $custom, $eventtype, $woo_order_id, $woo_product_id) {
	global $taxonomy_profile_name, $taxonomy_agency_name;
	$var_names_array = array('1' => 'premium', '2' => 'featured', '3' => 'tour', '5' => 'vip', '6' => 'agency', '7' => 'escort', '8' => 'escort');
	$text_names_array = array('1' => __('premium', 'escortwp'), '2' => __('featured', 'escortwp'), '3' => __('tour', 'escortwp'), '5' => __('VIP', 'escortwp'), '6' => __('agency', 'escortwp'), '7' => __('escort', 'escortwp'), '8' => __('escort', 'escortwp'));
	$body = __('Hello','escortwp').'<br /><br /><br />'.sprintf(__('Your %s subscription from','escortwp'),$text_names_array[$paymenttype]).' '.get_option("email_sitename").' ';
	switch ($eventtype) {
		case '1': // Cancellation
				$subject = __('was canceled','escortwp');
				$body .= __('was canceled','escortwp').'.<br />';
				if(class_exists('WC_Subscriptions') && get_post_meta($woo_product_id, '_subscription_price', true)) {
				    if(WC_Subscriptions_Renewal_Order::is_renewal($woo_order_id)) {
				        $parent_id = WC_Subscriptions_Renewal_Order::get_parent_order_id($woo_order_id); /* This gets the original parent order id */
				        $parent_order = new WC_Order($parent_id);
				        foreach ($parent_order->get_items() as $item) { /* This loops through each item in the order */
				            $expiration = WC_Subscriptions_Order::get_next_payment_date($parent_order, $item['product_id']); /* This should get the next payment date... */
				        }
				    } elseif (WC_Subscriptions_Order::order_contains_subscription($woo_order_id)) {
				    	$woo_order_id_obj = new WC_Order($woo_order_id);
				        foreach($woo_order_id_obj->get_items() as $item ) { /* This loops through each item in the order */
				            $expiration = WC_Subscriptions_Order::get_next_payment_date($woo_order_id, $item['product_id']); /* This should get the next payment date... */
				        }
				    }
				    $expiration = strtotime($expiration);
				    pr($expiration);
				}

			break;

		case '2': // Expiration
				// $expiration = strtotime("-1 days");
				// $subject = __('has expired','escortwp');
				// $body .= __('has expired','escortwp').'.<br />';
			break;

		case '3': // Refund / Chargeback / Return / Void
				// $body = __('Hello','escortwp').'<br /><br />'.__('Your payment for','escortwp').' '.get_option("email_sitename").' ';
				// $subject = __('was refunded','escortwp');
				// $body .= __('was refunded','escortwp').'.<br />';
				// $expiration = strtotime("-1 days");
			break;
	}
	prd($expiration);


	if($expiration) {
		update_post_meta($custom, $var_names_array[$paymenttype].'_expire', $expiration);
	}
	delete_post_meta($custom, $var_names_array[$paymenttype].'_renew');

	if($send_email)
		dolce_email("", "", $customeremail, __('Your subscription on','escortwp')." ".get_option("email_sitename")." ".$subject, $body);
}


// Mark orders with virtual items as completed after payment
add_action( 'woocommerce_payment_complete', 'custom_mark_virtual_orders_completed' );
function custom_mark_virtual_orders_completed( $order_id ) {
    // Get the order object
    $order = wc_get_order( $order_id );

    // Check if the order contains virtual products
    $virtual_order = false;

    if ( count( $order->get_items() ) > 0 ) {
        foreach ( $order->get_items() as $item ) {
            // Check if the product is virtual
            if ( $item->get_product()->is_virtual() ) {
                $virtual_order = true;
                break;
            }
        }
    }

    // If the order contains virtual items, mark it as completed
    if ( $virtual_order ) {
        $order->update_status( 'completed' );
    }
}