<?php
/*
Template Name: Register Independent - Manage my Tours
*/

global $taxonomy_location_url, $taxonomy_profile_url;
$current_user = wp_get_current_user();
if (get_option("escortid".$current_user->ID) != $taxonomy_profile_url) { wp_redirect(get_bloginfo("url")); exit; }

if (isset($_POST['action']) && in_array($_POST['action'], array('addtour', 'edittour')) && is_user_logged_in()) {
	include (get_template_directory() . '/register-independent-manage-my-tours-process-data.php');
} // if isset
get_header(); ?>

<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox managetours">
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				//delete a city tour
				$('.tour .addedbuttons i').on('click', function(){
					var id = $(this).text();
					$('#tour'+id+' .addedbuttons').html('<b></b>');
					$.ajax({
						type: "GET",
						url: "<?php bloginfo('template_url'); ?>/ajax/delete-tour.php",
						data: "id=" + id,
						success: function(data){
							$('.deletemsg').html(data).fadeIn("slow").delay(1500).fadeOut("slow");
							$('#tour'+id).slideUp("slow");
						}
					});
				});

				//edit a city tour
				$('.tour .addedbuttons em').on('click', function(){
					var id = $(this).text();
					$('#tour'+id+' .addedbuttons em').hide();
					$('#tour'+id+' .addedbuttons').append('<b></b>');
					$('html,body').animate({ scrollTop: $('.managetours').offset().top }, { duration: 'slow', easing: 'swing'});
					$.ajax({
						type: "GET",
						url: "<?php bloginfo('template_url'); ?>/ajax/edit-tour.php",
						data: "id=" + id,
						success: function(data){
							$('.addtourform').html(data);
							$('html').scrollTop(0);
							$('#tour'+id+' .addedbuttons b').hide();
							$('#tour'+id+' .addedbuttons').append('<em>'+id+'</em>');
						}
					});
				});
			});
			</script>

        	<h3><?php _e('Manage my Tours','escortwp'); ?></h3>
			<?php if(is_user_logged_in()) { ?>
			<?php if ($err) { echo "<div class=\"err rad25\">$err</div>"; } ?>
			<?php if ($ok) { echo "<div class=\"ok rad25\">$ok</div>"; } ?>
			<div class="addtourform">
			<?php include (get_template_directory() . '/register-independent-add-tour-form.php'); ?>
			</div>

			<?php
			$args = array(
				'post_type' => 'tour',
				'posts_per_page' => -1,
				'orderby' => 'date',
				'order' => 'ASC',
				'meta_query' => array(
					array(
						'key' => 'belongstoescortid',
						'value' => get_option('escortpostid'.$current_user->ID),
						'compare' => '=',
						'type' => 'NUMERIC'
					)
				)
			);
			query_posts( $args );
			if (have_posts()) : ?>
			<div class="clear20"></div>
			<h4 class="l"><?php _e('Tours you already added','escortwp'); ?>:</h4><div class="deletemsg r"></div>
			<div class="clear10"></div>
			<div class="addedtours">
				<div class="tour tourhead">
					<div class="addedstart"><?php _e('Start','escortwp'); ?></div>
			    	<div class="addedend"><?php _e('End','escortwp'); ?></div>
				    <div class="addedplace"><?php _e('Place','escortwp'); ?></div>
			    	<div class="addedphone"><?php _e('Phone','escortwp'); ?></div>
			        <div class="addedbuttons"></div>
				</div>
				<?php
				while ( have_posts() ) : the_post();
					unset($city, $state, $country, $location);

					$city = get_term(get_post_meta(get_the_ID(), 'city', true), $taxonomy_location_url);
					if($city) $location[] = $city->name;

					if(showfield('state')) {
						$state = get_term(get_post_meta(get_the_ID(), 'state', true), $taxonomy_location_url);
						if($state) {
							$location[] = $state->name;
						}
					}

					$country = get_term(get_post_meta(get_the_ID(), 'country', true), $taxonomy_location_url);
					if($country) $location[] = $country->name;
					?>
					<div class="tour" id="tour<?php the_ID(); ?>">
						<span class="tour-info-mobile"><?php _e('Start','escortwp'); ?>:</span>
						<div class="addedstart"><?php echo date("d/m/Y", get_post_meta(get_the_ID(),'start', true)); ?></div>
						<span class="tour-info-mobile-clear"></span>

						<span class="tour-info-mobile"><?php _e('End','escortwp'); ?>:</span>
				    	<div class="addedend"><?php echo date("d/m/Y", get_post_meta(get_the_ID(),'end', true)); ?></div>
				    	<span class="tour-info-mobile-clear"></span>

				    	<span class="tour-info-mobile"><?php _e('Place','escortwp'); ?>:</span>
					    <div class="addedplace"><?php echo implode(", ", $location); ?></div>
					    <span class="tour-info-mobile-clear"></span>

					    <span class="tour-info-mobile"><?php _e('Phone','escortwp'); ?>:</span>
				    	<div class="addedphone"><?php echo get_post_meta(get_the_ID(),'phone', true); ?></div>

				        <div class="addedbuttons">
				        	<?php
				        	if(get_post_meta(get_the_ID(), 'needs_payment', true) == "1") {
				        		echo '<div class="pb"><a class="greenbutton payment-button rad25" href="'.get_permalink(get_option('escortpostid'.$current_user->ID)).'?unpaid_tour='.get_the_ID().'">'.__('Pay for tour','escortwp').'</a></div>';
				        	} else { ?>
				        		<i><?php the_ID(); ?></i><em><?php the_ID(); ?></em>
				        	<?php } ?>
				        </div>
					</div>
				<?php endwhile; ?>
			</div> <!-- ADDED TOURS -->
			<?php endif; ?>
			<?php wp_reset_query(); ?>
			<div class="clear"></div>
			<?php
			} else { // is user logged in else
				_e('You need to login or register to manage your tours','escortwp');
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