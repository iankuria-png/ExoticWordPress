<?php
/*
Template Name: Payment Settings
*/

$current_user = wp_get_current_user();
if (!current_user_can('level_10')) { wp_redirect(get_bloginfo("url")); exit; }
$err = ""; $ok = "";
$payment_plans = payment_plans();
if (isset($_POST['action']) && $_POST['action'] == 'paymentsettings') {
	$show_coupon_checkout = (int)$_POST['show_coupon_checkout'];
	update_option('show_coupon_checkout', $show_coupon_checkout);
	$show_address_checkout = (int)$_POST['show_address_checkout'];
	update_option('show_address_checkout', $show_address_checkout);

	foreach ($payment_plans as $plan_name => $plan) {
		if($_POST[$plan_name]) {
            $payment_plans[$plan_name]['price'] = filter_var($_POST[$plan_name]['price'], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
            if(!$payment_plans[$plan_name]['price']) {
            	if($payment_plans[$plan_name]['woo_product_id']) {
            		wp_delete_post($payment_plans[$plan_name]['woo_product_id']);
            	}
				if(array_key_exists('extra', $payment_plans[$plan_name])) {
					foreach ($payment_plans[$plan_name]['extra'] as $key => $value) {
						$payment_plans[$plan_name]['extra'][$key] = "0";
					}
				}
	            continue;
            }

            if(array_key_exists('duration', $payment_plans[$plan_name])) $payment_plans[$plan_name]['duration'] = (int)$_POST[$plan_name]['duration'];
            if(array_key_exists('exp', $payment_plans[$plan_name])) $payment_plans[$plan_name]['exp'] = (int)$_POST[$plan_name]['exp'];
            if(array_key_exists('extra', $payment_plans[$plan_name])) {
            	foreach ($payment_plans[$plan_name]['extra'] as $key => $value) {
		            $payment_plans[$plan_name]['extra'][$key] = (int)$_POST[$plan_name]['extra'][$key];
            	}
            }

            $wooo_product = get_post((int)$payment_plans[$plan_name]['woo_product_id']);
            if(!$payment_plans[$plan_name]['woo_product_id'] || !$wooo_product || $wooo_product->post_type != "product") {
				$product_args = array(
					'post_title' => sprintf(esc_html__($payment_plans[$plan_name]['woo_product_name'][0],'escortwp'),${$payment_plans[$plan_name]['woo_product_name'][1]}),
					'post_content' => sprintf(esc_html__($payment_plans[$plan_name]['woo_product_name'][0],'escortwp'),${$payment_plans[$plan_name]['woo_product_name'][1]}),
					'post_name' => sprintf(esc_html__($payment_plans[$plan_name]['woo_product_name'][0],'escortwp'),${$payment_plans[$plan_name]['woo_product_name'][1]}),
					'post_status' => 'publish',
					'post_author' => $current_user->ID,
					'post_type' => 'product',
					'ping_status' => 'closed',
				);
				// Insert the post into the database
				$product_id = wp_insert_post($product_args);
	            $payment_plans[$plan_name]['woo_product_id'] = $product_id;
	            update_post_meta($product_id, '_virtual', 'yes');
	            update_post_meta($product_id, '_downloadable', 'no');
            } else {
				$product_id = $wooo_product->ID;
            }
            update_post_meta($product_id, '_regular_price', $payment_plans[$plan_name]['price']);
            update_post_meta($product_id, '_price', $payment_plans[$plan_name]['price']);
            update_post_meta($product_id, 'payment_type', $plan_name);
		}
	}
	update_option('payment_plans', $payment_plans);
	$ok = "ok";
}
$show_address_checkout = get_option('show_address_checkout');
$show_coupon_checkout = get_option('show_coupon_checkout');

$currency = get_option('woocommerce_currency');

get_header(); ?>

<div class="contentwrapper">
	<div class="body">
		<div class="bodybox payment-settings-page">
			<?php
			if(!is_woocommerce_active) {
				$action = 'install-plugin';
				$slug = 'woocommerce';
				$install_link = wp_nonce_url(
				    add_query_arg(
				        array(
				            'action' => $action,
				            'plugin' => $slug
				        ),
				        admin_url( 'update.php' )
				    ),
				    $action.'_'.$slug
				);
				echo "<div class=\"ok rad25\">";
					echo __('The payments section works with the help of the free plugin WooCommerce.','escortwp')."<br />";
					echo __('Go to your Plugins section and install WooCommerce first','escortwp')."<br />";
					echo __('or','escortwp')."<br />";
					echo '<a href="'.$install_link.'">'.__('Click here to install WooCommerce automatically','escortwp').'</a>';
				echo "</div>";
			} else {
			?>
				<?php if ($err) { echo "<div class=\"err rad25\">$err</div>"; } ?>
				<?php if ($ok) { echo "<div class=\"ok rad25\">".__('Your settings have been saved','escortwp')."</div>"; } ?>
				<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="form-styling" novalidate>
					<input type="hidden" name="action" value="paymentsettings" />

		      		<h3 class="settingspagetitle"><?php _e('Payment Settings','escortwp'); ?></h3>
					<div class="clear30"></div>
					<div class="text-center"><input type="submit" name="submit" value="<?php _e('Save settings','escortwp'); ?>" class="pinkbutton rad3" /></div> <!--center-->
	        		<div class="clear20"></div>
	        		<?php foreach ($payment_plans as $plan_name => $plan) { ?>
						<fieldset class="fieldset rad5">
							<legend class="rad25"><?=ucfirst(strtolower(sprintf(esc_html__($plan['title'][0],'escortwp'),(isset($plan['title'][1]) ? ${$plan['title'][1]} : ""),(isset($plan['title'][2]) ? ${$plan['title'][2]} : ""))))?></legend>
							<div class="form-label">
								<label><?=ucfirst(strtolower(sprintf(esc_html__($plan['label'][0],'escortwp'),(isset($plan['label'][1]) ? ${$plan['label'][1]} : ""),(isset($plan['label'][2]) ? ${$plan['label'][2]} : ""))))?></label>
							</div>
							<div class="form-input">
								<?php
								if($plan['woo_product_id']) {
									$plan['price'] = get_post_meta($plan['woo_product_id'], '_price', true);
								}
								?>
								<input type="number" min="1" step="any" name="<?=$plan_name?>[price]" id="<?=$plan_name?>price" class="input" value="<?=$plan['price']?>" /> <?=$currency?>
								<div class="clear"></div>
								<?php
								if($plan['label_help']) {
									echo '<small><i>!</i> '.sprintf(esc_html__($plan['label_help'][0],'escortwp'),(isset($plan['label_help'][1]) ? ${$plan['label_help'][1]} : "")).'</small>';
								}
								?>
							</div> <!-- price --> <div class="formseparator"></div>

							<?php
							$woo_product = wc_get_product($plan['woo_product_id']);
							if($plan['woo_product_id'] && class_exists('WC_Subscriptions') && get_post_meta($plan['woo_product_id'], '_subscription_price', true)) {
							} else {
								if(array_key_exists('duration', $plan) ) { ?>
									<div class="form-label">
										<label><?php _e('The price is for a period of','escortwp'); ?></label>
									</div>
									<div class="form-input">
										<select name="<?=$plan_name?>[duration]">
											<option value=""><?php _e('Forever','escortwp'); ?></option>
									    	<?php
												foreach($payment_duration_a as $key => $p) {
													$selected = $plan['duration'] == $key ? ' selected="selected"' : "";
													echo '<option value="'.$key.'"'.$selected.'>'.__($p[0],'escortwp').'</option>'."\n";
													unset($selected);
												}
											?>
										</select>
										<div class="clear10"></div>
										<?php
											echo '<small>';
											$woo_sub_link = sprintf('<a href="%s" target="_blank">%s</a>',"https://woocommerce.com/products/woocommerce-subscriptions/",__('WooCommerce Subscriptions','escortwp'));
											printf(__('If you would like to set up this payment as a recurring payment then you will first need to install the %s plugin.','escortwp'),$woo_sub_link);
											echo "<br />";
											_e('After this you will be able to set up the payment as recurring by editing the payment in the WordPress section.','escortwp');
											echo '</small>';
										?>
									</div> <!-- duration --> <div class="formseparator"></div>
								<?php }
							}
							?>

							<?php if(array_key_exists('exp', $plan) ) { ?>
							<div class="form-label">
								<label class="nopadding"><?=sprintf(esc_html__($plan['exp_text'][0],'escortwp'),(isset($plan['exp_text'][1]) ? ${$plan['exp_text'][1]} : ""))?></label>
							</div>
							<div class="form-input">
								<label for="<?=$plan_name?>expdelete"><input type="radio" name="<?=$plan_name?>[exp]" id="<?=$plan_name?>expdelete" value="1"<?php if($plan['exp'] == "1") { echo ' checked="checked"'; } ?> /> <?=__('Delete profile','escortwp')?></label>
								<div class="clear10"></div>
								<label for="<?=$plan_name?>expprivate"><input type="radio" name="<?=$plan_name?>[exp]" id="<?=$plan_name?>expprivate" value="2"<?php if($plan['exp'] == "2") { echo ' checked="checked"'; } ?>  /> <?=__('Set profile to private','escortwp')?></label>
							</div> <!-- expiration --> <div class="formseparator"></div>
							<?php } ?>

							<?php if($plan_name == "vip") { ?>
								<div class="form-label">
									<label class="nopadding"><?php _e('Keep the following profile sections locked until payment is made','escortwp'); ?></label>
								</div>
								<div class="form-input">
									<label for="hide_photos">
										<input type="checkbox" name="<?=$plan_name?>[extra][hide_photos]" value="1" id="hide_photos"<?php if($plan['extra']['hide_photos'] == "1") { echo ' checked'; } ?> />
										<?php _e('All photos except the main one','escortwp'); ?>
									</label><div class="clear5"></div>

									<label for="hide_contact_info">
										<input type="checkbox" name="<?=$plan_name?>[extra][hide_contact_info]" value="1" id="hide_contact_info"<?php if($plan['extra']['hide_contact_info'] == "1") { echo ' checked'; } ?> />
										<?php _e('Contact information','escortwp'); ?>
									</label><div class="clear5"></div>

									<label for="hide_review_form">
										<input type="checkbox" name="<?=$plan_name?>[extra][hide_review_form]" value="1" id="hide_review_form"<?php if($plan['extra']['hide_review_form'] == "1") { echo ' checked'; } ?> />
										<?php _e('Add review section','escortwp'); ?>
									</label><div class="clear5"></div>
								</div> <!-- --> <div class="formseparator"></div>
							<?php } ?>

							<?php
							if($plan['woo_product_id'] && class_exists('WC_Subscriptions') && get_post_meta($plan['woo_product_id'], '_subscription_price', true)) {
								echo '<div class="text-center">';
									echo '<div class="clear20"></div>';
									_e('This is a recurring payment. Edit the duration from the WordPress section.');
								echo '</div>';
							}
							if($plan['woo_product_id']) {
								echo '<div class="text-center edit-payment-link-wrapper">';
								edit_post_link(__('Edit payment plan in WordPress','escortwp'), '<div class="clear5"></div>', '<div class="clear"></div>', $plan['woo_product_id']);
								echo '</div>';
							}
							?>
						</fieldset> <!-- paymentpackage <?=$plan_name?> -->
						<div class="clear30"></div>
	        		<?php } ?>

					<div class="form-label">
				    	<label><?=__('Show coupon field in checkout page', 'escortwp')?></label>
				    </div>
					<div class="form-input">
					    <label for="show_coupon_checkoutyes"><input type="radio" name="show_coupon_checkout" value="1" id="show_coupon_checkoutyes"<?php if($show_coupon_checkout == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    		<label for="show_coupon_checkoutno"><input type="radio" name="show_coupon_checkout" value="2" id="show_coupon_checkoutno"<?php if($show_coupon_checkout == '2') { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
			    		<small><i>!</i> <?php _e('Some payment processors require an address','escortwp'); ?>.</small>
				    </div> <!-- show coupon in checkout --> <div class="formseparator"></div>

					<div class="form-label">
				    	<label><?=__('Show address fields in checkout page', 'escortwp')?></label>
				    </div>
					<div class="form-input">
					    <label for="show_address_checkoutyes"><input type="radio" name="show_address_checkout" value="1" id="show_address_checkoutyes"<?php if($show_address_checkout == "1") { echo ' checked'; } ?> /> <?php _e('Yes','escortwp'); ?></label>
			    		<label for="show_address_checkoutno"><input type="radio" name="show_address_checkout" value="2" id="show_address_checkoutno"<?php if($show_address_checkout == '2') { echo ' checked'; } ?> /> <?php _e('No','escortwp'); ?></label>
			    		<small><i>!</i> <?php _e('Some payment processors require an address','escortwp'); ?>.</small>
				    </div> <!-- show address in checkout --> <div class="formseparator"></div>


					<div class="clear20"></div>
					<div class="text-center"><input type="submit" name="submit" value="<?php _e('Save settings','escortwp'); ?>" class="pinkbutton rad3" /></div> <!--center-->
		        </form>
		    <?php } // woocommerce is active or not ?>
		</div> <!-- BODY BOX -->
	</div> <!-- BODY -->
</div> <!-- contentwrapper -->

<?php get_sidebar("left"); ?>
<?php get_sidebar("right"); ?>
<div class="clear"></div>
<?php get_footer(); ?>