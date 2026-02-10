<?php
/*
Template Name: Register Main Page
*/

if (is_user_logged_in() || (get_option("hide2") == "1" && get_option("hide3") == "1" && get_option("hide9") == "1")) {
	wp_redirect(get_bloginfo('url'));
	die();
}
global $taxonomy_agency_name, $taxonomy_profile_url, $taxonomy_profile_name_plural;
get_header(); ?>

<div class="registerpage-container">
	<div class="registerpage-hero">
		<div class="registerpage-intro">
			<p class="registerpage-eyebrow"><?php _e('Exotic Escorts', 'escortwp'); ?></p>
			<h1 class="registerpage-title"><?php _e('Create Your Account', 'escortwp'); ?></h1>
			<p class="registerpage-subtitle">
				<?php _e('Choose the account that matches how you work. Fast approval, secure signup, premium visibility.', 'escortwp'); ?>
			</p>
			<div class="registerpage-trust">
				<?php _e('Secure signup', 'escortwp'); ?> <span class="dot">•</span>
				<?php _e('Fast approval', 'escortwp'); ?> <span class="dot">•</span>
				<?php _e('Real profiles', 'escortwp'); ?>
			</div>
		</div>

		<div class="registerpage-bento">

		<?php if (get_option("hide2") != "1") { // if independent registration disabled ?>
			<div class="usertype glass-card card--independent">
				<div class="usertype-header">
					<div class="usertype-icon">
						<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
							stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
							<circle cx="12" cy="7" r="4"></circle>
						</svg>
					</div>
					<h4 class="pink-gradient-text">
						<?php printf(esc_html__('Independent %s', 'escortwp'), ucwords($taxonomy_profile_name)); ?>
					</h4>
					<p class="usertype-subtitle">
						<?php _e('Create a personal profile to manage your own bookings and content.', 'escortwp'); ?>
					</p>
				</div>
				<div class="usertype-content">
					<ul class="userlist userlist-modern">
						<li><span class="icon-check" aria-hidden="true"></span><?php _e('Add a single profile', 'escortwp'); ?></li>
						<li><span class="icon-check" aria-hidden="true"></span><?php _e('Add profile pictures', 'escortwp'); ?></li>
						<li><span class="icon-check" aria-hidden="true"></span><?php _e('Add contact information', 'escortwp'); ?></li>
						<?php if (payment_plans('premium', 'price')) { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('Upgrade to premium', 'escortwp'); ?> <span
									class="price-badge"><?php echo get_reg_price('premium'); ?></span></li>
						<?php } ?>
						<?php if (payment_plans('featured', 'price')) { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('Featured position', 'escortwp'); ?> <span
									class="price-badge"><?php echo get_reg_price('featured'); ?></span></li>
						<?php } ?>
						<?php if (get_option("hide8") != "1") { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('Add tours', 'escortwp'); ?> <span
									class="price-badge"><?php echo get_reg_price('tours'); ?></span></li>
						<?php } ?>
						<?php if (get_option("hide5") != "1") { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('Add blacklisted clients', 'escortwp'); ?></li>
						<?php } ?>
						<?php if (get_option("hide6") != "1" && get_option("allowadpostingprofiles") == "1") { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('Post classified ads', 'escortwp'); ?></li>
						<?php } ?>
					</ul>
					<div class="usertype-footer">
						<div class="price-display"><?php echo get_reg_price('indescreg', 'free'); ?></div>
						<a href="<?php echo get_permalink(get_option('escort_reg_page_id')); ?>"
							class="registerbutton modern-btn icon-btn full-width"><?php _e('Register Now', 'escortwp'); ?>
							<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
								stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<line x1="5" y1="12" x2="19" y2="12"></line>
								<polyline points="12 5 19 12 12 19"></polyline>
							</svg></a>
					</div>
				</div>
			</div>
		<?php } ?>


		<?php if (get_option("hide3") != "1") { // if agency registration disabled ?>
			<div class="usertype glass-card card--agency">
				<div class="usertype-header">
					<div class="usertype-icon">
						<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
							stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
							<path d="M9 22v-4h6v4"></path>
							<path d="M8 6h.01"></path>
							<path d="M16 6h.01"></path>
							<path d="M12 6h.01"></path>
							<path d="M12 10h.01"></path>
							<path d="M12 14h.01"></path>
							<path d="M16 10h.01"></path>
							<path d="M16 14h.01"></path>
							<path d="M8 10h.01"></path>
							<path d="M8 14h.01"></path>
						</svg>
					</div>
					<h4 class="pink-gradient-text">
						<?php printf(esc_html__('Register as %s', 'escortwp'), ucwords($taxonomy_agency_name)); ?>
					</h4>
					<p class="usertype-subtitle">
						<?php printf(esc_html__('Manage multiple %s profiles under one agency account.', 'escortwp'), $taxonomy_profile_name_plural); ?>
					</p>
				</div>
				<div class="usertype-content">
					<ul class="userlist userlist-modern">
						<li><span
								class="icon-check" aria-hidden="true"></span><?php printf(esc_html__('Add %s under a single account', 'escortwp'), $taxonomy_profile_name_plural); ?>
							<span class="price-badge"><?php echo get_reg_price('agescortreg'); ?></span>
						</li>
						<li><span class="icon-check" aria-hidden="true"></span><?php _e('Add profile pictures', 'escortwp'); ?></li>
						<li><span class="icon-check" aria-hidden="true"></span><?php _e('Can add contact information', 'escortwp'); ?></li>
						<?php if (get_option("hide6") != "1" && get_option("allowadpostingagencies") == "1") { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('Post classified ads', 'escortwp'); ?></li>
						<?php } ?>
						<?php if (payment_plans('premium', 'price')) { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('Upgrade a profile to premium', 'escortwp'); ?> <span
									class="price-badge"><?php echo get_reg_price('premium'); ?></span></li>
						<?php } ?>
						<?php if (payment_plans('featured', 'price')) { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('Featured position for a profile', 'escortwp'); ?>
								<span class="price-badge"><?php echo get_reg_price('featured'); ?></span></li>
						<?php } ?>
						<?php if (get_option("hide8") != "1") { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('Add tours to profiles', 'escortwp'); ?> <span
									class="price-badge"><?php echo get_reg_price('tours'); ?></span></li>
						<?php } ?>
						<?php if (get_option("hide5") != "1") { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('Add blacklisted clients', 'escortwp'); ?></li>
						<?php } ?>
					</ul>
					<div class="usertype-footer">
						<div class="price-display"><?php echo get_reg_price('agreg', 'free'); ?></div>
						<a href="<?php echo get_permalink(get_option('agency_reg_page_id')); ?>"
							class="registerbutton modern-btn icon-btn full-width"><?php _e('Register Now', 'escortwp'); ?>
							<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
								stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<line x1="5" y1="12" x2="19" y2="12"></line>
								<polyline points="12 5 19 12 12 19"></polyline>
							</svg></a>
					</div>
				</div>
			</div>
		<?php } ?>


		<?php if (get_option("hide9") != "1") { // if member registration disabled ?>
			<div class="usertype glass-card card--member">
				<div class="usertype-header">
					<div class="usertype-icon">
						<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
							stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
							<circle cx="9" cy="7" r="4"></circle>
							<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
							<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
						</svg>
					</div>
					<h4 class="pink-gradient-text"><?php _e('Register as Normal User', 'escortwp'); ?></h4>
					<p class="usertype-subtitle">
						<?php _e('Join the community to follow, rate, and contact profiles.', 'escortwp'); ?>
					</p>
				</div>
				<div class="usertype-content">
					<ul class="userlist userlist-modern">
						<li><span class="icon-check" aria-hidden="true"></span><?php _e('Mark favorite profiles', 'escortwp'); ?></li>
						<li><span class="icon-check" aria-hidden="true"></span><?php _e('See profile photos', 'escortwp'); ?>
							<?php
							if (payment_plans('vip', 'price') && payment_plans('vip', 'extra', 'hide_photos')) {
								echo ' <span class="showprice badge-vip">' . __('only VIP', 'escortwp') . '</span>';
							}
							?>
						</li>
						<li><span
								class="icon-check" aria-hidden="true"></span><?php printf(esc_html__('Can contact %s', 'escortwp'), $taxonomy_profile_name_plural); ?>
							<?php
							if (payment_plans('vip', 'price') && payment_plans('vip', 'extra', 'hide_contact_info')) {
								echo ' <span class="showprice badge-vip">' . __('only VIP', 'escortwp') . '</span>';
							}
							?>
						</li>
						<li><span
								class="icon-check" aria-hidden="true"></span><?php printf(esc_html__('Can add reviews to %s and rate them', 'escortwp'), $taxonomy_profile_name_plural); ?>
							<?php
							if (payment_plans('vip', 'price') && payment_plans('vip', 'extra', 'hide_review_form')) {
								echo ' <span class="showprice badge-vip">' . __('only VIP', 'escortwp') . '</span>';
							}
							?>
						</li>
						<?php if (get_option("hide6") != "1" && get_option("allowadpostingmembers") == "1") { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('Post classified ads', 'escortwp'); ?></li>
						<?php } ?>
						<?php if (payment_plans('vip', 'price')) { ?>
							<li><span class="icon-check" aria-hidden="true"></span><?php _e('VIP membership', 'escortwp'); ?> <span
									class="price-badge"><?php echo get_reg_price('vip'); ?></span></li>
						<?php } ?>
					</ul>
					<div class="usertype-footer">
						<div class="price-display"><?php echo get_reg_price('user', 'free'); ?></div>
						<a href="<?php echo get_permalink(get_option('member_register_page_id')); ?>"
							class="registerbutton modern-btn icon-btn full-width"><?php _e('Register Now', 'escortwp'); ?>
							<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
								stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<line x1="5" y1="12" x2="19" y2="12"></line>
								<polyline points="12 5 19 12 12 19"></polyline>
							</svg></a>
					</div>
				</div>
			</div>
		<?php } ?>
		</div> <!-- registerpage-bento -->
	</div> <!-- registerpage-hero -->
</div> <!-- registerpage-container -->
<?php get_footer(); ?>
