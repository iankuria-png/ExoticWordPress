<?php
/*
Template Name: Register Main Page
*/

if (is_user_logged_in() || (get_option("hide2") == "1" && get_option("hide3") == "1" && get_option("hide9") == "1")) { wp_redirect(get_bloginfo('url')); die(); }
global $taxonomy_agency_name, $taxonomy_profile_url, $taxonomy_profile_name_plural;
get_header(); ?>

<div class="registerpage">
	<h3 class="pagetitle"><?php _e('Create an Account','escortwp'); ?></h3>

	<?php if(get_option("hide2") != "1") { // if independent registration disabled ?>
	<div class="usertype rad5">
		<div class="usertype-title pinkdegrade rad3"><h4><?php printf(esc_html__('Register as Independent %s','escortwp'),ucwords($taxonomy_profile_name)); ?></h4></div>
		<div class="usertype-content">
			<ul class="userlist userlist-free">
				<li><span class="icon icon-ok"></span><?php _e('Add a single profile','escortwp'); ?></li>
				<li><span class="icon icon-ok"></span><?php _e('Add profile pictures','escortwp'); ?></li>
				<li><span class="icon icon-ok"></span><?php _e('Add contact information','escortwp'); ?></li>
				<?php if(payment_plans('premium','price')) { ?>
					<li><span class="icon icon-ok"></span><?php _e('Upgrade to premium','escortwp'); ?> <?php echo get_reg_price('premium'); ?></li>
				<?php } ?>
				<?php if(payment_plans('featured','price')) { ?>
					<li><span class="icon icon-ok"></span><?php _e('Featured position','escortwp'); ?> <?php echo get_reg_price('featured'); ?></li>
				<?php } ?>
				<?php if(get_option("hide8") != "1") { ?>
					<li><span class="icon icon-ok"></span><?php _e('Add tours','escortwp'); ?> <?php echo get_reg_price('tours'); ?></li>
				<?php } ?>
				<?php if(get_option("hide5") != "1") { ?><li><span class="icon icon-ok"></span><?php _e('Add blacklisted clients','escortwp'); ?></li><?php } ?>
				<?php if(get_option("hide6") != "1" && get_option("allowadpostingprofiles") == "1") { ?>
					<li><span class="icon icon-ok"></span><?php _e('Post classified ads','escortwp'); ?></li>
				<?php } ?>
				<li><span class="icon icon-ok"></span><?php _e('many more','escortwp'); ?></li>
			</ul>
			<div class="usertype-bottom">
				<?php echo get_reg_price('indescreg','free'); ?>
				<a href="<?php echo get_permalink(get_option('escort_reg_page_id')); ?>" class="registerbutton greenbutton rad3 l"><?php _e('Register here','escortwp'); ?><span class="icon-angle-right"></span></a>
			</div>
			<div class="clear"></div>
		</div>	<!-- usertype-content -->
	</div> <!-- usertype -->
	<?php } // if independent registration disabled ?>


	<?php if(get_option("hide3") != "1") { // if agency registration disabled ?>
	<div class="usertype rad5">
		<div class="usertype-title pinkdegrade rad3"><h4><?php printf(esc_html__('Register as %s','escortwp'),ucwords($taxonomy_agency_name)); ?></h4></div>
		<div class="usertype-content">
			<ul class="userlist userlist-free">
				<li><span class="icon icon-ok"></span><?php printf(esc_html__('Add %s under a single account','escortwp'),$taxonomy_profile_name_plural); ?> <?php echo get_reg_price('agescortreg'); ?></li>
				<li><span class="icon icon-ok"></span><?php _e('Add profile pictures','escortwp'); ?></li>
				<li><span class="icon icon-ok"></span><?php _e('Can add contact information','escortwp'); ?></li>
				<?php if(get_option("hide6") != "1" && get_option("allowadpostingagencies") == "1") { ?>
					<li><span class="icon icon-ok"></span><?php _e('Post classified ads','escortwp'); ?></li>
				<?php } ?>
				<?php if(payment_plans('premium','price')) { ?>
					<li><span class="icon icon-ok"></span><?php _e('Upgrade a profile to premium','escortwp'); ?> <?php echo get_reg_price('premium'); ?></li>
				<?php } ?>
				<?php if(payment_plans('featured','price')) { ?>
					<li><span class="icon icon-ok"></span><?php _e('Featured position for a profile','escortwp'); ?> <?php echo get_reg_price('featured'); ?></li>
				<?php } ?>
				<?php if(get_option("hide8") != "1") { ?>
					<li><span class="icon icon-ok"></span><?php _e('Add tours to profiles','escortwp'); ?> <?php echo get_reg_price('tours'); ?></li>
				<?php } ?>
				<?php if(get_option("hide5") != "1") { ?><li><span class="icon icon-ok"></span><?php _e('Add blacklisted clients','escortwp'); ?></li><?php } ?>
				<li><span class="icon icon-ok"></span><?php _e('many more','escortwp'); ?></li>
			</ul>
			<div class="usertype-bottom">
				<?php echo get_reg_price('agreg','free'); ?>
				<a href="<?php echo get_permalink(get_option('agency_reg_page_id')); ?>" class="registerbutton greenbutton rad3 l"><?php _e('Register here','escortwp'); ?><span class="icon-angle-right"></span></a>
			</div>
			<div class="clear"></div>
		</div>	<!-- usertype-content -->
	</div> <!-- usertype -->
	<?php } // if agency registration disabled ?>


	<?php if(get_option("hide9") != "1") { // if member registration disabled ?>
	<div class="usertype rad5">
		<div class="usertype-title pinkdegrade rad3"><h4><?php _e('Register as Normal User','escortwp'); ?></h4></div>
		<div class="usertype-content">
			<ul class="userlist userlist-free">
				<li><span class="icon icon-ok"></span><?php _e('Mark favorite profiles','escortwp'); ?></li>
				<li><span class="icon icon-ok"></span><?php _e('See profile photos','escortwp'); ?>
					<?php
						if(payment_plans('vip','price') && payment_plans('vip','extra','hide_photos')) {
							echo ' <span class="showprice rad3"><b>'.__('only VIP','escortwp').'</b></span>';
						}
					?>
				</li>
				<li><span class="icon icon-ok"></span><?php printf(esc_html__('Can contact %s','escortwp'),$taxonomy_profile_name_plural); ?>
					<?php
						if(payment_plans('vip','price') && payment_plans('vip','extra','hide_contact_info')) {
							echo ' <span class="showprice rad3"><b>'.__('only VIP','escortwp').'</b></span>';
						}
					?>
				</li>
				<li><span class="icon icon-ok"></span><?php printf(esc_html__('Can add reviews to %s and rate them','escortwp'),$taxonomy_profile_name_plural); ?>
					<?php
						if(payment_plans('vip','price') && payment_plans('vip','extra','hide_review_form')) {
							echo ' <span class="showprice rad3"><b>'.__('only VIP','escortwp').'</b></span>';
						}
					?>
				</li>
				<?php if(get_option("hide6") != "1" && get_option("allowadpostingmembers") == "1") { ?>
					<li><span class="icon icon-ok"></span><?php _e('Post classified ads','escortwp'); ?></li>
				<?php } ?>
				<?php if(payment_plans('vip','price')) { ?>
				<li><span class="icon icon-ok"></span><?php _e('VIP membership','escortwp'); ?> <?php echo get_reg_price('vip'); ?></li>
				<?php } ?>
			</ul>
			<div class="usertype-bottom">
				<?php echo get_reg_price('user','free'); ?>
				<a href="<?php echo get_permalink(get_option('member_register_page_id')); ?>" class="registerbutton greenbutton rad3 l"><?php _e('Register here','escortwp'); ?><span class="icon-angle-right"></span></a>
			</div>
			<div class="clear"></div>
		</div>	<!-- usertype-content -->
	</div> <!-- usertype -->
	<?php } // if member registration disabled ?>
	<div class="clear20"></div>
</div> <!-- registerpage -->

<div class="clear"></div>
<?php get_footer(); ?>