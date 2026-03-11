<?php
/**
 * Template Name: Template Profile Edit
 */

global $wpdb;
$current_user = wp_get_current_user();

if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url() );
    exit;
}

$user_id      = intval( $current_user->ID );
$userstatus   = get_option( 'escortid' . $user_id );
$agency_not_paid = ( get_option( 'agency_has_not_payed' ) === 'yes' );
$escort_not_paid = ( get_option( 'escort_has_not_payed' ) === 'yes' );

// Only escorts can edit
if ( 'escort' !== $userstatus || $escort_not_paid ) {
    esc_html_e( 'Other edit links will be shown after payment', 'escortwp' );
    exit;
}

$err = '';
$ok  = '';

// Handle profile update
if ( isset( $_POST['action'] ) && 'register' === $_POST['action'] ) {
    locate_template( array( 'register-independent-personal-info-process.php' ), true, false );
}

// Fetch existing data
$escort_post_id = intval( get_option( 'escortpostid' . $user_id ) );
$escort_post    = get_post( $escort_post_id );
$thumbnail      = get_first_image( $escort_post_id );
$aboutyou       = nl2br( esc_html( $escort_post->post_content ) );
$yourname       = esc_html( $escort_post->post_title );
$youremail      = esc_html( $current_user->user_email );
$phone          = esc_html( get_post_meta( $escort_post_id, 'phone', true ) );
$escortemail    = sanitize_email( get_post_meta( $escort_post_id, 'escortemail', true ) );
$website        = esc_url( get_post_meta( $escort_post_id, 'website', true ) );

// Location fields
$location_tax   = get_option( 'locationdropdown' ) === '1' ? 'dropdown' : 'text';
$country        = get_post_meta( $escort_post_id, 'country', true );
$state          = get_post_meta( $escort_post_id, 'state', true );
$city           = get_post_meta( $escort_post_id, 'city', true );

$gender         = get_post_meta( $escort_post_id, 'gender', true );
$birthday       = get_post_meta( $escort_post_id, 'birthday', true );
$birthday_parts = $birthday ? explode( '-', $birthday ) : array( '', '', '' );
$dateyear       = $birthday_parts[0] ?? '';
$datemonth      = $birthday_parts[1] ?? '';
$dateday        = $birthday_parts[2] ?? '';

// Additional meta
$fields = array(
    'ethnicity','haircolor','hairlength','bustsize','height','weight','build','looks','smoker','availability',
    'language1','language1level','language2','language2level','language3','language3level',
    'currency',
    'rate30min_incall','rate1h_incall','rate2h_incall','rate3h_incall','rate6h_incall','rate12h_incall','rate24h_incall',
    'rate30min_outcall','rate1h_outcall','rate2h_outcall','rate3h_outcall','rate6h_outcall','rate12h_outcall','rate24h_outcall',
    'services','extraservices','education','sports','hobbies','zodiacsign','sexualorientation','occupation'
);
foreach ( $fields as $f ) {
    ${$f} = get_post_meta( $escort_post_id, $f, true );
}

get_header();
$profile_menu = 'menu-active';
?>

<?php locate_template( array( 'template-menus.php' ), true, false ); ?>

