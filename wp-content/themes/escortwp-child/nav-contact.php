<?php
/*
Template Name: Contact page
*/

$err = '';
$ok = '';
$field_errors = array(
	'name' => '',
	'email' => '',
	'message' => '',
);

$contactformname = '';
$contactformemail = '';
$contactformwebsite = '';
$contactformmess = '';

if (is_user_logged_in() && !isset($_POST['action'])) {
	$current_user = wp_get_current_user();
	$contactformname = $current_user->display_name;
	$contactformemail = $current_user->user_email;
}

if (isset($_POST['action']) && $_POST['action'] === 'contactus') {
	$honeypot = isset($_POST['emails']) ? trim((string) wp_unslash($_POST['emails'])) : '';
	if ($honeypot !== '') {
		$err .= '.';
	}

	$contactformname = isset($_POST['contactformname']) ? trim(wp_strip_all_tags(wp_unslash($_POST['contactformname']))) : '';
	if ($contactformname === '') {
		$field_errors['name'] = __('Your name is missing', 'escortwp');
	}

	$contactformemail = isset($_POST['contactformemail']) ? trim((string) wp_unslash($_POST['contactformemail'])) : '';
	if ($contactformemail === '') {
		$field_errors['email'] = __('Your email is missing', 'escortwp');
	} elseif (!is_email($contactformemail)) {
		$field_errors['email'] = __('The email address seems to be wrong', 'escortwp');
	}

	$contactformwebsite = isset($_POST['contactformwebsite'])
		? substr(trim(wp_strip_all_tags(wp_unslash($_POST['contactformwebsite']))), 0, 200)
		: '';

	$contactformmess = isset($_POST['contactformmess'])
		? substr(trim(stripslashes(wp_kses(wp_unslash($_POST['contactformmess']), array()))), 0, 5000)
		: '';
	if ($contactformmess === '') {
		$field_errors['message'] = __('You need to write a message', 'escortwp');
	}

	foreach ($field_errors as $field_error) {
		if ($field_error !== '') {
			$err .= $field_error . '<br />';
		}
	}

	if (get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && get_option('recaptcha1')) {
		$recaptcha_error = verify_recaptcha();
		if (!empty($recaptcha_error)) {
			$err .= $recaptcha_error;
		}
	}

	if (!$err) {
		$body = __('Hello', 'escortwp') . ',<br /><br />' . __('Someone sent you a message from', 'escortwp') . ' ' . get_option('email_sitename') . ':<br /><br />
' . __('Sender information', 'escortwp') . ':<br />
' . __('Name', 'escortwp') . ': <b>' . esc_html($contactformname) . '</b><br />
' . __('Email', 'escortwp') . ': <b>' . esc_html($contactformemail) . '</b><br />
' . __('Website', 'escortwp') . ': <b>' . esc_html($contactformwebsite) . '</b><br />
' . __('Message', 'escortwp') . ':<br />' . nl2br(esc_html($contactformmess)) . '<br /><br />
' . __('You can send a message back to this person by replying to this email', 'escortwp') . '.';

		dolce_email(
			$contactformname,
			$contactformemail,
			get_bloginfo('admin_email'),
			__('Contact message from', 'escortwp') . ' ' . get_option('email_sitename'),
			$body,
			$contactformmess
		);
		$ok = __('Message sent', 'escortwp');

		$contactformwebsite = '';
		$contactformmess = '';
	}
}

get_header();
?>

