<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $payment_duration_a, $taxonomy_profile_name, $taxonomy_profile_name_plural, $taxonomy_location_url, $taxonomy_profile_url, $taxonomy_agency_url, $taxonomy_agency_name, $gender_a, $settings_theme_genders;
$current_user = wp_get_current_user();
?>
<div class="sidebar-right">
<?php
if(is_user_logged_in()) {
    $userid = $current_user->ID;
    $userstatus = get_option("escortid".$userid);
} else {
    $userid = "none"; $userstatus = "none";
}

// if classified ad is private and needs to be manually activated by an admin
if(is_single() && get_post_status() == "private" && get_post_type() == "ad" && (get_the_author_meta('ID') == $userid || current_user_can('level_10'))) {
    if(current_user_can('level_10')) { ?>
        <div class="sidebar-expire-notice reddegrade center">
            <?php _e('This ad requires manual activation','escortwp'); ?>
            <div class="clear5"></div>
            <form action="<?php echo get_permalink(get_the_ID()); ?>" method="post">
                <input type="hidden" name="action" value="activateprivatead" />
                <div class="clear5"></div>
                <input type="submit" name="submit" class="whitebutton rad25" value="<?php _e('Activate ad','escortwp'); ?>" />
                <div class="clear5"></div>
            </form>
        </div>
    <?php
    }
}

// if profile is private and needs to be manually activated by an admin
if(is_single() && get_post_status() == "private" && get_post_meta(get_the_ID(), "notactive", true) == "1" && (get_the_author_meta('ID') == $userid || current_user_can('level_10'))) {
    if(current_user_can('level_10')) { ?>
        <div class="sidebar-expire-notice reddegrade center">
            <?php _e('This profile requires manual activation','escortwp'); ?>
            <div class="clear5"></div>
            <form action="<?php echo get_permalink(get_the_ID()); ?>" method="post">
                <input type="hidden" name="action" value="activateprivateprofile" />
                <div class="clear5"></div>
                <input type="submit" name="submit" class="whitebutton rad25" value="<?php _e('Activate profile','escortwp'); ?>" />
                <div class="clear5"></div>
            </form>
        </div>
    <?php
    } else {
        echo '<div class="ok">'.__('This profile is currently set to private.','escortwp').'<br />'.__('This website requires all profiles to be manually activated by an admin.','escortwp').'</div>';
    }
}

// if profile or agency needs payment and is admin
if (is_single() && get_post_meta(get_the_ID(), "needs_payment", true) == "1" && current_user_can('level_10')) { ?>
    <div class="sidebar-expire-notice center">
        <form action="<?php echo get_permalink(get_the_ID()); ?>" method="post">
            <input type="hidden" name="action" value="activateunpaidprofile" />
            <?=__('This profile requires payment!','escortwp'); ?>
            <div class="clear5"></div>
            <?=__('Activate for a period of','escortwp')?>:
            <div class="clear10"></div>
            <select name="profileduration" class="activation-duration">
                <option value=""><?php _e('Forever','escortwp'); ?></option>
                <?php foreach($payment_duration_a as $key => $p) { echo '<option value="'.$key.'">'.__($p[0],'escortwp').'</option>'."\n"; } ?>
            </select>
            <div class="clear10"></div>
            <input type="submit" name="submit" class="activation-button whitebutton rad25" value="<?php _e('Activate profile','escortwp'); ?>" />
            <div class="clear5"></div>
        </form>
    </div>
<?php
}

// if agency's profile is private and needs to receive payment first
if($userstatus == $taxonomy_agency_url && get_post_status(get_option("agencypostid".$userid)) == "private" && get_post_meta(get_option("agencypostid".$userid), "needs_payment", true) == "1") {
    $agency_has_not_payed = "yes";
    echo '<div class="sidebar-expire-notice center">';
        printf(esc_html__('Your %s profile will not be shown in our website until you pay the registration fee.','escortwp'),$taxonomy_agency_name);
        echo '<div class="clear10"></div>';
        echo generate_payment_buttons("agreg", get_option("agencypostid".$userid));
        echo '<div class="clear5"></div>';
        echo '<small>'.format_price('agreg').'</small>';
    echo '</div>';
}

// if profile added by agency is private and agency needs payment
if(is_single() && get_post_status() == "private" && get_the_author_meta('ID') == $userid && $userstatus == $taxonomy_agency_url && get_post_type() == $taxonomy_profile_url && get_post_meta(get_the_ID(), "needs_ag_payment", true) == "1") {
    echo '<div class="sidebar-expire-notice center">';
        printf(esc_html__('This %s profile has been set to private. It will be reactivated after you pay your registration fee.','escortwp'),$taxonomy_profile_name);
        echo '<div class="clear10"></div>';
        echo generate_payment_buttons("agreg", get_option("agencypostid".$userid));
        echo '<div class="clear5"></div>';
        echo '<small>'.format_price('agreg').'</small>';
    echo '</div>';
}

// if escort from agency is private and needs payment
if(is_single() && get_post_status() == "private" && get_the_author_meta('ID') == $userid && $userstatus == $taxonomy_agency_url && get_post_type() == $taxonomy_profile_url && get_post_meta(get_the_ID(), "needs_payment", true) == "1") {
    $escort_from_agency_has_not_payed = "yes";
    echo '<div class="sidebar-expire-notice center">';
        echo sprintf(esc_html__('This %s profile will not be shown in the site until you pay the registration fee.','escortwp'),$taxonomy_profile_name);
        echo '<div class="clear10"></div>';
        echo generate_payment_buttons("agescortreg", get_the_ID());
        echo '<div class="clear5"></div>';
        echo '<small>'.format_price('agescortreg').'</small>';
    echo '</div>';
}

// if independent escort profile is private and needs to receive payment first
if($userstatus == $taxonomy_profile_url && get_post_status(get_option("escortpostid".$userid)) == "private" && get_post_meta(get_option("escortpostid".$userid), "needs_payment", true) == "1") {
    $escort_has_not_payed = "yes";
    echo '<div class="sidebar-expire-notice center">'.sprintf(esc_html__('Your %s profile will not be shown in the site until you pay the registration fee.','escortwp'),$taxonomy_profile_name);
        echo '<div class="clear10"></div>';
        echo generate_payment_buttons("indescreg", get_option("escortpostid".$userid));
        echo '<div class="clear5"></div>';
        echo '<small>'.format_price('indescreg').'</small>';
    echo '</div>';
}

// Buttons to buy premium & featured for independent
if(is_user_logged_in() && get_option("escortpostid".$userid) && get_post_type(get_option("escortpostid".$userid)) == $taxonomy_profile_url && 
    get_post_meta(get_option("escortpostid".$userid), 'independent', true) == "yes" && 
    !get_post_meta(get_option("escortpostid".$userid), "needs_payment", true)) {

    $user_profile_id = get_option("escortpostid".$userid);

    // premium
    if(payment_plans('premium','price') && get_post_meta($user_profile_id, "premium", true) == "0") { ?>
        <div class="orangebutton buypremium"><?php _e('Buy Premium Position','escortwp'); ?><span class="show-price rad3 greendegrade"><?php echo format_price('premium','small') ?></span></div>
        <div class="buypremium_details blueishdegrade">
            <?php closebtn('2') ?>
            <?php
            printf(esc_html__('Premium %1$s will always be shown first and before any normal %2$s, in all the pages of the site.','escortwp'),
                $taxonomy_profile_name_plural,$taxonomy_profile_name_plural);
            if(payment_plans('premium','duration')) {
                echo "<br />".__('Your premium status will be active for','escortwp').
                     ' <strong>'.__($payment_duration_a[payment_plans('premium','duration')][0],'escortwp').'</strong> ';
            }
            ?>
            <div class="clear10"></div>
            <?=generate_payment_buttons("premium", $user_profile_id);?>
            <div class="clear5"></div>
            <small><?=format_price("premium")?></small>
        </div>
        <div class="clear"></div>
    <?php }

    // featured
    if(payment_plans('featured','price') && get_post_meta($user_profile_id, "featured", true) != "1") { ?>
        <div class="pinkbutton buyfeatured"><?php _e('Buy Featured Position','escortwp'); ?><span class="show-price rad3 greendegrade"><?php echo format_price('featured','small') ?></span></div>
        <div class="buyfeatured_details blueishdegrade">
            <?php closebtn('2') ?>
            <?php _e('After you buy a featured position you will be placed in the header slider for maximum visibility.','escortwp'); ?> 
            <?php _e('Only the latest','escortwp'); ?> 
            <?php echo get_option("headerslideritems"); ?> 
            <?php printf(esc_html__('%s will be shown at one time.','escortwp'),$taxonomy_profile_name_plural); ?>
            <?php
            if(payment_plans('featured','duration')) {
                echo "<br />".__('Your featured status will be active for','escortwp').
                     ' <strong>'.__($payment_duration_a[payment_plans('featured','duration')][0],'escortwp').'</strong> ';
            }
            ?>
            <div class="clear10"></div>
            <?=generate_payment_buttons("featured", $user_profile_id);?>
            <div class="clear5"></div>
            <small><?=format_price("featured")?></small>
        </div>
        <div class="clear"></div>
    <?php }
}