<div class="bodybox p-10 mb-30 bootstrap-wrapper" style="border:1px solid #f8c1cf;border-top:none;border-radius:0 0 5px 5px;background-color:#fff;">

    <?php if ( $err ) : ?>
        <div class="err rad3"><?php echo esc_html( $err ); ?></div>
    <?php endif; ?>
    <?php if ( $ok ) : ?>
        <div class="ok rad3"><?php esc_html_e( 'Profile updated', 'escortwp' ); ?></div>
    <?php endif; ?>

    <form method="post" class="form-styling">
        <input type="hidden" name="action" value="register" />
        <input type="hidden" name="escort_post_id" value="<?php echo esc_attr( $escort_post_id ); ?>" />

        <div class="panel mt-20">
            <div class="panel-header">
                <b style="color:#E0006C;"><i class="fa fa-edit"></i> <?php esc_html_e( 'Edit My Profile', 'escortwp' ); ?></b>
            </div>
            <div class="panel-body pt-40">
                <div class="row">

                    <div class="col-md-12">
                        <label for="youremail"><?php esc_html_e( 'Your Email', 'escortwp' ); ?> <span class="required">*</span></label>
                        <input type="email" name="youremail" id="youremail" class="input longinput" value="<?php echo $youremail; ?>" required />

                        <label for="yourname"><?php esc_html_e( 'Name', 'escortwp' ); ?> <span class="required">*</span></label>
                        <input type="text" name="yourname" id="yourname" class="input longinput" value="<?php echo $yourname; ?>" required />

                        <label for="phone"><?php esc_html_e( 'Phone', 'escortwp' ); ?></label>
                        <input type="tel" name="phone" id="phone" class="input longinput" value="<?php echo $phone; ?>" />

                        <label for="website"><?php esc_html_e( 'Website', 'escortwp' ); ?></label>
                        <input type="url" name="website" id="website" class="input longinput" value="<?php echo $website; ?>" />

                        <label><?php esc_html_e( 'Location', 'escortwp' ); ?> <span class="required">*</span></label>
                        <?php if ( 'dropdown' === $location_tax ) : ?>
                            <?php
                            $args = array(
                                'show_option_none' => esc_html__( 'Select Country', 'escortwp' ),
                                'taxonomy'         => esc_html( $taxonomy_location_url ),
                                'name'             => 'country',
                                'selected'         => esc_attr( $country ),
                                'class'            => 'country select2',
                                'hide_empty'       => 0,
                            );
                            wp_dropdown_categories( $args );
                            ?>
                        <?php else : ?>
                            <input type="text" name="country" class="input longinput" value="<?php echo esc_attr( $country ); ?>" required />
                        <?php endif; ?>

                                                        wp_dropdown_categories( $args );
                                $city_parent = $country;
                                ?>
                            </div> <!-- country --> <div class="formseparator"></div>

                            <?php if(showfield('state')) { ?>
                                <div class="form-label">
                                    <label for="state"><?php _de('State',76); ismand('state'); ?></label>
                                </div>
                                <div class="form-input inputstates" data-text="<?=_d('Please select a country first',388)?>">
                                    <?php if(get_option('locationdropdown') == "1") {
                                        if($country > 0) {
                                            $city_parent = $state;
                                            $args = array(
                                                'show_option_all'    => '',
                                                'show_option_none'   => _d('Select State',1211),
                                                'show_last_update'   => 0,
                                                'show_count'         => 0,
                                                'parent'			 => $country,
                                                'hide_empty'         => 0,
                                                'exclude'            => '',
                                                'echo'               => 1,
                                                'selected'           => $state,
                                                'hierarchical'       => 1,
                                                'name'               => 'state',
                                                'id'                 => '',
                                                'class'              => 'state select2',
                                                'depth'              => 1,
                                                'tab_index'          => 0,
                                                'orderby'            => 'name',
                                                'order'              => 'ASC',
                                                'taxonomy'           => $taxonomy_location_url );
                                            wp_dropdown_categories( $args );
                                        } else {
                                            _de('Please select a country first',388);
                                        }
                                    } else { ?>
                                        <input type="text" name="state" id="state" class="input longinput" value="<?php echo $state; ?>" />
                                    <?php } ?>
                                </div> <!-- state --> <div class="formseparator"></div>
                            <?php } ?>


                            <div class="form-label">
                                <label for="city"><?php _de('City',81); ?> <i>*</i></label>
                            </div>
                            <?php
                            if(showfield('state')) {
                                $city_text = _d('Please select a state first',1212);
                            } else {
                                $city_text = _d('Please select a country first',388);
                            }
                            ?>
                            <div class="form-input inputcities" data-text="<?=$city_text?>">
                                <?php if(get_option('locationdropdown') == "1") {
                                    if(($country > 0 && !showfield('state')) || ($state > 0 && showfield('state'))) {
                                        $args = array(
                                            'show_option_all'    => '',
                                            'show_option_none'   => _d('Select City',387),
                                            'show_last_update'   => 0,
                                            'show_count'         => 0,
                                            'parent'			 => $city_parent,
                                            'hide_empty'         => 0,
                                            'exclude'            => '',
                                            'echo'               => 1,
                                            'selected'           => $city,
                                            'hierarchical'       => 1,
                                            'name'               => 'city',
                                            'id'                 => '',
                                            'class'              => 'city select2',
                                            'depth'              => 1,
                                            'tab_index'          => 0,
                                            'orderby'            => 'name',
                                            'order'              => 'ASC',
                                            'taxonomy'           => $taxonomy_location_url );
                                        wp_dropdown_categories( $args );
                                    } else {
                                        echo $city_text;
                                    }
                                } else { ?>
                                    <input type="text" name="city" id="city" class="input longinput" value="<?php echo $city; ?>" />
                                <?php } ?>
                            </div> <!-- city --> <div class="formseparator"></div>

                            <div class="form-label">
                                <label><?php _de('Gender',391); ?><i>*</i></label>
                            </div>
                            <div class="form-input" id="gender">
                                <?php
                                foreach($gender_a as $key=>$g) {
                                    if(in_array($key, $settings_theme_genders)) {
                                        ?>
                                        <label for="gender<?php echo $key ?>">
                                            <input type="radio" name="gender" value="<?php echo $key; ?>" id="gender<?php echo $key ?>"<?php if($gender == $key) { echo ' checked'; } ?> />
                                            <?php echo $g; ?><br />
                                        </label>
                                        <?php
                                    } // if in_array
                                } // foreach
                                ?>
                            </div> <!-- GENDER --> <div class="formseparator"></div>


                            <div class="form-label">
                                <label><?php _de('Date of birth',416); ?><i>*</i></label>
                                <small><?php _de('we\'ll calculate your age from this',417); ?></small>
                            </div>
                            <div class="form-input">
                                <select name="dateday" id="dateday" class="birthday select col33 l">
                                    <option value=""><?php _de('Day',418); ?></option>
                                    <?php
                                    for($i=1;$i<=31;$i+=1) {
                                        if ($dateday == $i) { $selected = ' selected="selected"'; }
                                        echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
                                        unset($selected);
                                    }
                                    ?>
                                </select>
                                <select name="datemonth" id="datemonth" class="birthday select col33 l">
                                    <option value=""><?php _de('Month',419); ?></option>
                                    <option value="1"<?php if($datemonth == "1") { echo ' selected="selected"'; } ?>><?php _de('January',420); ?></option>
                                    <option value="2"<?php if($datemonth == "2") { echo ' selected="selected"'; } ?>><?php _de('February',421); ?></option>
                                    <option value="3"<?php if($datemonth == "3") { echo ' selected="selected"'; } ?>><?php _de('March',422); ?></option>
                                    <option value="4"<?php if($datemonth == "4") { echo ' selected="selected"'; } ?>><?php _de('April',423); ?></option>
                                    <option value="5"<?php if($datemonth == "5") { echo ' selected="selected"'; } ?>><?php _de('May',424); ?></option>
                                    <option value="6"<?php if($datemonth == "6") { echo ' selected="selected"'; } ?>><?php _de('June',425); ?></option>
                                    <option value="7"<?php if($datemonth == "7") { echo ' selected="selected"'; } ?>><?php _de('July',426); ?></option>
                                    <option value="8"<?php if($datemonth == "8") { echo ' selected="selected"'; } ?>><?php _de('August',427); ?></option>
                                    <option value="9"<?php if($datemonth == "9") { echo ' selected="selected"'; } ?>><?php _de('September',428); ?></option>
                                    <option value="10"<?php if($datemonth == "10") { echo ' selected="selected"'; } ?>><?php _de('October',429); ?></option>
                                    <option value="11"<?php if($datemonth == "11") { echo ' selected="selected"'; } ?>><?php _de('November',430); ?></option>
                                    <option value="12"<?php if($datemonth == "12") { echo ' selected="selected"'; } ?>><?php _de('December',431); ?></option>
                                </select>
                                <select name="dateyear" id="dateyear" class="birthday select col33 l">
                                    <option value=""><?php _de('Year',432); ?></option>
                                    <?php
                                    $startyear = date('Y') - 18;
                                    $endyear = date('Y') - 80;
                                    for($i=$startyear;$i>=$endyear;$i--) {
                                        if ($dateyear == $i) { $selected = ' selected="selected"'; }
                                        echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
                                        unset($selected);
                                    }
                                    ?>
                                </select>
                            </div> <!-- DATE OF BIRTH --> <div class="formseparator"></div>

                            <?php if(showfield('ethnicity')) { ?>
                                <div class="form-label">
                                    <label for="ethnicity"><?php _de('Ethnicity',392); ismand('ethnicity'); ?></label>
                                </div>
                                <div class="form-input">
                                    <select name="ethnicity" id="ethnicity">
                                        <option value=""><?php _de('Select',393); ?></option>
                                        <?php foreach($ethnicity_a as $key=>$s) { ?>
                                            <option value="<?php echo $key; ?>"<?php if($ethnicity == $key) { echo ' selected="selected"'; } ?>><?php echo $s; ?></option>
                                        <?php } ?>
                                    </select>
                                </div> <!-- SKIN COLOR --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('haircolor')) { ?>
                                <div class="form-label">
                                    <label for="haircolor"><?php _de('Hair Color',403); ismand('haircolor'); ?></label>
                                </div>
                                <div class="form-input">
                                    <select name="haircolor" id="haircolor" class="haircolor">
                                        <option value=""><?php _de('Select',393); ?></option>
                                        <?php foreach($haircolor_a as $key=>$h) { ?>
                                            <option value="<?php echo $key; ?>"<?php if($haircolor == $key) { echo ' selected="selected"'; } ?>><?php echo $h; ?></option>
                                        <?php } ?>
                                    </select>
                                </div> <!-- HAIR COLOR --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('hairlength')) { ?>
                                <div class="form-label">
                                    <label for="hairlength"><?php _de('Hair length',404); ismand('hairlength'); ?></label>
                                </div>
                                <div class="form-input">
                                    <select name="hairlength" id="hairlength" class="hairlength">
                                        <option value=""><?php _de('Select',393); ?></option>
                                        <?php foreach($hairlength_a as $key=>$h) { ?>
                                            <option value="<?php echo $key; ?>"<?php if($hairlength == $key) { echo ' selected="selected"'; } ?>><?php echo $h; ?></option>
                                        <?php } ?>
                                    </select>
                                </div> <!-- HAIR LENGTH --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('bustsize')) { ?>
                                <div class="form-label">
                                    <label for="bustsize"><?php _de('Bust size',405); ismand('bustsize'); ?></label>
                                    <small><?php _de('mandatory only for females',787); ?></small>
                                </div>
                                <div class="form-input">
                                    <select name="bustsize" id="bustsize" class="bustsize">
                                        <option value=""><?php _de('Select',393); ?></option>
                                        <?php foreach($bustsize_a as $key=>$b) { ?>
                                            <option value="<?php echo $key; ?>"<?php if($bustsize == $key) { echo ' selected="selected"'; } ?>><?php echo $b; ?></option>
                                        <?php } ?>
                                    </select>
                                </div> <!-- BUST SIZE --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('height')) { ?>
                                <div class="form-label">
                                    <label for="height"><?php _de('Height',406); ismand('height'); ?></label>
                                </div>
                                <div class="form-input">
                                    <input type="text" name="height" size="4" id="height" class="input smallinput text-center" value="<?php echo $height; ?>" /> &nbsp; cm
                                </div> <!-- HEIGHT --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('weight')) { ?>
                                <div class="form-label">
                                    <label for="weight"><?php _de('Weight',413); ismand('weight'); ?></label>
                                </div>
                                <div class="form-input">
                                    <input type="text" name="weight" size="4" id="weight" class="input smallinput text-center" value="<?php echo $weight; ?>" /> &nbsp; kg
                                </div> <!-- WEIGHT --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('build')) { ?>
                                <div class="form-label">
                                    <label for="build"><?php _de('Build',407); ismand('build'); ?></label>
                                </div>
                                <div class="form-input">
                                    <select name="build" id="build" class="build">
                                        <option value=""><?php _de('Select',393); ?></option>
                                        <?php foreach($build_a as $key=>$b) { ?>
                                            <option value="<?php echo $key; ?>"<?php if($build == $key) { echo ' selected="selected"'; } ?>><?php echo $b; ?></option>
                                        <?php } ?>
                                    </select>
                                </div> <!-- BUILT --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('looks')) { ?>
                                <div class="form-label">
                                    <label for="looks"><?php _de('Looks',408); ismand('looks'); ?></label>
                                </div>
                                <div class="form-input">
                                    <select name="looks" id="looks" class="looks">
                                        <option value=""><?php _de('Select',393); ?></option>
                                        <?php foreach($looks_a as $key=>$l) { ?>
                                            <option value="<?php echo $key; ?>"<?php if($looks == $key) { echo ' selected="selected"'; } ?>><?php echo $l; ?></option>
                                        <?php } ?>
                                    </select>
                                </div> <!-- LOOKS --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php
                            if(showfield('availability')) {
                                if(!$availability) { $availability = array(); }
                                ?>
                                <div class="form-label">
                                    <label><?php _de('Availability',394); ismand('availability'); ?></label>
                                </div>
                                <div class="form-input">
                                    <label for="incall">
                                        <input type="checkbox" name="availability[]" value="1" id="incall"<?php if( in_array("1", $availability) ) { echo ' checked'; } ?> />
                                        <?php _de('Incall',258); ?>
                                    </label>
                                    <label for="outcall">
                                        <input type="checkbox" name="availability[]" value="2" id="outcall"<?php if( in_array("2", $availability) ) { echo ' checked'; } ?> />
                                        <?php _de('Outcall',259); ?>
                                    </label>
                                </div> <!-- AVAILABILITY --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('smoker')) { ?>
                                <div class="form-label">
                                    <label><?php _de('Smoker',395); ismand('smoker'); ?></label>
                                </div>
                                <div class="form-input">
                                    <label for="smokeyes"><input type="radio" name="smoker" value="1" id="smokeyes"<?php if($smoker == "1") { echo ' checked'; } ?> /> <?php _de('Yes',156); ?></label>
                                    <label for="smokeno"><input type="radio" name="smoker" value="2" id="smokeno"<?php if($smoker == "2") { echo ' checked'; } ?> /> <?php _de('No',157); ?></label>
                                </div> <!-- SMOKER --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('aboutyou')) { ?>
                                <div class="form-label">
                                    <?php
                                    $labeltext = _d('About you',433);
                                    if ($agencyid || current_user_can('level_10')) { $labeltext = _d('About the %s',82,$taxonomy_profile_name); }
                                    ?>
                                    <label for="aboutyou"><?php echo $labeltext; ismand('aboutyou'); ?></label>
                                </div>
                                <div class="form-input">
                                    <textarea name="aboutyou" id="aboutyou" class="textarea longtextarea" rows="7"><?php echo strip_tags($aboutyou); ?></textarea>
                                    <small><?php _de('html code will be removed',83); ?></small>
                                </div> <!-- about you --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('education')) { ?>
                                <div class="form-label">
                                    <label for="education"><?php _de('Education',434); ismand('education'); ?></label>
                                </div>
                                <div class="form-input">
                                    <input type="text" name="education" id="education" class="input longinput" value="<?php echo $education; ?>" />
                                </div> <!-- education --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('sports')) { ?>
                                <div class="form-label">
                                    <label for="sports"><?php _de('Sports',435); ismand('sports'); ?></label>
                                </div>
                                <div class="form-input">
                                    <input type="text" name="sports" id="sports" class="input longinput" value="<?php echo $sports; ?>" />
                                </div> <!-- sports --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('hobbies')) { ?>
                                <div class="form-label">
                                    <label for="hobbies"><?php _de('Hobbies',436); ismand('hobbies'); ?></label>
                                </div>
                                <div class="form-input">
                                    <input type="text" name="hobbies" id="hobbies" class="input longinput" value="<?php echo $hobbies; ?>" />
                                </div> <!-- hobbies --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('zodiacsign')) { ?>
                                <div class="form-label">
                                    <label for="zodiacsign"><?php _de('Zodiac sign',437); ismand('zodiacsign'); ?></label>
                                </div>
                                <div class="form-input">
                                    <input type="text" name="zodiacsign" id="zodiacsign" class="input longinput" value="<?php echo $zodiacsign; ?>" />
                                </div> <!-- zodiac sign --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('sexualorientation')) { ?>
                                <div class="form-label">
                                    <label for="sexualorientation"><?php _de('Sexual orientation',438); ismand('sexualorientation'); ?></label>
                                </div>
                                <div class="form-input">
                                    <input type="text" name="sexualorientation" id="sexualorientation" class="input longinput" value="<?php echo $sexualorientation; ?>" />
                                </div> <!-- sexual orientation --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('occupation')) { ?>
                                <div class="form-label">
                                    <label for="occupation"><?php _de('Occupation',439); ismand('occupation'); ?></label>
                                </div>
                                <div class="form-input">
                                    <input type="text" name="occupation" id="occupation" class="input longinput" value="<?php echo $occupation; ?>" />
                                </div> <!-- occupation --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('language')) { ?>
                                <div class="form-label">
                                    <label><?php _de('Languages spoken',440); ismand('language'); ?></label>
                                </div>
                                <div class="form-input" id="language">
                                    <input type="text" name="language1" class="input" value="<?php echo $language1; ?>" />
                                    <select name="language1level" id="language1level" class="language1level">
                                        <option value=""><?php _de('Select level',441); ?></option>
                                        <?php foreach($languagelevel_a as $key=>$l) { ?>
                                            <option value="<?php echo $key; ?>"<?php if($language1level == $key) { echo ' selected="selected"'; } ?>><?php echo $l; ?></option>
                                        <?php } ?>
                                    </select><div class="clear10"></div>

                                    <input type="text" name="language2" class="input" value="<?php echo $language2; ?>" />
                                    <select name="language2level" id="language2level" class="language2level">
                                        <option value=""><?php _de('Select level',441); ?></option>
                                        <?php foreach($languagelevel_a as $key=>$l) { ?>
                                            <option value="<?php echo $key; ?>"<?php if($language2level == $key) { echo ' selected="selected"'; } ?>><?php echo $l; ?></option>
                                        <?php } ?>
                                    </select><div class="clear10"></div>

                                    <input type="text" name="language3" class="input" value="<?php echo $language3; ?>" />
                                    <select name="language3level" id="language3level" class="language3level">
                                        <option value=""><?php _de('Select level',441); ?></option>
                                        <?php foreach($languagelevel_a as $key=>$l) { ?>
                                            <option value="<?php echo $key; ?>"<?php if($language3level == $key) { echo ' selected="selected"'; } ?>><?php echo $l; ?></option>
                                        <?php } ?>
                                    </select>
                                </div> <!-- language --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('rates')) { ?>
                                <div class="form-label">
                                    <label><?php _de('Rates',396); ismand('rates'); ?></label>
                                    <?php if(ismand('rates', 'no')) { echo '<small>'._d('at least one rate',442).'</small>'; } ?>
                                </div>
                                <div class="form-input">
                                    <div class="col30 l currency-label-text"><?php _de('Currency',122); ?>:</div>
                                    <div class="col60 l currency-label-dropdown">
                                        <select name="currency" id="currency" class="col100">
                                            <?php
                                            foreach($currency_a as $key=>$c) {
                                                if($currency == $key) { $selected = ' selected="selected"'; }
                                                echo '<option value="'.$key.'"'.$selected.'>'.$c[0].' - '.$c[1].'</option>'."\n";
                                                unset($selected);
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="clear20"></div>

                                    <div class="rates l">
                                        <div class="col30 l">&nbsp;</div>
                                        <div class="col30 text-center l hide-incall"><b><?php _de('Incall',258); ?></b></div>
                                        <div class="col30 text-center l hide-outcall"><b><?php _de('Outcall',259); ?></b></div>
                                    </div>
                                    <div class="clear10"></div>
                                    <div class="rates l">
                                        <div class="col30 l rates-label"><?php echo "30 "._d('minutes',1079); ?></div>
                                        <div class="col30 l hide-incall"><input type="number" name="rate30min_incall" maxlength="15" value="<?php echo $rate30min_incall; ?>" class="input" /></div>
                                        <div class="col30 l hide-outcall"><input type="number" name="rate30min_outcall" maxlength="15" value="<?php echo $rate30min_outcall; ?>" class="input" /></div>
                                    </div>
                                    <div class="rates l">
                                        <div class="col30 l rates-label"><?php echo "1 "._d('hour',733); ?></div>
                                        <div class="col30 l hide-incall"><input type="number" name="rate1h_incall" maxlength="15" value="<?php echo $rate1h_incall; ?>" class="input" /></div>
                                        <div class="col30 l hide-outcall"><input type="number" name="rate1h_outcall" maxlength="15" value="<?php echo $rate1h_outcall; ?>" class="input" /></div>
                                    </div>
                                    <div class="rates l">
                                        <div class="col30 l rates-label"><?php echo "2 "._d('hours',734); ?></div>
                                        <div class="col30 l hide-incall"><input type="number" name="rate2h_incall" maxlength="15" value="<?php echo $rate2h_incall; ?>" class="input" /></div>
                                        <div class="col30 l hide-outcall"><input type="number" name="rate2h_outcall" maxlength="15" value="<?php echo $rate2h_outcall; ?>" class="input" /></div>
                                    </div>
                                    <div class="rates l">
                                        <div class="col30 l rates-label"><?php echo "3 "._d('hours',734); ?></div>
                                        <div class="col30 l hide-incall"><input type="number" name="rate3h_incall" maxlength="15" value="<?php echo $rate3h_incall; ?>" class="input" /></div>
                                        <div class="col30 l hide-outcall"><input type="number" name="rate3h_outcall" maxlength="15" value="<?php echo $rate3h_outcall; ?>" class="input" /></div>
                                    </div>
                                    <div class="rates l">
                                        <div class="col30 l rates-label"><?php echo "6 "._d('hours',734); ?></div>
                                        <div class="col30 l hide-incall"><input type="number" name="rate6h_incall" maxlength="15" value="<?php echo $rate6h_incall; ?>" class="input" /></div>
                                        <div class="col30 l hide-outcall"><input type="number" name="rate6h_outcall" maxlength="15" value="<?php echo $rate6h_outcall; ?>" class="input" /></div>
                                    </div>
                                    <div class="rates l">
                                        <div class="col30 l rates-label"><?php echo "12 "._d('hours',734); ?></div>
                                        <div class="col30 l hide-incall"><input type="number" name="rate12h_incall" maxlength="15" value="<?php echo $rate12h_incall; ?>" class="input" /></div>
                                        <div class="col30 l hide-outcall"><input type="number" name="rate12h_outcall" maxlength="15" value="<?php echo $rate12h_outcall; ?>" class="input" /></div>
                                    </div>
                                    <div class="rates l">
                                        <div class="col30 l rates-label"><?php echo "24 "._d('hours',734); ?></div>
                                        <div class="col30 l hide-incall"><input type="number" name="rate24h_incall" maxlength="15" value="<?php echo $rate24h_incall; ?>" class="input" /></div>
                                        <div class="col30 l hide-outcall"><input type="number" name="rate24h_outcall" maxlength="15" value="<?php echo $rate24h_outcall; ?>" class="input" /></div>
                                    </div>
                                    <div class="clear"></div>
                                </div> <!-- RATES --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('services')) { ?>
                                <div class="form-label">
                                    <label><?php _de('Services',399); ismand('services'); ?></label>
                                </div>
                                <div class="form-input">
                                    <?php
                                    $services[] = $services;
                                    foreach($services_a as $key=>$service) { ?>
                                        <div class="col50 one-service l">
                                            <label for="service<?php echo $key; ?>">
                                                <input type="checkbox" name="services[]" value="<?php echo $key; ?>" id="service<?php echo $key; ?>"<?php if( in_array($key, $services) ) { echo ' checked'; } ?> />
                                                <?php echo $service; ?>
                                            </label>
                                            <div class="clear5"></div>
                                        </div> <!-- one service -->
                                    <?php } // foreach ?>
                                </div> <!-- SERVICES --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(showfield('extraservices')) { ?>
                                <div class="form-label">
                                    <label for="extraservices"><?php _de('Extra services',449); ismand('extraservices'); ?></label>
                                </div>
                                <div class="form-input">
                                    <input type="text" name="extraservices" id="extraservices" class="input longinput" value="<?php echo $extraservices; ?>" />
                                </div> <!-- extra services --> <div class="formseparator"></div>
                            <?php } // showfield ?>

                            <?php if(get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && !is_user_logged_in() && get_option("recaptcha2")) { ?>
                                <div class="form-input">
                                    <div class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_sitekey'); ?>"></div>
                                </div> <!-- message --> <div class="formseparator"></div>
                            <?php } ?>
                            <div class="reg_trams_condition">
                                <p> I Agree to Exotic Africa's <a target="_blank" href="https://www.exotic-africa.com/terms-conditions-use/">Terms and conditions</a></p>
                            </div>

                        <div class="center mt-20">
                            <button type="submit" class="bluebutton rad25"><?php esc_html_e( 'Update Profile', 'escortwp' ); ?></button>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<?php get_footer(); ?>
