<?php
/**
 * Child Theme Override: Template Menus
 */
$current_user = wp_get_current_user();
$user_id      = intval( $current_user->ID );
$userstatus   = get_option( 'escortid' . $user_id );

// Determine base URLs
$base_url = esc_url( site_url() );
$profile_slug = ( 'agency' === $userstatus ) ? '/agency-profile' : '/profile';

// Menu active classes (should be set in template pages)
$profile_menu  = isset( $profile_menu ) ? esc_attr( $profile_menu ) : '';
$messages_menu = isset( $messages_menu ) ? esc_attr( $messages_menu ) : '';
$myescorts_menu= isset( $myescorts_menu ) ? esc_attr( $myescorts_menu ) : '';
$photos_menu   = isset( $photos_menu ) ? esc_attr( $photos_menu ) : '';
$tours_menu    = isset( $tours_menu ) ? esc_attr( $tours_menu ) : '';
$orders_menu   = isset( $orders_menu ) ? esc_attr( $orders_menu ) : '';
$settings_menu = isset( $settings_menu ) ? esc_attr( $settings_menu ) : '';
?>

<style type="text/css">
.menu-box{border:3px solid gainsboro;border-radius:5px;color:#346ca5;background:#fff;cursor:pointer;}
.mobile-menu-box{cursor:pointer;border:3px solid gainsboro;border-radius:5px;color:#346ca5;background:#fff;}
.menu-box:hover,.menu-active{background:#d633a3;color:#fff;}
.menu-box i{font-size:2em;}
.panel{margin-bottom:20px;background:#fff;border:1px solid #ddd;border-radius:4px;box-shadow:0 1px 1px rgba(0,0,0,.05);}
.panel-header{color:#333;background:#FFF0F4;padding:10px 15px;border-bottom:1px solid #ddd;border-top-left-radius:3px;border-top-right-radius:3px;}
.panel-body{padding:15px;}
.section-box b,.row b{color:#FE5ACB;}
.section-box{border-bottom:1px dotted #FE5ACB;padding:5px;}
.services .icon-ok{color:green;}
th.hide-incall{background:#FE5ACB;color:#FFF;}
.sidebar_expire_notice{padding:10px 0;}
.sidebar_expire_notice b{color:#fff;}
@media(max-width:767px){#mobile-menu{display:block;}#desktop-menu{display:none;}.menu-box i{display:none;}.featured-mobile{display:block;}.menu-box h5 i{display:inline;font-size:1em;}}
@media(min-width:768px) and (max-width:1180px){.body{margin:0 80px!important;}}
@media(min-width:768px){#mobile-menu{display:none;}#desktop-menu{display:flex!important;}#desktop-menu .col-md-2{width:110px!important;}.menu-box h5 i{display:none;}.featured-mobile{display:none;}}
.featured-mobile{display:none;}.disabled{pointer-events:none;opacity:0.4;}
</style>

<div class="contentwrapper" style="background:#FFF0F4;">
    <div class="body mt-10 mb-10" style="border:none;">
        <div class="bodybox p-10" style="border:1px solid #f8c1cf;background:#7ca9c8;border-radius:5px 5px 0 0;">
            <div class="bootstrap-wrapper">
                <div class="row" id="mobile-menu">
                    <div class="col-md-2 text-center mobile-menu-box p-5 pb-0">
                        <h3 class="pb-5"><b><i class="fa fa-th-list"></i> <?php esc_html_e( 'DASHBOARD MENU', 'escortwp' ); ?></b></h3>
                    </div>
                </div>
                <div id="desktop-menu" class="row">
                    <div class="col-md-2">
                        <a href="<?php echo $base_url . $profile_slug; ?>">
                            <div class="menu-box <?php echo $profile_menu; ?> p-10 profile text-center">
                                <i class="fa fa-user"></i>
                                <h5><i class="fa fa-user"></i> <?php esc_html_e( 'Profile', 'escortwp' ); ?></h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="<?php echo $base_url; ?>/messages">
                            <div class="menu-box <?php echo $messages_menu; ?> p-10 message text-center">
                                <i class="fa fa-envelope"></i>
                                <h5><i class="fa fa-envelope"></i> <?php esc_html_e( 'Messages', 'escortwp' ); ?></h5>
                            </div>
                        </a>
                    </div>
                    <?php if ( 'agency' === $userstatus ) : ?>
                        <div class="col-md-2 col-xs-6">
                            <a href="<?php echo $base_url; ?>/agency-escorts">
                                <div class="menu-box <?php echo $myescorts_menu; ?> p-10 text-center">
                                    <i class="fa fa-sitemap"></i>
                                    <h5><i class="fa fa-sitemap"></i> <?php esc_html_e( 'Escorts', 'escortwp' ); ?></h5>
                                </div>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="col-md-2 col-xs-6 <?php echo ( 'member' === $userstatus ) ? 'disabled' : ''; ?>">
                            <a href="<?php echo $base_url; ?>/photos">
                                <div class="menu-box <?php echo $photos_menu; ?> p-10 text-center">
                                    <i class="fa fa-camera"></i>
                                    <h5><i class="fa fa-camera"></i> <?php esc_html_e( 'Photos', 'escortwp' ); ?></h5>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-2 col-xs-6 <?php echo ( 'member' === $userstatus || 'agency' === $userstatus ) ? 'disabled' : ''; ?>">
                        <a href="<?php echo $base_url; ?>/tours">
                            <div class="menu-box <?php echo $tours_menu; ?> p-10 text-center">
                                <i class="fa fa-plane"></i>
                                <h5><i class="fa fa-plane"></i> <?php esc_html_e( 'Tours', 'escortwp' ); ?></h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-6">
                        <a href="<?php echo $base_url; ?>/orders">
                            <div class="menu-box <?php echo $orders_menu; ?> p-10 text-center">
                                <i class="fa fa-th-list"></i>
                                <h5><i class="fa fa-th-list"></i> <?php esc_html_e( 'Orders', 'escortwp' ); ?></h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-xs-6">
                        <a href="<?php echo $base_url; ?>/settings">
                            <div class="menu-box <?php echo $settings_menu; ?> p-10 text-center">
                                <i class="fa fa-cog"></i>
                                <h5><i class="fa fa-cog"></i> <?php esc_html_e( 'Settings', 'escortwp' ); ?></h5>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            var toggled = false;
            $('#mobile-menu').click(function(){
                toggled = !toggled;
                $('#mobile-menu .fa').toggleClass('fa-th-list fa-caret-square-o-down');
                $('#desktop-menu').toggle('slow');
            });
        });
    </script>
</div>
