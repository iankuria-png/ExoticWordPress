<?php
/*
Template Name: Edit Registration Form for Escorts
*/

$current_user = wp_get_current_user();
if (!current_user_can('level_10')) { wp_redirect(get_bloginfo("url")); exit; }

$err = ""; $ok = "";
$fields = get_option('regfieldsescort');
if (isset($_POST['action']) && $_POST['action'] == 'editregformescorts') {
	/*
	[key] 'inputname'
	[0] 'name'
	[1] 'showinreg'
	[2] 'mandatory'
	[3] 'useinsearch'

	LEGEND for showinreg, mandatory, useinsearch
	1 = yes
	2 = no
	3 = yes, can't edit
	4 = no, can't edit
	*/
	foreach($fields as $key=>$f) {
		if($fields[$key][1] != "3" && $fields[$key][1] != "4") {
			if($_POST[$key.'showinreg'] == "1") { $fields[$key][1] = '1'; } else { $fields[$key][1] = '2'; }
		}
		if($fields[$key][1] != "2") {
			if($fields[$key][2] != "3" && $fields[$key][2] != "4") {
				if($_POST[$key.'mandatory'] == "1") { $fields[$key][2] = '1'; } else { $fields[$key][2] = '2'; }
			}
			if($fields[$key][3] != "3" && $fields[$key][3] != "4") {
				if($_POST[$key.'useinsearch'] == "1") { $fields[$key][3] = '1'; } else { $fields[$key][3] = '2'; }
			}
		} else {
			if($fields[$key][2] != "3" && $fields[$key][2] != "4") {
				$fields[$key][2] = '2';
			}
			if($fields[$key][3] != "3" && $fields[$key][3] != "4") {
				$fields[$key][3] = '2';
			}
		}
	}

	if(!$err) {
		update_option("regfieldsescort", $fields);
		if(isset($_POST['reset'])) {
			$fields = $escortregfields;
			update_option("regfieldsescort", $fields);
		}
		$ok = "ok";
	}
}

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox edit-registration-content">
			<h3 class="settingspagetitle"><?php printf(esc_html__('Edit Registration Form for %s Profiles','escortwp'),ucwords($taxonomy_profile_name)); ?></h3>
            <div class="clear30"></div>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var elems = $('.ios-checkbox');
					elems.each(function(index, el) {
						var switchery = new Switchery(el, { color: '#41cb59', size: 'medium' });
					});
				});
			</script>

			<?php if ($err) { echo "<div class=\"err rad25\">$err</div>"; } ?>
			<?php if ($ok) { echo "<div class=\"ok rad25\">".__('Your settings have been saved','escortwp')."</div>"; } ?>
			<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post">
				<input type="hidden" name="action" value="editregformescorts" />
				<div class="clear10"></div>
				<table class="editregfields">
					<tr class="tabletop">
						<th><?php _e('Field name','escortwp'); ?></th>
						<th><?php _e('Show in registration page','escortwp'); ?></th>
						<th><?php _e('Mandatory','escortwp'); ?></th>
						<th><?php _e('Use in search page','escortwp'); ?></th>
					</tr>
					<?php
						$i = "1";
						foreach($fields as $key => $field) {
							$class = $i % 2 ? ' class="blip"' : "";
							echo '<tr'.$class.'>';
							echo '<td class="alignleft">'.__($field[0],'escortwp').'</td>';
							echo '<td>'.build_checkbox_edit_fields_page($field[1], $key, "1").'</td>';
							echo '<td>'.build_checkbox_edit_fields_page($field[2], $key, "2").'</td>';
							echo '<td>'.build_checkbox_edit_fields_page($field[3], $key, "3").'</td>';
							echo '</tr>';
							$i++; unset($class);
						}
					?>
				</table>

				<div class="clear20"></div>
				<div class="text-center">
					<input type="submit" name="submit" value="<?php _e('Save settings','escortwp'); ?>" class="submit-button pinkbutton rad25" />
					<div class="clear30"></div>
					<input type="submit" name="reset" value="<?php _e('Restore Defaults','escortwp'); ?>" class="redbutton rad25" />
				</div>
			</form>
			<div class="clear"></div>
		</div> <!-- BODY BOX -->
	</div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>