// For agencies
if(is_single() && get_the_author_meta('ID') == $userid && get_post_type() == $taxonomy_profile_url && !get_post_meta(get_the_ID(), "needs_payment", true) && 
    get_option("agencypostid".$userid) && get_post_type(get_option("agencypostid".$userid)) == $taxonomy_agency_url && !get_post_meta(get_option("agencypostid".$userid), "needs_payment", true)) {

    // premium
    if(payment_plans('premium','price') && get_post_meta(get_the_ID(), "premium", true) == "0") { ?>
        <div class="orangebutton buypremium"><?php _e('Buy Premium Position','escortwp'); ?><span class="show-price rad3 greendegrade"><?php echo format_price('premium','small') ?></span></div>
        <div class="buypremium_details blueishdegrade">
            <?php closebtn('2') ?>
            <?php
            printf(esc_html__('Premium %1$s will always be shown first and before any normal %2$s, in all the pages of the site.','escortwp'),
                $taxonomy_profile_name_plural,$taxonomy_profile_name_plural);
            if(payment_plans('premium','duration')) {
                echo "<br />".__('Your premium status will be active for','escortwp').
                     ' <strong>'.__($payment_duration_a[payment_plans('premium','duration')][0],'escortwp').'</strong> ';
            }
            ?>
            <div class="clear10"></div>
            <?=generate_payment_buttons("premium", get_the_ID());?>
            <div class="clear5"></div>
            <small><?=format_price("premium")?></small>
        </div>
        <div class="clear"></div>
    <?php }

    // featured
    if(payment_plans('featured','price') && get_post_meta(get_the_ID(), "featured", true) != "1") { ?>
        <div class="pinkbutton buyfeatured"><?php _e('Buy Featured Position','escortwp'); ?><span class="show-price rad3 greendegrade"><?php echo format_price('featured','small') ?></span></div>
        <div class="buyfeatured_details blueishdegrade">
            <?php closebtn('2') ?>
            <?php _e('After you buy a featured position you will be placed in the header slider for maximum visibility.','escortwp'); ?> 
            <?php _e('Only the latest','escortwp'); ?> 
            <?php echo get_option("headerslideritems"); ?> 
            <?php printf(esc_html__('%s will be shown at one time.','escortwp'),$taxonomy_profile_name_plural); ?>
            <?php
            if(payment_plans('featured','duration')) {
                echo "<br />".__('Your featured status will be active for','escortwp').
                     ' <strong>'.__($payment_duration_a[payment_plans('featured','duration')][0],'escortwp').'</strong> ';
            }
            ?>
            <div class="clear10"></div>
            <?=generate_payment_buttons("featured", get_the_ID());?>
            <div class="clear5"></div>
            <small><?=format_price("featured")?></small>
        </div>
        <div class="clear"></div>
    <?php }
}

// If user is not VIP
if($userstatus == "member" && !get_user_meta($userid, "vip", true) && payment_plans('vip','price')) {
    echo '<div class="sidebar-expire-notice reddegrade center">';
        echo __('VIP status costs','escortwp').' <strong>'.format_price('vip','small')."</strong><br />";
        if(payment_plans('vip','duration')) {
            echo __('Your VIP status will be active for','escortwp').
                 ' <strong>'.__($payment_duration_a[payment_plans('vip','duration')][0],'escortwp').'</strong> ';
        }
        echo '<div class="clear10"></div>';
        $benefits = [];
        if(payment_plans('vip','extra','hide_photos')) {
            $benefits[] = "<li>".__('see the full list of photos','escortwp')."</li>";
        }
        if(payment_plans('vip','extra','hide_contact_info')) {
            $benefits[] = "<li>".sprintf(esc_html__('contact all %s profiles','escortwp'),$taxonomy_profile_name)."</li>";
        }
        if(payment_plans('vip','extra','hide_review_form')) {
            $benefits[] = "<li>".sprintf(esc_html__('add reviews to %s profiles','escortwp'),$taxonomy_profile_name)."</li>";
        }
        if($benefits) {
            echo __('VIP users can','escortwp').":<br />";
            echo "<ul>".implode("", $benefits)."</ul>";
            echo '<div class="clear20"></div>';
        }
        echo '<div class="clear20"></div>';
        echo '<div class="text-center">'.generate_payment_buttons("vip", $userid, 'Upgrade to VIP')."</div>";
        echo '<div class="clear5"></div>';
        echo '<small>'.format_price('vip').'</small>';
    echo '</div>';
}

// Edit menu for agency users
if ($userstatus == $taxonomy_agency_url && !get_user_meta($current_user->ID, "emailhash", true )) { ?>
    <div class="dropdownlinks dropdownlinks-dropdown my-account-links">
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.admineditbuttons .deleteprofile').on('click', function(){
                    $('.bodybox').hide();
                    $('.agency_options_delete').slideDown('fast', function() {$('html, body').animate({ scrollTop: $(this).offset().top }, 400);});
                });
                $('.agency_options_add_profile .closebtn, .agency_options_edit_agency .closebtn, .agency_options_add_logo .closebtn, .agency_options_delete .closebtn').on('click', function(){
                    $('.bodybox').slideDown('fast');
                    $('.agency_options_add_profile, .agency_options_edit_agency,  .agency_options_add_logo, .agency_options_delete').hide();
                });
            });
        </script>
        <h4><span class="icon icon-menu"></span><?php _e('My Account','escortwp'); ?></h4>
        <ul>
            <?php if($agency_has_not_payed=="yes"){ ?>
                <li class="ok text-center"><?php _e('Other profile links will be shown after payment','escortwp'); ?></li>
            <?php } ?>
            <li><a href="<?php echo get_permalink(get_option("agencypostid".$userid)); ?>"><span class="icon icon-star-empty"></span> <?php _e('View my Profile','escortwp'); ?></a></li>
            <li><a href="<?php echo get_permalink(get_option('agency_edit_personal_info_page_id')); ?>"><span class="icon icon-pencil"></span> <?php _e('Edit my Profile','escortwp'); ?></a></li>
            <li><a href="<?php echo get_permalink(get_option('agency_upload_logo_page_id')); ?>"><span class="icon icon-picture"></span> <?php printf(esc_html__('%s Logo','escortwp'),ucfirst($taxonomy_agency_name)); ?></a></li>
            <?php if(is_woocommerce_active){ ?>
                <li class="<?php echo wc_get_account_menu_item_classes('orders'); ?>">
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>"><span class="icon icon-dollar"></span> <?=__('My Payments','escortwp')?></a>
                </li>
            <?php }
            if($agency_has_not_payed!="yes"){ ?>
                <li><a href="<?php echo get_permalink(get_option('agency_manage_escorts_page_id')); ?>"><span class="icon icon-users"></span> <?php printf(esc_html__('Manage my %s','escortwp'),ucwords($taxonomy_profile_name_plural)); ?></a></li>
                <?php if(get_option("hide6")!="1" && get_option("allowadpostingagencies")=="1"){ ?>
                    <li><a href="<?php echo get_permalink(get_option('manage_ads_page_id')); ?>"><span class="icon icon-doc-text"></span> <?php _e('Classified Ads','escortwp'); ?></a></li>
                <?php }
                if(get_option("hide5")!="1"){ ?>
                    <li><a href="<?php echo get_permalink(get_option('escort_blacklist_clients_page_id')); ?>"><span class="icon icon-block"></span> <?php _e('Blacklisted Clients','escortwp'); ?></a></li>
                <?php }
                if(get_option("hide4")!="1"){ ?>
                    <li><a href="<?php echo get_permalink(get_option('blacklisted_escorts_page_id')); ?>"><span class="icon icon-block"></span> <?php printf(esc_html__('Blacklisted %s','escortwp'),ucfirst($taxonomy_profile_name_plural)); ?></a></li>
                <?php }
            } ?>
            <li><a href="<?php echo get_permalink(get_option('change_password_page_id')); ?>"><span class="icon icon-key-outline"></span><?php _e('Change Password','escortwp'); ?></a></li>
            <li><a href="<?php echo wp_logout_url(home_url()."/"); ?>"><span class="icon icon-logout"></span> <?php _e('Log Out','escortwp'); ?></a></li>
            <li class="text-center">
                <div class="clear20"></div>
                <a href="<?php echo get_permalink(get_option("agencypostid".$userid)); ?>#delete-account" class="delete delete-account-button redbutton center rad25"><?=__('Delete my account','escortwp')?></a>
            </li>
        </ul>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
