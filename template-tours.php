<?php
/*
Template Name: Template Tours
*/

global $current_user, $taxonomy_location_url;
$current_user = wp_get_current_user();

// Redirect non-logged-in users to login
if ( ! is_user_logged_in() ) {
    wp_redirect( esc_url( home_url( '/wp-login.php' ) ) );
    exit;
}

$userid     = $current_user->ID;
$userstatus = get_option( 'escortid' . intval( $userid ) );

// Only escorts can access this page
if ( 'escort' !== $userstatus ) {
    wp_redirect( esc_url( home_url() ) );
    exit;
}

$escort_post_id = get_option( 'escortpostid' . intval( $userid ) );
$thumbnail      = get_first_image( $escort_post_id );
$escort         = get_post( $escort_post_id );

// Handle add/edit tour form submission
if ( isset( $_POST['action'] ) && in_array( $_POST['action'], array( 'addtour', 'edittour' ), true ) ) {
    locate_template( array( 'register-independent-manage-my-tours-process-data.php' ), true, false );
}

get_header();
$tours_menu = 'menu-active';
?>

<?php locate_template( array( 'template-menus.php' ), true, false ); ?>

<div class="bodybox p-10 mb-30 bootstrap-wrapper" style="border:1px solid #f8c1cf; border-top:none; border-radius:0 0 5px 5px; background-color:#fff;">
    <div class="panel mt-20">
        <div class="panel-header">
            <b style="color:#E0006C;"><i class="fa fa-plane" aria-hidden="true"></i> <?php esc_html_e( 'Manage my tours', 'escortwp' ); ?></b>
        </div>
        <div class="panel-body pt-40">
            <?php if ( ! empty( $err ) ) : ?>
                <div class="err rad3"><?php echo esc_html( $err ); ?></div>
            <?php endif; ?>
            <?php if ( ! empty( $ok ) ) : ?>
                <div class="ok rad3"><?php echo esc_html( $ok ); ?></div>
            <?php endif; ?>

            <?php
            // Fetch tours belonging to this escort
            $query_args = array(
                'post_type'      => 'tour',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'ASC',
                'meta_query'     => array(
                    array(
                        'key'     => 'belongstoescortid',
                        'value'   => intval( $escort_post_id ),
                        'compare' => '=',
                        'type'    => 'NUMERIC',
                    ),
                ),
            );
            $tours = new WP_Query( $query_args );

            if ( $tours->have_posts() ) : ?>
                <div class="addedtours ml-10 mr-10">
                    <div class="tour tourhead">
                        <div class="addedstart"><?php esc_html_e( 'Start', 'escortwp' ); ?></div>
                        <div class="addedend"><?php esc_html_e( 'End', 'escortwp' ); ?></div>
                        <div class="addedplace"><?php esc_html_e( 'Place', 'escortwp' ); ?></div>
                        <div class="addedphone"><?php esc_html_e( 'Phone', 'escortwp' ); ?></div>
                        <div class="addedbuttons"></div>
                    </div>
                    <?php
                    while ( $tours->have_posts() ) : $tours->the_post();
                        // Build tournament location array
                        $location = array();
                        $city_id  = get_post_meta( get_the_ID(), 'city', true );
                        if ( $city = get_term( intval( $city_id ), $taxonomy_location_url ) ) {
                            $location[] = $city->name;
                        }
                        if ( showfield( 'state' ) ) {
                            $state_id = get_post_meta( get_the_ID(), 'state', true );
                            if ( $state = get_term( intval( $state_id ), $taxonomy_location_url ) ) {
                                $location[] = $state->name;
                            }
                        }
                        $country_id = get_post_meta( get_the_ID(), 'country', true );
                        if ( $country = get_term( intval( $country_id ), $taxonomy_location_url ) ) {
                            $location[] = $country->name;
                        }
                        ?>
                        <div class="tour" id="tour<?php the_ID(); ?>">
                            <span class="tour-info-mobile"><?php esc_html_e( 'Start', 'escortwp' ); ?>:</span>
                            <div class="addedstart"><?php echo esc_html( date_i18n( 'd/m/Y', get_post_meta( get_the_ID(), 'start', true ) ) ); ?></div>
                            <span class="tour-info-mobile-clear"></span>

                            <span class="tour-info-mobile"><?php esc_html_e( 'End', 'escortwp' ); ?>:</span>
                            <div class="addedend"><?php echo esc_html( date_i18n( 'd/m/Y', get_post_meta( get_the_ID(), 'end', true ) ) ); ?></div>
                            <span class="tour-info-mobile-clear"></span>

                            <span class="tour-info-mobile"><?php esc_html_e( 'Place', 'escortwp' ); ?>:</span>
                            <div class="addedplace"><?php echo esc_html( implode( ", ", $location ) ); ?></div>
                            <span class="tour-info-mobile-clear"></span>

                            <span class="tour-info-mobile"><?php esc_html_e( 'Phone', 'escortwp' ); ?>:</span>
                            <div class="addedphone"><?php echo esc_html( get_post_meta( get_the_ID(), 'phone', true ) ); ?></div>
                            <div class="addedbuttons">
                                <?php
                                if ( 'private' === get_post_status() && '1' === get_post_meta( get_the_ID(), 'needs_payment', true ) ) {
                                    echo '<div class="pb">' . generate_payment_buttons( '3', get_the_ID() ) . '</div>';
                                } else {
                                    echo '<i>' . esc_html( get_the_ID() ) . '</i><em>' . esc_html( get_the_ID() ) . '</em>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div> <!-- ADDED TOURS -->
            <?php else : ?>
                <h4><?php esc_html_e( "You didn't add any tours yet", 'escortwp' ); ?></h4>
            <?php endif; ?>

            <div class="clear"></div>
            <div id="btn-create" class="mt-40 mb-10 addreview-button rad25 bluebutton" style="width:130px;"><span class="icon-plus-circled"></span><?php esc_html_e( 'Create Tour', 'escortwp' ); ?></div>

        </div> <!-- panel-body -->
    </div> <!-- panel -->

    <div class="panel" id="create-update-tour" style="display:none;">
        <div class="panel-header">
            <b style="color:#E0006C;"><i class="fa fa-plus" aria-hidden="true"></i> <?php esc_html_e( 'Create a tour', 'escortwp' ); ?></b>
        </div>
        <div class="panel-body">
            <div class="addtourform managetours">
                <?php locate_template( array( 'register-independent-add-tour-form.php' ), true, false ); ?>
            </div>
        </div>
    </div><!-- Finish panel create-update-tour -->
</div><!-- Finish main bodybox -->

<script type="text/javascript">
jQuery(document).ready(function($) {
    $( '#btn-create' ).click(function() {
        $( '#create-update-tour' ).slideDown('slow');
    });
    $( '#create-update-tour' ).on('click', '#btn-close', function() {
        $( '#create-update-tour' ).slideUp('slow');
    });
    // delete a city tour
    $('.tour .addedbuttons i').on('click', function() {
        var id = $(this).text();
        $('#tour' + id + ' .addedbuttons').html('<b></b>');
        $.get(
            '<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/ajax/delete-tour.php',
            { id: id },
            function(data) {
                $('.deletemsg').html(data).fadeIn('slow').delay(1500).fadeOut('slow');
                $('#tour' + id).slideUp('slow');
            }
        );
    });
    // edit a city tour
    $('.tour .addedbuttons em').on('click', function() {
        $('#create-update-tour').slideDown('slow');
        var id = $(this).text();
        $('#tour' + id + ' .addedbuttons em').hide();
        $('#tour' + id + ' .addedbuttons').append('<b></b>');
        $('html,body').animate({ scrollTop: $('.managetours').offset().top }, 'swing');
        $.get(
            '<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/ajax/edit-tour.php',
            { id: id },
            function(data) {
                $('.addtourform').html(data);
                $('html').scrollTop(0);
                $('#tour' + id + ' .addedbuttons b').hide();
                $('#tour' + id + ' .addedbuttons').append('<em>' + id + '</em>');
            }
        );
    });
});
</script>

<?php get_footer(); ?>
