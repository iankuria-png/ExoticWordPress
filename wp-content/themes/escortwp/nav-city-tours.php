<?php
/*
Template Name: City Tours
*/

global $taxonomy_location_url;
if ((int)$_POST['tours_location'] > "0") {
	$tours_location = (int)$_POST['tours_location'];
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox tours-page">
			<?php
				if ($tours_location) {
					$tour_location_name = get_term($tours_location, $taxonomy_location_url);
					$tour_location_name = $tour_location_name->name;
					$after_h3 .= " ".__('in','escortwp')." ".$tour_location_name;
				}
			?>
        	<h3><?php _e('Tours Happening Now','escortwp'); ?><?php echo $after_h3 ?></h3>
			<?php if (have_posts()) : ?>
				<div class="clear"></div>
				<?php while (have_posts()) : the_post(); ?>
		                <?php the_content(); ?><?php edit_post_link(__('Add some text here','escortwp'), '<div class="clear"></div>', '<div class="clear10"></div>'); ?>
				<?php endwhile; ?>
			<?php endif; ?>
			<div class="clear"></div>
            <div class="filter">
            	<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="tours-location-form">
					<script type="text/javascript">
						jQuery(document).ready(function($) {
							$('.tours-location').on('change', function(event) {
								$('.tours-location-form').submit();
							});
						});
					</script>
		            <?php
		            echo '<div class="title-label">'.__('Only show tour from','escortwp').'</div>';
					$args = array(
						'show_option_all'    => __('All locations','escortwp'),
						'option_none_value'  => '-1',
						'orderby'            => 'name',
						'order'              => 'ASC',
						'show_count'         => 0,
						'hide_empty'         => 1,
						'echo'               => 1,
						'selected'           => $tours_location,
						'hierarchical'       => 1,
						'name'               => 'tours_location',
						'class'              => 'tours-location select2',
						'taxonomy'           => $taxonomy_location_url,
					);
					wp_dropdown_categories( $args );
		            ?>
                </form>
            </div> <!-- FILTER --> <div class="clear10"></div>

			<?php
			$posts_per_page = $tours_location ? "-1" : "40";
			$args = array(
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
				'order' => 'ASC',
				'paged' => $paged,
				'posts_per_page' => $posts_per_page
			);

			if($tours_location) {
				$args = array_merge($args, array('tax_query' => array( array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $tours_location ) )));
			}
			query_posts($args);

			$i = 1;
			if ( have_posts() ) : while ( have_posts() ) : the_post();
				include (get_template_directory() . '/loop-show-tour.php');
			endwhile;
				$total = ceil($wp_query->found_posts / $posts_per_page);
				dolce_pagination($total, $paged);
				echo '<div class="clear20"></div>';
			else:
				_e('No tours here yet','escortwp');
			endif;
			wp_reset_query();
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