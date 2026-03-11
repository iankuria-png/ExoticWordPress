<?php
if (have_posts()) :
while (have_posts()) : the_post();

global $taxonomy_profile_url, $taxonomy_agency_name, $taxonomy_agency_url, $taxonomy_location_url;
$current_user = wp_get_current_user();
$userid = $current_user->ID;
$userstatus = get_option("escortid".$userid);

if (current_user_can('level_10')) {
	if (isset($_POST['action']) && $_POST['action'] == 'deletereview') {
		$reviewidtodelete = get_the_ID();
		$agencyurl = get_permalink(get_post_meta($reviewidtodelete, 'agencyid', true));

		wp_delete_post( $reviewidtodelete, true ); // delete post
		wp_redirect($agencyurl); exit();
	}
} // if the super admin wants to delete the review


get_header(); ?>

		<div class="contentwrapper">
		<div class="body">
        	<div class="bodybox">
				<?php
				if (!is_user_logged_in()) {
					echo '<div class="err rad25">'.__('You need to','escortwp').' <a href="'.get_permalink(get_option('main_reg_page_id')).'">'.__('register','escortwp').'</a> '.__('or','escortwp').' <a href="'.wp_login_url(get_permalink()).'">'.__('login','escortwp').'</a> '.__('to be able to view this review','escortwp').'</div>';
				} else {
					if (current_user_can('level_10')) { ?>
					<script type="text/javascript">
					jQuery(document).ready(function($) {
						$('.agencyeditbuttons .pinkbutton').on('click', function(){
							var id = $(this).attr("id");
							$('.agency_options_dropdowns').slideUp();
							$('.agency_options_'+id).slideDown();
							$('.girlsingle, .agencyeditbuttons').slideUp();
						});
						$('.agency_options_dropdowns .closebtn').on('click', function(){
							$(this).parent().slideUp();
							$('.girlsingle, .agencyeditbuttons').slideDown();
						});
					});
					</script>
					<div class="agencyeditbuttons">
					    <div class="pinkbutton redbutton rad25 l" id="delete"><?php _e('Delete','escortwp'); ?></div>
					</div> <!-- AGENCY EDIT BUTTONS -->
					<div class="clear"></div>

					<div class="agency_options_delete agency_options_dropdowns">
						<?php _e('Are you sure you want to delete this review?','escortwp'); ?>
						<?php closebtn(); ?>
						<div class="clear10"></div>
						<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="text-center">
							<input type="submit" name="submit" value="<?php _e('Delete','escortwp'); ?>" class="pinkbutton redbutton rad3" />
							<input type="hidden" name="action" value="deletereview" />
						</form>
					</div> <!-- DELETE -->
				<?php } // if admin ?>

                <div class="girlsingle">
					<h4 class="rad3"><?php _e('Review details','escortwp'); ?>:</h4> <div class="clear10"></div>
					<?php
					if (get_post_meta(get_the_ID(), 'reviewfor', true) == 'agency') {
						$reviwfor = get_post(get_post_meta(get_the_ID(), 'agencyid', true));
						$taxonomy = $taxonomy_agency_name;
					}
					if (get_post_meta(get_the_ID(), 'reviewfor', true) == 'profile') {
						$reviwfor = get_post(get_post_meta(get_the_ID(), 'escortid', true));
						$taxonomy = $taxonomy_profile_name;
					}
					?>

    	            <div class="girlinfo col100">
                        <b><?php _e('Rating','escortwp'); ?>:</b><span class="valuecolumn"><div class="starrating l"><div class="starrating_stars star<?php echo get_post_meta(get_the_ID(), 'rateagency', true); ?>"></div></div></span><div class="clear10"></div>
						<b><?php printf(esc_html__('Review for %s','escortwp'),$taxonomy); ?>:</b><span class="valuecolumn"><a href="<?php echo get_permalink($reviwfor->ID); ?>" title="<?php echo $reviwfor->post_title; ?>"><?php echo $reviwfor->post_title; ?></a></span><div class="clear10"></div>
						<b><?php _e('Added by','escortwp'); ?>:</b><span class="valuecolumn"><?php echo substr(get_the_author_meta('display_name'), 0, 2) ?>...</span><div class="clear10"></div>
                	</div> <!-- GIRL INFO LEFT -->
					<div class="clear30"></div>
                	
                    <div class="clientreviewtext">
	                    <h4 class="rad3"><?php _e('Client review','escortwp'); ?>:</h4>
                        <?php the_content(); ?>
						<div class="clear"></div>
                    </div> <!-- GIRL INFO RIGHT --> <div class="clear20"></div>
                </div> <!-- GIRL SINGLE -->
                <?php } // end of is_user_logged_in check | reviews can't be viewed by visitors ?>
		<?php endwhile; ?>
	<?php endif; ?>
            </div> <!-- BODY BOX -->
            <div class="clear"></div>
        </div> <!-- BODY -->
        </div> <!-- contentwrapper -->

		<?php get_sidebar("left"); ?>
		<?php get_sidebar("right"); ?>
    	<div class="clear"></div>
<?php get_footer(); ?>