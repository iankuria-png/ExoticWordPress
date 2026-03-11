<?php
global $taxonomy_location_url, $taxonomy_profile_name_plural, $taxonomy_agency_url;
get_header();
?>

		<div class="contentwrapper">
		<div class="body">
        	<div class="bodybox">
				<?php
				$from = get_term(get_queried_object_id(), $taxonomy_location_url);
				$from = $from->name;

				$posts_per_page = "40";
				$term = get_term(get_queried_object_id(), $taxonomy_location_url);
				if(isset($_GET['page'])) { $paged = (int)$_GET['page']; }
				if(isset($wp_query->query_vars['page'])) { $paged = $wp_query->query_vars['page']; }

				$premium_all_args = array(
					'post_type' => array($taxonomy_profile_url, $taxonomy_agency_url), 'posts_per_page' => "1", 'paged' => "1",
					'orderby' => 'meta_value_num', 'meta_key' => 'premium_since',
					'meta_query' => array( array( 'key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC' ) ),
					'tax_query' => array( array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id ) )
				);
				$premium_all = new WP_Query( $premium_all_args ); $premium_found_posts = $premium_all->found_posts;

				$normal_all_args = array(
					'post_type' => array($taxonomy_profile_url, $taxonomy_agency_url), 'posts_per_page' => "1", 'paged' => "1",
					'meta_query' => array( array( 'key' => 'premium', 'value' => '0', 'compare' => '=', 'type' => 'NUMERIC' ) ),
					'tax_query' => array( array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id ) )
				);
				$normal_all = new WP_Query( $normal_all_args ); $normal_found_posts = $normal_all->found_posts;

				$premium_args = array(
					'post_type' => array($taxonomy_profile_url, $taxonomy_agency_url),
					'posts_per_page' => $posts_per_page,
					'orderby' => 'meta_value_num', 'meta_key' => 'premium_since',
					'paged' => $paged,
					'meta_query' => array( array( 'key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC' ) ),
					'tax_query' => array( array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id ) )
				);
				$premium = new WP_Query( $premium_args );

				if($paged < "2") {
					$normal_offset = "0";
				} else {
					$normal_offset = ($paged-1)*$posts_per_page - $premium_found_posts;
					if($normal_offset < 0) { $normal_offset = "0"; }
				}
				$normal_args = array(
					'offset' => $normal_offset,
					'post_type' => array($taxonomy_profile_url, $taxonomy_agency_url),
					'posts_per_page' => $posts_per_page - count($premium->posts),
					'orderby' => 'date',
					'order' => 'DESC',
					'meta_query' => array( array( 'key' => 'premium', 'value' => '0', 'compare' => '=', 'type' => 'NUMERIC' ) ),
					'tax_query' => array( array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id ) )
				);
				$all = $premium;
				if(count($premium->posts) < $posts_per_page) {
					// only query the normal posts if we need to
					$normal = new WP_Query( $normal_args );

					$q = array_merge($premium->posts, $normal->posts);
					$all->post_count = count($q);
					$all->posts = $q;
				}

				// Show profiles
				$i = 1;
				if ( $all->have_posts() ) : 
					echo '<h3>'.ucfirst($taxonomy_profile_name_plural)." ".__('from','escortwp')." ".$from.'</h3>';
					$admin_link_text = __('Add some text here', 'escortwp');
					if(get_queried_object()->description && get_queried_object()->description != "randomly_generated_data") {
						echo '<div class="taxonomy-description-box">'.nl2br(get_queried_object()->description).'</div>';
						$admin_link_text = __('Edit the text', 'escortwp');
					}
					if(current_user_can('level_10')) {
						echo '<div class="text-center"><a href="'.get_edit_term_link(get_queried_object_id(), $taxonomy_location_url).'">'.$admin_link_text.'</a><div class="clear10"></div></div>';
					}
					echo '<div class="clear"></div>';

					while ( $all->have_posts() ) : $all->the_post();
						include (get_template_directory() . '/loop-show-profile.php');
					endwhile;

					$total = ceil(($premium_found_posts + $normal_found_posts) / $posts_per_page);
					dolce_pagination($total, $paged);
				endif;
				wp_reset_postdata();

				// Show tours
				$args = array(
					'tax_query' => array( array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id ) ),
					'post_status' => 'publish',
					'post_type' => 'tour',
					'meta_key' => 'start',
					'meta_query' => array(
						array(
							'key' => 'start',
							'value' => mktime(0, 0, 0, date("m"), date("d"), date("Y")),
							'compare' => '<=',
							'type' => 'NUMERIC'
						),
						array(
							'key' => 'end',
							'value' => mktime(23, 59, 59, date("m"), date("d"), date("Y")),
							'compare' => '>=',
							'type' => 'NUMERIC'
						)
					),
					'orderby' => 'meta_value_num',
					'order' => 'rand',
					'posts_per_page' => $posts_per_page
				);
				$tours = new WP_Query( $args );

				if ( $tours->have_posts() ) {
					if (($premium_found_posts + $normal_found_posts) > "0") {
						echo '<div class="clear20"></div>';
					}
					echo '<h3>'.__('Tours happening now in','escortwp').' '.$from.'</h3>';

					while ( $tours->have_posts() ) : $tours->the_post();
						include (get_template_directory() . '/loop-show-tour.php');
					endwhile;
				}
				wp_reset_postdata();

				if (($premium_found_posts + $normal_found_posts + $tours->found_posts) == "0") {
					echo '<h3>'.ucfirst($taxonomy_profile_name_plural)." ".__('from','escortwp')." ".$from.'</h3>';
					echo '<div class="clear"></div>';
					printf(esc_html__('No %s here yet','escortwp'),$taxonomy_profile_name_plural);
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