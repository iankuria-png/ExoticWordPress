<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }
?>
<?php if ( $err && $_POST['action'] == 'addclassifiedad') { echo "<div class=\"err rad25\">$err</div>"; } ?>
<form action="<?php echo get_permalink(get_the_ID()); ?>" method="post" class="form-styling">
    <small class="mandatory l"><?php _e('Fields marked with <i>*</i> are mandatory','escortwp'); ?></small>
    <div class="clear20"></div>

    <input type="hidden" name="action" value="addclassifiedad" />
    <div class="form-label">
        <label for="classifiedadtype"><?php _e('Classified ad type','escortwp'); ?><i>*</i></label>
    </div>
    <div class="form-input">
        <?php _e('I am','escortwp'); ?> &nbsp;
        <select name="classifiedadtype" id="classifiedadtype">
    	   <option value="offering"<?php if ($classifiedadtype == "offering") { echo ' selected="selected"'; }?>><?php _e('offering','escortwp'); ?></option>
            <option value="looking"<?php if ($classifiedadtype == "looking") { echo ' selected="selected"'; }?>><?php _e('looking','escortwp'); ?></option>
        </select>
    </div> <!-- classifiedadtype --> <div class="formseparator"></div>

    <div class="form-label">
	   <label for="title"><?php _e('Title','escortwp'); ?><i>*</i></label>
    </div>
    <div class="form-input">
        <input type="text" name="adtitle" id="title" class="input longinput" value="<?php echo $title; ?>" />
    </div> <!-- title --> <div class="formseparator"></div>

    <div class="form-label">
        <label for="description"><?php _e('Description','escortwp'); ?><i>*</i></label>
    </div>
    <div class="form-input">
        <textarea name="description" id="description" class="textarea longtextarea" rows="7"><?php echo $description; ?></textarea>
        <small><?php _e('html code will be removed','escortwp'); ?></small>
	</div> <!-- description --> <div class="formseparator"></div>

    <div class="clear20"></div>
    <div class="mandatory"><?php _e('At least one of the fields below should be filled in','escortwp'); ?></div>
    <div class="clear10"></div>

    <div class="form-label">
		<label for="classifiedademail"><?php _e('Email','escortwp'); ?></label>
    </div>
    <div class="form-input">
        <input type="email" name="classifiedademail" id="classifiedademail" class="input longinput" value="<?php echo $classifiedademail; ?>" />
    </div> <!-- email --> <div class="formseparator"></div>

    <div class="form-label">
		<label for="classifiedadphone"><?php _e('Phone','escortwp'); ?></label>
    </div>
    <div class="form-input">
        <input type="text" name="classifiedadphone" id="classifiedadphone" class="input longinput" value="<?php echo $classifiedadphone; ?>" />
    </div> <!-- phone --> <div class="formseparator"></div>

    <div class="text-center"><input type="submit" name="submit" value="<?php if ($single_page) { _e('Update Classified Ad','escortwp'); } else { _e('Post this ad','escortwp'); }?>" class="pinkbutton rad3" /></div> <!--center-->
</form>