<?php
/*
Template Name: Nav Blacklisted Escorts
*/

global $taxonomy_location_url;
$current_user = wp_get_current_user();
if (isset($_POST['action']) && $_POST['action'] == 'search') {
	$search_query = array();
    $yourname = substr(wp_filter_nohtml_kses($_POST['yourname']),0, 50);
	if ($yourname) {
		$search_query[] = array(
			'key' => 'name',
			'value' => $yourname,
			'compare' => '='
		);
	}

    $phone = substr(wp_filter_nohtml_kses($_POST['phone']),0, 50);
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
		if (!$gender_a[$gender]) { $err .= __('Please choose a gender','escortwp')."<br />"; unset($gender); } else {
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
	unset($yourname, $phone, $escortemail, $country, $city, $gender, $haircolor);
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox">
			<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('.searchescort').on('click', function(){
					$('.searchescortform, .show-profiles').slideToggle("slow");
					$(this).slideToggle();
				});
				$('.searchescortform .closebtn').on('click', function(){
					$(this).parent().slideToggle();
					$('.searchescort, .show-profiles').slideToggle();
				});
			});
			</script>
			<?php if (count($search_query) < "1") { ?>
            	<h3 class="l"><?php printf(esc_html__('Blacklisted %s','escortwp'),$taxonomy_profile_name_plural); ?></h3>
			<?php } else {?>
				<h3 class="l"><?php _e('Search results','escortwp'); ?></h3>
			<?php } ?>

            <div class="searchescort pinkbutton rad3 r"><?php printf(esc_html__('Search %s','escortwp'),$taxonomy_profile_name_plural); ?></div>
			<div class="clear10"></div>
			<div class="searchescortform"<?php if($err && $_POST['action'] == "search") { echo ' style="display: block;"'; } ?>>
				<?php closebtn(); ?>
				<?php
				$search_page = "yes";
				if ( $err && $_POST['action'] == 'search') { echo "<div class=\"err rad25\">$err</div>"; }
			    include (get_template_directory() . '/blacklisted-escorts-form.php');
				unset($search_page);
				?>
			</div> <!-- SEARCH B ESCORT FORM -->

			<div class="show-profiles">
			<?php if (count($search_query) > "0") { ?>
				<?php
				$args = array(
					'post_type' => 'b'.$taxonomy_profile_url,
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'orderby' => 'title',
					'order' => 'ASC',
					'meta_query' => $search_query
				);
				query_posts( $args );
				if ( have_posts() ) : ?>
				<table class="listagencies rad3">
					<tr class="trhead rad3">
						<th class="rad3"><?php _e('Name','escortwp'); ?></th>
				        <th class="rad3"><?php _e('Country','escortwp'); ?></th>
				        <th class="rad3"><?php _e('City','escortwp'); ?></th>
				        <th class="rad3"><?php _e('Date added','escortwp'); ?></th>
					</tr>
				<?php
				while ( have_posts() ) : the_post();
					$city = get_term(get_post_meta(get_the_ID(), 'city', true), $taxonomy_location_url);
					$city = $city->name;
					$country = get_term(get_post_meta(get_the_ID(), 'country', true), $taxonomy_location_url);
					$country = $country->name;
				?>
					<tr class="agencytr">
				    	<td><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></td>
				        <td><?php echo $country; ?></td>
				        <td><?php echo $city; ?></td>
				        <td><?php the_time("d F Y"); ?></td>
				    </tr>
				<?php endwhile; ?>
				</table>
				<?php
				else:
					printf(esc_html__('No %s found','escortwp'),$taxonomy_profile_name_plural);
				endif;
				wp_reset_query();
				?>
            <?php } else {
				$posts_per_page = "40";
				$args = array(
					'post_type' => 'b'.$taxonomy_profile_url,
					'posts_per_page' => $posts_per_page
				);
				query_posts($args);
				$i = 1;
				if ( have_posts() ) : while ( have_posts() ) : the_post();
					include (get_template_directory() . '/loop-show-profile.php');
				endwhile;
					$total = ceil($wp_query->found_posts / $posts_per_page);
					dolce_pagination($total, $paged);
				else:
					printf(esc_html__('No %s here yet','escortwp'),$taxonomy_profile_name_plural);
				endif;
				wp_reset_query();
				?>
    		<?php } ?>
    		</div> <!-- show profiles section -->
			<div class="clear"></div>
        </div> <!-- BODY BOX -->
    </div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>