<?php
/**
 * EscortWP Child Theme functions
 */


if ( ! defined( 'isdolcetheme' ) ) {
    define( 'isdolcetheme', 1 );
}

/** Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue parent & child styles, plus child custom script.
 */
function escortwp_child_enqueue_assets() {
	// Parent stylesheet
	wp_enqueue_style(
		'escortwp-parent-style',
		get_template_directory_uri() . '/style.css'
	);

	// Child stylesheet (depends on parent)
	wp_enqueue_style(
		'escortwp-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'escortwp-parent-style' ),
		wp_get_theme()->get( 'Version' )
	);

	// Child custom JavaScript
	wp_enqueue_script(
		'escortwp-child-custom-script',
		get_stylesheet_directory_uri() . '/js/custom-script.js',
		array( 'jquery' ),
		false,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'escortwp_child_enqueue_assets' );

/**
 * Register additional widget areas.
 */
function escortwp_child_register_widgets() {
	register_sidebar( array(
		'name'          => __( 'Footer - Home Only', 'escortwp' ),
		'id'            => 'footer-home-only',
		'before_widget' => '<div id="%1$s" class="widgetbox rad3 widget %2$s l">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="widgettitle">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => __( 'Header Ads', 'escortwp' ),
		'id'            => 'sidebar-id-header-ads',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => '',
	) );

	register_sidebar( array(
		'name'          => __( 'Box Ads', 'escortwp' ),
		'id'            => 'box-ads',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '',
		'after_title'   => '',
	) );
}
add_action( 'widgets_init', 'escortwp_child_register_widgets' );

/**
 * Instead of redeclaring get_escort_labels(), use gettext to swap VERIFIED → REAL PIC
 */
add_filter( 'gettext', function( $translated, $original, $domain ) {
	if ( 'VERIFIED' === $original && 'escortwp' === $domain ) {
		return 'REAL PIC';
	}
	return $translated;
}, 20, 3 );

// Yoast Sitemap override
function force_empty_escort_categories_in_sitemap( $terms, $taxonomy ) {
	if ( 'escorts-from' === $taxonomy ) {
		$all_terms = get_terms( array(
			'taxonomy'   => 'escorts-from',
			'hide_empty' => false, // include even empty
		) );
		if ( ! is_wp_error( $all_terms ) ) {
			return $all_terms;
		}
	}
	return $terms;
}
add_filter( 'wpseo_sitemap_exclude_empty_terms', '__return_false' ); // don’t drop empty terms
add_filter( 'wpseo_sitemap_entries_per_page', function() { return 5000; } );
add_filter( 'wpseo_get_terms', 'force_empty_escort_categories_in_sitemap', 10, 2 );

// Escort & Uploads 404 Redirects
add_action( 'template_redirect', function() {
	if ( is_404() ) {
		$request_uri = $_SERVER['REQUEST_URI'];
		if ( preg_match( '#^/escort/.*$#', $request_uri ) || preg_match( '#^/uploads/.*$#', $request_uri ) ) {
			wp_redirect( home_url(), 301 );
			exit;
		}
	}
} );

// Trigger update_counts.php on certain profile-visibility changes
add_action( 'init', 'trigger_update_counts_on_profile_visibility_change' );
function trigger_update_counts_on_profile_visibility_change() {
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['action'] ) ) {
		$action = $_POST['action'];
		if ( in_array( $action, array( 'activateprivateprofile', 'settoprivate' ), true ) ) {
			$script_path = '/home/exotickenya/update_counts.php'; // adjust path as needed
			if ( file_exists( $script_path ) ) {
				include $script_path;
				error_log( "update_counts.php triggered via action: {$action}" );
			} else {
				error_log( "update_counts.php not found at {$script_path}" );
			}
		}
	}
}

add_action('wp_footer', 'pass_user_id_to_js');
function pass_user_id_to_js() {
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        ?>
        <script type="text/javascript">
            var escortwp_user_id = <?php echo json_encode($current_user->ID); ?>;
        </script>
        <?php
    }
}

add_action('wp_enqueue_scripts', 'load_sweetalert2_script');
function load_sweetalert2_script() {
    wp_enqueue_script(
        'sweetalert2',
        'https://cdn.jsdelivr.net/npm/sweetalert2@11',
        array(), // no dependencies
        null,
        true     // load in footer
    );
}

// Recently Viewed: return cards HTML for given IDs (works for logged-out users too)
add_action('wp_ajax_nopriv_escortwp_recently_viewed', 'escortwp_recently_viewed_cards');
add_action('wp_ajax_escortwp_recently_viewed', 'escortwp_recently_viewed_cards');

function escortwp_recently_viewed_cards() {
	if ( empty($_POST['ids']) ) { wp_die(); }

	$ids_raw = preg_split('/\s*,\s*/', (string) $_POST['ids']);
	$ids     = array_values(array_unique(array_filter(array_map('intval', $ids_raw))));
	if ( empty($ids) ) { wp_die(); }

	// IMPORTANT: adjust this if your CPT slug differs
	global $taxonomy_profile_url;
	if ( empty($taxonomy_profile_url) ) {
		$taxonomy_profile_url = 'escort'; // fallback slug if global not set
	}

	$q = new WP_Query(array(
		'post_type'      => $taxonomy_profile_url,
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => count($ids),
		'post_status'    => 'publish',
		'no_found_rows'  => true,
	));

	ob_start();
	if ( $q->have_posts() ) {
		while ( $q->have_posts() ) { $q->the_post();
			// Reuse your existing profile card template
			include get_template_directory() . '-child/loop-show-profile.php';
		}
	}
	wp_reset_postdata();
	echo ob_get_clean();
	wp_die();
}

/**
 * AJAX handler to check profile status for activation button
 */
add_action('wp_ajax_escortwp_check_profile_status', 'escortwp_check_profile_status');
add_action('wp_ajax_nopriv_escortwp_check_profile_status', 'escortwp_check_profile_status_nopriv');

function escortwp_check_profile_status() {
    if (!isset($_POST['user_id']) || !is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $user_id = intval($_POST['user_id']);
    $current_user_id = get_current_user_id();
    
    // Security check - user can only check their own status
    if ($user_id !== $current_user_id) {
        wp_send_json_error('Invalid user');
    }
    
    // Get the escort post ID for this user
    $escort_post_id = intval(get_option('escortpostid' . $user_id));
    
    if (!$escort_post_id) {
        wp_send_json_error('No profile found');
    }
    
    // Check if profile is private
    $is_private = ('private' === get_post_status($escort_post_id));
    
    wp_send_json_success(array(
        'is_private' => $is_private,
        'post_id' => $escort_post_id,
        'post_status' => get_post_status($escort_post_id),
        'user_id' => $user_id
    ));
}

function escortwp_check_profile_status_nopriv() {
    wp_send_json_error('User not logged in');
}

// Image resizing fix
add_action( 'wp_footer', function () {
    if ( is_admin() ) return; // front-end only
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Find any profile images that have sizes="auto" and remove it
        document.querySelectorAll('.girl .thumb img[sizes="auto"], img.mobile-ready-img[sizes="auto"]').forEach(function(img) {
            img.removeAttribute('sizes');
        });
    });
    </script>
    <?php
});

?>