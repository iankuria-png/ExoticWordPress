<?php
global $taxonomy_location_url, $taxonomy_profile_name_plural, $taxonomy_agency_url, $taxonomy_profile_url;
get_header();
?>

<style>
.new-location-ul li {
    display: inline-block;
    border: 1px solid #444444;
    padding: 5px 10px;
    border-radius: 10px;
    margin-right: 10px;
    font-weight: bold;
}
</style>

<div class="contentwrapper">
    <div class="body">

        <!-- 2022 child-term list -->
        <div class="bodybox">
            <?php
            $term       = get_queried_object();
            $from       = get_term( $term->term_id, $taxonomy_location_url )->name;
            echo '<h3>' . ucfirst( $taxonomy_profile_name_plural ) . ' From ' . esc_html( $from ) . '</h3>';
            echo '<div class="clear"></div>';

            $termchildren = get_term_children( $term->term_id, $term->taxonomy );
            echo '<ul class="new-location-ul">';
            foreach ( $termchildren as $child ) {
                $child_term = get_term_by( 'id', $child, $term->taxonomy );
                echo '<li><a href="' . esc_url( get_term_link( $child_term ) ) . '">' 
                     . esc_html( $child_term->name ) . ' &ndash; ' . intval( $child_term->count ) 
                     . '</a></li>';
            }
            echo '</ul>';
            echo '<div class="clear"></div>';
            ?>
        </div>

        <!-- 2022 VIP Escorts -->
        <div class="bodybox bodybox-homepage featured-mobile featured-desktop">
            <h3 class="l"><?php _e( 'VIP Escorts', 'escortwp' ); ?></h3>
            <div class="clear"></div>
            <div style="display:flex;flex-wrap:wrap;">
                <?php
                $term_slug    = get_query_var( 'term' );
                $taxonomyName = get_query_var( 'taxonomy' );
                $current_term = get_term_by( 'slug', $term_slug, $taxonomyName );

                $featured_args = array(
                    'post_type'      => $taxonomy_profile_url,
                    'tax_query'      => array(
                        array(
                            'taxonomy' => $taxonomy_location_url,
                            'field'    => 'id',
                            'terms'    => $current_term->term_id,
                        ),
                    ),
                    'meta_query'     => array(
                        array(
                            'key'     => 'featured',
                            'value'   => '1',
                            'compare' => '=',
                            'type'    => 'NUMERIC',
                        ),
                    ),
                    'orderby'        => 'rand',
                    'posts_per_page' => -1,
                );
                $featured_query = new WP_Query( $featured_args );
                if ( $featured_query->have_posts() ) {
                    while ( $featured_query->have_posts() ) {
                        $featured_query->the_post();
                        include get_template_directory() . '-child/loop-show-profile.php';
                    }
                }
                wp_reset_postdata();
                ?>
            </div>
            <div class="clear"></div>
        </div>

        <?php
        // 2025 premium + normal pagination
        $posts_per_page = 40;
        $paged          = max( 1, get_query_var( 'page', 1 ) );

        // Count all premium & normal
        $premium_all_args = array(
            'post_type'      => array( $taxonomy_profile_url, $taxonomy_agency_url ),
            'posts_per_page' => 1,
            'paged'          => 1,
            'meta_query'     => array(
                array( 'key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC' ),
            ),
            'tax_query'      => array(
                array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id ),
            ),
        );
        $premium_all = new WP_Query( $premium_all_args );
        $premium_count = $premium_all->found_posts;

        $normal_all_args = array(
            'post_type'      => array( $taxonomy_profile_url, $taxonomy_agency_url ),
            'posts_per_page' => 1,
            'paged'          => 1,
            'meta_query'     => array(
                array( 'key' => 'premium', 'value' => '0', 'compare' => '=', 'type' => 'NUMERIC' ),
            ),
            'tax_query'      => array(
                array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id ),
            ),
        );
        $normal_all = new WP_Query( $normal_all_args );
        $normal_count = $normal_all->found_posts;

        // Query premium for this page
        $premium_args = array(
            'post_type'      => array( $taxonomy_profile_url, $taxonomy_agency_url ),
            'posts_per_page' => $posts_per_page,
            'paged'          => $paged,
            'orderby'        => 'meta_value_num',
            'meta_key'       => 'premium_since',
            'meta_query'     => array(
                array( 'key' => 'premium', 'value' => '1', 'compare' => '=', 'type' => 'NUMERIC' ),
            ),
            'tax_query'      => array(
                array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id ),
            ),
        );
        $premium = new WP_Query( $premium_args );

        // Calculate normal offset
        if ( $paged < 2 ) {
            $normal_offset = 0;
        } else {
            $normal_offset = ( $paged - 1 ) * $posts_per_page - $premium_count;
            $normal_offset = max( 0, $normal_offset );
        }
        $normal_args = array(
            'offset'         => $normal_offset,
            'post_type'      => array( $taxonomy_profile_url, $taxonomy_agency_url ),
            'posts_per_page' => $posts_per_page - count( $premium->posts ),
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => array(
                array( 'key' => 'premium', 'value' => '0', 'compare' => '=', 'type' => 'NUMERIC' ),
            ),
            'tax_query'      => array(
                array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id ),
            ),
        );

        // Merge premium + normal
        $all = $premium;
        if ( count( $premium->posts ) < $posts_per_page ) {
            $normal      = new WP_Query( $normal_args );
            $all->posts  = array_merge( $premium->posts, $normal->posts );
            $all->post_count = count( $all->posts );
        }

        // Display them
        if ( $all->have_posts() ) {
            echo '<div class="bodybox">';
            echo '<h3>' . ucfirst( $taxonomy_profile_name_plural ) . ' ' . __( 'from', 'escortwp' ) . ' ' . esc_html( $from ) . '</h3>';
            echo '<div class="clear"></div>';
            while ( $all->have_posts() ) {
                $all->the_post();
                include get_template_directory() . '-child/loop-show-profile.php';
            }
            $total_pages = ceil( ( $premium_count + $normal_count ) / $posts_per_page );
            dolce_pagination( $total_pages, $paged );
            echo '</div>';
            wp_reset_postdata();
        }

        // Tours
        $tours_args = array(
            'post_type'      => 'tour',
            'post_status'    => 'publish',
            'meta_key'       => 'start',
            'meta_query'     => array(
                array( 'key' => 'start', 'value' => mktime(0,0,0), 'compare' => '<=', 'type' => 'NUMERIC' ),
                array( 'key' => 'end',   'value' => mktime(23,59,59), 'compare' => '>=', 'type' => 'NUMERIC' ),
            ),
            'tax_query'      => array(
                array( 'taxonomy' => $taxonomy_location_url, 'field' => 'id', 'terms' => $term->term_id ),
            ),
            'orderby'        => 'meta_value_num',
            'order'          => 'rand',
            'posts_per_page' => $posts_per_page,
        );
        $tours = new WP_Query( $tours_args );
        if ( $tours->have_posts() ) {
            if ( $premium_count + $normal_count > 0 ) {
                echo '<div class="clear20"></div>';
            }
            echo '<h3>' . __( 'Tours happening now in', 'escortwp' ) . ' ' . esc_html( $from ) . '</h3>';
            while ( $tours->have_posts() ) {
                $tours->the_post();
                include get_template_directory() . '/loop-show-tour.php';
            }
            wp_reset_postdata();
        }

        // No results?
        if ( $premium_count + $normal_count + $tours->found_posts === 0 ) {
            echo '<div class="bodybox">';
            echo '<h3>' . ucfirst( $taxonomy_profile_name_plural ) . ' ' . __( 'from', 'escortwp' ) . ' ' . esc_html( $from ) . '</h3>';
            echo '<div class="clear"></div>';
            printf( esc_html__( 'No %s here yet', 'escortwp' ), $taxonomy_profile_name_plural );
            echo '</div>';
        }
        ?>

        <!-- 2025 term description & top_text -->
        <div class="bodybox">
            <?php echo term_description( get_queried_object_id(), $taxonomy_location_url ); ?>
            <div class="top-mobile-expand">
                <?php
                $category = get_queried_object();
                echo get_field( 'top_text', $category->taxonomy . '_' . $category->term_id );
                ?>
                <a href="#" class="r-more">More</a>
                <a href="#" class="r-less">Less</a>
            </div>
        </div>

    </div> <!-- body -->
</div> <!-- contentwrapper -->

<?php get_sidebar( 'left' ); ?>
<?php get_sidebar( 'right' ); ?>
<div class="clear"></div>
<?php get_footer(); ?>
