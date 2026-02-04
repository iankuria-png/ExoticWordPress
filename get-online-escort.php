<?php
/**
 * AJAX handler to get the count and list of “online” escorts/agencies.
 * Place this file in your child theme root (e.g. get-online-escort.php).
 */

require_once dirname( __FILE__, 4 ) . '/wp-load.php';
session_start();

global $wpdb, $taxonomy_profile_url, $taxonomy_agency_url, $taxonomy_location_url;

// Normalize the incoming action
$action = $_POST['action'] ?? '';

/**
 * Hide the escort count badge when user clicks “hide”
 */
if ( 'set-session' === $action ) {
    $_SESSION["{$taxonomy_profile_url}-count"] = 'hide';
    exit;
}

/**
 * Return the number of “online” escorts/agencies
 */
if ( 'get-online-escort-count' === $action ) {
    $options_table = $wpdb->options;
    $values        = [ $taxonomy_profile_url, $taxonomy_agency_url ];
    // Build SQL to find all option_names where option_value is profile or agency
    $placeholders = implode( ' OR option_value = ', array_fill( 0, count( $values ), '%s' ) );
    $sql          = "SELECT option_name
                     FROM {$options_table}
                     WHERE option_value = {$placeholders}";
    $option_names = $wpdb->get_col( $wpdb->prepare( $sql, ...$values ) );

    $cnt           = 0;
    $option_prefix = $taxonomy_profile_url . 'id';

    foreach ( $option_names as $option_name ) {
        if ( 0 === strpos( $option_name, $option_prefix ) ) {
            // Extract user ID from option key
            $author_id = substr( $option_name, strlen( $option_prefix ) );
            if ( logintokencheck( $author_id ) ) {
                // Count all published (non-private) posts of type profile by that author
                $posts_table = $wpdb->posts;
                $author_posts = $wpdb->get_col( $wpdb->prepare( "
                    SELECT ID FROM {$posts_table}
                    WHERE post_author = %d
                      AND post_type   = %s
                      AND post_status != %s
                ", $author_id, $taxonomy_profile_url, 'private' ) );
                $cnt += count( $author_posts );
            }
        }
    }

    echo esc_html( $cnt );
    exit;
}

/**
 * Return the HTML for each “online” escort
 */
if ( 'get-online-escort' === $action ) {
    $options_table = $wpdb->options;
    $values        = [ $taxonomy_profile_url, $taxonomy_agency_url ];
    $placeholders  = implode( ' OR option_value = ', array_fill( 0, count( $values ), '%s' ) );
    $sql           = "SELECT option_name
                      FROM {$options_table}
                      WHERE option_value = {$placeholders}";
    $option_names  = $wpdb->get_col( $wpdb->prepare( $sql, ...$values ) );

    $option_prefix         = $taxonomy_profile_url . 'id';
    $is_escort_available   = false;
    $posts_table           = $wpdb->posts;

    foreach ( $option_names as $option_name ) {
        if ( 0 === strpos( $option_name, $option_prefix ) ) {
            $author_id = substr( $option_name, strlen( $option_prefix ) );
            if ( logintokencheck( $author_id ) ) {
                if ( defined( 'isdolcetheme' ) && 1 !== isdolcetheme ) {
                    die();
                }

                // Get all non-private profiles by this author
                $escort_posts = $wpdb->get_results( $wpdb->prepare( "
                    SELECT * FROM {$posts_table}
                    WHERE post_author = %d
                      AND post_type   = %s
                      AND post_status != %s
                ", $author_id, $taxonomy_profile_url, 'private' ) );

                foreach ( $escort_posts as $post ) {
                    $is_escort_available = true;
                    $escort_post_id      = intval( $post->ID );
                    $linktitle           = ucwords( $post->post_name );
                    $imagealt            = $linktitle;
                    $permalink           = get_permalink( $escort_post_id );
                    $premium             = get_post_meta( $escort_post_id, 'premium', true );
                    $thumbclass          = ( '1' === $premium ) ? ' girlpremium' : '';

                    // Build location array
                    $location = [];
                    $city_terms = wp_get_post_terms( $escort_post_id, $taxonomy_location_url );
                    if ( ! empty( $city_terms ) && ! is_wp_error( $city_terms ) ) {
                        $city_term     = $city_terms[0];
                        $location[]    = $city_term->name;
                        $state_term    = get_term( $city_term->parent, $taxonomy_location_url );
                        if ( $state_term && ! is_wp_error( $state_term ) ) {
                            $location[] = $state_term->name;
                            $country    = get_term( $state_term->parent, $taxonomy_location_url );
                            if ( $country && ! is_wp_error( $country ) ) {
                                $location[] = $country->name;
                            }
                        }
                    }

                    // Detect any video attachment
                    $videos = get_children( [
                        'post_parent'    => $escort_post_id,
                        'post_status'    => 'inherit',
                        'post_type'      => 'attachment',
                        'post_mime_type' => 'video',
                        'numberposts'    => 1,
                    ] );
                    $phone = get_post_meta( $escort_post_id, 'phone', true );
                    ?>
                    <div class="girl" itemscope itemtype="http://schema.org/Person">
                        <div class="thumb rad3<?php echo esc_attr( $thumbclass ); ?>">
                            <div class="thumbwrapper">
                                <a href="<?php echo esc_url( $permalink ); ?>" title="<?php echo esc_attr( $linktitle ); ?>">
                                    <?php if ( ! empty( $videos ) ) : ?>
                                        <span class="label-video">
                                            <img src="<?php echo esc_url( get_template_directory_uri() . '/i/video-th-icon.png' ); ?>" alt="" />
                                        </span>
                                    <?php endif; ?>
                                    <div class="model-info model-info_fix">
                                        <?php echo wp_kses_post( get_escort_labels( $escort_post_id ) ); ?>
                                        <div class="clear"></div>
                                        <div class="desc">
                                            <div class="girl-name" itemprop="name">
                                                <?php echo esc_html( $linktitle ); ?>
                                                <i style="font-size:13px;color:#00e600" class="fa fa-circle" aria-hidden="true"></i>
                                            </div>
                                            <div class="clear"></div>
                                            <?php if ( ! empty( $location ) ) : ?>
                                                <span class="girl-desc-location" itemprop="homeLocation">
                                                    <span class="icon-location"></span>
                                                    Escorts <?php echo esc_html( $location[0] ); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <img
                                        class="mobile-ready-img rad3"
                                        src="<?php echo esc_url( get_first_image( $escort_post_id ) ); ?>"
                                        data-responsive-img-url="<?php echo esc_url( get_first_image( $escort_post_id, '4' ) ); ?>"
                                        alt="<?php echo esc_attr( "escort {$imagealt} in " . ( $location[0] ?? '' ) ); ?>"
                                        itemprop="image"
                                    />
                                    <?php if ( '1' === $premium ) : ?>
                                        <div class="premiumlabel rad3">
                                            <span><?php echo esc_html__( 'PREMIUM', 'escortwp' ); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <?php if ( $phone ) : ?>
                                    <div class="phone_btn_loop">
                                        <a href="tel:<?php echo esc_attr( $phone ); ?>" itemprop="telephone">
                                            <span class="model_Call_me">
                                                Call <?php echo esc_html( $linktitle ); ?>
                                            </span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <div class="clear"></div>
                            </div>
                            <?php
                            // If you have $agency_manage_escort_buttons defined elsewhere:
                            echo wp_kses_post( $agency_manage_escort_buttons ?? '' );
                            ?>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <?php
                }
            }
        }
    }

    if ( ! $is_escort_available ) {
        echo '<div class="no-escort-available">';
        esc_html_e( 'No Escort is Available right Now!', 'escortwp' );
        echo '</div>';
    }

    exit;
}
