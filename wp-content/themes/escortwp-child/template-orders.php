<?php
/**
 * Template Name: Template Orders
 */

global $current_user, $wpdb, $taxonomy_location_url, $taxonomy_agency_url;
$current_user = wp_get_current_user();

// Redirect non-logged-in users
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url() );
    exit;
}

$user_id    = intval( $current_user->ID );
$userstatus = get_option( 'escortid' . $user_id );

// Prevent admins from viewing this page
if ( current_user_can( 'manage_options' ) ) {
    wp_redirect( home_url() );
    exit;
}

// Payment checks
$escort_post_id       = get_option( 'escortpostid' . $user_id );
$agency_not_paid      = ( get_option( 'agency_has_not_payed' ) === 'yes' );
$escort_not_paid      = ( get_option( 'escort_has_not_payed' ) === 'yes' );
if ( $agency_not_paid || $escort_not_paid ) {
    esc_html_e( 'Other edit links will be shown after payment', 'escortwp' );
    exit;
}

// Handle classified ad submission
if ( isset( $_POST['action'] ) && 'addclassifiedad' === $_POST['action'] ) {
    locate_template( array( 'manage-classified-ads-info-process.php' ), true, false );
}

get_header();
$orders_menu = 'menu-active';
?>

<?php locate_template( array( 'template-menus.php' ), true, false ); ?>

