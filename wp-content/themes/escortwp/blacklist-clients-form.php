<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }
?>
<form action="<?php echo get_permalink(get_option('escort_blacklist_clients_page_id')); ?>" method="post" class="form-styling" novalidate>
	<input type="hidden" name="action" value="add" />
	<?php if ($editclient == "yes") { ?>
	<input type="hidden" name="clientid" value="<?php echo $clientid; ?>" />
	<?php } ?>
    <div class="clear10"></div>
	<div class="form-label">
		<label for="bcemail"><?php _e('Client email','escortwp'); ?></label>
	</div>
	<div class="form-input">
		<input type="email" id="bcemail" class="input longinput" name="bcemail" value="<?php echo $bcemail; ?>" autocomplete="off" />
	</div> <!-- email --> <div class="formseparator"></div>

	<div class="form-label">
		<label for="bcphone"><?php _e('Client phone number','escortwp'); ?></label>
	</div>
	<div class="form-input">
		<input type="tel" id="bcphone" class="input longinput" name="bcphone" value="<?php echo $bcphone; ?>" autocomplete="off" />
	</div> <!-- phone --> <div class="formseparator"></div>
	
	<div class="form-label">
		<label for="bcnote"><?php _e('Add a short note','escortwp'); ?></label>
	</div>
	<div class="form-input">
		<input type="text" id="bcnote" class="input longinput" name="bcnote" value="<?php echo $bcnote; ?>" />
	</div> <!-- note --> <div class="formseparator"></div>

    <div class="clear10"></div>
    <div class="text-center"><input type="submit" name="submit" value="<?php if ($editclient == "yes") { _e('Update Blacklisted Client','escortwp'); } else { _e('Add Client to Blacklist','escortwp'); } ?>" class="pinkbutton rad3" /><?php if (!$editclient) { ?> <?php _e('or','escortwp') ?> <input type="submit" name="search" value="<?php _e('Search for this Client','escortwp'); ?>" class="blueishbutton rad3" style="border: 1px solid #E0006C" /><?php } ?></div> <!--center-->
</form>