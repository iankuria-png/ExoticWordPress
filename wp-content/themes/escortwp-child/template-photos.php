<?php
/*
Template Name: Template Photos
*/

global $current_user,$taxonomy_location_url,$wpdb;
$current_user = wp_get_current_user();

// Require login
if(!is_user_logged_in()) {
    wp_redirect( site_url('/wp-login.php') );
    exit;
}

$userid = $current_user->ID;
$userstatus = get_option("escortid".$userid);

// Only escorts may access
if($userstatus!='escort') {
    wp_redirect( site_url() );
    exit;
}

// Payment-required check for private profiles
$escort_post_id = get_option("escortpostid".$userid);
$post = get_post($escort_post_id);
$is_private = (get_post_status($escort_post_id) == "private");
$needs_payment = (get_post_meta($escort_post_id, "needs_payment", true) == "1");
$escort_has_not_payed = ($is_private && $needs_payment) ? "yes" : "no";
if($escort_has_not_payed == "yes") {
    _e('Other edit links will be shown after payment',966);
    exit;
}

// Fetch existing media
$photos = get_children(
    array(
        'post_parent' => $escort_post_id,
        'post_status' => 'inherit',
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'order' => 'ASC',
        'orderby' => 'menu_order ID'
    )
);
$videos = get_children(
    array(
        'post_parent' => $escort_post_id,
        'post_status' => 'inherit',
        'post_type' => 'attachment',
        'post_mime_type' => 'video',
        'order' => 'ASC',
        'orderby' => 'menu_order ID'
    )
);

$max_img = get_option('maximgupload');
$max_vid = get_option('maxvideoupload');
$photos_left = (int)$max_img - count($photos);
$videos_left = (int)$max_vid - count($videos);
$adminnote = get_post_meta($escort_post_id, "adminnote", true);

get_header();
$photos_menu = 'menu-active';
?>

