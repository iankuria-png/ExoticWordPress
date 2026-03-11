<?php
/**
 * Template Name: Template Profile
 */

// Get current user and ensure login
$current_user = wp_get_current_user();
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url() );
    exit;
}

$user_id     = $current_user->ID;
$userstatus  = get_option( 'escortid' . intval( $user_id ) );

// Only escorts or members can view
if ( ! in_array( $userstatus, array( 'escort', 'member' ), true ) ) {
    wp_redirect( home_url() );
    exit;
}

// Get escort post and metadata
$escort_post_id = intval( get_option( 'escortpostid' . intval( $user_id ) ) );
$escort_post    = get_post( $escort_post_id );

// Prepare profile fields
$aboutyou    = nl2br( wp_kses_post( apply_filters( 'the_content', $escort_post->post_content ) ) );
$yourname    = esc_html( $escort_post->post_title );
$phone       = esc_html( get_post_meta( $escort_post_id, 'phone', true ) );
$escortemail = sanitize_email( get_post_meta( $escort_post_id, 'escortemail', true ) );
$website_raw = get_post_meta( $escort_post_id, 'website', true );
$website     = esc_url( $website_raw );

// Location handling
$location = array();
if ( '1' === get_option( 'locationdropdown' ) ) {
    $country = get_post_meta( $escort_post_id, 'country', true );
    $state   = ( showfield( 'state' ) ) ? get_post_meta( $escort_post_id, 'state', true ) : '';
    $city    = get_post_meta( $escort_post_id, 'city', true );
    $location = array_filter( array( esc_html( $city ), esc_html( $state ), esc_html( $country ) ) );
} else {
    $terms = wp_get_post_terms( $escort_post_id, $taxonomy_location_url );
    if ( ! is_wp_error( $terms ) && ! empty( $terms[0] ) ) {
        $city_term    = $terms[0];
        $state_term   = get_term( $city_term->parent, $taxonomy_location_url );
        $country_term = ( $state_term && ! is_wp_error( $state_term ) ) ? get_term( $state_term->parent, $taxonomy_location_url ) : false;
        $location[]   = '<a href="' . esc_url( get_term_link( $city_term ) ) . '">' . esc_html( $city_term->name ) . '</a>';
        if ( showfield( 'state' ) && $state_term ) {
            $location[] = '<a href="' . esc_url( get_term_link( $state_term ) ) . '">' . esc_html( $state_term->name ) . '</a>';
        }
        if ( $country_term && ! is_wp_error( $country_term ) ) {
            $location[] = '<a href="' . esc_url( get_term_link( $country_term ) ) . '">' . esc_html( $country_term->name ) . '</a>';
        }
    }
}

// Calculate age
$birthday = get_post_meta( $escort_post_id, 'birthday', true );
$age      = ( $birthday ) ? floor( ( time() - strtotime( $birthday ) ) / YEAR_IN_SECONDS ) : '';

// Handle privacy toggle
if ( isset( $_POST['action'] ) && 'settoprivate' === $_POST['action'] ) {
    // Only owner or admin can toggle
    if ( current_user_can( 'edit_post', $escort_post_id ) ) {
        $current_status = get_post_status( $escort_post_id );
        $new_status     = ( 'publish' === $current_status ) ? 'private' : 'publish';
        wp_update_post( array( 'ID' => $escort_post_id, 'post_status' => $new_status ) );
    }
    // Refresh to show new status
    wp_redirect( get_permalink() );
    exit;
}

get_header();
$profile_menu = 'menu-active';
?>

<?php locate_template( array( 'template-menus.php' ), true, false ); ?>

