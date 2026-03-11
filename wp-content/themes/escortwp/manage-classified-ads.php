<?php
/*
Template Name: Manage Classified Ads
*/

if(get_option("hide6") == "1" && !current_user_can('level_10')) { wp_redirect(site_url(), "301"); }

global $taxonomy_agency_url;
$current_user = wp_get_current_user();
if ( !is_user_logged_in() || 
    (get_option("escortid".$current_user->ID) == $taxonomy_agency_name && get_option("allowadpostingagencies") != "1") || 
    (get_option("escortid".$current_user->ID) == $taxonomy_profile_name && get_option("allowadpostingprofiles") != "1") ||
    (get_option("escortid".$current_user->ID) == "member" && get_option("allowadpostingmembers") != "1")
) {
    if(!current_user_can('level_10')) {
        wp_redirect(get_bloginfo("url")); exit;
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'addclassifiedad') {
	include (get_template_directory() . '/manage-classified-ads-info-process.php');
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox">
        	<h3><?php _e('Add a classified ad','escortwp'); ?></h3>
			<?php include (get_template_directory() . '/manage-classified-ads-form.php'); ?>
            <div class="clear"></div>
        </div> <!-- BODY BOX -->

        <div class="clear"></div>
    	<div class="bodybox">
        	<h3><?php _e('Your classified ads','escortwp'); ?></h3>
            <div class="clear10"></div>
            <?php
            query_posts("post_type=ad&orderby=date&sort=ASC&author=".$current_user->ID."&posts_per_page=-1");
            if ( have_posts() ) : ?>
            <table class="listagencies rad3">
            	<tr class="trhead rad3">
            		<th class="rad3"><?php _e('Title','escortwp'); ?></th>
                    <th class="rad3"><?php _e('Type','escortwp'); ?></th>
                    <th class="rad3"><?php _e('Date added','escortwp'); ?></th>
            	</tr>
            <?php while ( have_posts() ) : the_post(); ?>
            	<tr class="agencytr">
                	<td><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></td>
                    <td><?php echo get_post_meta(get_the_ID(), "type", true); ?></td>
                    <td><?php the_time("d F Y"); ?></td>
                </tr>
            <?php endwhile; ?>
            </table>
            <?php
            else:
            	_e('No classified ads yet','escortwp');
            endif;
            wp_reset_query();
            ?>
            <div class="clear"></div>
        </div> <!-- BODY BOX -->
    </div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>