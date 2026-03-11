<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Silence all errors unless debug is on
if ( ! defined( 'WP_DEBUG' ) ) {
    define( 'WP_DEBUG', false );
}
ini_set( 'display_errors', WP_DEBUG ? 1 : 0 );

// Aside wrapper for mobile sidebar
echo '<div class="sidebar-mobile">';

global $payment_duration_a,
       $taxonomy_profile_name,
       $taxonomy_profile_name_plural,
       $taxonomy_agency_name,
       $taxonomy_location_url,
       $taxonomy_profile_url,
       $taxonomy_agency_url,
       $gender_a,
       $settings_theme_genders;

// Get current user
$current_user = wp_get_current_user();
$userid       = is_user_logged_in() ? $current_user->ID : 0;
$userstatus   = $userid ? get_option( "escortid{$userid}" ) : 'none';

// 1) Private‐ad activation (admin only)
if ( is_single() && get_post_type() === 'ad' && 'private' === get_post_status() ) {
    if ( current_user_can( 'manage_options' ) ) {
        ?>
        <div class="sidebar-notice notice-error text-center">
            <?php esc_html_e( 'This ad requires manual activation', 'escortwp' ); ?>
            <form method="post" class="mt-2">
                <input type="hidden" name="action" value="activateprivatead">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'Activate ad', 'escortwp' ); ?>
                </button>
            </form>
        </div>
        <?php
    }
}

// 2) Private profile activation
if ( is_single() && get_post_type() === $taxonomy_profile_url && 'private' === get_post_status() && '1' === get_post_meta( get_the_ID(), 'notactive', true ) ) {
    if ( current_user_can( 'manage_options' ) ) {
        ?>
        <div class="sidebar-notice notice-error text-center">
            <?php esc_html_e( 'This profile requires manual activation', 'escortwp' ); ?>
            <form method="post" class="mt-2">
                <input type="hidden" name="action" value="activateprivateprofile">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'Activate profile', 'escortwp' ); ?>
                </button>
            </form>
        </div>
        <?php
    } else {
        printf( '<div class="notice notice-info">%s<br>%s</div>',
            esc_html__( 'This profile is currently set to private.', 'escortwp' ),
            esc_html__( 'This website requires all profiles to be manually activated by an admin.', 'escortwp' )
        );
    }
}

