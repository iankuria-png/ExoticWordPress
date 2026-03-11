<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }
?>
<?php if ($err) { echo "<div class=\"err rad25\">$err</div>"; } ?>
<?php
if ($member_edit_page == "yes") {
    $form_url = get_permalink(get_option('member_edit_personal_info_page_id'));
} else {
    $form_url = get_permalink(get_option('member_register_page_id'));
}
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    //check if the current user is already taken
    $('.register-form-member #user').keyup(function(){
        var user = $('#user').val();
        var userlength = document.getElementById("user").value.length;
        if(userlength >= 4 && userlength <= 30) {
            $('.checkuser').empty();
            $.ajax({
                type: "GET",
                url: "<?php bloginfo('template_url'); ?>/ajax/check-username.php",
                data: "user=" + user,
                success: function(data){
                    $('.checkuser').html(data);
                }
            });
        };
    });

    $('.register-form-member').on('submit', function(event) {
        if($('.register-form-member input[name="tos_accept"]').length && !$('.register-form-member input[name="tos_accept"]').is(':checked')) {
            $('.register-form-member .form-input-accept-tos').addClass('form-input-accept-tos-err');
            return false;
        }

        var button = $('.register-form-member .registersubmit');
        if(button.prop("disabled") === false) {
            button.prop("disabled",true);
            setTimeout(function() {
                button.prop("disabled",false);
            }, 2000);
        }
    });
});
</script>
<form action="<?php echo $form_url; ?>" method="post" class="form-styling register-form-member">
    <div class="text-center"><small class="mandatory"><?php _e('Fields marked with <i>*</i> are mandatory','escortwp'); ?></small></div>
    <div class="clear20"></div>
    <input type="hidden" name="action" value="emails" />
    <input type="hidden" name="action" value="registermember" />
    <input type="text" name="emails" value="" class="hide" />

        <div class="form-label">
            <label for="memberemail"><?php _e('Email','escortwp'); ?><i>*</i></label>
        </div>
        <div class="form-input">
            <input type="email" name="memberemail" id="memberemail" class="input longinput" value="<?php echo $memberemail; ?>" required />
        </div> <!-- email --> <div class="formseparator"></div>

        <div class="form-label">
           <label for="membername"><?php _e('Name','escortwp'); ?><i>*</i></label>
           <small><?php _e('will be publicly shown','escortwp'); ?></small>
        </div>
        <div class="form-input">
            <input type="text" name="membername" id="membername" class="input longinput" value="<?php echo $membername; ?>" required />
        </div> <!-- name --> <div class="formseparator"></div>

    <?php if(!$member_edit_page) { ?>
        <div class="form-label">
           <label for="user"><?php _e('Username','escortwp'); ?><i>*</i></label>
           <small class="checkuser"><?php _e('Between 4 and 30 characters','escortwp'); ?></small>
        </div>
        <div class="form-input">
            <input type="text" name="user" id="user" class="input longinput" minlength="4" maxlength="30" value="<?php echo $user; ?>" autocomplete="off" required />
        </div> <!-- user --> <div class="formseparator"></div>

        <div class="form-label">
            <label for="pass"><?php _e('Password','escortwp'); ?><i>*</i></label>
            <small><?php _e('Between 6 and 30 characters','escortwp'); ?></small>
        </div>
        <div class="form-input">
            <input type="password" name="pass" id="pass" class="input longinput" minlength="6" maxlength="30" value="<?php echo $pass; ?>" autocomplete="off" required />
        </div> <!-- password --> <div class="formseparator"></div>
    <?php } ?>

    <?php if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && !is_user_logged_in() && get_option("recaptcha4")) { ?>
        <div class="form-input">
            <div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_sitekey'); ?>"></div>
        </div> <!-- captcha --> <div class="formseparator"></div>
    <?php } ?>

    <?php
    $tos_page_data = get_post(get_option('tos_page_id'));
    $data_protection_page_data = get_post(get_option('data_protection_page_id'));
    $tos_pages_links = array();
    if(($tos_page_data || $data_protection_page_data) && !is_user_logged_in()) {
        if($tos_page_data) {
            $message = sprintf(__('I agree with the %s of this website', 'escortwp'), '<a href="'.get_permalink(get_option('tos_page_id')).'" target="_blank">'.$tos_page_data->post_title.'</a>');
        }
        if($data_protection_page_data) {
            $message = sprintf(__('I agree with the %s of this website', 'escortwp'), '<a href="'.get_permalink(get_option('data_protection_page_id')).'" target="_blank">'.$data_protection_page_data->post_title.'</a>');
        }
        if($data_protection_page_data && $tos_page_data) {
            $message = sprintf(__('I agree with the %s and the %s of this website', 'escortwp'), '<a href="'.get_permalink(get_option('tos_page_id')).'" target="_blank">'.$tos_page_data->post_title.'</a>', '<a href="'.get_permalink(get_option('data_protection_page_id')).'" target="_blank">'.$data_protection_page_data->post_title.'</a>');
        }
    ?>
    <div class="formseparator"></div>
    <div class="form-input col100 center form-input-accept-tos">
        <label for="tos_checkbox" class="rad25">
            <input type="checkbox" name="tos_accept" value="1" id="tos_checkbox"<?php if($_POST['tos_accept'] == "1") { echo ' checked'; } ?> />
            <?=$message?>
        </label>
    </div> <!-- message --> <div class="clear15"></div>
    <?php } ?>

    <div class="text-center"><input type="submit" name="submit" value="<?php if($member_edit_page) { _e('Update','escortwp'); } else { _e('Register','escortwp'); } ?>" class="pinkbutton rad3 registersubmit" /></div> <!--center-->
</form>