<?php include (get_template_directory() . '/template-menus.php'); ?>
<div class="bodybox p-10 mb-30 bootstrap-wrapper" style="border:1px solid #f8c1cf;border-top:none;border-radius:0 0 5px 5px;background-color:#fff;">
    <?php if ($userid || current_user_can('level_10')) {
        include (get_template_directory_uri() . '/register-agency-manage-escorts-option-buttons.php');
    }?>
    <?php if ($adminnote) {
        echo '<div class="clear"></div>';
        echo '<div class="err rad3">'.$adminnote.'</div>';
    }?>
    <?php if ($userid || current_user_can('level_10')) { ?>
        <style>
            .upload-button-style {background:#ddd;color:#666;padding:12px 20px;float:right;font-weight:bold;font-size:15px;}
        </style>
    <?php } ?>
    <div class="panel">
        <div class="panel-header">
            <b style="color:#E0006C;">My Photos & Videos</b>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="picture-upload-border profile-page-no-media-wrapper profile-page-no-media-wrapper-photos <?=(get_option('allowvideoupload')=="1")?"col50 l":"col100"?>">
                        <div class="profile-page-no-media profile-page-no-photos profile-page-no-photos-click rad3 col100 text-center" id="profile-page-no-photos">
                            <div class="icon icon-picture"></div>
                            <div class="for-browsers" data-mobile-text="<?php _e('Tap here to upload your images',1094); ?>">
                                <p><?php _e('Drag your images here to upload them',1091); ?> <?php _e('or <u>Select from a folder</u>',1092); ?></p>
                            </div>
                            <p class="max-photos"><?php _e('You can upload a maximum of %s images',1093,'<b>'.$photos_left.'</b>'); ?></p>
                            <div class="clear"></div>
                        </div>
                        <div class="profile_photos_button_container hide">
                            <input id="profile_photos_upload" name="file_upload" type="file" multiple />
                        </div>
                    </div>
                </div>
            </div>

            <div class="row m-20" id="Content">
                <div style='width:100%' class="girlsingle" itemscope itemtype="http://schema.org/Person">
                    <?php if($photos || $videos) {
                        if ($userid || current_user_can('level_10')) {
                            echo '<div class="image-buttons-legend r"><div class="first l"><span class="button-delete icon-cancel"></span> '._e('Delete image',496).'</div><div class="last l"><span class="button-main-image icon-ok"></span> '._e('Mark as main image',589).'</div></div>';
                        }
                    }

                    <div class="clear10"></div>
                    <div class="thumbs" itemscope itemtype="http://schema.org/ImageGallery">
                        <?php
                        $nrofphotos = count($photos) - 1;
                        $nrofvideos = count($videos);
                        if(count($photos) > 0 || $nrofvideos > 0) {
                            if($nrofvideos > 0) {
                                $and_videos = ' '._e('and %d more videos',1268,'<span class="nr rad5 pinkdegrade">'.$nrofvideos.'</span>');
                            }
                            if(get_option("viphide1") && get_option("paymentgateway") && !is_user_logged_in()) {
                                echo '<div class="lockedsection rad5"><div class="icon icon-lock l"></div>'._e('This %s has %s more photos',1097,array($taxonomy_profile_name,'<span class="nr rad5 pinkdegrade">'.$nrofphotos.'</span>')).$and_videos.'.<br />'._e('You need to','617').' <a href="'.get_permalink(get_option('main_reg_page_id')).'">'._e('register',618).'</a> '._e('or',619).' <a href="'.wp_login_url(get_permalink()).'">'._e('login',620).'</a> '._e('to view other photos',873).'.<div class="clear"></div></div>';
                            } else {
                                $unlocked = get_user_meta($userid,'unlocked_escorts',true)?:array();
                                if(get_option("viphide1") && get_option("paymentgateway") && !get_user_meta($userid,'vip',true) && !in_array($escort_post_id,$unlocked) && !current_user_can('level_10')) {
                                    echo '<div class="lockedsection rad5"><div class="icon icon-lock l"></div>'._e('This profile has %d more photos',1097,$nrofphotos).$and_videos.'.<br />';
                                    if(get_option("vipunlock")=="yes") {
                                        echo _e('To see all photos unlock by paying',877).' <strong>'.format_price('4').'</strong>.';
                                        echo '<div class="center">'.generate_payment_buttons('4',$userid.'-'.$escort_post_id).'</div>';
                                    } else {
                                        echo _e('You need VIP status to see the rest photos',878).'. '._e('VIP costs',879).' <strong>'.format_price('5').'</strong>.';
                                        echo '<div class="center">'.generate_payment_buttons('5',$userid).'</div>';
                                    }
                                    echo '<div class="clear"></div></div>';
                                } else {
                                    // Videos
                                    foreach($videos as $video) {
                                        $imagebuttons = ($userid||current_user_can('level_10'))?'<span class="edit-buttons"><span class="icon button-delete icon-cancel rad3"></span></span>':'';
                                        echo '<div class="profile-video-thumb-wrapper"><div class="profile-img-thumb profile-video-thumb rad3" id="'.$video->ID.'">'.$imagebuttons.'<a href="#vid'.$video->ID.'" rel="profile-video"><img src="'.get_template_directory_uri().'/i/video-placeholder.svg" class="video-image-play" /></a></div></div>';
                                    }
                                    if(count($videos)>0) echo '<div class="clear10"></div>';
                                    // Photos
                                    foreach($photos as $photo) {
                                        $thumb = wp_get_attachment_image_src($photo->ID,'profile-thumb');
                                        echo '<div class="profile-img-thumb-wrapper"><div class="profile-img-thumb" id="'.$photo->ID.'">'.('<span class="edit-buttons"><span class="icon button-delete icon-cancel rad3"></span><span class="icon button-main-image icon-ok rad3"></span></span>').'<a href="'.$photo->guid.'" rel="profile-photo"><img src="'.$thumb[0].'" class="mobile-ready-img rad3" /></a></div></div>';
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                    <div class="clear20"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
