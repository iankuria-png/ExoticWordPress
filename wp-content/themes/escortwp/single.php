<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

$post_taxonomy = $wp_query->query['post_type'];

global $taxonomy_profile_url, $taxonomy_agency_url;
if($post_taxonomy == $taxonomy_profile_url) {
	get_template_part( 'single-profile' ); die();
} elseif ($post_taxonomy == 'b'.$taxonomy_profile_url) {
	get_template_part( 'single-bprofile' ); die();
} elseif ($post_taxonomy == $taxonomy_agency_url) {
	get_template_part( 'single-agency' ); die();
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
		<div class="bodybox pagedesign">
			<h3><?php the_title(); ?></h3>
			<?php while (have_posts()) : the_post(); ?>
				<?php the_content(); ?>
				<div class="clear"></div>
				<?php edit_post_link(__('Edit','escortwp'), '<br />', ''); ?>
			<?php endwhile; ?>
			<div class="clear"></div>
		</div> <!-- BODY BOX -->
	</div> <!-- BODY -->
	</div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>