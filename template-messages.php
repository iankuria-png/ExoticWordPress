<?php
/**
 * Template Name: Template Messages
 */

global $wpdb;
$current_user = wp_get_current_user();
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url() );
    exit;
}
$user_id        = intval( $current_user->ID );
$userstatus     = get_option( 'escortid' . $user_id );
$escort_post_id = intval( get_option( 'escortpostid' . $user_id ) );
$thumbnail      = get_first_image( $escort_post_id );
$escort         = get_post( $escort_post_id );

$table = $wpdb->prefix . 'messages';
$conversations = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT sub.* FROM (SELECT * FROM {$table} ORDER BY created DESC) AS sub WHERE (sub.s_author_id = %d OR sub.r_author_id = %d) GROUP BY sub.conv_id ORDER BY sub.created DESC",
        $user_id,
        $user_id
    )
);

get_header();
$messages_menu = 'menu-active';
?>

<?php locate_template( array( 'template-menus.php' ), true, false ); ?>

<div class="bodybox p-10 mb-30 bootstrap-wrapper" style="border:1px solid #f8c1cf; border-top:none; border-radius:0 0 5px 5px; background-color:#fff;">
    <div class="panel">
        <div class="panel-header">
            <b style="color:#E0006C;"><?php esc_html_e( 'All Conversations', 'escortwp' ); ?></b>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="addedtours ml-10 mr-10" style="width:100%;">
                    <div class="tour tourhead">
                        <?php if ( 'agency' === $userstatus ) : ?>
                            <div style="width:10%;text-align:center;" class="addedplace"><?php esc_html_e( 'Sender', 'escortwp' ); ?></div>
                            <div style="width:10%;text-align:center;" class="addedplace"><?php esc_html_e( 'Receiver', 'escortwp' ); ?></div>
                        <?php else : ?>
                            <div style="width:20%;text-align:center;" class="addedplace"><?php esc_html_e( 'Sender', 'escortwp' ); ?></div>
                        <?php endif; ?>
                        <div style="width:50%;text-align:center;" class="addedplace"><?php esc_html_e( 'Message', 'escortwp' ); ?></div>
                        <div style="width:15%;text-align:center;" class="addedplace"><?php esc_html_e( 'Date', 'escortwp' ); ?></div>
                        <div style="width:15%;text-align:center;" class="addedplace"><?php esc_html_e( 'Action', 'escortwp' ); ?></div>
                    </div>

                    <style>
                        @media screen and (max-width:768px){
                            .conv-off{display:none!important;}
                            .res-msg{clear:both;width:100%!important;text-align:left!important;margin:0!important;}
                            .sender{color:#346CA5;font-weight:bold;}
                        }
                        .date{font-size:12px;}
                    </style>

                    <?php foreach ( $conversations as $conv ) :
                        $s_id = intval( $conv->s_author_id );
                        $r_id = intval( $conv->r_author_id );
                        if ( $user_id === $s_id ) {
                            $name     = $conv->r_author_name;
                            $recipient= $conv->s_author_name;
                            $other_id = $r_id;
                        } else {
                            $name     = $conv->s_author_name;
                            $recipient= $conv->r_author_name;
                            $other_id = $s_id;
                        }
                    ?>
                        <div class="tour">
                            <?php if ( 'agency' === $userstatus ) : ?>
                                <div style="width:10%;" class="addedplace res-msg sender"><?php echo esc_html( $name ); ?></div>
                                <div style="width:10%;" class="addedplace res-msg sender"><?php echo esc_html( $recipient ); ?></div>
                            <?php else : ?>
                                <div style="width:20%;" class="addedplace res-msg sender"><?php echo esc_html( $name ); ?></div>
                            <?php endif; ?>
                            <div style="width:50%;" class="addedplace res-msg"><?php echo esc_html( $conv->msg ); ?></div>
                            <div style="width:15%;" class="addedplace res-msg date"><?php echo esc_html( $conv->created ); ?></div>
                            <div style="width:15%;" class="addedplace res-msg">
                                <button class="openchat" data-author_id="<?php echo esc_attr( $other_id ); ?>" data-post_id="<?php echo esc_attr( $conv->post_id ); ?>" data-author_name="<?php echo esc_attr( $name ); ?>">
                                    <?php esc_html_e( 'Open', 'escortwp' ); ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