<?php }

// Edit menu for independent profile users
if ($userstatus == $taxonomy_profile_url && !get_user_meta($current_user->ID, "emailhash", true )){ ?>
    <div class="dropdownlinks dropdownlinks-dropdown dropdownlinks-profile">
        <h4><span class="icon icon-menu"></span><?php _e('My Account','escortwp'); ?></h4>
        <ul>
            <li><a href="<?php echo get_permalink(get_option('escortpostid'.$userid)); ?>"><span class="icon icon-star-empty"></span> <?php _e('View my Profile','escortwp'); ?></a></li>
            <li><a href="<?php echo get_permalink(get_option('escort_edit_personal_info_page_id')); ?>"><span class="icon icon-pencil"></span> <?php _e('Edit my Profile','escortwp'); ?></a></li>
            <?php if(is_woocommerce_active){ ?>
                <li class="<?php echo wc_get_account_menu_item_classes('orders'); ?>">
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>"><span class="icon icon-dollar"></span> <?=__('My Payments','escortwp')?></a>
                </li>
            <?php }
            if($escort_has_not_payed=="yes"){ ?>
                <li><?php _e('Other edit links will be shown after payment','escortwp'); ?></li>
            <?php } else {
                if(get_option("hide8")!="1"){ ?>
                    <li><a href="<?php echo get_permalink(get_option('escort_tours_page_id')); ?>"><span class="icon icon-airplane"></span> <?php _e('Tours','escortwp'); ?></a></li>
                <?php }
                if(get_option("hide6")!="1" && get_option("allowadpostingprofiles")=="1"){ ?>
                    <li><a href="<?php echo get_permalink(get_option('manage_ads_page_id')); ?>"><span class="icon icon-doc-text"></span> <?php _e('Classified Ads','escortwp'); ?></a></li>
                <?php } ?>
                <li><a href="<?php echo get_permalink(get_option('change_password_page_id')); ?>"><span class="icon icon-key-outline"></span> <?php _e('Change Password','escortwp'); ?></a></li>
                <?php if(get_option("hide7")!="1"){ ?>
                    <li><a href="<?php echo get_permalink(get_option('escort_verified_status_page_id')); ?>"><span class="icon icon-check"></span> <?php _e('Verified status','escortwp'); ?></a></li>
                <?php }
                if(get_option("hide5")!="1"){ ?>
                    <li><a href="<?php echo get_permalink(get_option('escort_blacklist_clients_page_id')); ?>"><span class="icon icon-block"></span> <?php _e('Blacklisted Clients','escortwp'); ?></a></li>
                <?php }
            } ?>
            <li><a href="<?php echo wp_logout_url(home_url()."/"); ?>"><span class="icon icon-logout"></span> <?php _e('Log Out','escortwp'); ?></a></li>
            <?php if(!get_post_meta(get_option('escortpostid'.$userid), 'notactive', true) && !get_post_meta(get_option('escortpostid'.$userid), 'needs_payment', true)){ ?>
                <li>&nbsp;</li>
                <li>
                    <?php
                    $button_text = (get_post_status(get_option('escortpostid'.$userid))=="publish") ? __('Set to private','escortwp') : __('Set as visible','escortwp');
                    $button_class = (get_post_status(get_option('escortpostid'.$userid))=="publish") ? "pinkbutton redbutton" : "greenbutton";
                    ?>
                    <form action="<?php echo get_permalink(get_option('escortpostid'.$userid)); ?>" method="post" class="text-center">
                        <input type="hidden" name="action" value="settoprivate" />
                        <input type="submit" name="submit" value="<?=$button_text?>" class="<?=$button_class?> center rad25" />
                    </form>
                </li>
                <li class="text-center">
                    <a href="<?php echo get_permalink(get_option('escortpostid'.$userid)); ?>#delete-account" class="delete delete-account-button redbutton center rad25"><?=__('Delete my account','escortwp')?></a>
                </li>
            <?php } ?>
        </ul>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
<?php }

// Edit menu for members
if ($userstatus == "member" && !get_user_meta($current_user->ID, "emailhash", true )){ ?>
    <div class="dropdownlinks dropdownlinks-dropdown">
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('.delete-account-button').on('click',function(){
                    $(this).hide();
                    $('.sidebar-right .dropdownlinks .member-delete-account-wrapper').slideDown('fast',function(){
                        $('html, body').animate({ scrollTop: $(this).offset().top },400);
                    });
                });
            });
        </script>
        <h4><span class="icon icon-user"></span><?php _e('My Account','escortwp'); ?></h4>
        <ul>
            <li><a href="<?php echo get_permalink(get_option('member_favorite_escorts_page_id')); ?>"><span class="icon icon-heart"></span> <?php printf(esc_html__('My Favorite %s','escortwp'),ucwords($taxonomy_profile_name_plural)); ?></a></li>
            <li><a href="<?php echo get_permalink(get_option('member_edit_personal_info_page_id')); ?>"><span class="icon icon-pencil"></span><?php _e('Edit my Profile','escortwp'); ?></a></li>
            <?php if(is_woocommerce_active){ ?>
                <li class="<?php echo wc_get_account_menu_item_classes('orders'); ?>">
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>"><span class="icon icon-dollar"></span> <?=__('My Payments','escortwp')?></a>
                </li>
            <?php }
            if(get_option("hide6")!="1" && get_option("allowadpostingmembers")=="1"){ ?>
                <li><a href="<?php echo get_permalink(get_option('manage_ads_page_id')); ?>"><span class="icon icon-doc-text"></span> <?php _e('Classified Ads','escortwp'); ?></a></li>
            <?php }
            if(get_option("hide1")!="1"){ ?>
                <li><a href="<?php echo get_permalink(get_option('member_reviews_page_id')); ?>"><span class="icon icon-doc-text"></span><?php _e('My Reviews','escortwp'); ?></a></li>
            <?php } ?>
            <li><a href="<?php echo get_permalink(get_option('change_password_page_id')); ?>"><span class="icon icon-key-outline"></span><?php _e('Change Password','escortwp'); ?></a></li>
            <li><a href="<?php echo wp_logout_url(home_url()."/"); ?>"><span class="icon icon-logout"></span><?php _e('Log Out','escortwp'); ?></a></li>
            <li class="text-center">
                <div class="clear10"></div>
                <div class="member-delete-account-wrapper">
                    <?=__('Are you sure you want to delete your account?','escortwp')?><br/>
                    <?=__('You won\'t be able to recover it after deletion.','escortwp')?><br/>
                    <form action="" method="post" class="text-center">
                        <input type="hidden" name="action" value="member_delete_account" />
                        <button type="submit" class="delete-account-button redbutton center rad25"><?=__('Yes, delete my account','escortwp')?></button>
                    </form>
                </div>
                <div class="delete-account-button redbutton center rad25"><?=__('Delete my account','escortwp')?></div>
            </li>
        </ul>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
<?php }

