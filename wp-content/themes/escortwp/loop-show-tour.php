<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $taxonomy_location_url;
$belongstoescortid = get_post_meta(get_the_ID(), "belongstoescortid", true);
$premium = get_post_meta($belongstoescortid, "premium", true);
$escort = get_post($belongstoescortid);
$linktitle = $escort->post_title;

$cityid = get_post_meta(get_the_ID(), "city", true);
$city = get_term( $cityid, $taxonomy_location_url);
$thumbclass = $premium == "1" ? " girlpremium" : '';

if($belongstoescortid != "") {

$location = array();
if($city) {
	$location[] = $city->name;

	$state = get_term($city->parent, $taxonomy_location_url);
	if($state) {
		$location[] = $state->name;

		$country = get_term($state->parent, $taxonomy_location_url);
		if(!is_wp_error($country)) {
			$location[] = $country->name;
		}
	}
}
?>
    <div class="girl tour-thumb" itemscope itemtype ="http://schema.org/Person">
		<div class="thumb rad3<?php echo $thumbclass; ?>">
			<div class="clear"></div>
			<div class="thumbwrapper">
        		<a href="<?php echo get_permalink($belongstoescortid) ?>" title="<?php echo $linktitle; ?>">
        			<?php
					if(is_array($videos) && count($videos) > 0) {
						echo '<span class="label-video"><img src="'.get_template_directory_uri().'/i/video-th-icon.png" alt="" /></span>';
					}
					?>
        			<div class="model-info">
						<?php echo get_escort_labels($belongstoescortid); ?>
						<div class="clear"></div>
						<div class="desc">
							<div class="girl-name" href="<?php the_permalink(); ?>" title="<?php echo $linktitle; ?>" itemprop="name"><?=$linktitle?></div>
							<div class="clear"></div>
							<span class="girl-desc-location" itemprop="homeLocation"><span class="icon-location"></span><?=implode(", ", $location)?></span>
							<div class="clear"></div>
				            <div class="tour rad3">
				                <i><small><?php _e('from','escortwp'); ?></small></i> <?php echo date("d/m/Y", get_post_meta(get_the_ID(),'start', true)); ?><br />
				                <i><small><?php _e('till','escortwp'); ?></small></i> <?php echo date("d/m/Y", get_post_meta(get_the_ID(),'end', true)); ?>
							</div>
						</div> <!-- desc -->
					</div> <!-- model-info -->
            		<img class="mobile-ready-img rad3" src="<?php echo get_first_image($belongstoescortid); ?>" data-responsive-img-url="<?php echo get_first_image($belongstoescortid, '4'); ?>" alt="<?php echo $imagealt; ?>" itemprop="image" />
					<?php if ($premium == "1") { echo '<div class="premiumlabel rad3"><span>'.__('PREMIUM','escortwp').'</span></div>'; } ?>
				</a>
				<div class="clear"></div>
			</div>
			<?php echo $agency_manage_escort_buttons; ?>
		</div> <!-- THUMB --> <div class="clear"></div>
    </div> <!-- GIRL/TOUR -->
<?php
} // if $belongstoescortid != ""
unset($premium_label, $verified_label, $new_label, $belongstoescortid, $premium, $thumbclass);
?>