<?php
/*
Template Name: Template Settings
*/

global $current_user, $taxonomy_location_url, $taxonomy_agency_url, $taxonomy_profile_url, $agency_has_not_payed, $escort_has_not_payed;
$current_user = wp_get_current_user();

// Redirect non-logged-in users to login
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url() );
    exit;
}

// Redirect admins
if ( current_user_can( 'manage_options' ) ) {
    wp_redirect( home_url() );
    exit;
}

// Payment checks
if ( 'yes' === $agency_has_not_payed || 'yes' === $escort_has_not_payed ) {
    esc_html_e( 'Other edit links will be shown after payment', 'escortwp' );
    exit;
}

// Ensure user has appropriate role or status
$user_id    = $current_user->ID;
$escort_id  = get_option( 'escortid' . intval( $user_id ) );
if ( ! in_array( $escort_id, array( $taxonomy_agency_url, $taxonomy_profile_url, 'member' ), true ) ) {
    wp_redirect( home_url() );
    exit;
}

$err = '';
$ok  = '';

// Handle password change submission
if ( isset( $_POST['action'], $_POST['pass'], $_POST['cpass'] ) && 'change' === $_POST['action'] ) {
    $pass  = $_POST['pass'];
    $cpass = $_POST['cpass'];
    if ( '' !== $pass ) {
        if ( $pass === $cpass ) {
            if ( strlen( $pass ) < 6 || strlen( $pass ) > 50 ) {
                $err .= esc_html__( 'Your password must be between 6 and 50 characters', 'escortwp' ) . '<br />';
            } elseif ( false !== strpos( stripslashes( $pass ), '\\' ) ) {
                $err .= esc_html__( 'Passwords may not contain the character "\\"', 'escortwp' ) . '<br />';
            } else {
                $update = wp_update_user( array(
                    'ID'        => intval( $user_id ),
                    'user_pass' => $pass,
                ) );
                if ( is_wp_error( $update ) ) {
                    $err .= esc_html( $update->get_error_message() ) . '<br />';
                } else {
                    $ok = sprintf(
                        /* translators: %s: new password */
                        esc_html__( 'Your new password is: %s', 'escortwp' ),
                        esc_html( $pass )
                    );
                }
            }
        } else {
            $err .= esc_html__( 'Password and Confirm password must be the same', 'escortwp' ) . '<br />';
        }
    } else {
        $err .= esc_html__( 'The password field is empty', 'escortwp' ) . '<br />';
    }
}

get_header();
$settings_menu = 'menu-active';
?>

<?php locate_template( array( 'template-menus.php' ), true, false ); ?>
<div class="bodybox p-10 mb-30 bootstrap-wrapper" style="border:1px solid #f8c1cf; border-top:none; border-radius:0 0 5px 5px; background-color:#fff;">

    <?php if ( $ok ) : ?>
        <div class="ok rad3"><?php echo esc_html( $ok ); ?></div>
    <?php endif; ?>

    <?php if ( $err ) : ?>
        <div class="err rad3"><?php echo wp_kses_post( $err ); ?></div>
    <?php endif; ?>

    <div class="panel mt-20">
        <div class="panel-header">
            <b style="color:#E0006C;"><i class="fa fa-key" aria-hidden="true"></i> <?php esc_html_e( 'Change password', 'escortwp' ); ?></b>
        </div>
        <div class="panel-body pl-40 pb-40">
            <form method="post" class="form-styling">
                <input type="hidden" name="action" value="change" />
                <div class="clear20"></div>
                <div class="form-label">
                    <label for="pass"><?php esc_html_e( 'New Password', 'escortwp' ); ?></label>
                    <small><?php esc_html_e( 'Must be between 6 and 50 characters', 'escortwp' ); ?></small>
                </div>
                <div class="form-input">
                    <input type="password" name="pass" id="pass" class="input longinput" autocomplete="off" required />
                </div>
                <div class="form-separator"></div>

                <div class="clear20"></div>
                <div class="form-label">
                    <label for="cpass"><?php esc_html_e( 'Confirm Password', 'escortwp' ); ?></label>
                </div>
                <div class="form-input">
                    <input type="password" name="cpass" id="cpass" class="input longinput" autocomplete="off" required />
                </div>
                <div class="form-separator"></div>

                <div class="clear20"></div>
                <div class="center">
                    <input type="submit" name="submit" value="<?php esc_html_e( 'Update Password', 'escortwp' ); ?>" class="bluebutton rad3" />
                </div>
            </form>
            <div class="clear"></div>
        </div><!-- Finish panel-body -->
    </div><!-- Finish panel -->
</div> <!-- contentwrapper -->

<?php get_footer(); ?>