// edit escort dropdown menu for agencies and admins
if (is_single() && get_post_type() == $taxonomy_profile_url && ((get_the_author_meta('ID')==$userid && $userstatus==$taxonomy_agency_url) || current_user_can('level_10'))) {
    ?>
    <div class="agencyeditbuttons dropdownlinks dropdownlinks-dropdown">
        <h4><span class="icon icon-user"></span><?php printf(esc_html__('Edit %s','escortwp'),$taxonomy_profile_name); ?></h4>
        <ul>
            <?php if(isset($escort_from_agency_has_not_payed) && $escort_from_agency_has_not_payed=="yes" && !current_user_can('level_10')) { ?>
                <li><?php _e('Other edit links will be shown after payment','escortwp'); ?></li>
            <?php } else { ?>
                <li><a class="editprofile"><span class="icon icon-pencil"></span> <?php _e('Edit Profile','escortwp'); ?></a></li>
                <?php if(get_option("hide8")!="1"){ ?><li><a class="addtours"><span class="icon icon-airplane"></span> <?php _e('Add Tours','escortwp'); ?></a></li><?php }
                if(get_option("hide7")!="1"){ ?><li><a class="verified_status"><span class="icon icon-check"></span> <?php _e('Verified status','escortwp'); ?></a></li><?php }
            } ?>

            <?php if(current_user_can('level_10')){ ?>
                <li><a class="addanote"><span class="icon icon-doc-text"></span> <?php _e('Add a note','escortwp'); ?></a></li>
                <li>
                    <form action="<?php echo get_permalink(get_the_ID()); ?>" method="post">
                        <input type="hidden" name="action" value="escortupgrade" />
                        <?php
                        $premium_status = get_post_meta(get_the_ID(),"premium",true);
                        $featured_status= get_post_meta(get_the_ID(),"featured",true);
                        $expiration_status= get_post_meta(get_the_ID(),"escort_expire",true);
                        ?>
                        <div class="upgradeescortparent">
                            <div class="upgradebuttons text-center">
                                <div class="upgradebutton pinkbutton rad25 center"><?php _e('Premium','escortwp');?></div>
                                <?php if($premium_status=="1"){ ?><input type="submit" name="delpremium" value="X" class="rad25 redbutton center"/><?php } ?>
                            </div>
                            <div class="upgradeescortbox rad3">
                                <?php closebtn('2');?>
                                <?php if($premium_status=="1"){ echo __('Extend expiration with','escortwp').":<br />"; }
                                      else { echo __('Add premium status for','escortwp').":<br />"; } ?>
                                <div class="clear5"></div>
                                <div class="text-center">
                                    <select name="premiumduration">
                                        <option value=""><?php _e('Forever','escortwp');?></option>
                                        <?php foreach($payment_duration_a as $key=>$p){ echo '<option value="'.$key.'">'.__($p[0],'escortwp').'</option>'; } ?>
                                    </select>
                                </div>
                                <div class="clear10"></div>
                                <div class="text-center">
                                    <input type="submit" name="premium" value="<?php echo $premium_status=="1"?__('Extend','escortwp'):__('Add','escortwp'); ?> <?php _e('Premium','escortwp');?>" class="whitebutton ok-button rad25"/>
                                    <?php if($premium_status=="1"){ ?>
                                        <div class="clear10"></div>
                                        <input type="submit" name="delpremium" value="<?=__('Delete premium','escortwp')?>" class="rad25 del-button redbutton"/>
                                    <?php } ?>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="clear10"></div>

                        <div class="upgradeescortparent">
                            <div class="upgradebuttons text-center">
                                <div class="upgradebutton pinkbutton rad25 center"><?php _e('Featured','escortwp');?></div>
                                <?php if($featured_status=="1"){ ?><input type="submit" name="delfeatured" value="X" class="rad25 redbutton center"/><?php } ?>
                            </div>
                            <div class="upgradeescortbox rad3">
                                <?php closebtn('2');?>
                                <?php if($featured_status=="1"){ echo __('Extend expiration with','escortwp').":<br />"; }
                                      else{ echo __('Add featured status for','escortwp').":<br />"; } ?>
                                <div class="clear5"></div>
                                <div class="text-center">
                                    <select name="featuredduration">
                                        <option value=""><?php _e('Forever','escortwp');?></option>
                                        <?php foreach($payment_duration_a as $key=>$p){ echo '<option value="'.$key.'">'.__($p[0],'escortwp').'</option>'; } ?>
                                    </select>
                                </div>
                                <div class="clear10"></div>
                                <div class="text-center">
                                    <input type="submit" name="featured" value="<?php echo $featured_status=="1"?__('Extend','escortwp'):__('Add','escortwp'); ?> <?php _e('Featured','escortwp');?>" class="whitebutton ok-button rad25"/>
                                    <?php if($featured_status=="1"){ ?>
                                        <div class="clear10"></div>
                                        <input type="submit" name="delfeatured" value="<?=__('Delete Featured','escortwp')?>" class="rad25 del-button redbutton"/>
                                    <?php } ?>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="clear10"></div>

                        <div class="upgradeescortparent">
                            <div class="upgradebuttons text-center">
                                <div class="upgradebutton pinkbutton rad25 center"><?php _e('Profile expiration','escortwp');?></div>
                                <?php if($expiration_status){ ?><input type="submit" name="delexpiration" value="X" class="rad25 redbutton center"/><?php } ?>
                            </div>
                            <div class="upgradeescortbox rad3">
                                <?php closebtn('2');?>
                                <?php if($expiration_status){ echo __('Extend account expiration period with','escortwp').":<br />"; }
                                      else{ echo __('Profile will expire after','escortwp').":<br />"; } ?>
                                <div class="clear5"></div>
                                <div class="text-center">
                                    <select name="profileduration">
                                        <option value=""><?php _e('Forever','escortwp');?></option>
                                        <?php foreach($payment_duration_a as $key=>$p){ echo '<option value="'.$key.'">'.__($p[0],'escortwp').'</option>'; } ?>
                                    </select>
                                </div>
                                <div class="clear10"></div>
                                <div class="text-center">
                                    <input type="submit" name="expirationperiod" value="<?php echo $expiration_status?__('Extend','escortwp'):__('Add','escortwp'); ?> <?php _e('Expiration','escortwp');?>" class="whitebutton ok-button center rad25"/>
                                    <div class="clear10"></div>
                                    <input type="submit" name="delexpiration" value="<?=__('Set as expired','escortwp')?>" class="rad25 del-button redbutton"/>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="clear10"></div>

                        <div class="text-center">
                            <input type="submit" name="verified" value="<?php echo get_post_meta(get_the_ID(),"verified",true)=="1"?__('Remove','escortwp'):__('Mark as','escortwp'); ?> <?php _e('Verified','escortwp');?>" class="pinkbutton mark-as-verified rad25 center <?php if(get_post_meta(get_the_ID(),"verified",true)=="1") echo "redbutton";?>"/>
                        </div>
                        <div class="clear"></div>
                    </form>
                </li>
            <?php } ?>
            <?php if(!get_post_meta(get_option('escortpostid'.$userid),'notactive',true)){ ?>
                <li>
                    <?php
                    $button_text = (get_post_status(get_the_ID())=="publish")?__('Set to private','escortwp'):__('Activate profile','escortwp');
                    ?>
                    <form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="text-center">
                        <input type="hidden" name="action" value="settoprivate" />
                        <input type="submit" name="submit" value="<?=$button_text?>" class="admin-set-to-private pinkbutton rad25 center<?php if(get_post_status(get_the_ID())=="publish") echo " redbutton";?>"/>
                    </form>
                    <div class="clear"></div>
                </li>
            <?php } ?>
            <li class="text-center"><a class="admin-delete-profile delete redbutton rad25 center"><?php _e('Delete','escortwp'); ?></a></li>
        </ul>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
<?php } // end agency/admin edit ?>

