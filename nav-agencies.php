<?php
/*
Template Name: List all Agencies
*/

get_header(); ?>

    <div class="contentwrapper">
    <div class="body">
        <div class="bodybox">
            <h3><?php echo ucfirst( $taxonomy_agency_name_plural ); ?></h3>
            <?php
            if ( have_posts() ) :
                echo '<div class="clear"></div>';
                while ( have_posts() ) : the_post();
                    the_content();
                    edit_post_link(
                        __( 'Add some text here', 'escortwp' ),
                        '<div class="clear"></div>',
                        '<div class="clear10"></div>'
                    );
                endwhile;
            endif;
            ?>
            <div class="clear"></div>

            <?php
            // figure out current page
            if ( isset( $_GET['page'] ) ) {
                $paged = (int) $_GET['page'];
            }
            if ( $wp_query->query_vars['page'] ) {
                $paged = $wp_query->query_vars['page'];
            }

            $posts_per_page = 40;
            $args = array(
                'post_type'      => $taxonomy_agency_url,
                'paged'          => $paged,
                'posts_per_page' => $posts_per_page,
                'orderby'        => 'title',  // preserve 2022 ordering
                'order'          => 'ASC',    // preserve 2022 ordering
            );
            query_posts( $args );

            if ( have_posts() ) :
                while ( have_posts() ) : the_post();
                    // load your child‐theme’s loop
                    include get_stylesheet_directory() . '/loop-show-profile.php';
                endwhile;
            ?>
            </table>
            <?php
                $total = ceil( $wp_query->found_posts / $posts_per_page );
                dolce_pagination( $total, $paged );
            else :
                printf(
                    esc_html__( 'No %s here yet', 'escortwp' ),
                    $taxonomy_agency_name_plural
                );
            endif;
            wp_reset_query();
            ?>
            <div class="clear"></div>
        </div> <!-- .bodybox -->
    </div> <!-- .body -->
    </div> <!-- .contentwrapper -->

<?php get_sidebar( 'left' ); ?>
<?php get_sidebar( 'right' ); ?>
<div class="clear"></div>

<?php get_footer(); ?>