<div class="bodybox p-10 mt-10 mb-30 bootstrap-wrapper" style="border:1px solid #f8c1cf; border-top:none; border-radius:0 0 5px 5px; background-color:#fff;">
    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4 pt-20">
                    <h3 style="color:#E0006C;" class="pb-0">
                        <i class="fa fa-user" aria-hidden="true"></i> <?php echo $yourname; ?>
                        <?php if ( 'private' === get_post_status( $escort_post_id ) ) : ?>
                            : <?php esc_html_e( 'Private', 'escortwp' ); ?>
                        <?php endif; ?>
                    </h3>
                    <hr>
                    <div class="pl-20">
                        <?php if ( $age ) : ?>
                            <div class="section-box"><b><?php esc_html_e( 'Age', 'escortwp' ); ?>:</b> <span class="valuecolumn"><?php echo esc_html( $age ); ?> <?php esc_html_e( 'years', 'escortwp' ); ?></span></div>
                        <?php endif; ?>

                        <?php if ( $location ) : ?>
                            <div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                                <div class="section-box"><b><?php esc_html_e( 'Location', 'escortwp' ); ?>:</b> <span class="valuecolumn"><?php echo wp_kses_post( implode( ', ', $location ) ); ?></span></div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $phone ) : ?>
                            <div class="section-box"><b><?php esc_html_e( 'Phone', 'escortwp' ); ?>:</b> <span class="valuecolumn"><a href="tel:<?php echo $phone; ?>"><?php echo $phone; ?></a></span></div>
                        <?php endif; ?>

                        <?php if ( $website ) : ?>
                            <div class="section-box"><b><?php esc_html_e( 'Website', 'escortwp' ); ?>:</b> <span class="valuecolumn"><a href="<?php echo $website; ?>" target="_blank" rel="nofollow"><?php echo esc_html( wp_parse_url( $website, PHP_URL_HOST ) ); ?></a></span></div>
                        <?php endif; ?>

                        <?php if ( 'member' === $userstatus ) : ?>
                            <div class="section-box"><b><?php esc_html_e( 'Email', 'escortwp' ); ?>:</b> <span class="valuecolumn"><?php echo esc_html( $current_user->user_email ); ?></span></div>
                            <a href="<?php echo esc_url( site_url( '/profile-edit' ) ); ?>"><button class="addreview-button rad25 bluebutton"><i class="fa fa-pencil-square-o"></i> <?php esc_html_e( 'Edit Profile', 'escortwp' ); ?></button></a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <center><img src="<?php echo esc_url( get_first_image( $escort_post_id ) ); ?>" alt="<?php echo $yourname; ?>" style="border-radius:50%; box-shadow:1px 1px 3px 1px grey;"></center>
                </div>

                <div class="col-md-4 pt-40 text-center">
                    <?php if ( 'escort' === $userstatus ) : ?>
                        <a href="<?php echo esc_url( site_url( '/edit-profile' ) ); ?>"><button class="addreview-button rad25 bluebutton"><i class="fa fa-pencil-square-o"></i> <?php esc_html_e( 'Edit Profile', 'escortwp' ); ?></button></a>
                        <a href="<?php echo esc_url( get_permalink( $escort_post_id ) ); ?>"><button class="addreview-button rad25 bluebutton"><i class="fa fa-link"></i> <?php esc_html_e( 'Public Link', 'escortwp' ); ?></button></a>

                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="settoprivate" />
                            <button type="submit" class="addreview-button rad25 bluebutton<?php echo ( 'publish' === get_post_status( $escort_post_id ) ) ? ' redbutton' : ''; ?>"><i class="fa fa-lock"></i> <?php echo ( 'publish' === get_post_status( $escort_post_id ) ) ? esc_html__( 'Set to private', 'escortwp' ) : esc_html__( 'Activate profile', 'escortwp' ); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            </div><!-- /.row -->
        </div><!-- /.panel-body -->
    </div><!-- /.panel -->

    <?php if ( 'escort' === $userstatus ) : ?>
        <div class="panel">
            <div class="panel-header"><b><?php esc_html_e( 'About Me', 'escortwp' ); ?></b></div>
            <div class="panel-body"><?php echo $aboutyou; ?></div>
        </div>
    <?php endif; ?>

</div><!-- /.contentwrapper -->

<?php get_footer(); ?>