<?php
// admin menu dropdown
if (current_user_can('level_10')) {
    // edit agency dropdown menu for admins
    if (is_single() && get_post_type() == $taxonomy_agency_url) { ?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('.admineditbuttons .addprofile').on('click',function(){
                    $('.bodybox').hide();
                    $('.agency_options_add_profile').slideDown('fast',function(){
                        $('html, body').animate({ scrollTop: $(this).offset().top },400);
                    });
                });
                $('.admineditbuttons .editprofile').on('click',function(){
                    $('.bodybox').hide();
                    $('.agency_options_edit_agency').slideDown('fast',function(){
                        $('html, body').animate({ scrollTop: $(this).offset().top },400);
                    });
                });
                $('.admineditbuttons .addlogo').on('click',function(){
                    $('.bodybox').hide();
                    $('.agency_options_add_logo').slideDown('fast',function(){
                        $('html, body').animate({ scrollTop: $(this).offset().top },400);
                    });
                });
                $('.admineditbuttons .deleteprofile').on('click',function(){
                    $('.bodybox').hide();
                    $('.agency_options_delete').slideDown('fast',function(){
                        $('html, body').animate({ scrollTop: $(this).offset().top },400);
                    });
                });
                $('.agency_options_add_profile .closebtn, .agency_options_edit_agency .closebtn, .agency_options_add_logo .closebtn, .agency_options_delete .closebtn').on('click',function(){
                    $('.bodybox').slideDown('fast',function(){
                        $('html, body').animate({ scrollTop: $(this).offset().top },400);
                    });
                    $('.agency_options_add_profile, .agency_options_edit_agency, .agency_options_add_logo, .agency_options_delete').hide();
                });
            });
        </script>
        <div class="admineditbuttons dropdownlinks dropdownlinks-dropdown">
            <h4><span class="icon icon-user"></span><?php printf(esc_html__('Edit %s','escortwp'),$taxonomy_agency_name); ?></h4>
            <ul>
                <li><a class="editprofile"><span class="icon icon-pencil"></span> <?php _e('Edit Profile','escortwp'); ?></a></li>
                <li><a class="addlogo"><span class="icon icon-picture"></span> <?php _e('Add logo','escortwp'); ?></a></li>
                <li><a class="addprofile"><span class="icon icon-user"></span> <?php printf(esc_html__('Add %s','escortwp'),$taxonomy_profile_name); ?></a></li>
                <li>
                    <form action="<?php echo get_permalink(get_the_ID()); ?>" method="post">
                        <input type="hidden" name="action" value="agencyupgrade" />
                        <?php $expiration_status = get_post_meta(get_the_ID(),"agency_expire",true); ?>
                        <div class="upgradeescortparent">
                            <div class="upgradebuttons text-center">
                                <div class="upgradebutton pinkbutton rad25<?php echo $expiration_status?" l":" center";?>"><?php _e('Profile expiration','escortwp');?></div>
                                <?php if($expiration_status){ ?><input type="submit" name="delexpiration" value="X" class="pinkbutton rad25 redbutton r delbtn"/><?php } ?>
                            </div>
                            <div class="upgradeescortbox rad3">
                                <?php closebtn('2');?>
                                <?php if($expiration_status){ echo __('Extend expiration period with','escortwp').":<br />"; } else { echo __('Profile will expire after','escortwp').":<br />"; }?>
                                <div class="clear5"></div>
                                <div class="text-center">
                                    <select name="profileduration">
                                        <option value=""><?php _e('Forever','escortwp');?></option>
                                        <?php foreach($payment_duration_a as $key=>$p){ echo '<option value="'.$key.'">'.__($p[0],'escortwp').'</option>'; } ?>
                                    </select>
                                </div>
                                <div class="clear10"></div>
                                <div class="center upgradebuttons">
                                    <input type="submit" name="expirationperiod" value="<?php echo $expiration_status?__('Extend','escortwp'):__('Add','escortwp');?> <?php _e('Expiration','escortwp');?>" class="whitebutton rad25 upgradebutton"/>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </form>
                </li>
                <li>
                    <form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="text-center">
                        <input type="hidden" name="action" value="needs_payment" />
                        <input type="submit" name="submit" value="<?=__('Needs payment','escortwp')?>" class="pinkbutton rad25 redbutton center needs-payment"/>
                        <div class="clear"></div>
                    </form>
                </li>
                <li class="text-center"><a class="deleteprofile center"><span class="redbutton rad25 l"><?php _e('Delete','escortwp'); ?></span></a></li>
            </ul>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    <?php }
    // handle manuallyactivatetour and manuallyactivatevip here (same as parent)...
    if (isset($ok) && $ok && ($_POST['action']=='manuallyactivatetour' || $_POST['action']=='manuallyactivatevip')){
        echo "<div class=\"ok\">$ok</div>";
    }
    ?>
    <div class="dropdownlinks dropdownlinks-dropdown">
        <h4><span class="icon icon-menu"></span><?php _e('Admin Links','escortwp'); ?></h4>
        <ul>
            <?php if(is_woocommerce_active){ /* echo Woo links */ } ?>
            <li><a href="<?php echo get_permalink(get_option('escort_reg_page_id')); ?>"><span class="icon icon-user"></span> <?php printf(esc_html__('Add %s','escortwp'),ucwords($taxonomy_profile_name)); ?></a></li>
            <li><a href="<?php echo get_permalink(get_option('agency_reg_page_id')); ?>"><span class="icon icon-users"></span> <?php printf(esc_html__('Add %s','escortwp'),ucwords($taxonomy_agency_name)); ?></a></li>
            <li>
                <a class="manuallyactivatevip"><span class="icon icon-user"></span> <?php _e('VIP User','escortwp'); ?></a>
                <div class="upgradeescortbox manuallyactivatevip_div ok rad5">
                    <?php closebtn('2');?>
                    <form action="" method="post">
                        <input type="hidden" name="action" value="manuallyactivatevip"/>
                        <?php _e('Enter the user ID you want to make VIP','escortwp');?>:
                        <div class="clear10"></div>
                        <input type="text" class="input text-center" name="userid" value="" size="5"/>
                        <div class="clear10"></div>
                        <div class="text-center">
                            <?php _e('VIP lasts for','escortwp');?>:
                            <select name="vipduration">
                                <option value=""><?php _e('Forever','escortwp');?></option>
                                <?php foreach($payment_duration_a as $key=>$p){ echo '<option value="'.$key.'">'.__($p[0],'escortwp').'</option>'; } ?>
                            </select>
                        </div>
                        <div class="clear10"></div>
                        <input type="submit" name="addvip" class="whitebutton ok-button rad25" value="<?php _e('Add VIP user','escortwp');?>"/>
                        <div class="clear10"></div>
                        <input type="submit" name="removevip" class="redbutton del-button rad25" value="<?php _e('Remove VIP','escortwp');?>"/>
                    </form>
                </div>
            </li>
            <li>
                <a class="manuallyactivatetour"><span class="icon icon-airplane"></span> <?php _e('Activate Tour','escortwp');?></a>
                <div class="upgradeescortbox manuallyactivatetour_div ok rad5">
                    <?php closebtn('2');?>
                    <form action="" method="post">
                        <input type="hidden" name="action" value="manuallyactivatetour"/>
                        <?php _e('Enter the tour ID','escortwp');?>:
                        <div class="clear10"></div>
                        <input type="text" class="input" name="tourid" value="" size="5"/>
                        <div class="clear10"></div>
                        <input type="submit" name="submit" class="whitebutton ok-button rad25" value="<?php _e('Activate tour','escortwp');?>"/>
                    </form>
                </div>
            </li>
            <?php if(get_option("hide6")!="1"){ ?>
                <li><a href="<?php echo get_permalink(get_option('manage_ads_page_id')); ?>"><span class="icon icon-doc-text"></span> <?php _e('Classified Ads','escortwp');?></a></li>
            <?php }
            if(get_option("hide5")!="1"){ ?>
                <li><a href="<?php echo get_permalink(get_option('escort_blacklist_clients_page_id')); ?>"><span class="icon icon-block"></span> <?php _e('Blacklisted Clients','escortwp');?></a></li>
            <?php }
            if(get_option("hide4")!="1"){ ?>
                <li><a href="<?php echo get_permalink(get_option('blacklisted_escorts_page_id')); ?>"><span class="icon icon-block"></span> <?php printf(esc_html__('Blacklisted %s','escortwp'),ucfirst($taxonomy_profile_name_plural)); ?></a></li>
            <?php } ?>
            <li><a href="<?php echo get_permalink(get_option('site_settings_page_id')); ?>"><span class="icon icon-cog-alt"></span> <?php _e('Site Settings','escortwp');?></a></li>
            <li><a href="<?php echo get_permalink(get_option('content_settings_page_id')); ?>"><span class="icon icon-cog-alt"></span> <?php _e('Content Settings','escortwp');?></a></li>
            <li><a href="<?php echo get_permalink(get_option('edit_registration_form_escort')); ?>"><span class="icon icon-cog-alt"></span> <?php _e('Registration Form','escortwp');?></a></li>
            <li><a href="<?php echo get_permalink(get_option('edit_payment_settings_page_id')); ?>"><span class="icon icon-dollar"></span> <?php _e('Payment Settings','escortwp');?></a></li>
            <li><a href="<?php echo get_permalink(get_option('email_settings_page_id')); ?>"><span class="icon icon-mail"></span> <?php _e('Email Settings','escortwp');?></a></li>
            <li><a href="<?php echo get_permalink(get_option('edit_user_types')); ?>"><span class="icon icon-user"></span> <?php _e('Edit User Types','escortwp');?></a></li>
            <li><a href="<?php echo admin_url('edit-tags.php?taxonomy='.$taxonomy_location_url); ?>"><span class="icon icon-plus-circled"></span> <?php _e('Add Countries','escortwp');?></a></li>
            <li><a href="<?php echo get_permalink(get_option('generate_demo_data_page')); ?>"><span class="icon icon-plus-circled"></span> <?php _e('Generate Demo Data','escortwp');?></a></li>
            <li><a href="<?=get_template_directory_uri()?>/_Documentation/Read%20me.html"><span class="icon icon-question-circle"></span> <?php _e('Documentation / Help','escortwp');?></a></li>
            <li><a href="<?php echo admin_url(); ?>"><span class="icon icon-w"></span> <?php _e('WordPress Dashboard','escortwp');?></a></li>
            <li>&nbsp;</li>
            <li><a href="<?php echo wp_logout_url(home_url()."/"); ?>"><span class="icon icon-logout"></span> <?php _e('Log Out','escortwp');?></a></li>
        </ul>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
