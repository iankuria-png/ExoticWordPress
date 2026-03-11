<?php
/*
Template Name: Blacklisted Clients
*/

global $taxonomy_agency_url, $taxonomy_profile_url;
$current_user = wp_get_current_user();
if (!current_user_can('level_10') && get_option("escortid".$current_user->ID) != $taxonomy_agency_url && get_option("escortid".$current_user->ID) != $taxonomy_profile_url) {
	wp_redirect(get_bloginfo("url")); exit;
}


$err = ""; $ok = "";
if (isset($_POST['action']) && $_POST['action'] == 'add' && is_user_logged_in() && !isset($_POST['search'])) {
    $bcemail = $_POST['bcemail'];
	if (!$bcemail) { $err .= __('Please write the client\'s email','escortwp')."<br />"; } else {
		if ( !is_email($bcemail) ) { $err .= __('The emails seems to be wrong','escortwp')."<br />"; }
	}

    $bcphone = wp_strip_all_tags($_POST['bcphone'], true);
	if (!$bcphone) { $err .= __('Please add the client\'s phone number','escortwp')."<br />"; }
	if ($bcphone && strlen($bcphone) < 5) {
		$err .= __('The phone number must be at least 5 digits','escortwp')."<br />";
	}

    $bcnote = wp_strip_all_tags($_POST['bcnote'], true);
	if (!$bcnote) { $err .= __('Please add a note about the client','escortwp')."<br />"; }

	if ($_POST['clientid']) {
		$clientid = (int)$_POST['clientid'];

		$userid = $current_user->ID;

		$client = get_post($clientid);
		$client_author = $client->post_author;

		if ($client_author != $userid && !current_user_can('level_10')) {
			$err .= __('You are not allowed to edit this client','escortwp');
		}
	}

	if ( $err == "" ) {
		$blacklistclient_cat_id = term_exists( 'Blacklisted Clients', "category" );
		if (!$blacklistclient_cat_id) {
			$arg = array('description' => 'Blacklisted clients');
			wp_insert_term('Blacklisted Clients', "category", $arg);
			$blacklistclient_cat_id = term_exists( 'Blacklisted Clients', "category" );
		}
		$blacklistclient_cat_id = $blacklistclient_cat_id['term_id'];
		$blacklistclient = array(
			'post_title' => $current_user->display_name." - blacklisted client",
			'post_status' => 'publish',
			'post_author' => $current_user->ID,
			'post_category' => array($blacklistclient_cat_id),
			'post_type' => 'bclient',
			'ping_status' => 'closed'
		);
		if ($clientid) {
			$blacklistclient_id = $clientid;
		} else {
			$blacklistclient_id = wp_insert_post( $blacklistclient );
		}
		update_post_meta($blacklistclient_id, "email", $bcemail);
		update_post_meta($blacklistclient_id, "phone", $bcphone);
		update_post_meta($blacklistclient_id, "note", $bcnote);
		unset($bcemail, $bcphone, $bcnote);
		if ($clientid) {
			$ok = __('The client has been modified','escortwp');
		} else {
			$ok = __('The client has been added to the blacklist','escortwp');
		}
	}
} // if isset action

