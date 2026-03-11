<?php
/*
Template Name: Register Member - See Reviews
*/

global $taxonomy_location_url, $taxonomy_profile_url, $taxonomy_agency_url;
$current_user = wp_get_current_user();
if (get_option("escortid".$current_user->ID) != "member") { wp_redirect(get_bloginfo("url")); exit; }

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox">
        	<h3><?php _e('Your Reviews','escortwp'); ?></h3>
			<?php
			if ( is_user_logged_in() ) {
				$userid = $current_user->ID;

				$args = array(
					'author' => $userid,
					'post_type' => 'review',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'orderby' => 'date',
					'order' => 'ASC'
				);

				query_posts($args);
				if ( have_posts() ) : ?>
					<div class="clear20"></div>
					<table class="listagencies rad3">
						<tr class="trhead rad3">
							<th class="rad3">&nbsp;</th>
							<th class="rad3"><?php _e('User','escortwp'); ?></th>
					        <th class="rad3"><?php echo ucfirst($taxonomy_profile_name); ?></th>
					        <th class="rad3"><?php _e('Date','escortwp'); ?></th>
					        <th class="rad3"><?php _e('Rating','escortwp'); ?></th>
						</tr>
						<?php
						while ( have_posts() ) : the_post();
							$country = get_term( get_post_meta(get_the_ID(), 'countrymeeting', true), $taxonomy_location_url);
							$city = get_term( get_post_meta(get_the_ID(), 'citymeeting', true), $taxonomy_location_url);
							if (get_post_meta(get_the_ID(), 'reviewfor', true) == 'agency') {
								$escort_or_agency = get_post(get_post_meta(get_the_ID(), 'agencyid', true));
								$rating_number = get_post_meta(get_the_ID(), 'rateagency', true);
							} elseif (get_post_meta(get_the_ID(), 'reviewfor', true) == 'profile') {
								$escort_or_agency = get_post(get_post_meta(get_the_ID(), 'escortid', true));
								$rating_number = get_post_meta(get_the_ID(), 'rateescort', true);
							}
							?>
							<tr class="agencytr">
								<td><a href="<?php the_permalink() ?>"><?php _e('View','escortwp'); ?></a></td>
						    	<td><?php echo get_the_author_meta('display_name'); ?></td>
								<td><?php echo '<a href="'.get_permalink($escort_or_agency->ID).'">'.$escort_or_agency->post_title.'</a>'; ?></td>
						        <td><?php echo the_time("d F Y"); ?></td>
						        <td><div class="starrating l"><div class="starrating_stars star<?php echo $rating_number; ?>"></div></div></td>
						    </tr>
							<?php
						endwhile;
						?>
					</table>
				<?php
				else:
					_e('No reviews yet','escortwp');
				endif;
				wp_reset_query();
			} else { // is user logged in else
				_e('You need to login or register to see this page','escortwp');
			} // is user logged in
			?>
            <div class="clear"></div>
        </div> <!-- BODY BOX -->

        <div class="clear"></div>
    </div> <!-- BODY -->
	</div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>