<?php } // end super admin ?>

<div class="clear"></div>
<?php
if (
    (get_post_type() == $taxonomy_profile_url ||
     get_post_type() == $taxonomy_agency_url ||
     get_post_type() == "ad" ||
     get_post_type() == "review") 
    && is_single() &&
    current_user_can('level_10')
) { ?>
    <div class="dropdownlinks dropdownlinks-userid">
        <h4><span class="icon icon-cog-alt"></span><?php _e('User details','escortwp'); ?>:</h4>
        <div class="clear5"></div>
        <small><?php _e('User ID','escortwp'); ?></small>: <b><?php echo get_the_author_meta('ID'); ?></b><br />
        <small><?php _e('Username','escortwp'); ?></small>: <b><?php echo get_the_author_meta('user_login'); ?></b><br />
        <small><?php _e('Email','escortwp'); ?></small>: <b><?php echo get_the_author_meta('user_email'); ?></b><br />
        <?php if(get_option('escortid'.get_the_author_meta('ID'))) { ?>
            <small><?php _e('Type','escortwp'); ?></small>: <b><?php echo get_option('escortid'.get_the_author_meta('ID')); ?></b>
        <?php } ?>
        <div class="clear5"></div>
        <div class="text-center">
            <a class="edit-user" href="<?=
                get_admin_url('', 'user-edit.php?user_id='.get_the_author_meta('ID'))
            ?>">
                <?php _e('Edit user','escortwp'); ?>
            </a>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
<?php }
?>


<?php
// show agency expiration date to admins
if(is_single() && get_post_meta(get_the_ID(),"agency_expire",true) && current_user_can('level_10') && get_post_type()==$taxonomy_agency_url){
    $agency_expire_date = date("d M Y", get_post_meta(get_the_ID(),"agency_expire",true));
    if($agency_expire_date){
        echo '<div class="sidebar-expire-notice pinkdegrade center">';
        echo '<small>'.sprintf(esc_html__('This %s profile is active until','escortwp'),$taxonomy_agency_name).':</small><b>'.$agency_expire_date.'</b>';
        echo '</div>';
    }
}

// show agency expiration date to agencies
if(get_post_type()==$taxonomy_profile_url && get_the_author_meta('ID')==$userid){
    // nothing
} elseif(is_user_logged_in() && $userstatus==$taxonomy_agency_url && get_post_type(get_option('agencypostid'.$userid))==$taxonomy_agency_url && !get_post_meta(get_option('agencypostid'.$userid),"needs_payment",true)){
    $agency_profile_id = get_option('agencypostid'.$userid);
    if(get_post_meta($agency_profile_id,"agency_expire",true)){
        $agency_expire_date = date("d M Y", get_post_meta($agency_profile_id,"agency_expire",true));
        echo '<div class="sidebar-expire-notice-mobile pinkdegrade text-center" data-payment-plan="agreg">';
            echo '<div class="expiration-date">'.__('Profile expiration:','escortwp').' <b>'.$agency_expire_date.'</b></div>';
            if(get_post_meta($agency_profile_id,"agency_expire",true) && payment_plans('agreg','price')){
                echo '<div class="sidebar-expire-mobile-extent-button greenbutton rad25">'.__('Extend','escortwp').'</div>';
            }
        echo '</div>';
        echo '<div class="sidebar-expire-notice sidebar-expire-notice-has-mobile pinkdegrade center" data-payment-plan="agreg">';
            echo '<small>'.sprintf(esc_html__('Your %s profile is active until','escortwp'),$taxonomy_agency_name).':</small><b>'.$agency_expire_date.'</b>';
            if(get_post_meta($agency_profile_id,"agency_renew",true)){
                // cancel subscription button
            } elseif(get_post_meta($agency_profile_id,"agency_expire",true) && payment_plans('agreg','price') && !current_user_can('level_10')){
                echo '<div class="clear20"></div>';
                echo '<div class="text-center">'.generate_payment_buttons('agreg',$agency_profile_id,__('Extend registration','escortwp')).'</div>';
                echo '<div class="clear5"></div>';
                echo '<small>'.format_price('agreg').'</small>';
            }
        echo '</div>';
    }
}

// show profile expiration dates to agencies and admins
if(is_single() && get_post_type()==$taxonomy_profile_url && ((get_the_author_meta('ID')==$userid && $userstatus==$taxonomy_agency_url) || current_user_can('level_10')) && !get_post_meta(get_the_ID(),"needs_payment",true)){
    // registration expiration
    if(get_post_meta(get_the_ID(),"escort_expire",true)){
        $escort_expire_date = date("d M Y", get_post_meta(get_the_ID(),"escort_expire",true));
        echo '<div class="sidebar-expire-notice-mobile pinkdegrade text-center" data-payment-plan="agescortreg">';
            echo '<div class="expiration-date">'.__('Profile expiration:','escortwp').' <b>'.$escort_expire_date.'</b></div>';
            if(get_post_meta(get_the_ID(),"escort_expire",true) && payment_plans('agescortreg','price') && !current_user_can('level_10')){
                echo '<div class="sidebar-expire-mobile-extent-button greenbutton rad25">'.__('Extend','escortwp').'</div>';
            }
        echo '</div>';
        echo '<div class="sidebar-expire-notice sidebar-expire-notice-has-mobile pinkdegrade center" data-payment-plan="agescortreg">';
            if(current_user_can('level_10')){
                $exp_text = sprintf(esc_html__('This %s profile is active until','escortwp'),$taxonomy_profile_name);
            } else {
                $exp_text = sprintf(esc_html__('The %s profile you added is active until','escortwp'),$taxonomy_profile_name);
            }
            echo '<small>'.$exp_text.':</small><b>'.$escort_expire_date.'</b>';
            if(!current_user_can('level_10')){
                if(get_post_meta(get_the_ID(),"escort_renew",true)){
                    // cancel subscription button
                } elseif(get_post_meta(get_the_ID(),"escort_expire",true) && payment_plans('agescortreg','price')){
                    echo '<div class="clear20"></div>';
                    echo '<div class="text-center">'.generate_payment_buttons('agescortreg',get_the_ID(),__('Extend registration','escortwp')).'</div>';
                    echo '<div class="clear5"></div>';
                    echo '<div class="text-center"><small>'.format_price('agescortreg').'</small></div>';
                }
            }
        echo '</div>';
    }
    // premium expiration
    if(get_post_meta(get_the_ID(),"premium",true)=="1"){
        if(get_post_meta(get_the_ID(),"premium_expire",true)){
            $premium_expire_date = date("d M Y", get_post_meta(get_the_ID(),"premium_expire",true));
            $premium_mobile_expire_text = __('Premium expiration:','escortwp').' <b>'.$premium_expire_date.'</b>';
        } else {
            $premium_expire_date = strtolower(__('forever','escortwp'));
            $premium_mobile_expire_text = __('Premium status is active <b>forever</b>','escortwp');
        }

        echo '<div class="sidebar-expire-notice-mobile orangedegrade text-center" data-payment-plan="premium">';
            echo '<div class="expiration-date">'.$premium_mobile_expire_text.'</div>';
            if(get_post_meta(get_the_ID(),"premium_expire",true) && payment_plans('premium','price') && !current_user_can('level_10')){
                echo '<div class="sidebar-expire-mobile-extent-button greenbutton rad25">'.__('Extend','escortwp').'</div>';
            }
        echo '</div>';
        echo '<div class="sidebar-expire-notice sidebar-expire-notice-has-mobile orangedegrade center" data-payment-plan="premium">';
            echo '<small>'.__('The premium status for this profile is active until','escortwp').':</small><b>'.$premium_expire_date.'</b>';
            if(!current_user_can('level_10')){
                if(get_post_meta(get_the_ID(),"premium_renew",true)){
                    // cancel subscription button
                } elseif(get_post_meta(get_the_ID(),"premium_expire",true) && payment_plans('premium','price')){
                    echo '<div class="clear20"></div>';
                    echo '<div class="text-center">'.generate_payment_buttons('premium',get_the_ID(),__('Extend premium','escortwp')).'</div>';
                    echo '<div class="clear5"></div>';
                    echo '<small>'.format_price('premium').'</small>';
                }
            }
        echo '</div>';
    }
    // featured expiration
    if(get_post_meta(get_the_ID(),"featured",true)=="1"){
        if(get_post_meta(get_the_ID(),"featured_expire",true)){
            $featured_expire_date = date("d M Y", get_post_meta(get_the_ID(),"featured_expire",true));
            $featured_mobile_expire_text = __('Featured expiration:','escortwp').' <b>'.$featured_expire_date.'</b>';
        } else {
            $featured_expire_date = strtolower(__('forever','escortwp'));
            $featured_mobile_expire_text = __('Featured status is active <b>forever</b>','escortwp');
        }

        echo '<div class="sidebar-expire-notice-mobile pinkdegrade text-center" data-payment-plan="featured">';
            echo '<div class="expiration-date">'.$featured_mobile_expire_text.'</div>';
            if(get_post_meta(get_the_ID(),"featured_expire",true) && payment_plans('featured','price') && !current_user_can('level_10')){
                echo '<div class="sidebar-expire-mobile-extent-button greenbutton rad25">'.__('Extend','escortwp').'</div>';
            }
        echo '</div>';
        echo '<div class="sidebar-expire-notice sidebar-expire-notice-has-mobile pinkdegrade center" data-payment-plan="featured">';
            echo '<small>'.__('The featured status for this profile is active until','escortwp').':</small><b>'.$featured_expire_date.'</b>';
            if(!current_user_can('level_10')){
                if(get_post_meta(get_the_ID(),"featured_renew",true)){
                    // cancel subscription button
                } elseif(get_post_meta(get_the_ID(),"featured_expire",true) && payment_plans('featured','price')){
                    echo '<div class="clear20"></div>';
                    echo '<div class="text-center">'.generate_payment_buttons('featured',get_the_ID(),__('Extend featured','escortwp')).'</div>';
                    echo '<div class="clear5"></div>';
                    echo '<small>'.format_price('featured').'</small>';
                }
            }
        echo '</div>';
    }
}

