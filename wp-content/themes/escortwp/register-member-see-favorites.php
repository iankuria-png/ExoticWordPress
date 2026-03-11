<?php
/*
Template Name: Register Member - See Favorites
*/

$current_user = wp_get_current_user();
if (get_option("escortid".$current_user->ID) != "member") { wp_redirect(get_bloginfo("url")); exit; }

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox">
        	<h3><?php printf(esc_html__('My Favorite %s','escortwp'),ucwords($taxonomy_profile_name_plural)); ?></h3>
			<?php
				$userid = $current_user->ID;
				$favorites = get_user_meta( $userid, "favorites", true);
				if ($favorites) {
					$favorites = array_unique(explode(",", $favorites));
					$i = 1;
					foreach($favorites as $fav) {
						$post = get_post($fav);
						include (get_template_directory() . '/loop-show-profile.php');
						unset($fav);
					} // foreach
				} else {
					printf(esc_html__('You have no favorite %s yet.','escortwp'),$taxonomy_profile_name_plural);
				}
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