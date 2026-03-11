<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $taxonomy_location_url, $taxonomy_profile_url;
$args = array(
	'post_type' => $taxonomy_profile_url,
	'orderby' => 'rand',
	'meta_query' => array( array('key' => 'featured', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC') ),
	'posts_per_page' => get_option("headerslideritems")
);
$noneed = "0";
if(get_option("locationsliderpage") == "1" && $wp_query->queried_object->taxonomy == $taxonomy_location_url) {
	$args['tax_query'] = array(array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $wp_query->queried_object->term_taxonomy_id));
	$profiles = new WP_Query($args);
	if ($profiles->found_posts > 0) {
		$noneed = "1";
	}
}
if($noneed == "0") {
	unset($args['tax_query']);
	$profiles = new WP_Query($args);
}

if ($profiles->have_posts()) :
	?>
<div class="all all-header-slider"<?php if(get_option("autoscrollheaderslider") == "1") echo ' data-autoscroll="yes"'; ?>>
	<div class="sliderall">
		<div class="slider owl-carousel">
			<?php
			while ( $profiles->have_posts() ) : $profiles->the_post();
				$category = wp_get_post_terms(get_the_ID(), $taxonomy_location_url);
				$linktitle = get_the_title();
				$imagealt = get_the_title();
				$premium = get_post_meta(get_the_ID(), "premium", true);
				$videos = get_children( array('post_parent' => get_the_ID(), 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'video', 'numberposts' => '1') );
			?>
			<div class="slide l">
	    		<a href="<?php the_permalink(); ?>" class="girlimg" title="<?php echo $linktitle; ?>">
	        		<img src="<?php echo get_first_image(get_the_ID(), 2); ?>" data-responsive-img-url="<?php echo get_first_image(get_the_ID(), '4'); ?>" alt="<?php echo $imagealt; ?>" class="mobile-ready-img" />
	        		<?php
					if ($premium == "1") { echo '<div class="premiumlabel"><span>'.__('PREMIUM','escortwp').'</span></div>'; }
					$escort_label = array();
					$daysago = date("Y-m-d H:i:s", strtotime("-".get_option('newlabelperiod')." days"));
					if (get_the_date('Y-m-d H:i:s') > $daysago) { $escort_label[] = '<span class="label label-new rad3 pinkdegrade">'.__('NEW','escortwp').'</span>'; }

					if (get_post_status() == "private") { $escort_label[] = '<span class="label label-private rad3 reddegrade">'.__('PRIVATE','escortwp').'</span>'; }

					$verified = get_post_meta(get_the_ID(), "verified", true);
					if ($verified == "1") { $escort_label[] = '<span class="label label-verified rad3 greendegrade">'.__('VERIFIED','escortwp').'</span>'; }

					$last_online = get_user_meta(get_the_author_meta('ID'), 'last_online', true);
					if($last_online >= (current_time('timestamp') - 60*5)) {
						$escort_label[] = '<span class="label online-label"><span class="icon icon-circle"></span><span class="text-label">'.__('Online','escortwp').'</span></span>';
					}

					if($escort_label) {
						echo '<span class="labels">'.implode('<br />', $escort_label).'</span>';
					}

					if(count($videos) > 0) {
						echo '<span class="label-video"><img src="'.get_template_directory_uri().'/i/video-th-icon.png" alt="" /></span>';
					}
	        		?>
		        	<span class="girlinfo">
						<span class="modelinfo">
			        		<span class="modelname"><?php the_title(); ?></span>
							<span class="clear"></span>
							<?php
							$location = array();
							$city = wp_get_post_terms(get_the_ID(), $taxonomy_location_url);
							if($city) {
								$location[] = $city[0]->name;

								$state = get_term($city[0]->parent, $taxonomy_location_url);
								if($state) {
									$location[] = $state->name;

									$country = get_term($state->parent, $taxonomy_location_url);
									if(!is_wp_error($country)) {
										$location[] = $country->name;
									}
								}
							}
							?>
							<span class="modelinfo-location"><?=implode(", ", $location)?></span>
			            </span>
		    	    </span> <!-- girlinfo -->
	    	    </a> <!-- GIRL IMG -->
		    </div> <!-- slide -->
			<?php endwhile; ?>
		</div> <!-- slider -->
	</div> <!-- slider all -->
	<div class="clear"></div>
</div> <!-- ALL -->
<?php
endif;
wp_reset_postdata();
?>