// show expiration dates to independent profiles, in all pages
if(is_user_logged_in() && $userstatus==$taxonomy_profile_url && get_post_type(get_option('escortpostid'.$userid))==$taxonomy_profile_url && !get_post_meta(get_option('escortpostid'.$userid),"needs_payment",true)){
    $independent_profile_id = get_option('escortpostid'.$userid);

    // registration expiration
    if(get_post_meta($independent_profile_id,"escort_expire",true)){
        $escort_expire_date = date("d M Y", get_post_meta($independent_profile_id,"escort_expire",true));
        echo '<div class="sidebar-expire-notice-mobile pinkdegrade text-center" data-payment-plan="reg">';
            echo '<div class="expiration-date">'.__('Profile expiration:','escortwp').' <b>'.$escort_expire_date.'</b></div>';
            if(get_post_meta($independent_profile_id,"escort_expire",true) && payment_plans('indescreg','price')){
                echo '<div class="sidebar-expire-mobile-extent-button greenbutton rad25">'.__('Extend','escortwp').'</div>';
            }
        echo '</div>';
        echo '<div class="sidebar-expire-notice sidebar-expire-notice-has-mobile pinkdegrade center" data-payment-plan="reg">';
            echo '<small>'.sprintf(esc_html__('Your %s profile is active until','escortwp'),$taxonomy_profile_name).':</small>';
            echo '<b>'.$escort_expire_date.'</b>';
            echo '<div class="clear"></div>';
            echo '<small>'.human_time_diff(time(), get_post_meta($independent_profile_id,"escort_expire",true)).' '.__('remaining','escortwp').'</small>';
            if(get_post_meta($independent_profile_id,"escort_renew",true)){
                // cancel subscription button
            } elseif(get_post_meta($independent_profile_id,"escort_expire",true) && payment_plans('indescreg','price')){
                echo '<div class="clear20"></div>';
                echo '<div class="text-center">'.generate_payment_buttons('indescreg',$independent_profile_id,__('Extend registration','escortwp')).'</div>';
                echo '<div class="clear5"></div>';
                echo '<div class="text-center"><small>'.format_price('indescreg').'</small></div>';
            }
        echo '</div>';
    }

    // premium expiration
    if(get_post_meta($independent_profile_id,"premium",true)=="1"){
        $premium_expire = get_post_meta($independent_profile_id,"premium_expire",true);
        if($premium_expire){
            $premium_expire_date = date("d M Y", $premium_expire);
            $premium_mobile_expire_text = __('Premium expiration:','escortwp').' <b>'.$premium_expire_date.'</b>';
        } else {
            $premium_expire_date = strtolower(__('forever','escortwp'));
            $premium_mobile_expire_text = __('Premium status is active <b>forever</b>','escortwp');
        }

        echo '<div class="sidebar-expire-notice-mobile orangedegrade text-center" data-payment-plan="premium">';
            echo '<div class="expiration-date">'.$premium_mobile_expire_text.'</div>';
            if($premium_expire && payment_plans('premium','price')){
                echo '<div class="sidebar-expire-mobile-extent-button greenbutton rad25">'.__('Extend','escortwp').'</div>';
            }
        echo '</div>';
        echo '<div class="sidebar-expire-notice sidebar-expire-notice-has-mobile orangedegrade center" data-payment-plan="premium">';
            echo '<small>'.__('Your premium status is active until','escortwp').':</small><b>'.$premium_expire_date.'</b>';
            if($premium_expire){
                echo '<small>'.human_time_diff(time(), $premium_expire).' '.__('remaining','escortwp').'</small>';
            }
            if(get_post_meta($independent_profile_id,"premium_renew",true)){
                // cancel subscription button
            } elseif($premium_expire && payment_plans('premium','price')){
                echo '<div class="clear20"></div>';
                echo '<div class="text-center">'.generate_payment_buttons('premium',$independent_profile_id,__('Extend premium','escortwp')).'</div>';
                echo '<div class="clear5"></div>';
                echo '<small>'.format_price('premium').'</small';
            }
        echo '</div>';
    }

    // featured expiration
    if(get_post_meta($independent_profile_id,"featured",true)=="1"){
        $featured_expire = get_post_meta($independent_profile_id,"featured_expire",true);
        if($featured_expire){
            $featured_expire_date = date("d M Y", $featured_expire);
            $featured_mobile_expire_text = __('Featured expiration:','escortwp').' <b>'.$featured_expire_date.'</b>';
        } else {
            $featured_expire_date = strtolower(__('forever','escortwp'));
            $featured_mobile_expire_text = __('Featured status is active <b>forever</b>','escortwp');
        }

        echo '<div class="sidebar-expire-notice-mobile bluedegrade text-center" data-payment-plan="featured">';
            echo '<div class="expiration-date">'.$featured_mobile_expire_text.'</div>';
            if($featured_expire && payment_plans('featured','price')){
                echo '<div class="sidebar-expire-mobile-extent-button greenbutton rad25">'.__('Extend','escortwp').'</div>';
            }
        echo '</div>';
        echo '<div class="sidebar-expire-notice sidebar-expire-notice-has-mobile bluedegrade center" data-payment-plan="featured">';
            echo '<small>'.__('You featured status is active until','escortwp').':</small><b>'.$featured_expire_date.'</b>';
            if($featured_expire){
                echo '<small>'.human_time_diff(time(), $featured_expire).' '.__('remaining','escortwp').'</small>';
            }
            if(get_post_meta($independent_profile_id,"featured_renew",true)){
                // cancel subscription button
            } elseif($featured_expire && payment_plans('featured','price')){
                echo '<div class="clear20"></div>';
                echo '<div class="text-center">'.generate_payment_buttons('featured',$independent_profile_id,__('Extend featured','escortwp')).'</div>';
                echo '<div class="clear5"></div>';
                echo '<small>'.format_price('featured').'</small>';
            }
        echo '</div>';
    }
}

