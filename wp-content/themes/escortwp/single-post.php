<?php $blog_section = "yes"; get_header(); ?>
		<div class="contentwrapper">
			<div class="body theblog blogsingle">
    	    	<div <?php post_class("bodybox pagedesign") ?> id="post-<?php the_ID(); ?>">
				<h3 class="post_title"><a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
				<div class="under_the_title"></div>
				<div class="entry">
					<?php while (have_posts()) : the_post(); ?>
                		<?php the_content(); ?><?php edit_post_link(__('Edit','escortwp'), '<br />', ''); ?>
					<?php endwhile; ?>
					<div class="clear"></div>
				</div>

					<p class="postmetadata"> <?php the_tags(__('Tags','escortwp').': ', ', ', '<br />'); echo __('Posted in','escortwp')." "; the_category(', '); ?></p>
					<?php comments_template(); ?>
                	<div class="clear"></div>
            	</div> <!-- BODY BOX -->
        	</div> <!-- BODY -->
			</div> <!-- contentwrapper -->

		<?php get_sidebar("left"); ?>
		<?php get_sidebar("right"); ?>
    	<div class="clear"></div>
<?php get_footer(); ?>