<div class="tosdisclaimer-overlay"></div>
<div class="tosdisclaimer-wrapper rad3">
	<div class="tosdisclaimer vcenter rad3">
		<h4><?=__('AGE VERIFICATION','escortwp')?></h4>
		<?php
	        if (get_option("sitelogo")) {
		        echo '<div class="disclaimer-logo"><img src="'.get_option('sitelogo').'" alt="'.get_bloginfo('name').'" /></div>';
			}
		?>
		<p><?=__('This website may contain nudity and sexuality, and is intended for a mature audience.','escortwp')?></p>
		<p><?=__('You must be 18 or older to enter.','escortwp')?></p>

		<div class="clear20"></div>
	    <div class="tosdisclaimerbuttons">
			<div class="rad25 greenbutton entertosdisclaimer"><?=__('I\'m 18 or older','escortwp')?></div>
			<div class="rad25 redbutton closetosdisclaimer"><?=__('Leave','escortwp')?></div>
		</div>
		<div class="clear20"></div>
	</div> <!-- TOS ALERT -->
</div> <!-- TOS WRAPPER -->