// show member's VIP expiration
if(is_user_logged_in() && get_user_meta($userid,'vip',true)) {
    woo_show_sidebar_expiration_notice('5');
}

// Blog categories
global $blog_section;
if($blog_section == 'yes') { ?>
    <div class="dropdownlinks blog-categories">
        <h4><?php _e('Our Blog','escortwp'); ?></h4>
        <ul>
        <?php
        $blog_cats = get_categories();
        foreach($blog_cats as $subcat) {
            if(!$subcat->category_parent) {
                $cat_id = $subcat->cat_ID;
                $name   = $subcat->cat_name;
                $link   = get_term_link((int)$cat_id,'category');
                $li     = '';
                foreach($blog_cats as $subcat2) {
                    if($subcat2->category_parent == $cat_id) {
                        $li .= '<li class="cat-item cat-item-'.$subcat2->cat_ID.'">';
                        $li .= '&nbsp;&nbsp;&nbsp;- <a href="'.get_term_link($subcat2->cat_ID,'category').'">'.$subcat2->cat_name.'</a>';
                        $li .= '</li>';
                    }
                }
                echo '<li class="cat-item cat-item-'.$cat_id.'">';
                    echo '<a href="'.$link.'">'.$name.'</a>';
                    if($li) echo '<ul>'.$li.'</ul>';
                echo '</li>';
            }
        }
        ?>
        </ul>
    </div>
    <div class="clear"></div>
<?php } ?>

<?php
// ==== CUSTOM QUICK-SEARCH SLIDER (from 2022 child) ====
?>
<style>
    .customcss {
        position: fixed;
        top: 55% !important;
        right: -89px;
        cursor: pointer;
        background-color: #ff0000;
        padding: 5px 10px 8px;
        color: #fff;
        z-index: 999;
        transform: rotate(90deg);
        transform-origin: left top 0;
    }
    .customcss:hover {
        background-color: #ff0000;
    }
    .sliderquicksearch {
        position: fixed;
        top: 40% !important;
        right: -210px;
        box-shadow: 1px 2px 2px #524f4f;
        z-index: 999;
        display: none;
    }
    .slider-search-cities-input { width:160px !important; }
    .slider-search-cities-input select {
        height: 30px;
        font-size: 1em;
        color: #286c9b;
    }
    span.icon.button-delete.icon-search.rad3.customcss { display: none; }
    .close-search {
        color: #fff;
        float: right;
        font-weight: bold;
        cursor: pointer;
    }
    @media screen and (max-width: 640px) {
        .quicksearch.sliderquicksearch {
            position: fixed;
            top: 3% !important;
            left: 3%;
            width: 94%;
            height: 94%;
            z-index: 9999999999999999;
            border: 2px solid #A1D4F0;
        }
    }
</style>

<?php if(get_option("quickescortsearch") == "1") {

    if(isset($city) && is_object($city))   { $city   = $city->term_id; }
    if(isset($state) && is_object($state)) { $state  = $state->term_id; }
    if(isset($country) && is_object($country)) { $country = $country->term_id; }
    ?>
    <div class="quicksearch">
        <a class="close-search">X</a>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            var c = ".search-country",
                parent_div = ".quicksearch",
                city_div = <?php echo showfield('state') ? "'.search-cities-input'" : "'.search-cities-input'"; ?>,
                state_c = <?php echo showfield('state') ? "'#state'" : "null"; ?>,
                state_div = <?php echo showfield('state') ? "'.search-cities-input'" : "null"; ?>;

            function show_search_cities(e) {
                var country = $(parent_div+' '+e).val();
                $(parent_div+' '+city_div).text('');
                <?php if(showfield('state')){ ?>$(parent_div+' '+state_div).text('');<?php } ?>
                if(country<1) return true;
                loader($(e).parents(parent_div).find(city_div));
                $.ajax({
                    type: "GET",
                    url: "<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
                    data: "id="+country+"&selected=<?php echo $city;?>&hide_empty=1&class=col100&select2=yes",
                    success:function(data){
                        $(e).parents(parent_div).find(city_div).html(data+'<div class="formseparator"></div>');
                        if($(window).width()>960) $('.select2').select2({minimumResultsForSearch:20,width:'auto',dropdownAutoWidth:true});
                    }
                });
            }

            if(country>0) show_search_cities(c);
            $(parent_div+' '+c).change(function(){ show_search_cities(c); });

            <?php if(showfield('state')){ ?>
            $(parent_div).on("change", state_c, function(){
                var state = $(this).val();
                if(state<1) return true;
                loader($(this).parents(parent_div).find(state_div));
                $.ajax({
                    type:"GET",
                    url:"<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
                    data:"id="+state+"&selected=<?php echo $city;?>&hide_empty=1&class=col100&select2=yes",
                    success:function(data){
                        $(parent_div).find(state_div).html(data+'<div class="formseparator"></div>');
                        if($(window).width()>960) $('.select2').select2();
                    }
                });
            });
            <?php } ?>

            $('.close-search').click(function(){
                $('.quicksearch').hide();
            });
        });
        </script>

        <h4><?php _e('Quick Search','escortwp'); ?>:</h4>
        <form action="<?php echo get_permalink(get_option('search_page_id'));?>" method="post" class="form-styling">
            <input type="hidden" name="action" value="search" />

            <?php
            $args = array(
                'show_option_none' => __('Country','escortwp'),
                'orderby'          => 'name',
                'order'            => 'ASC',
                'hide_empty'       => 1,
                'name'             => 'country',
                'class'            => 'search-country col100 select2',
                'taxonomy'         => $taxonomy_location_url,
            );
            $cats = get_categories(array_merge($args, ['parent'=>0,'number'=>2,'fields'=>'ids']));
            if(count($cats)===1){
                echo '<input type="hidden" name="country" class="search-country" value="'.$cats[0].'" />';
                echo '<script>jQuery(function($){$(".search-country").trigger("change");});</script>';
            } else {
                echo '<div class="form-input col100">';
                wp_dropdown_categories($args);
                echo '</div><div class="formseparator"></div>';
            }
            ?>

            <?php if(showfield('state')): ?>
            <div class="search-states-input form-input col100"></div>
            <?php endif; ?>

            <div class="search-cities-input form-input col100"></div>

            <div class="form-input col100">
                <select name="gender" class="select2">
                <?php foreach($gender_a as $k=>$g){
                    if(in_array($k,$settings_theme_genders)) echo '<option value="'.$k.'">'.__($g,'escortwp').'</option>';
                }?>
                </select>
            </div><div class="formseparator"></div>

            <div class="form-input col100">
                <label><input type="checkbox" name="premium" value="1" /> <?php _e('Only premium','escortwp'); ?></label>
            </div><div class="formseparator"></div>

            <div class="form-input col100">
                <label><input type="checkbox" name="independent" value="1" /> <?php _e('Only independent','escortwp'); ?></label>
            </div><div class="formseparator"></div>

            <div class="form-input col100">
                <label><input type="checkbox" name="verified" value="1" /> <?php _e('Only verified','escortwp'); ?></label>
            </div><div class="formseparator"></div>

            <div class="center col100">
                <input type="submit" class="submit-button blueishbutton rad3" value="<?php _e('Search

','escortwp');?>" />
                <div class="clear5"></div>
                <a class="adv" href="<?php echo get_permalink(get_option('search_page_id'));?>">
                    <span class="icon icon-search"></span><?php _e('Advanced search','escortwp');?>
                </a>
            </div>
        </form>
        <div class="clear"></div>
    </div><!-- QUICK SEARCH -->
    <div class="clear"></div>
<?php } ?>

<?php if ( is_active_sidebar('widget-sidebar-right') || current_user_can('level_10')) : ?>
    <div class="widgetbox-wrapper">
    <?php if ( !dynamic_sidebar('Sidebar Right') && current_user_can('level_10')) : ?>
        <?php _e('Go to your','escortwp');?> <a href="<?php echo admin_url('widgets.php');?>"><?php _e('widgets page','escortwp');?></a> <?php _e('to add content here','escortwp');?>.
    <?php endif; ?>
    </div><!-- SIDEBAR BOX -->
<?php endif; ?>

<?php dynamic_sidebar('Right Ads'); ?>

</div><!-- SIDEBAR RIGHT -->
