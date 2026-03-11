<?php
/*
Template Name: Blacklisted Escorts
*/

global $taxonomy_agency_url, $taxonomy_profile_name, $taxonomy_profile_name_plural;
$current_user = wp_get_current_user();
if (!current_user_can('level_10') && get_option("escortid".$current_user->ID) != $taxonomy_agency_url) {
	wp_redirect(get_bloginfo("url")); exit;
}

$err = ""; $ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'addescort') {
	include (get_template_directory() . '/blacklisted-escorts-personal-info-process.php');
}
$search_query = array();
if (isset($_POST['action']) && $_POST['action'] == 'search') {
	$search_query = array();
    $yourname = wp_strip_all_tags($_POST['yourname'], true);
	if ($yourname) {
		$search_query[] = array(
			'key' => 'name',
			'value' => $yourname,
			'compare' => '='
		);
	}

    $phone = wp_strip_all_tags($_POST['phone'], true);
	if ($phone) {
		$search_query[] = array(
			'key' => 'phone',
			'value' => $phone,
			'compare' => '='
		);
	}

    $escortemail = $_POST['escortemail'];
	if ($escortemail) {
		if ( !is_email($escortemail) ) {
			$err .= sprintf(esc_html__('The %s email seems to be wrong.','escortwp'),$taxonomy_profile_name)."<br />";
		} else {
			$search_query[] = array(
				'key' => 'email',
				'value' => $escortemail,
				'compare' => '='
			);
		}
	}

	global $taxonomy_location_url;
	if ($_POST['country'] && $_POST['country'] > 0) {
		$country = (int)$_POST['country'];
		if (!term_exists( $country, $taxonomy_location_url ) || $country < 1) {
			$err .= __('The country you selected doesn\'t exist in our database','escortwp')."<br />"; unset($country, $city);
		} else {
			$search_query[] = array(
				'key' => 'country',
				'value' => $country,
				'compare' => '='
			);
		} // if term exists country
	}

	if ($_POST['city']) {
		$city = substr(wp_filter_nohtml_kses($_POST['city']),0, 50);
		$search_query[] = array(
			'key' => 'city',
			'value' => $city,
			'compare' => '='
		);
	}

	if ($_POST['gender']) {
		$gender = (int)$_POST['gender'];
		if (!$gender_a[$gender]) { $err .= __('Please choose your gender','escortwp')."<br />"; unset($gender); } else {
			$search_query[] = array(
				'key' => 'gender',
				'value' => $gender,
				'compare' => '='
			);
		}
	}
	
	if (count($search_query) == "0") {
		$err = __('You have to fill in at least one search field','escortwp');
	}
	unset($yourname, $phone, $escortemail, $country, $city, $city_id, $gender, $haircolor);
} // if search

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox">
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.addbescort').on('click', function(){
					$('.searchescortform, .option-buttons').slideUp();
					$('.addnewescortform').slideDown("slow");
				});
				$('.searchescort').on('click', function(){
					$('.addnewescortform, .option-buttons').slideUp();
					$('.searchescortform').slideDown("slow");
				});
				$('.closebtn').on('click', function(){
					$(this).parent().slideUp();
					$('.option-buttons').slideDown();
				});
			});
			</script>
			<div class="option-buttons">
            	<h3 class="l"><?php printf(esc_html__('Blacklisted %s','escortwp'),$taxonomy_profile_name); ?></h3>
                <div class="r">
	                <div class="searchescort pinkbutton rad3 r"><span><?php printf(esc_html__('Search %s','escortwp'),$taxonomy_profile_name_plural); ?></span></div>
	                <div class="r">&nbsp;</div>
                	<div class="addbescort pinkbutton rad3 r"><span><?php printf(esc_html__('Add a new %s','escortwp'),$taxonomy_profile_name); ?></span></div>
				</div> <!-- RIGHT --> <div class="clear"></div>
			</div> <!-- option-buttons -->

			<div class="addnewescortform" <?php if($err && $_POST['action'] == "addescort") { echo ' style="display: block;"'; } ?>>
				<?php closebtn(); ?>
				<?php include (get_template_directory() . '/blacklisted-escorts-form.php'); ?>
			</div> <!-- ADD NEW ESCORT FORM -->

			<div class="searchescortform"<?php if($err && $_POST['action'] == "search") { echo ' style="display: block;"'; } ?>>
				<?php closebtn(); ?>
				<?php
				$search_page = "yes";
				if ( $err && $_POST['action'] == 'search') { echo "<div class=\"err rad25\">$err</div>"; }
			    include (get_template_directory() . '/blacklisted-escorts-form.php');
				unset($search_page);
				?>
			</div> <!-- ADD NEW ESCORT FORM -->
            <div class="clear"></div>
        </div> <!-- BODY BOX -->

		<?php if (count($search_query) > "0") { ?>
            <div class="clear"></div>
        	<div class="bodybox profiles-you-added">
            	<h3><?php _e('Search results','escortwp'); ?></h3>
				<?php
				$args = array(
					'post_type' => 'b'.$taxonomy_profile_url,
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'orderby' => 'title',
					'order' => 'ASC',
					'meta_query' => $search_query
				);
				query_posts($args);
				$i = 1;
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						include (get_template_directory() . '/loop-show-profile.php');
					endwhile;
				else:
					printf(esc_html__('No %s here yet','escortwp'),$taxonomy_profile_name_plural);
				endif;
				wp_reset_query();
				?>
            <div class="clear10"></div>
            </div> <!-- BODY BOX -->
        <?php } ?>

		<?php if ($_POST['action'] != 'search') { ?>
            <div class="clear"></div>
        	<div class="bodybox">
            	<h3><?php printf(esc_html__('%s you added','escortwp'),ucfirst($taxonomy_profile_name_plural)); ?></h3>
	            <div class="clear10"></div>
				<?php
				$args = array(
					'post_type' => 'b'.$taxonomy_profile_url,
					'author' => $current_user->ID,
					'posts_per_page' => -1
				);
				query_posts($args);
				$i = 1;
				if ( have_posts() ) :
					while ( have_posts() ) : the_post();
						include (get_template_directory() . '/loop-show-profile.php');
					endwhile;
				else:
					printf(esc_html__('No %s here yet','escortwp'),$taxonomy_profile_name_plural);
				endif;
				wp_reset_query();
				?>
	            <div class="clear"></div>
            </div> <!-- BODY BOX -->
        <?php } // if there is a search then don't show added blacklisted profiles ?>
    </div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>