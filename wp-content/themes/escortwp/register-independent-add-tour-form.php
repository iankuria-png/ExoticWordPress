<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }

global $taxonomy_location_url, $taxonomy_agency_url;
if(payment_plans('tours','price') && !$edittour) {
	echo "<div class=\"ok rad25\">".__('Adding a tour will cost','escortwp')." ".format_price('tours', "small")."<br />
	".__('Your tour will automatically be activated after you complete the payment','escortwp')."</div><div class=\"clear20\"></div>";
}
?>

<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/css/jquery.ui.all.css" />
<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/css/jquery.ui.datepicker-custom.css" />
<?php
wp_enqueue_script("jquery-ui-core");
wp_enqueue_script("jquery-ui-widget");
wp_enqueue_script("jquery-ui-datepicker");
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $( "#start" ).datepicker({
		dateFormat: 'dd/mm/yy',
		changeMonth: true,
		changeYear: true,
		constrainInput: true,
		gotoCurrent: true,
		minDate: 0,
		firstDay: 1,
		showButtonPanel: true,
		onClose: function() {
			var date = $(this).datepicker('getDate');
			if (date){
				date.setDate(date.getDate() + 1);
				$("#end").datepicker( "option", "minDate", date ).focus();
				$('.mobile-menu').hide('fast');
			}
		}
	});
    $( "#end" ).datepicker({
		dateFormat: 'dd/mm/yy',
		changeMonth: true,
		changeYear: true,
		constrainInput: true,
		gotoCurrent: true,
		//minDate: 0,
		firstDay: 1,
		showButtonPanel: true
	});
});
</script>
<?php
if ($is_escort_page == "yes") {
	$form_url = get_permalink($escort_post_id_for_tours);
} else {
	$form_url = get_permalink(get_option('escort_tours_page_id'));
}
if(!isset($edittour)) $edittour = "";
?>
<form action="<?php echo $form_url; ?>" method="post" class="form-styling add-tour-form<?=$edittour ? '2' : ''?>" novalidate>
	<input type="hidden" name="action" value="<?php if($edittour) { echo "edittour"; } else { echo "addtour"; } ?>" />
	<?php if ($edittour == "yes") { ?>
	<input type="hidden" name="tourid" value="<?php echo $tourid; ?>" />
	<?php } elseif ($userstatus == $taxonomy_agency_url || current_user_can('level_10')) { ?>
	<input type="hidden" name="belongstoescortid" value="<?php the_ID(); ?>" />
	<?php } ?>
	
   	<?php if(get_option('locationdropdown') == "1") { ?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			//get cities from the selected country in the countries dropdown
			var c = ".tourcountry";
			var parent_div = ".add-tour-form<?=$edittour? '2' : ''?>";
			<?php if(showfield('state')) { ?>
				var city_div = '.inputstates';

				var state_c = '.tourstate';
				var state_div = '.inputcities';
			<?php } else { ?>
				var city_div = '.inputcities';
			<?php } ?>

			$(parent_div+' '+c).change(function(){ show_search_cities(c); });
			function show_search_cities(e) {
				var country = $(parent_div+' '+e).val();
				$(parent_div+' '+city_div).text($(city_div).data('text'));
				<?php if(showfield('state')) { ?>
					$(parent_div+' '+state_div).text($(state_div).data('text'));
				<?php } ?>

				if(country < 1) return true;

				loader($(e).parents(parent_div).find(city_div));
				$.ajax({
					type: "GET",
					url: "<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
					<?php if(showfield('state')) { ?>
						data: "id=" + country +"&hide_empty=0&state=yes&is_tour=yes&select2=yes",
					<?php } else { ?>
						data: "id=" + country +"&hide_empty=0&is_tour=yes&select2=yes",
					<?php } ?>
					success: function(data){
						$(e).parents(parent_div).find(city_div).html(data);
						if($(window).width() > "960") { $('.select2').select2(); }
					}
				});
			}

			<?php if(showfield('state')) { ?>
				$(parent_div).on("change", state_c, function(){
					show_search_cities_when_states(state_c);
				});
				function show_search_cities_when_states(e) {
					var state = $(parent_div+' '+e).val();
					$(parent_div+' '+state_div).text($(state_div).data('text'));
					if(state < 1) {
						return true;
					}

					loader($(e).parents(parent_div).find(state_div));
					$.ajax({
						type: "GET",
						url: "<?php bloginfo('template_url'); ?>/ajax/get-cities.php",
						data: "id=" + state +"&hide_empty=0&is_tour=yes&select2=yes",
						success: function(data){
							$(e).parents(parent_div).find(state_div).html(data);
							if($(window).width() > "960") { $('.select2').select2(); }
						}
					});
				}
			<?php } // if showfield('state') ?>
		});
	</script>
	<?php } // if the city and state need to be dropdowns ?>
	<div class="form-label">
		<label for="tourcountry"><?php _e('Country','escortwp'); ?><i>*</i></label>
	</div>
	<div class="form-input">
		<?php
		if(!isset($tourcountry)) $tourcountry = "";
		$args = array(
			'show_option_none'   => __('Select country','escortwp'),
			'hide_empty'         => 0,
			'echo'               => 1,
			'selected'           => $tourcountry,
			'hierarchical'       => 1,
			'name'               => 'tourcountry',
			'id'                 => '',
			'class'              => 'tourcountry select2',
			'depth'              => 1,
		    'orderby'            => 'name',
		    'order'              => 'ASC',
			'taxonomy'           => $taxonomy_location_url );

		$categories_count_array = array(
			'show_option_all' => '',
			'show_count' => '0',
			'hide_empty' => '1',
			'show_option_none' => '',
			'pad_counts' => '0',
			'taxonomy' => $taxonomy_location_url,
			'parent' => 0,
			'number' => '2',
			'fields' => 'ids'
		);
		$categories_count_data = get_categories($categories_count_array);
		if(count($categories_count_data) == "1") {
			unset($categories_count_array['fields']);
			$country_list = get_categories($categories_count_array);
			$country_list = array_values($country_list);
			echo '<div class="clear10"></div>'.$country_list[0]->name;
			echo '<input type="hidden" name="tourcountry" class="tourcountry" value="'.$country_list[0]->term_id.'" />';
			?>
			<script type="text/javascript"> jQuery(document).ready(function($) { $('.add-tour-form .tourcountry, .add-tour-form2 .tourcountry').trigger('change'); }); </script>
			<?php
		} else {
			wp_dropdown_categories($args);
		}
		$city_parent = $tourcountry;
		?>
    </div> <!-- tourcountry --> <div class="formseparator"></div>

	<?php if(showfield('state')) { ?>
	<div class="form-label">
		<label for="state"><?php _e('State','escortwp'); ismand('state'); ?></label>
	</div>
	<div class="form-input inputstates" data-text="<?=__('Please select a country first','escortwp')?>">
		<?php if(get_option('locationdropdown') == "1") {
				if($tourcountry > 0) {
					$tourcity_parent = $tourstate;
					$args = array(
						'show_option_all'    => '',
						'show_option_none'   => __('Select State','escortwp'),
						'show_last_update'   => 0,
						'show_count'         => 0,
						'parent'			 => $tourcountry,
						'hide_empty'         => 0,
						'exclude'            => '',
						'echo'               => 1,
						'selected'           => $tourstate,
						'hierarchical'       => 1, 
						'name'               => 'tourstate',
						'id'                 => '',
						'class'              => 'tourstate select2',
						'depth'              => 1,
						'tab_index'          => 0,
					    'orderby'            => 'name',
					    'order'              => 'ASC',
						'taxonomy'           => $taxonomy_location_url );
					wp_dropdown_categories( $args );
				} else {
					_e('Please select a country first','escortwp');
				}
		} else { ?>
			<input type="text" name="tourstate" id="tourstate" class="input longinput" value="<?php echo $tourstate; ?>" />
		<?php } ?>
	</div> <!-- state --> <div class="formseparator"></div>
   	<?php } ?>

    <div class="form-label">
		<label for="city"><?php _e('City','escortwp'); ?> <i>*</i></label>
	</div>
	<?php
	if(showfield('state')) {
		$city_text = __('Please select a state first','escortwp');
	} else {
		$city_text = __('Please select a country first','escortwp');
	}
	?>
	<div class="form-input inputcities" data-text="<?=$city_text?>">
		<?php if(get_option('locationdropdown') == "1") {
			if(($tourcountry > 0 && !showfield('state')) || ($tourstate > 0 && showfield('state'))) {
				$args = array(
					'show_option_all'    => '',
					'show_option_none'   => __('Select City','escortwp'),
					'show_last_update'   => 0,
					'show_count'         => 0,
					'parent'			 => $tourcity_parent,
					'hide_empty'         => 0,
					'exclude'            => '',
					'echo'               => 1,
					'selected'           => $tourcity,
					'hierarchical'       => 1, 
					'name'               => 'tourcity',
					'id'                 => '',
					'class'              => 'tourcity select2',
					'depth'              => 1,
					'tab_index'          => 0,
				    'orderby'            => 'name',
				    'order'              => 'ASC',
					'taxonomy'           => $taxonomy_location_url );
				wp_dropdown_categories( $args );
			} else {
				echo $city_text;
			}
		} else {
			if(!isset($tourcity)) $tourcity = "";
			?>
			<input type="text" name="tourcity" id="tourcity" class="input longinput" value="<?php echo $tourcity; ?>" />
		<?php } ?>
	</div> <!-- city --> <div class="formseparator"></div>

	<div class="form-label">
		<label for="start"><?php _e('Start date','escortwp'); ?> <i>*</i></label>
	</div>
	<div class="form-input">
		<?php
		if(!isset($start)) $start = "";
		if(!isset($end)) $end = "";
		?>
		<input type="text" id="start" class="input ll-skin-melon" name="start" value="<?php if ($start) { echo date("d/m/Y", $start); } ?>" autocomplete="off" />
	</div> <!-- start date --> <div class="formseparator"></div>
	
	<div class="form-label">
		<label for="end"><?php _e('End date','escortwp'); ?> <i>*</i></label>
	</div>
	<div class="form-input">
		<input type="text" id="end" class="input" name="end" value="<?php if ($end) { echo date("d/m/Y", $end); } ?>" autocomplete="off" />
	</div> <!-- end date --> <div class="formseparator"></div>

	<div class="form-label">
		<label for="tourphone"><?php _e('Phone','escortwp'); ?> <i>*</i></label>
	</div>
	<div class="form-input">
		<?php
		if(!isset($tourphone)) $tourphone = "";
		?>
		<input type="text" id="tourphone" class="input longinput" name="tourphone" value="<?php echo $tourphone; ?>" />
	</div> <!-- phone --> <div class="formseparator"></div>

    <div class="clear10"></div>
    <div class="text-center"><input type="submit" name="submit" value="<?php if ($edittour == "yes") { _e('Update Tour','escortwp'); } else { _e('Add Tour','escortwp'); } ?>" class="pinkbutton center rad3" /></div> <!--center-->
</form>