// 3) Admin “needs payment” override
if ( is_single() && current_user_can( 'manage_options' ) && '1' === get_post_meta( get_the_ID(), 'needs_payment', true ) ) {
    ?>
    <div class="sidebar-notice notice-warning text-center">
        <?php esc_html_e( 'This profile requires payment!', 'escortwp' ); ?>
        <form method="post" class="mt-2">
            <input type="hidden" name="action" value="activateunpaidprofile">
            <?php esc_html_e( 'Activate for a period of:', 'escortwp' ); ?>
            <select name="profileduration" class="ml-2">
                <option value=""><?php esc_html_e( 'Forever', 'escortwp' ); ?></option>
                <?php foreach ( $payment_duration_a as $key => $plan ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>">
                        <?php echo esc_html( $plan[0] ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button button-primary ml-2">
                <?php esc_html_e( 'Activate profile', 'escortwp' ); ?>
            </button>
        </form>
    </div>
    <?php
}

// 4) Agency must pay first
if ( $userstatus === $taxonomy_agency_url ) {
    $agency_id = get_option( "agencypostid{$userid}" );
    if ( $agency_id && 'private' === get_post_status( $agency_id ) && '1' === get_post_meta( $agency_id, 'needs_payment', true ) ) {
        ?>
        <div class="sidebar-notice notice-warning text-center">
            <?php
            /* translators: %s = agency name */
            printf( esc_html__( 'Your %s profile will not be shown until you pay the registration fee.', 'escortwp' ), $taxonomy_agency_name );
            ?>
            <div class="mt-2">
                <?php echo generate_payment_buttons( 'agreg', $agency_id ); ?>
            </div>
            <div class="mt-1 small">
                <?php echo esc_html( format_price( 'agreg' ) ); ?>
            </div>
        </div>
        <?php
    }
}

// 5) Escort‐from‐agency must pay
if ( is_single() && get_post_type() === $taxonomy_profile_url && $userstatus === $taxonomy_agency_url && 'private' === get_post_status() && '1' === get_post_meta( get_the_ID(), 'needs_ag_payment', true ) ) {
    ?>
    <div class="sidebar-notice notice-warning text-center">
        <?php
        /* translators: %s = profile name */
        printf( esc_html__( 'This %s profile will be reactivated after you pay your registration fee.', 'escortwp' ), $taxonomy_profile_name );
        ?>
        <div class="mt-2">
            <?php echo generate_payment_buttons( 'agescortreg', $agency_id ); ?>
        </div>
        <div class="mt-1 small">
            <?php echo esc_html( format_price( 'agescortreg' ) ); ?>
        </div>
    </div>
    <?php
}

// 6) Independent escort must pay
if ( $userstatus === $taxonomy_profile_url ) {
    $escort_id = get_option( "escortpostid{$userid}" );
    if ( $escort_id && 'private' === get_post_status( $escort_id ) && '1' === get_post_meta( $escort_id, 'needs_payment', true ) ) {
        ?>
        <div class="sidebar-notice notice-warning text-center">
            <?php
            /* translators: %s = profile name */
            printf( esc_html__( 'Your %s profile will not be shown until you pay the registration fee.', 'escortwp' ), $taxonomy_profile_name );
            ?>
            <div class="mt-2">
                <?php echo generate_payment_buttons( 'indescreg', $escort_id ); ?>
            </div>
            <div class="mt-1 small">
                <?php echo esc_html( format_price( 'indescreg' ) ); ?>
            </div>
        </div>
        <?php
    }
}

// 7) Quick‐search toggle for mobile
if ( get_option( 'quickescortsearch' ) === '1' ) {
    ?>
    <button class="quicksearch-toggle button button-secondary">
        <?php esc_html_e( 'Search', 'escortwp' ); ?>
    </button>
    <div class="quicksearch-form">
        <h4><?php esc_html_e( 'Quick Search', 'escortwp' ); ?></h4>
        <form action="<?php echo esc_url( get_permalink( get_option( 'search_page_id' ) ) ); ?>" method="post">
            <input type="hidden" name="action" value="search">
            <?php
            // Country dropdown
            wp_dropdown_categories( array(
                'taxonomy'         => $taxonomy_location_url,
                'name'             => 'country',
                'show_option_none' => __( 'Country', 'escortwp' ),
                'class'            => 'select2',
            ) );
            ?>
            <p>
                <select name="gender" class="select2">
                    <?php foreach ( $gender_a as $key => $label ) : 
                        if ( in_array( $key, $settings_theme_genders, true ) ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>">
                                <?php echo esc_html( $label ); ?>
                            </option>
                    <?php endif; endforeach; ?>
                </select>
            </p>
            <p>
                <label><input type="checkbox" name="premium" value="1"> <?php esc_html_e( 'Only premium', 'escortwp' ); ?></label>
            </p>
            <p>
                <label><input type="checkbox" name="independent" value="1"> <?php esc_html_e( 'Only independent', 'escortwp' ); ?></label>
            </p>
            <button type="submit" class="button button-primary"><?php esc_html_e( 'Search', 'escortwp' ); ?></button>
            <a href="<?php echo esc_url( get_permalink( get_option( 'search_page_id' ) ) ); ?>" class="button button-link">
                <?php esc_html_e( 'Advanced search', 'escortwp' ); ?>
            </a>
        </form>
    </div>
    <script>
    (function($){
      $('.quicksearch-toggle').on('click', function(){
        $('.quicksearch-form').slideToggle();
      });
    })(jQuery);
    </script>
    <?php
}

echo '</div>'; // .sidebar-mobile
