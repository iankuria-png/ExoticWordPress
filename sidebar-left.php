<?php
if (!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set('display_errors', error_reporting);
if (error_reporting == '1') { error_reporting(E_ALL); }
if (isdolcetheme !== 1) { die(); }

global $taxonomy_profile_name_plural, $taxonomy_location_url;
?>

<div class="sidebar-left l">

	<div class="countries sidebarcountries location-filter">
		<div class="location-header">
			<h4>Locations</h4>
			<p class="location-subcopy">Find escorts by county or city</p>
			<div class="location-actions">
				<button type="button" class="location-toggle location-expand">Expand all</button>
				<button type="button" class="location-toggle location-collapse">Collapse</button>
			</div>
		</div>
        <ul class="country-list location-list">
			<?php
			$args = array(
				'show_option_all' => '',
				'show_count' => '0',
				'hide_empty' => '0',
				'title_li' => '',
				'show_option_none' => '',
				'pad_counts' => '0',
				'taxonomy' => $taxonomy_location_url
			);
			wp_list_categories($args);
			?>
        </ul>
		<div class="clear"></div>
	</div>

	<style>
		.seefromcss {
			position: fixed;
			top: 25% !important;
			right: -160px;
			cursor: pointer;
			background-color: #ff0000;
			color: #fff;
			padding: 5px 10px 8px 5px;
			z-index: 999;
			transform: rotate(90deg);
			transform-origin: left top 0;
		}
		.slidercountries {
			display: none;
			width: 245px !important;
			position: fixed;
			top: 25% !important;
			right: -250px;
			height: 250px;
			overflow-y: scroll !important;
			box-shadow: 1px 2px 2px #524f4f;
			z-index: 999;
		}
		.close-country {
			float: right;
			font-weight: bold;
			cursor: pointer;
		}
		@media (max-width: 520px) {
			.seefromcss { top: 20% !important; }
			.slidercountries { top: 20% !important; }
		}
		@media screen and (max-width: 640px){
			.mobile-menu-div { display: flex; }
			.countries.slidercountries {
				display: none;
				width: 94% !important;
				height: 94% !important;
				top: 3% !important;
				left: 3%;
				z-index: 999999999;
				border: 2px solid #346CA5;
			}
		}
	</style>

	<!-- <span class="icon button-delete icon-search rad3 seefromcss"> Escorts Near Me</span> -->

	<div class="countries slidercountries" id="slidercountries" style="display:none;">
		<a class="close-country">X</a>
		<h4>Choose a Location</h4>
		<ul class="country-list location-list">
			<?php wp_list_categories($args); ?>
		</ul>
		<div class="clear"></div>
	</div>

	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(".seefromcss").click(function(){
			var pos = $(this).position();
			var $q = $(".slidercountries");
			var doc_width = $(document).width();
			var from_right = parseFloat(doc_width) - parseFloat(pos.left);
			if(from_right > 50) {
				$(this).animate({right:"-160px"},1500);
				$q.animate({right:"-250px"},1500);
				$(this).removeClass('icon-cancel').addClass('icon-search');
			} else {
				$(this).animate({right:"85px"},1500);
				$q.animate({right:"1px"},1500);
				$(this).removeClass('icon-search').addClass('icon-cancel');
			}
		});
	});
	</script>

	<div class="clear"></div>

	<?php if (is_active_sidebar('widget-sidebar-left') || current_user_can('level_10')) : ?>
	<div class="widgetbox-wrapper">
		<?php if (!dynamic_sidebar('Sidebar Left') && current_user_can('level_10')) : ?>
			<?php _e('Go to your','escortwp'); ?> <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e('widgets page','escortwp'); ?></a> <?php _e('to add content here','escortwp'); ?>.
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php // dynamic_sidebar('Left Ads'); ?>
</div>