<div class="bodybox p-10 mb-30 bootstrap-wrapper" style="border:1px solid #f8c1cf; border-top:none; border-radius:0 0 5px 5px; background-color:#fff;">

    <?php if ( ! empty( $ok ) ) : ?>
        <div class="ok rad3"><?php echo esc_html( $ok ); ?></div>
    <?php endif; ?>

    <?php if ( ! empty( $err ) ) : ?>
        <div class="err rad3"><?php echo esc_html( $err ); ?></div>
    <?php endif; ?>

    <?php if ( 'escort' === $userstatus ) : ?>
        <div class="row">
            <div class="col-md-4">
                <div class="panel mt-20">
                    <div class="panel-header">
                        <b style="color:#E0006C;"><i class="fa fa-th-list" aria-hidden="true"></i> <?php esc_html_e( 'Profile Status', 'escortwp' ); ?></b>
                    </div>
                    <div class="panel-body">
                        <?php
                        $expire = get_post_meta( $escort_post_id, 'escort_expire', true );
                        if ( $expire ) {
                            $expire_date = date_i18n( 'd M Y', intval( $expire ) );
                            $label       = ( $userstatus === $taxonomy_agency_url )
                                ? sprintf( esc_html__( 'The %s profile you added is active until:', 'escortwp' ), esc_html__( 'escort', 'escortwp' ) )
                                : sprintf( esc_html__( 'Your %s profile is active until:', 'escortwp' ), esc_html__( 'escort', 'escortwp' ) );
                            echo '<div class="sidebar_expire_notice bluedegrade center">';
                            echo '<small>' . esc_html( $label ) . '</small><br><b>' . esc_html( $expire_date ) . '</b>';

                            // Extend registration button
                            if ( get_post_meta( $escort_post_id, 'escort_renew', true ) ) {
                                // Subscription active - cancel option could go here
                            } else {
                                $price_key = ( $userstatus === $taxonomy_agency_url ) ? 'agescortregprice' : 'indescregprice';
                                if ( get_option( $price_key ) && get_option( 'paymentgateway' ) ) {
                                    echo '<div class="clear10"></div>';
                                    echo '<div class="center">' . generate_payment_buttons( '8', esc_html( $escort_post_id ), esc_html__( 'Extend registration', 'escortwp' ) ) . '</div>';
                                    echo '<div class="center">' . format_price( '8', true ) . '</div>';
                                }
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel mt-20">
                    <div class="panel-header">
                        <b style="color:#E0006C;"><i class="fa fa-th-list" aria-hidden="true"></i> <?php esc_html_e( 'Featured Status', 'escortwp' ); ?></b>
                    </div>
                    <div class="panel-body">
                        <?php
                        $is_featured = ( get_post_meta( $escort_post_id, 'featured', true ) === '1' );
                        $needs_pay   = ( get_post_meta( $escort_post_id, 'needs_payment', true ) === '1' );
                        if ( $is_featured && ! $needs_pay ) {
                            $feat_expire = get_post_meta( $escort_post_id, 'featured_expire', true );
                            $expire_date = $feat_expire ? date_i18n( 'd M Y', intval( $feat_expire ) ) : esc_html__( 'forever', 'escortwp' );

                            echo '<div class="sidebar_expire_notice bluedegrade center">';
                            echo '<small>' . esc_html__( 'Your featured status is active until:', 'escortwp' ) . '</small><br><b>' . esc_html( $expire_date ) . '</b>';

                            if ( $feat_expire && get_option( 'featuredprice' ) && get_option( 'paymentgateway' ) ) {
                                echo '<div class="clear10"></div>';
                                echo '<div class="center">' . generate_payment_buttons( '2', esc_html( $escort_post_id ), esc_html__( 'Extend featured', 'escortwp' ) ) . '</div>';
                                echo '<div class="center">' . format_price( '2', true ) . '</div>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel mt-20">
                    <div class="panel-header">
                        <b style="color:#E0006C;"><i class="fa fa-th-list" aria-hidden="true"></i> <?php esc_html_e( 'Premium Status', 'escortwp' ); ?></b>
                    </div>
                    <div class="panel-body">
                        <?php
                        $is_premium = ( get_post_meta( $escort_post_id, 'premium', true ) === '1' );
                        if ( $is_premium && ! $needs_pay ) {
                            $prem_expire = get_post_meta( $escort_post_id, 'premium_expire', true );
                            $expire_date = $prem_expire ? date_i18n( 'd M Y', intval( $prem_expire ) ) : esc_html__( 'forever', 'escortwp' );

                            echo '<div class="sidebar_expire_notice orangedegrade center">';
                            echo '<small>' . esc_html__( 'Your premium status is active until:', 'escortwp' ) . '</small><br><b>' . esc_html( $expire_date ) . '</b>';

                            if ( $prem_expire && get_option( 'premiumprice' ) && get_option( 'paymentgateway' ) ) {
                                echo '<div class="clear10"></div>';
                                echo '<div class="center">' . generate_payment_buttons( '1', esc_html( $escort_post_id ), esc_html__( 'Extend premium', 'escortwp' ) ) . '</div>';
                                echo '<div class="center">' . format_price( '1', true ) . '</div>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="panel mt-20">
        <div class="panel-header">
            <b style="color:#E0006C;"><i class="fa fa-th-list" aria-hidden="true"></i> <?php esc_html_e( 'Classified Ads', 'escortwp' ); ?></b>
        </div>
        <div class="panel-body pl-40 pr-40 pb-40">
            <div class="row">
                <?php
                $ads_query = new WP_Query( array(
                    'post_type'      => 'ad',
                    'author'         => $user_id,
                    'posts_per_page' => -1,
                    'orderby'        => 'date',
                    'order'          => 'ASC',
                ) );

                if ( $ads_query->have_posts() ) :
                ?>
                    <table class="listagencies rad3">
                        <thead>
                            <tr class="trhead rad3">
                                <th><?php esc_html_e( 'Title', 'escortwp' ); ?></th>
                                <th><?php esc_html_e( 'Type', 'escortwp' ); ?></th>
                                <th><?php esc_html_e( 'Date added', 'escortwp' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ( $ads_query->have_posts() ) : $ads_query->the_post(); ?>
                            <tr class="agencytr">
                                <td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
                                <td><?php echo esc_html( get_post_meta( get_the_ID(), 'type', true ) ); ?></td>
                                <td><?php echo esc_html( get_the_date( 'd F Y' ) ); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p><?php esc_html_e( 'No classified ads yet', 'escortwp' ); ?></p>
                <?php endif; wp_reset_postdata(); ?>

                <div class="clear"></div>
                <div id="btn-create" class="mt-40 mb-10 addreview-button rad25 bluebutton" style="width:130px;"><span class="icon-plus-circled"></span> <?php esc_html_e( 'Create Ads', 'escortwp' ); ?></div>
            </div>
        </div>

        <div class="panel" id="create-ads" style="display:none;">
            <div class="panel-header">
                <b style="color:#E0006C;"><i class="fa fa-plus" aria-hidden="true"></i> <?php esc_html_e( 'Create classified ads', 'escortwp' ); ?></b>
            </div>
            <div class="panel-body">
                <?php locate_template( array( 'manage-classified-ads-form.php' ), true, false ); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#btn-create').click(function() {
        $('#create-ads').slideDown('slow');
    });
    $('#create-ads').on('click', '#btn-close', function() {
        $('#create-ads').slideUp('slow');
    });
});
</script>

<?php get_footer(); ?>
