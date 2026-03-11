<?php
/*
Template Name: Blog
*/

$blog_section = 'yes';
get_header(); ?>

	<div class="contentwrapper">
	<div class="body theblog">
    	<div class="bodybox pagedesign">
			<?php
			$current_cat = get_category(get_query_var('cat'));
			$args =  array( 'post_type' => 'post', 'paged' => $paged, 'category_name' => $current_cat->slug );
			query_posts($args);
			if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
					<h3 class="post_title"><a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
					<div class="under_the_title"></div>
					<div class="entry">
						<?php
						if ( is_archive() ) {
							the_excerpt(__('Continue reading','escortwp'));
						} else {
							the_content(__('Continue reading','escortwp')); edit_post_link(__('Edit','escortwp'), '', '');
						}
						?>
						<div class="clear"></div>
					</div>

					<div class="postmetadata">
						<div class="l">
							<?php the_tags(__('Tags','escortwp').': ', ', ', '<br />'); ?>
							<?php _e('Posted in','escortwp'); ?> <?php the_category(', ') ?>
						</div>
						<div class="r">
							<?php comments_popup_link(__('No Comments','escortwp'), __('1 Comment','escortwp'), __('% Comments','escortwp')); ?>
						</div>
						<div class="clear"></div>
					</div>
				</div> <!-- post class -->

			<?php endwhile; ?>
				<div class="navigation">
					<div class="alignleft l"><?php next_posts_link(__('Previous page','escortwp')) ?></div>
					<div class="alignright r"><?php previous_posts_link(__('Next page','escortwp')) ?></div>
					<div class="clear"></div>
				</div>
			<?php else : ?>
				<h4 class="text-center"><?php _e('No articles found','escortwp'); ?></h4>
			<?php endif; ?>
            <div class="clear"></div>
        </div> <!-- BODY BOX -->
    </div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>