<div class="contentwrapper contact-shell">
	<div class="body">
		<div class="bodybox contact-hero">
			<header class="contact-hero__header">
				<h1><?php esc_html_e('Contact us', 'escortwp'); ?></h1>
				<p><?php esc_html_e('Need help with listings, verification, or your account? Send your message and our team normally responds within one business day.', 'escortwp'); ?></p>
			</header>
			<?php if (have_posts()): ?>
				<div class="contact-hero__content">
					<?php while (have_posts()): the_post(); ?>
						<?php the_content(); ?>
						<?php edit_post_link(__('Click to add some text here', 'escortwp'), '<br />', ''); ?>
					<?php endwhile; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="bodybox contact-form-shell">
			<?php
			$status_class = '';
			$status_message = '';
			if (!empty($err)) {
				$status_class = 'is-error';
				$status_message = $err;
			} elseif (!empty($ok)) {
				$status_class = 'is-success';
				$status_message = $ok;
			}
			?>
			<div class="contact-form__status <?php echo esc_attr($status_class); ?>" role="status" aria-live="polite" data-contact-status>
				<?php echo wp_kses_post($status_message); ?>
			</div>

			<form action="<?php echo esc_url(get_permalink(get_the_ID())); ?>" method="post" class="form-styling contact-form" data-contact-form>
				<input type="hidden" name="action" value="contactus" />
				<input type="text" name="emails" value="" autocomplete="off" tabindex="-1" class="contact-honeypot" />

				<div class="contact-form__field">
					<label for="contactformname"><?php esc_html_e('Name', 'escortwp'); ?> <i>*</i></label>
					<input
						type="text"
						name="contactformname"
						id="contactformname"
						class="input"
						value="<?php echo esc_attr($contactformname); ?>"
						required
						aria-required="true"
						aria-invalid="<?php echo $field_errors['name'] ? 'true' : 'false'; ?>"
						aria-describedby="contactformname-error" />
					<p id="contactformname-error" class="contact-form__error"><?php echo esc_html($field_errors['name']); ?></p>
				</div>

				<div class="contact-form__field">
					<label for="contactformemail"><?php esc_html_e('Email', 'escortwp'); ?> <i>*</i></label>
					<input
						type="email"
						name="contactformemail"
						id="contactformemail"
						class="input"
						value="<?php echo esc_attr($contactformemail); ?>"
						required
						aria-required="true"
						aria-invalid="<?php echo $field_errors['email'] ? 'true' : 'false'; ?>"
						aria-describedby="contactformemail-error" />
					<p id="contactformemail-error" class="contact-form__error"><?php echo esc_html($field_errors['email']); ?></p>
				</div>

				<div class="contact-form__field">
					<label for="contactformwebsite"><?php esc_html_e('Website', 'escortwp'); ?></label>
					<input
						type="url"
						name="contactformwebsite"
						id="contactformwebsite"
						class="input"
						value="<?php echo esc_attr($contactformwebsite); ?>"
						placeholder="https://" />
				</div>

				<div class="contact-form__field">
					<label for="contactformmess"><?php esc_html_e('Message', 'escortwp'); ?> <i>*</i></label>
					<textarea
						name="contactformmess"
						id="contactformmess"
						class="textarea"
						rows="7"
						required
						aria-required="true"
						aria-invalid="<?php echo $field_errors['message'] ? 'true' : 'false'; ?>"
						aria-describedby="contactformmess-error"><?php echo esc_textarea($contactformmess); ?></textarea>
					<p id="contactformmess-error" class="contact-form__error"><?php echo esc_html($field_errors['message']); ?></p>
					<small><?php esc_html_e('HTML code will be removed.', 'escortwp'); ?></small>
				</div>

				<?php if (get_option('recaptcha_sitekey') && get_option('recaptcha_secretkey') && get_option('recaptcha1')): ?>
					<div class="contact-form__field">
						<div class="g-recaptcha" data-sitekey="<?php echo esc_attr(get_option('recaptcha_sitekey')); ?>"></div>
					</div>
				<?php endif; ?>

				<div class="contact-form__actions">
					<button type="submit" name="submit" class="pinkbutton rad3 contact-form__submit" data-contact-submit>
						<span class="contact-form__submit-label"><?php esc_html_e('Send message', 'escortwp'); ?></span>
						<span class="contact-form__submit-loading" aria-hidden="true"><?php esc_html_e('Sending...', 'escortwp'); ?></span>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php get_sidebar('left'); ?>
<?php get_sidebar('right'); ?>
<div class="clear"></div>

<?php get_footer(); ?>