get_header(); ?>

	<div class="contentwrapper">
	<div class="body">
    	<div class="bodybox blacklisted-clients-page">
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					//delete a client from the blacklist
					$('.blacklistedclients .addedbuttons .button-delete').on('click', function(){
						var id = $(this).parent().attr('id');
						$('#client'+id).slideUp("slow");
						$.ajax({
							type: "GET",
							url: "<?php bloginfo('template_url'); ?>/ajax/delete-client.php",
							data: "id=" + id,
							success: function(data){
								$('.deletemsg').html(data).fadeIn("slow").delay(1500).fadeOut("slow");
							}
						});
					});

					//edit a client
					$('.blacklistedclients .addedbuttons .button-edit').on('click', function(){
						var id = $(this).parent().attr('id');
						$('.add_blacklist').slideUp('slow');
						$.ajax({
							type: "GET",
							url: "<?php bloginfo('template_url'); ?>/ajax/edit-client.php",
							data: "id=" + id,
							success: function(data){
								$('.edit_blacklist_form').html(data).parent().slideDown('slow');
							}
						});
					});

					//close search results
					$('.blacklisted_clients_search_results .closebtn').on('click', function(){
						$(this).parent().slideUp("slow");
					});
					//close search results
					$('.edit_blacklist .closebtn').on('click', function(){
						$(this).parent().slideUp("slow");
						$('.add_blacklist').slideDown('slow');
					});
				});
			</script>
        	<h3><?php _e('Blacklisted Clients','escortwp'); ?></h3>

			<?php if ($err) { echo "<div class=\"err rad25\">$err</div>"; } ?>
			<?php if ($ok) { echo "<div class=\"ok rad25\">$ok</div>"; } ?>

			<div class="add_blacklist">
				<?php include (get_template_directory() . '/blacklist-clients-form.php'); ?>
			</div>
			<div class="edit_blacklist hide">
				<?php closebtn(); ?>
				<div class="clear10"></div>
				<div class="edit_blacklist_form"></div>
			</div>

			<?php
			if (is_user_logged_in() && isset($_POST['search'])) {
			    $bcemail = $_POST['bcemail'];
				if ( $bcemail && !is_email($bcemail) ) { $err .= __('The email seems to be wrong','escortwp')."<br />"; }

			    $bcphone = wp_strip_all_tags($_POST['bcphone'], true);
				if ($bcphone && strlen($bcphone) < 5) {
					$err .= __('The phone number must be at least 5 digits long','escortwp')."<br />";
				}

				if (!$bcemail && !$bcphone) { $err .= __('Please write an email or a phone number for the search','escortwp')."<br />"; }

				if ($err) { echo "<div class=\"err rad25\">$err</div>"; }
				if ($ok) { echo "<div class=\"ok rad25\">$ok</div>"; }

				if (!$err) {
					$email_array = array( 'key' => 'email', 'value' => $bcemail, 'compare' => '=' );
					$phone_array = array( 'key' => 'phone', 'value' => $bcphone, 'compare' => '=' );

					if ($bcemail && $bcphone) {
						$meta_query = array($email_array, $phone_array);
					} elseif (!$bcemail && $bcphone) {
						$meta_query = array($phone_array);
					} elseif ($bcemail && !$bcphone) {
						$meta_query = array($email_array);
					}

					$args = array(
						'post_type' => 'bclient',
						'post_status' => 'publish',
						'posts_per_page' => 10,
						'meta_query' => $meta_query
					);
					$all = new WP_Query( $args ); ?>
					<div class="blacklisted_clients_search_results">
						<div class="clear20"></div>
						<h4 class="l"><?php _e('Search results','escortwp'); ?>:</h4>
						<?php closebtn(); ?>
						<div class="clear10"></div>

						<?php if ($all->have_posts()) : ?>
						<div class="addedblacklistedclients">
							<div class="blacklistedclients clienthead">
								<div class="addedemail"><?php _e('Email','escortwp'); ?></div>
						    	<div class="addedphone"><?php _e('Phone','escortwp'); ?></div>
							    <div class="addednote"><?php _e('Note','escortwp'); ?></div>
							</div>
						<?php while ( $all->have_posts() ) : $all->the_post(); ?>
							<div class="blacklistedclients" id="client<?php the_ID(); ?>">
								<div class="addedemail"><?php echo get_post_meta(get_the_ID(),'email', true); ?></div>
						    	<div class="addedphone"><?php echo get_post_meta(get_the_ID(),'phone', true); ?></div>
							    <div class="addednote">
							    	<?php if (current_user_can('level_10')) { ?>
							    		<div class="addedbuttons">
							    			<span class="icon button-edit icon-pencil"></span>
							    			<span class="icon button-delete icon-cancel"></span>
							    		</div>
							    	<?php } ?>
							    	<?php echo get_post_meta(get_the_ID(),'note', true); ?>
							    </div>
							</div>
						<?php endwhile; ?>
						</div><div class="clear20"></div>
						<?php else:
						_e('No results found','escortwp');
						endif; wp_reset_query(); ?>
					</div> <!-- BLACKLISTED CLIENTS SEARCH RESULTS-->
					<?php
					unset($bcemail, $bcphone, $bcnote);
				} // if no error
			} // if isset search
			?>
		</div> <!-- bodybox -->

		<?php
		$args = array(
			'author' => $current_user->ID,
			'post_type' => 'bclient',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'date',
			'order' => 'ASC'
		);
		query_posts( $args );
		if ( have_posts() ) : ?>
			<div class="bodybox blacklisted-clients-page">
				<h4 class="l"><?php _e('Clients you blacklisted','escortwp'); ?>:</h4><div class="deletemsg r"></div>
				<div class="clear10"></div>
				<div class="addedblacklistedclients">
					<div class="blacklistedclients clienthead pinkdegrade">
						<div class="addedemail"><?php _e('Email','escortwp'); ?></div>
				    	<div class="addedphone"><?php _e('Phone','escortwp'); ?></div>
					    <div class="addednote"><?php _e('Note','escortwp'); ?></div>
				        <div class="addedbuttons"></div>
					</div>
				<?php while ( have_posts() ) : the_post(); ?>
					<div class="blacklistedclients" id="client<?php the_ID(); ?>">
						<div class="addedemail"><?php echo get_post_meta(get_the_ID(),'email', true); ?></div>
				    	<div class="addedphone"><?php echo get_post_meta(get_the_ID(),'phone', true); ?></div>
					    <div class="addednote">
					    	<div class="addedbuttons" id="<?php the_ID(); ?>"><span class="icon button-edit icon-pencil"></span><span class="icon button-delete icon-cancel"></span></div>
					    	<?php echo get_post_meta(get_the_ID(),'note', true); ?>
					    </div>
					</div>
				<?php endwhile; ?>
				</div> <!-- addedblacklistedclients -->
                <div class="clear"></div>
            </div> <!-- BODY BOX -->
		<?php endif; wp_reset_query(); ?>
    </div> <!-- BODY -->
    </div> <!-- contentwrapper -->

	<?php get_sidebar("left"); ?>
	<?php get_sidebar("right"); ?>
	<div class="clear"></div>

<?php get_footer(); ?>