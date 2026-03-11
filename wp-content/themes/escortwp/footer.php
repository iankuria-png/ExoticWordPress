<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $taxonomy_profile_name;
?>
	<div class="clear10"></div>

	<?php if ( is_active_sidebar('widget-footer') || current_user_can('level_10')) : ?>
	<div class="footer">
		<?php if ( !dynamic_sidebar('Footer') && current_user_can('level_10')) : ?>
		<div class="widgetbox rad3 placeholder-widgettext">
			<?php _e('Go to your','escortwp'); ?> <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e('widgets page','escortwp'); ?></a> <?php _e('to add content here','escortwp'); ?>.
		</div> <!-- widgetbox -->
		<?php endif; ?>
        <div class="clear"></div>
	</div> <!-- FOOTER -->
	<?php endif; ?>

    <div class="underfooter">
		<div>
			&copy; <?php echo date('Y'); ?> <?php bloginfo('site_name'); ?>
		</div><div class="clear"></div>
	</div>
</div> <!-- ALL -->
<?php wp_footer(); ?>
<?php
if (escortwp_age_gate_should_render()) {
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		if(Cookies.get('tos18') == "yes") {
			$(".tosdisclaimer-overlay, .tosdisclaimer-wrapper").hide();
		}
		$('.entertosdisclaimer').on('click', function(){
			$(".tosdisclaimer-overlay, .tosdisclaimer-wrapper").fadeOut('150');
			Cookies.set('tos18', 'yes', { expires: 60 });
		});
		$('.closetosdisclaimer').on('click', function(){
			window.location = "https://www.google.com/";
		});
	});
</script>
<?php
	include (get_template_directory() . '/footer-tos-18years-agreement-overlay.php');
} // if $_COOKIE != yes
?>
</body>
</html>
<!--
Lovers can see to do their amorous rites
By their own beauties; or, if love be blind,
It best agrees with night. Come, civil night,
-->
