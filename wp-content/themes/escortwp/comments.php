<?php
if(!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set( 'display_errors', error_reporting );
if(error_reporting == '1') { error_reporting( E_ALL ); }
if(isdolcetheme !== 1) { die(); }
?>

<div class="comments"><a name="comments"></a>
<?php if ($comments) : ?>
    <div class="clear20"></div>
    <div class="commentlistall">
        <?php wp_list_comments( array( 'callback' => 'theme_comments', 'style' => 'div', 'type' => 'comment') ); ?>
        <div class="clear"></div>
	</div>

<?php else : // or, if we don't have comments:

	/* If there are no comments and comments are closed,
	 * let's leave a little note, shall we?
	 */
	if ( ! comments_open() ) :
?>
	<?php // If comments are open, but there are no comments. ?>
	<h4 class="commentscount"><?php _e('No one commented yet. Be the first.','escortwp'); ?></h4>
<?php endif; // end ! comments_open() ?>

<?php endif; // end have_comments() ?>



<?php if ('open' == $post->comment_status) : ?>

<?php //comment_form(); ?>


            <div class="clear20"></div>
            <div class="commform" id="respond"><a name="respond"></a>
			<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
            	<div class="clear10"></div>
<?php if ( !$user_ID ) : ?>
                <div class="commname l">
                	<span class="icon icon-user"></span> <?php _e('Name','escortwp'); ?><br />
                    <input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" class="comminput" tabindex="1" />
                </div> <!-- commanme -->
                <div class="commemail r">
                	<span class="icon icon-mail"></span> <?php _e('Email','escortwp'); ?><br />
                    <input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" class="comminput" tabindex="2" />
                </div> <!-- commemail -->
                <div class="clear10"></div>
<?php endif; ?>
	            <div class="commtext col-100 rad5">
                	<?php _e('Your comment','escortwp'); ?>
                    <div class="clear"></div>
                    <textarea name="comment" id="comment" class="commtextarea" tabindex="3" rows="20" cols="20"></textarea>
                </div> <!-- TEXT -->
                <div class="clear10"></div>
                <div class="text-center"><input type="submit" class="pinkbutton commsubmitbutton rad25" name="submit" value="<?php _e('Submit Comment','escortwp'); ?>" tabindex="4" /></div>
				<?php $replytoid = isset($_GET['replytocom']) ? (int) $_GET['replytocom'] : 0; ?>
				<input type='hidden' name='comment_parent' id='comment_parent' value='<?php echo $replytoid; ?>' />
                <input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
                <?php do_action('comment_form', $post->ID); ?>
                <a rel="nofollow" id="cancel-comment-reply-link" href="#respond" class="cancel-comment-reply-link rad5" style="display: none;"><?php _e('Cancel reply','escortwp'); ?></a>
            </form>
            </div> <!-- COMMENTS FORM -->
<?php endif; // if you delete this the sky will fall on your head ?>

</div> <!-- COMMENTS -->