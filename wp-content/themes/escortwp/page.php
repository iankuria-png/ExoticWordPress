<?php get_header(); ?>
	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox pagedesign">
        	<h3><?php the_title(); ?></h3>
			<?php while (have_posts()) : the_post(); ?>
            	<?php the_content(); ?><?php edit_post_link(__('Edit','escortwp'), '<br />', ''); ?>
			<?php endwhile; ?>
            <div class="clear"></div>
        </div> <!-- BODY BOX -->
    </div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>
<?php get_footer(); ?>