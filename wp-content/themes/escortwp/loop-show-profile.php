<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $taxonomy_location_url;
$category = wp_get_post_terms(get_the_ID(), $taxonomy_location_url);

$linktitle = get_the_title();
$imagealt = get_the_title();

$premium = get_post_meta(get_the_ID(), "premium", true);
$thumbclass = ($premium == "1") ? " girlpremium" : "";

$location = array();
$city = wp_get_post_terms(get_the_ID(), $taxonomy_location_url);
if($city) {
	$location[] = $city[0]->name;

	$state = get_term($city[0]->parent, $taxonomy_location_url);
	if($state) {
		$location[] = $state->name;

		$country = get_term($state->parent, $taxonomy_location_url);
		if(!is_wp_error($country)) {
			$location[] = $country->name;
		}
	}
}

$videos = get_children( array('post_parent' => get_the_ID(), 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'video', 'numberposts' => '1') );
?>
    <div class="girl" itemscope itemtype ="http://schema.org/Person">
		<div class="thumb rad3<?php echo $thumbclass; ?>">
			<div class="thumbwrapper">
        		<a href="<?php the_permalink(); ?>" title="<?php echo $linktitle; ?>">
        			<?php
					if(count($videos) > 0) {
						echo '<span class="label-video"><img src="'.get_template_directory_uri().'/i/video-th-icon.png" alt="" /></span>';
					}
					?>
        			<div class="model-info">
						<?php echo get_escort_labels(get_the_ID()); ?>
						<div class="clear"></div>
						<div class="desc">
							<div class="girl-name" title="<?php echo $linktitle; ?>" itemprop="name"><?php the_title(); ?></div>
							<div class="clear"></div>
							<div class="girl-desc-location" itemprop="homeLocation"><span class="icon-location"></span><?=implode(", ", $location)?></div>
						</div> <!-- desc -->
					</div> <!-- model-info -->
					<img class="mobile-ready-img rad3" src="<?php echo get_first_image(get_the_ID(), '5'); ?>" srcset="<?php echo get_first_image(get_the_ID(), '5'); ?> 170w, <?php echo get_first_image(get_the_ID()); ?> 280w, <?php echo get_first_image(get_the_ID(), '4'); ?> 400w" data-responsive-img-url="<?php echo get_first_image(get_the_ID(), '5'); ?>" alt="<?php echo $imagealt; ?>" itemprop="image" />
					<?php
					if ($premium == "1") {
						echo '<div class="premiumlabel rad3"><span>'.__('PREMIUM','escortwp').'</span></div>';
					}
					?>

				</a>
				<div class="clear"></div>
			</div>
			<?php if(isset($agency_manage_escort_buttons)) echo $agency_manage_escort_buttons;; ?>
		</div> <!-- THUMB --> <div class="clear"></div>
    </div> <!-- GIRL -->
<?php
if($i % 5) { } else { echo '<div class="show-separator show5profiles clear"></div>'; }
if($i % 4) { } else { echo '<div class="show-separator show4profiles clear hide"></div>'; }
if($i % 3) { } else { echo '<div class="show-separator show3profiles clear hide"></div>'; }
if($i % 2) { } else { echo '<div class="show-separator show2profiles clear hide"></div>'; }
$i++;
unset($escort_label, $belongstoescortid, $class);
?>