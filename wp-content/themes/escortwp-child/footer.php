<?php
if (!defined('error_reporting')) { define('error_reporting', '0'); }
ini_set('display_errors', error_reporting);
if (error_reporting == '1') { error_reporting(E_ALL); }
if (isdolcetheme !== 1) { die(); }

global $taxonomy_profile_name;
?>
<div class="clear10"></div>

<footer class="footer site-footer" role="contentinfo">
	<div class="site-footer__container">
		<?php
		if (is_front_page() && is_active_sidebar('footer-home-only')) {
			dynamic_sidebar('footer-home-only');
		}
		?>

		<?php if (is_active_sidebar('widget-footer') || current_user_can('level_10')) : ?>
			<div class="site-footer__widgets">
				<?php if (!dynamic_sidebar('Footer') && current_user_can('level_10')) : ?>
					<div class="widgetbox rad3 placeholder-widgettext">
						<?php _e('Go to your', 'escortwp'); ?> <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e('widgets page', 'escortwp'); ?></a> <?php _e('to add content here', 'escortwp'); ?>.
					</div> <!-- widgetbox -->
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="clear"></div>
</footer> <!-- FOOTER -->

<div class="underfooter site-footer__bottom">
	<div class="site-footer__container">
		<div>
			&copy; <?php echo date('Y'); ?> <?php bloginfo('site_name'); ?>
		</div>
		<div class="clear"></div>
	</div>
</div>
</div> <!-- ALL -->
</main> <!-- #main-content -->

<?php wp_footer(); ?>

<?php
// Legacy "Activate Chat" FAB temporarily disabled (replacing with Support Board).
if (false) :
	/*    $allowed_test_user_id = 33891;
	if (get_current_user_id() !== $allowed_test_user_id) {
		return;
	} */

	$current_user_id = get_current_user_id();
	$has_chat = $current_user_id
		? (bool) get_user_meta($current_user_id, 'chat_user_id', true)
		: false;

	$chat_label = $has_chat ? 'Open Chat' : 'Activate Chat';
	?>

	<button
		id="chat-fab"
		class="floating-chat <?php echo $has_chat ? 'chat-active' : 'chat-inactive'; ?>"
		aria-haspopup="true"
		aria-expanded="false"
		aria-label="Chat options"
	>
		<span class="chat-text"><?php echo esc_html($chat_label); ?></span>
	</button>

	<div id="chat-menu" class="chat-menu" role="menu" aria-hidden="true">

		<!-- Admin chat -->
		<a
			href="<?php echo esc_url( add_query_arg([
				'laravel-sso' => 1,
				'target' => '/chat/13',
			], home_url('/')) ); ?>"
			class="chat-menu-item primary"
			role="menuitem"
		>
			💬 Chat with Admin
			<small>Get help instantly</small>
		</a>

		<!-- User chat -->
		<a
			href="https://www.exotickenya.com/?laravel-sso=1"
			class="chat-menu-item"
			role="menuitem"
		>
			💬 My Chats
			<small>Open your conversations</small>
		</a>

	</div>

	<style>
		/* Floating Chat Base */
	.floating-chat {
		position: fixed;
		right: 20px;
		bottom: 20px;
		padding: 14px 22px;
		border-radius: 999px;
		background: linear-gradient(135deg, #e11d48, #be123c);
		color: #fff;
		font-weight: 700;
		font-size: 15px;
		text-decoration: none;
		z-index: 999999;
		box-shadow: 0 10px 30px rgba(225,29,72,0.4);
		display: flex;
		align-items: center;
		justify-content: center;
		transition: transform .2s ease, box-shadow .2s ease;
	}

	/* Hover effect */
	.floating-chat:hover {
		transform: scale(1.08);
		box-shadow: 0 18px 45px rgba(225,29,72,0.6);
	}

	/* Ã°Å¸Å¡Â¨ Attention-grabbing pulse (NOT activated yet) */
	.chat-inactive {
		animation: chatPulse 1.2s infinite;
	}

	/* Calm state once activated */
	.chat-active {
		animation: none;
		background: linear-gradient(135deg, #16a34a, #15803d);
		box-shadow: 0 10px 30px rgba(22,163,74,0.35);
	}

	.chat-menu {
		position: fixed;
		right: 20px;
		bottom: 90px;
		width: 240px;
		background: #fff;
		border-radius: 14px;
		box-shadow: 0 20px 45px rgba(0,0,0,0.18);
		padding: 8px;
		z-index: 999999;
		display: none;
	}

	.chat-menu.open {
		display: block;
	}

	.chat-menu-item {
		display: block;
		padding: 12px 14px;
		border-radius: 10px;
		text-decoration: none;
		color: #111;
		font-weight: 600;
		transition: background .15s ease;
	}

	.chat-menu-item small {
		display: block;
		font-weight: 400;
		font-size: 12px;
		color: #666;
	}

	.chat-menu-item:hover {
		background: #f3f4f6;
	}

	.chat-menu-item.primary {
		background: #e11d48;
		color: #fff;
	}

	.chat-menu-item.primary small {
		color: #ffe4e6;
	}

	.chat-menu-item.primary:hover {
		background: #be123c;
	}


	/* Pulse animation */
	@keyframes chatPulse {
		0% {
			transform: scale(1);
			box-shadow: 0 0 0 0 rgba(225,29,72,0.7);
		}
		70% {
			transform: scale(1.08);
			box-shadow: 0 0 0 20px rgba(225,29,72,0);
		}
		100% {
			transform: scale(1);
			box-shadow: 0 0 0 0 rgba(225,29,72,0);
		}
	}

	</style>
	<script>
	(function () {
		const fab = document.getElementById('chat-fab');
		const menu = document.getElementById('chat-menu');

		if (!fab || !menu) return;

		fab.addEventListener('click', function (e) {
			e.stopPropagation();
			const open = menu.classList.toggle('open');
			fab.setAttribute('aria-expanded', open);
			menu.setAttribute('aria-hidden', !open);
		});

		document.addEventListener('click', function () {
			menu.classList.remove('open');
			fab.setAttribute('aria-expanded', 'false');
			menu.setAttribute('aria-hidden', 'true');
		});
	})();
	</script>
<?php endif; ?>
<?php
if (escortwp_age_gate_should_render()) {
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('.entertosdisclaimer').on('click', function() {
		$(".tosdisclaimer-overlay, .tosdisclaimer-wrapper").fadeOut('150');
		Cookies.set('tos18', 'yes', { expires: 60 });
	});
	$('.closetosdisclaimer').on('click', function() {
		window.location = "https://www.google.com/";
	});
});
</script>
<?php include (get_template_directory() . '/footer-tos-18years-agreement-overlay.php'); ?>
<?php } ?>

<?php
$wallet_feature_state = function_exists('escortwp_child_wallet_feature_state')
	? escortwp_child_wallet_feature_state(array('template' => 'footer'))
	: 'coming_soon';
$wallet_feature_enabled = ($wallet_feature_state === 'enabled');
$wallet_unavailable_message = function_exists('escortwp_child_wallet_unavailable_message')
	? escortwp_child_wallet_unavailable_message(array('template' => 'footer'))
	: 'Wallet payments are currently unavailable.';
$crm_api_base_url = function_exists('escortwp_child_get_crm_api_base_url')
	? escortwp_child_get_crm_api_base_url()
	: 'https://testing.exotic-ads.com';
$stk_retry_enabled = function_exists('escortwp_child_stk_retry_enabled')
	? escortwp_child_stk_retry_enabled(array('template' => 'footer'))
	: false;
$wallet_runtime_user_id = get_current_user_id();
$wallet_runtime_profile_id = function_exists('escortwp_child_wallet_current_profile_id')
	? escortwp_child_wallet_current_profile_id($wallet_runtime_user_id)
	: 0;
$wallet_runtime_summary = function_exists('escortwp_child_get_wallet_cached_summary')
	? escortwp_child_get_wallet_cached_summary($wallet_runtime_user_id)
	: array();
$wallet_runtime_config = function_exists('escortwp_child_get_wallet_synced_config')
	? escortwp_child_get_wallet_synced_config()
	: array();
?>
<script>
// Global variable to store selected platform
let selectedPlatform = null;
window.exoticWalletFeature = {
    enabled: <?php echo $wallet_feature_enabled ? 'true' : 'false'; ?>,
    state: <?php echo wp_json_encode($wallet_feature_state); ?>,
    reason: <?php echo wp_json_encode($wallet_unavailable_message); ?>
};
window.exoticCrmConfig = {
    apiBaseUrl: <?php echo wp_json_encode($crm_api_base_url); ?>,
    stkRetryEnabled: <?php echo $stk_retry_enabled ? 'true' : 'false'; ?>
};
window.exoticWalletRuntime = {
    ajaxUrl: <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
    nonce: <?php echo wp_json_encode(wp_create_nonce('exotic_wallet_actions')); ?>,
    userId: <?php echo (int) $wallet_runtime_user_id; ?>,
    postId: <?php echo (int) $wallet_runtime_profile_id; ?>,
    summary: <?php echo wp_json_encode($wallet_runtime_summary); ?>,
    syncedConfig: <?php echo wp_json_encode($wallet_runtime_config); ?>
};
const walletFeatureEnabled = !!(window.exoticWalletFeature && window.exoticWalletFeature.enabled);
const crmApiBaseUrl = String((window.exoticCrmConfig && window.exoticCrmConfig.apiBaseUrl) || '').replace(/\/$/, '');
const crmStkRetryEnabled = !!(window.exoticCrmConfig && window.exoticCrmConfig.stkRetryEnabled);

function crmApiUrl(path) {
    const normalizedPath = String(path || '').startsWith('/') ? path : `/${path}`;
    return `${crmApiBaseUrl}${normalizedPath}`;
}

function fireExoticSwal(options) {
    const baseOptions = {
        customClass: {
            popup: 'exotic-swal-popup',
            title: 'exotic-swal-title',
            htmlContainer: 'exotic-swal-html',
            confirmButton: 'exotic-swal-confirm',
            cancelButton: 'exotic-swal-cancel',
            closeButton: 'exotic-swal-close'
        },
        buttonsStyling: false,
        background: '#0f0f10',
        color: '#f3f4f6'
    };
    return Swal.fire(Object.assign(baseOptions, options));
}

function bindActivateButtons() {
    jQuery(document).off('click', '.activate-account-btn').on('click', '.activate-account-btn', function(e) {
        e.preventDefault();
        detectAndSetPlatform();
    });
}

jQuery(document).ready(function($) {
    if (typeof escortwp_user_id !== 'undefined') {
        bindActivateButtons();

        if ($('.dropdownlinks-dropdown h4:contains("My Account")').length > 0) {
            // Check profile status via AJAX first
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'escortwp_check_profile_status',
                    user_id: escortwp_user_id
                },
                success: function(response) {
                    console.log('Profile status response:', response); // For debugging

                    if (response.success && response.data.is_expired) {
                        if ($('.activate-account-btn').length === 0) {
                            addActivateButton();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Could not check profile status:', error);
                }
            });
        }
    }
});

function addActivateButton() {
    var activateBtn = `
        <div class="activate-account-btn-wrapper">
            <button type="button" class="activate-account-btn" data-user-id="${escortwp_user_id}">
                <span class="activate-account-btn__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                        <path d="M12 2l3 7h7l-5.5 4 2.5 7-7-4.5L5 20l2.5-7L2 9h7z"></path>
                    </svg>
                </span>
                <span class="activate-account-btn__text">Activate Account</span>
            </button>
        </div>
    `;

    $('.dropdownlinks-dropdown h4:contains("My Account")').closest('.dropdownlinks-dropdown')
        .prepend(activateBtn);
}

function detectAndSetPlatform() {
    // Show loading state
    fireExoticSwal({
        title: 'Loading Platform Information',
        html: `
            <div class="exotic-modal__loader">
                <div class="exotic-loading-wave">
                    <span></span><span></span><span></span><span></span>
                </div>
                <p class="exotic-modal__subtext">Detecting your platform...</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });

    // Get current domain
    const currentDomain = window.location.hostname;
    const isLocal = currentDomain === 'localhost' || currentDomain.endsWith('.local');
    
    // Fetch platforms from API
    $.ajax({
        url: crmApiUrl('/api/platforms'),
        method: 'GET',
        success: function(response) {
            // Check if response is valid
            if (!response || !response.platforms || !Array.isArray(response.platforms)) {
                console.error('Invalid platforms API response format:', response);
                fireExoticSwal({
                    icon: 'error',
                    title: 'Data Error',
                    text: response?.message || 'Received invalid platforms data format from server'
                });
                return;
            }
            
            const platforms = response.platforms;
            
            // Check if platforms array is empty
            if (platforms.length === 0) {
                fireExoticSwal({
                    icon: 'info',
                    title: 'No platforms available',
                    text: response.message || 'Please contact support for assistance.'
                });
                return;
            }

            if (isLocal) {
                const storedOverride = localStorage.getItem('exotic_platform_override');
                if (storedOverride) {
                    const overridePlatform = platforms.find(platform => String(platform.id) === String(storedOverride));
                    if (overridePlatform) {
                        selectedPlatform = {
                            id: overridePlatform.id,
                            name: overridePlatform.name,
                            product_id: overridePlatform.product_id
                        };
                        Swal.close();
                        showPackageSelection();
                        return;
                    }
                    localStorage.removeItem('exotic_platform_override');
                }
            }

            // Find platform that matches current domain
            const matchedPlatform = platforms.find(platform => {
                try {
                    const platformDomain = new URL(platform.domain).hostname;
                    return platformDomain === currentDomain;
                } catch (e) {
                    console.error('Error parsing platform domain:', platform.domain, e);
                    return false;
                }
            });

            if (!matchedPlatform) {
                if (isLocal) {
                    showPlatformPicker(platforms);
                } else {
                    fireExoticSwal({
                        icon: 'error',
                        title: 'Platform Not Found',
                        text: 'Could not identify your platform. Please contact support.'
                    });
                }
                return;
            }

            // Set the matched platform
            selectedPlatform = {
                id: matchedPlatform.id,
                name: matchedPlatform.name,
                product_id: matchedPlatform.product_id
            };

            Swal.close();
            showPackageSelection();
        },
        error: function(xhr) {
            fireExoticSwal({
                icon: 'error',
                title: 'Connection Error',
                text: xhr.responseJSON?.message || 'Could not load platform information. Please check your connection and try again.'
            });
        }
    });
}

function showPlatformPicker(platforms) {
    const options = platforms.map(platform => {
        const safeName = (platform.name || 'Platform').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return `
            <button type="button" class="platform-picker__item" data-platform-id="${platform.id}">
                <span class="platform-picker__name">${safeName}</span>
                <span class="platform-picker__domain">${platform.domain || ''}</span>
            </button>
        `;
    }).join('');

    fireExoticSwal({
        title: 'Select Platform',
        html: `
            <div class="platform-picker">
                <p class="platform-picker__subtitle">Choose a platform for this local environment.</p>
                <div class="platform-picker__grid">
                    ${options}
                </div>
                <p class="platform-picker__note">This selection is saved locally for development only.</p>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true
    });

    jQuery('.platform-picker__item').on('click', function() {
        const platformId = jQuery(this).data('platform-id');
        const selected = platforms.find(platform => String(platform.id) === String(platformId));
        if (!selected) {
            return;
        }
        localStorage.setItem('exotic_platform_override', String(selected.id));
        selectedPlatform = {
            id: selected.id,
            name: selected.name,
            product_id: selected.product_id
        };
        Swal.close();
        showPackageSelection();
    });
}

function showPackageSelection() {
    // Show animated loading state
    fireExoticSwal({
        title: 'Loading Premium Packages',
        html: `
            <div class="exotic-modal__loader">
                <div class="exotic-loading-wave">
                    <span></span><span></span><span></span><span></span>
                </div>
                <p class="exotic-modal__subtext">Preparing exclusive offers for you...</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });

    // Fetch products from Laravel API
    $.ajax({
        url: crmApiUrl('/api/products'),
        method: 'GET',
        success: function(response) {
            Swal.close();
            
            // Check if response is valid and contains products array
            if (!response || !response.products || !Array.isArray(response.products)) {
                console.error('Invalid API response format:', response);
                fireExoticSwal({
                    icon: 'error',
                    title: 'Data Error',
                    text: response?.message || 'Received invalid data format from server'
                });
                return;
            }
            
            const products = response.products;
            
            // Check if products array is empty
            if (products.length === 0) {
                fireExoticSwal({
                    icon: 'info',
                    title: 'No packages available',
                    text: response.message || 'Please contact support for assistance.'
                });
                return;
            }

            // Since product_id is null, show ALL available products
            // Build product list HTML with all products
            const planMeta = (planName) => {
                const lower = String(planName || '').toLowerCase();
                if (lower.includes('vip')) {
                    return {
                        tone: 'vip',
                        label: 'VIP',
                        subtitle: 'Maximum visibility with priority placement',
                        badge: ''
                    };
                }
                if (lower.includes('premium')) {
                    return {
                        tone: 'premium',
                        label: 'Premium',
                        subtitle: 'Best balance of visibility and value',
                        badge: ''
                    };
                }
                if (lower.includes('basic')) {
                    return {
                        tone: 'basic',
                        label: 'Basic',
                        subtitle: 'Essential presence with steady visibility',
                        badge: ''
                    };
                }
                return {
                    tone: 'standard',
                    label: planName,
                    subtitle: 'Flexible listing plan',
                    badge: '',
                };
            };

            let productOptions = products.map(product => {
                const meta = planMeta(product.name);
                const featureList = (product.description || 'Premium account features')
                    .split('\n')
                    .map(feature => feature.trim())
                    .filter(Boolean)
                    .slice(0, 2);

                return `
                <button class="plan-card plan-card--${meta.tone} ${meta.badge ? 'plan-card--featured' : ''}"
                    type="button"
                    aria-pressed="false"
                    data-plan-id="${product.id}"
                    data-plan-name="${product.name}"
                    data-plan-label="${meta.label}"
                    data-currency="${product.currency}"
                    data-monthly="${product.monthly_price}"
                    data-biweekly="${product.biweekly_price}"
                    data-weekly="${product.weekly_price}"
                    data-recommended="${meta.badge ? 'true' : 'false'}"
                >
                    <div class="plan-card__header">
                        <div class="plan-card__title-wrap">
                            <div class="plan-card__title-row">
                                <span class="plan-card__name">${meta.label}</span>
                                ${meta.badge ? `<span class="plan-card__badge">${meta.badge}</span>` : ''}
                            </div>
                            <p class="plan-card__subtitle">${meta.subtitle}</p>
                        </div>
                    </div>

                    <div class="plan-card__features">
                        ${featureList.map(feature => 
                            `<div class="feature-item">
                                <span class="feature-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <path d="M20 6L9 17l-5-5"></path>
                                    </svg>
                                </span>
                                <span class="feature-text">${feature}</span>
                            </div>`
                        ).join('')}
                    </div>
                </button>
            `}).join('');

            fireExoticSwal({
                title: 'Upgrade Your Listing',
                html: `
                    <div class="package-selection-header">
                        <h3>Choose a plan to reactivate and boost visibility</h3>
                        <p>Instant activation after payment.</p>
                        <div class="currency-display">
                            <small>Prices in ${products[0]?.currency || 'KES'}</small>
                        </div>
                    </div>
                    <div class="plan-grid">
                        ${productOptions}
                    </div>
                    <div class="plan-duration">
                        <div class="plan-duration__label">Billing duration</div>
                        <div class="duration-toggle" role="tablist" aria-label="Select billing duration">
                            <button class="duration-option is-active" type="button" data-duration="monthly" role="tab">Monthly</button>
                            <button class="duration-option" type="button" data-duration="biweekly" role="tab">2 Weeks</button>
                            <button class="duration-option" type="button" data-duration="weekly" role="tab">Weekly</button>
                        </div>
                        <div class="duration-hint">You can change this later. No hidden fees.</div>
                    </div>
                    <div class="plan-summary">
                        <div class="plan-summary__name" data-plan-summary-name>Premium</div>
                        <div class="plan-summary__price">
                            <span data-plan-summary-price>KES 0</span>
                            <span class="plan-summary__period" data-plan-summary-period>per month</span>
                        </div>
                        <div class="plan-summary__note">Renews manually • Secure checkout</div>
                    </div>
                    <button class="plan-cta" type="button">Continue</button>
                    <div class="plan-footer-note">Support available • Secure payment • Instant activation</div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                didOpen: () => {
                    const popup = Swal.getPopup();
                    const $popup = jQuery(popup);
                    let selectedDuration = 'monthly';

                    const durationLabels = {
                        monthly: 'per month',
                        biweekly: 'per 2 weeks',
                        weekly: 'per week'
                    };

                    const updateSummary = ($card) => {
                        if (!$card || !$card.length) return;
                        const planName = $card.data('plan-name');
                        const planLabel = $card.data('plan-label') || planName;
                        const currency = $card.data('currency');
                        const priceRaw = $card.data(selectedDuration);
                        const price = parseFloat(priceRaw || 0);
                        const formatted = `${currency} ${price.toLocaleString()}`;
                        $popup.find('[data-plan-summary-name]').text(planLabel);
                        $popup.find('[data-plan-summary-price]').text(formatted);
                        $popup.find('[data-plan-summary-period]').text(durationLabels[selectedDuration]);
                        $popup.find('.plan-cta').text(`Continue with ${planLabel}`);
                    };

                    const selectPlanCard = ($card) => {
                        $popup.find('.plan-card').removeClass('is-selected');
                        $popup.find('.plan-card').attr('aria-pressed', 'false');
                        $card.addClass('is-selected');
                        $card.attr('aria-pressed', 'true');
                        updateSummary($card);
                    };

                    const $defaultCard = $popup.find('.plan-card[data-recommended="true"]').first();
                    if ($defaultCard.length) {
                        selectPlanCard($defaultCard);
                    } else {
                        selectPlanCard($popup.find('.plan-card').first());
                    }

                    $popup.on('click', '.plan-card', function() {
                        selectPlanCard(jQuery(this));
                    });

                    $popup.on('click', '.duration-option', function() {
                        $popup.find('.duration-option').removeClass('is-active');
                        $popup.find('.duration-option').attr('aria-selected', 'false');
                        jQuery(this).addClass('is-active');
                        jQuery(this).attr('aria-selected', 'true');
                        selectedDuration = jQuery(this).data('duration');
                        updateSummary($popup.find('.plan-card.is-selected'));
                    });

                    $popup.find('.duration-option.is-active').attr('aria-selected', 'true');

                    $popup.on('click', '.plan-cta', function() {
                        const $card = $popup.find('.plan-card.is-selected');
                        if (!$card.length) return;
                        selectPackage(
                            null,
                            $card.data('plan-id'),
                            $card.data('plan-name'),
                            $card.data('monthly'),
                            $card.data('biweekly'),
                            $card.data('weekly'),
                            $card.data('currency'),
                            selectedDuration
                        );
                    });
                }
            });
        },
        error: function(xhr) {
            fireExoticSwal({
                icon: 'error',
                title: 'Connection Error',
                text: xhr.responseJSON?.message || 'Could not load packages. Please check your connection and try again.'
            });
        }
    });
}

function selectPackage(event, id, name, monthlyPrice, biweeklyPrice, weeklyPrice, currency, forcedDuration = null) {
    let duration = forcedDuration;
    let price = null;

    if (!duration && event) {
        const packageCard = $(event.target).closest('.package-card');
        const selectedOption = packageCard.find('.pricing-option.active');
        duration = selectedOption.data('duration');
        price = parseFloat(selectedOption.data('price'));
    }

    if (!duration) {
        duration = 'monthly';
    }

    if (!price) {
        const priceMap = {
            monthly: monthlyPrice,
            biweekly: biweeklyPrice,
            weekly: weeklyPrice
        };
        price = parseFloat(priceMap[duration] || monthlyPrice || 0);
    }

    // ── Build wallet payment option ──
    const walletBalance = parseFloat(jQuery('.wallet-amount').attr('data-wallet-amount') || 0);
    const walletCurrency = jQuery('.wallet-currency').first().text() || currency;
    const hasWallet = walletBalance > 0;
    const canAfford = walletBalance >= price;
    const deficit = price - walletBalance;
    const walletFeatureReason = String((window.exoticWalletFeature && window.exoticWalletFeature.reason) || 'Wallet payments are currently unavailable.');
    const walletBadgeHtml = '<span class="payment-badge-coming-soon">Unavailable</span>';

    let walletOptionHtml = '';
    if (walletFeatureEnabled && hasWallet) {
        walletOptionHtml = `
                <div class="payment-method payment-method--wallet" id="pay-from-wallet-btn">
                    <div class="payment-icon payment-icon--wallet" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" width="22" height="22">
                            <rect x="2" y="4" width="20" height="16" rx="3"></rect>
                            <path d="M2 10h20"></path>
                            <circle cx="17" cy="15" r="1.5"></circle>
                        </svg>
                    </div>
                    <div class="payment-content">
                        <div class="payment-name">Pay from Wallet</div>
                        <div class="payment-desc">Balance: ${walletCurrency} ${walletBalance.toLocaleString()}</div>
                        ${!canAfford ? `<div class="payment-shortfall">You need ${walletCurrency} ${deficit.toLocaleString()} more</div>` : ''}
                    </div>
                    <div class="payment-arrow">→</div>
                </div>`;
    } else if (!walletFeatureEnabled) {
        const walletDesc = hasWallet
            ? `Balance: ${walletCurrency} ${walletBalance.toLocaleString()}`
            : walletFeatureReason;
        walletOptionHtml = `
                <div class="payment-method payment-method--wallet is-disabled" id="pay-from-wallet-btn" aria-disabled="true" tabindex="-1">
                    <div class="payment-icon payment-icon--wallet" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" width="22" height="22">
                            <rect x="2" y="4" width="20" height="16" rx="3"></rect>
                            <path d="M2 10h20"></path>
                            <circle cx="17" cy="15" r="1.5"></circle>
                        </svg>
                    </div>
                    <div class="payment-content">
                        <div class="payment-name">Pay from Wallet</div>
                        <div class="payment-desc">${walletDesc}</div>
                        ${hasWallet && !canAfford ? `<div class="payment-shortfall">You need ${walletCurrency} ${deficit.toLocaleString()} more</div>` : ''}
                        ${walletBadgeHtml}
                    </div>
                    <div class="payment-arrow">→</div>
                </div>`;
    }

    fireExoticSwal({
        title: 'Select Payment Method',
        html: `
            <div class="payment-header">
                <div class="selected-package">
                    <span class="package-name">${name} (${duration === 'monthly' ? 'Monthly' : duration === 'biweekly' ? 'Biweekly' : 'Weekly'})</span>
                    <span class="package-price">${currency} ${price.toLocaleString()}</span>
                </div>
                <div class="selected-platform">
                    <small>For: ${selectedPlatform.name}</small>
                </div>
            </div>
            <div class="payment-method-container">
                ${walletOptionHtml}

                <div class="payment-method" onclick="showMobileMoneyOptions('${id}', '${name}', ${price}, '${currency}', '${duration}')">
                    <div class="payment-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" width="22" height="22">
                            <rect x="6" y="2" width="12" height="20" rx="2"></rect>
                            <line x1="12" y1="18" x2="12" y2="18"></line>
                        </svg>
                    </div>
                    <div class="payment-content">
                        <div class="payment-name">Mobile Money</div>
                        <div class="payment-desc">MPESA</div>
                    </div>
                    <div class="payment-arrow">→</div>
                </div>

                <div class="payment-method" onclick="initiateCardPayment('${id}', '${name}', ${price}, '${currency}', '${duration}')">
                    <div class="payment-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" width="22" height="22">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                    </div>
                    <div class="payment-content">
                        <div class="payment-name">Credit/Debit Card</div>
                        <div class="payment-desc">Visa, Mastercard, etc.</div>
                    </div>
                    <div class="payment-arrow">→</div>
                </div>

            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        didOpen: function() {
            // Wallet payment click handler — enabled only in local/dev mode
            const $walletButton = jQuery('#pay-from-wallet-btn');
            $walletButton.off('click.walletPay');
            if (walletFeatureEnabled && $walletButton.length && !$walletButton.hasClass('is-disabled')) {
                $walletButton.on('click.walletPay', function() {
                    payFromWallet(id, name, price, currency, duration);
                });
            }
        }
    });
}

/* ==========================================================================
   WALLET PAYMENT — Pay for subscription from wallet balance
   ========================================================================== */

function payFromWallet(productId, name, price, currency, duration) {
    if (typeof window.__crmWalletPayFromWallet === 'function') {
        return window.__crmWalletPayFromWallet(productId, name, price, currency, duration);
    }

    fireExoticSwal({
        icon: 'info',
        title: 'Wallet Unavailable',
        text: String((window.exoticWalletFeature && window.exoticWalletFeature.reason) || 'Wallet payments are currently unavailable.')
    });
}

function executeWalletDebit(productId, name, price, currency, duration) {
    if (typeof window.__crmWalletExecuteWalletDebit === 'function') {
        return window.__crmWalletExecuteWalletDebit(productId, name, price, currency, duration);
    }

    fireExoticSwal({
        icon: 'info',
        title: 'Wallet Unavailable',
        text: String((window.exoticWalletFeature && window.exoticWalletFeature.reason) || 'Wallet payments are currently unavailable.')
    });
}

function showMobileMoneyOptions(id, name, price, currency, duration) {
    fireExoticSwal({
        title: 'Pay with Mobile Money',
        html: `
            <div class="mobile-money-header">
                <div class="package-info">
                    <span class="package-name">${name} (${duration === 'monthly' ? 'Monthly' : duration === 'biweekly' ? 'Biweekly' : 'Weekly'})</span>
                    <span class="package-price">${currency} ${price.toLocaleString()}</span>
                </div>
            </div>
            <p class="provider-prompt">Select your mobile money provider:</p>
            <div class="mobile-money-options">
                <div class="mobile-option" onclick="requestPhoneNumber('${id}', '${name}', ${price}, '${currency}', 'mpesa', '${duration}')">
                    <div class="provider-logo">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/1/15/M-PESA_LOGO-01.svg" alt="MPESA">
                    </div>
                    <div class="provider-name">MPESA</div>
                </div>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true
    });
}

function requestPhoneNumber(id, name, price, currency, provider, duration) {
    fireExoticSwal({
        title: `Pay with ${provider === 'mpesa' ? 'MPESA' : 'Airtel Money'}`,
        html: `
            <div class="payment-summary">
                <div class="package-name">${name} (${duration === 'monthly' ? 'Monthly' : duration === 'biweekly' ? 'Biweekly' : 'Weekly'})</div>
                <div class="package-price">${currency} ${price.toLocaleString()}</div>
            </div>
            <div class="phone-input-container">
                <label for="payment-phone">Enter your phone number:</label>
                <div class="input-wrapper">
                    <span class="country-code">+254</span>
                    <input type="tel" id="payment-phone" class="phone-input" 
                        placeholder="${provider === 'mpesa' ? '7XXXXXXXX' : '7XXXXXXXX'}" 
                        pattern="[0-9]{9}" 
                        inputmode="numeric" />
                </div>
                <small class="input-hint">Enter your 9-digit phone number without the country code</small>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Proceed to Payment',
        focusConfirm: false,
        preConfirm: () => {
            const phone = document.getElementById('payment-phone').value;
            if (!phone || !/^[0-9]{9}$/.test(phone)) {
                Swal.showValidationMessage('Please enter a valid 9-digit phone number');
                return false;
            }
            
            const formattedPhone = '254' + phone;
            return { phone: formattedPhone };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            processPayment(id, name, price, currency, provider, result.value.phone, duration);
        }
    });
}

function initiateCardPayment(id, name, price, currency, duration = null) {
    // First, collect customer information
    fireExoticSwal({
        title: 'Payment Information',
        html: `
            <div class="customer-info-form">
                <div class="form-group">
                    <label for="firstName">First Name:</label>
                    <input type="text" id="firstName" class="swal2-input" placeholder="Enter your first name" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name:</label>
                    <input type="text" id="lastName" class="swal2-input" placeholder="Enter your last name" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" class="swal2-input" placeholder="254712345678" required>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Continue to Payment',
        preConfirm: () => {
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const phone = document.getElementById('phone').value;

            // Validation
            if (!firstName || firstName.trim().length < 2) {
                Swal.showValidationMessage('Please enter a valid first name');
                return false;
            }
            if (!lastName || lastName.trim().length < 2) {
                Swal.showValidationMessage('Please enter a valid last name');
                return false;
            }
            if (!phone || phone.length < 10) {
                Swal.showValidationMessage('Please enter a valid phone number');
                return false;
            }

            return {
                firstName: firstName.trim(),
                lastName: lastName.trim(),
                phone: phone.trim()
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            processCardPayment(id, name, price, currency, duration, result.value);
        }
    });
}

function processCardPayment(id, name, price, currency, duration, customerInfo) {
    // Show loading indicator
    fireExoticSwal({
        title: 'Preparing Payment',
        html: `
            <div class="processing-payment">
                <div class="loading-spinner"><div class="spinner"></div></div>
                <p class="processing-text">Setting up secure payment gateway...</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });

    // Prepare payload
    const payload = {
        product_id: id,
        product_name: name,
        price: price,
        currency: currency,
        duration: duration || 'monthly',
        user_id: window.escortwp_user_id || 1,
        platform_id: window.selectedPlatform?.id || 1,
        first_name: customerInfo.firstName,
        last_name: customerInfo.lastName,
        phone: customerInfo.phone
    };

    console.log("Payload being sent to Laravel:", payload);

    // Make API call to Laravel endpoint
    fetch(crmApiUrl('/api/initiate-card-payment'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    })
    .then(async response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            let errorText = await response.text();
            console.error('Server error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Payment response from Laravel:', data);
        
        if (!data.status || !data.payment_data) {
            throw new Error(data.message || data.error || 'Invalid response from server');
        }

        // Close the loading dialog
        Swal.close();

        // Create and submit the form to CyberSource
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'https://secureacceptance.cybersource.com/pay'; // Use production URL
        form.style.display = 'none';

        // Add all the payment data as hidden form fields
        Object.keys(data.payment_data).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = data.payment_data[key];
            form.appendChild(input);
        });

        // Append form to body and submit
        document.body.appendChild(form);
        
        // Show redirect message
        fireExoticSwal({
            title: 'Redirecting to Payment Gateway',
            text: 'Please wait...',
            allowOutsideClick: false,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            form.submit();
        });

    })
    .catch(error => {
        console.error('Payment initiation failed:', error);
        fireExoticSwal({
            icon: 'error',
            title: 'Payment Error',
            html: `
                <div class="payment-error">
                    <p>${error.message || 'Failed to initiate payment. Please try again.'}</p>
                    <details class="payment-error__details">
                        <summary class="payment-error__summary">Technical Details</summary>
                        <pre class="payment-error__stack">
${error.stack || 'No stack trace available'}
                        </pre>
                    </details>
                </div>
            `
        });
    });
}

// Alternative version without customer info collection (if you want to skip the form)
function initiateCardPaymentDirect(id, name, price, currency, duration = null, firstName = 'Customer', lastName = 'User', phone = '254700000000') {
    // Show loading indicator
    fireExoticSwal({
        title: 'Preparing Payment',
        html: `
            <div class="processing-payment">
                <div class="loading-spinner"><div class="spinner"></div></div>
                <p class="processing-text">Setting up secure payment gateway...</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });

    // Prepare payload
    const payload = {
        product_id: id,
        product_name: name,
        price: price,
        currency: currency,
        duration: duration || 'monthly',
        user_id: window.escortwp_user_id || 1,
        platform_id: window.selectedPlatform?.id || 1,
        first_name: firstName,
        last_name: lastName,
        phone: phone
    };

    // Make API call
    fetch(crmApiUrl('/api/initiate-card-payment'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (!data.status || !data.payment_data) {
            throw new Error(data.message || 'Invalid response from server');
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = data.test_mode 
            ? 'https://testsecureacceptance.cybersource.com/pay'
            : 'https://secureacceptance.cybersource.com/pay';
        form.style.display = 'none';

        Object.keys(data.payment_data).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = data.payment_data[key];
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    })
    .catch(error => {
        console.error('Payment initiation failed:', error);
        fireExoticSwal({
            icon: 'error',
            title: 'Payment Error',
            text: error.message || 'Failed to initiate payment. Please try again.'
        });
    });
}


function showPaypalPayment(id, name, price, currency, duration) {
    fireExoticSwal({
        title: 'PayPal Payment',
        html: `
            <div class="paypal-payment-container">
                <div class="payment-summary">
                    <div class="package-name">${name} (${duration === 'monthly' ? 'Monthly' : duration === 'biweekly' ? 'Biweekly' : 'Weekly'})</div>
                    <div class="package-price">${currency} ${price.toLocaleString()}</div>
                </div>
                
                <div class="paypal-logo">
                    <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" alt="PayPal">
                </div>
                
                <p class="paypal-description">
                    You will be redirected to PayPal to complete your payment.
                    Click "Continue to PayPal" to proceed with the secure checkout.
                </p>
            </div>
            `,
        showCancelButton: true,
        confirmButtonText: 'Continue to PayPal',
        icon: 'info',
        focusConfirm: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Show animated redirect
            fireExoticSwal({
                title: 'Redirecting to PayPal',
                html: `
                    <div class="exotic-modal__loader">
                        <div class="exotic-loading-wave">
                            <span></span><span></span><span></span><span></span>
                        </div>
                        <p class="exotic-modal__subtext">Please wait while we connect you to PayPal...</p>
                    </div>
                `,
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                allowOutsideClick: false,
                willClose: () => {
                    // In a real implementation, you would redirect to PayPal
                    console.log('Redirecting to PayPal payment');
                    // window.location.href = 'https://api.exoticnairobi.com/paypal-checkout?product=' + id + '&duration=' + duration;
                }
            });
        }
    });
}

function processPayment(id, name, price, currency, provider, phone, duration) {
    // First, collect user details required by Kopo Kopo
    fireExoticSwal({
        title: 'Complete Your Details',
        html: `
            <div class="user-details-form">
                <div class="form-group">
                    <label class="form-label" for="firstName">First Name *</label>
                    <input type="text" id="firstName" class="swal2-input" placeholder="Enter your first name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="lastName">Last Name *</label>
                    <input type="text" id="lastName" class="swal2-input" placeholder="Enter your last name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email (Optional)</label>
                    <input type="email" id="email" class="swal2-input" placeholder="Enter your email">
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Proceed to Payment',
        preConfirm: () => {
            const firstName = document.getElementById('firstName').value.trim();
            const lastName = document.getElementById('lastName').value.trim();
            const email = document.getElementById('email').value.trim();
            
            if (!firstName) {
                Swal.showValidationMessage('First name is required');
                return false;
            }
            if (!lastName) {
                Swal.showValidationMessage('Last name is required');
                return false;
            }
            if (email && !isValidEmail(email)) {
                Swal.showValidationMessage('Please enter a valid email address');
                return false;
            }
            
            return {
                firstName: firstName,
                lastName: lastName,
                email: email
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            initiatePayment(id, name, price, currency, provider, phone, duration, result.value);
        }
    });
}

function initiatePayment(id, name, price, currency, provider, phone, duration, userDetails) {
    fireExoticSwal({
        title: 'Processing Payment',
        html: `
            <div class="processing-payment">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                </div>
                <p class="processing-text">Sending payment request to your MPESA...</p>
                <p class="amount-text">Amount: KES ${price}</p>
                <p class="phone-text">Phone: ${phone}</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });
    
    // Prepare the payment data with user details
    const paymentData = {
        product_id: id,
        platform_id: selectedPlatform.id,
        phone: phone,
        user_id: escortwp_user_id,
        duration: duration,
        amount: price,
        first_name: userDetails.firstName,
        last_name: userDetails.lastName,
        email: userDetails.email || null
    };

    // Make the AJAX request to initiate payment
    jQuery.ajax({
        url: crmApiUrl('/api/initiate-stk-payment'),
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(paymentData),
        success: function(response) {
            console.log('Payment response:', response);
            
            if (response.status === true) {
                // Handle successful STK push initiation
                showPaymentSuccess(response, userDetails, phone, price);
            } else {
                // Handle failed payment initiation
                showPaymentError(response.error || 'Payment initiation failed. Please try again.');
            }
        },
        error: function(xhr) {
            console.error('Payment error:', xhr);
            let errorMessage = 'Payment initiation failed. Please try again.';
            
            try {
                const response = xhr.responseJSON;
                console.log('Error response:', response);
                
                // Handle specific error types
                if (xhr.status === 429) {
                    // Handle pending request error
                    errorMessage = response.error || 'You have a pending payment request. Please complete it first.';
                    if (response.error_details && response.error_details.instructions) {
                        showPendingRequestError(response);
                        return;
                    }
                } else {
                    errorMessage = response.error || 
                                  response.message || 
                                  errorMessage;
                }
            } catch (e) {
                console.error('Error parsing error response:', e);
            }
            
            showPaymentError(errorMessage);
        }
    });
}

const STATUS_ICON_SVGS = {
    success: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
            <path d="M20 6L9 17l-5-5"></path>
        </svg>
    `,
    error: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    `,
    warning: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12" y2="17"></line>
        </svg>
    `,
    info: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12" y2="8"></line>
        </svg>
    `,
    question: `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
            <path d="M9.09 9a3 3 0 0 1 5.82 1c0 2-3 3-3 3"></path>
            <line x1="12" y1="17" x2="12" y2="17"></line>
            <circle cx="12" cy="12" r="10"></circle>
        </svg>
    `
};

function buildStatusMarkup(type, message) {
    const icon = STATUS_ICON_SVGS[type] || STATUS_ICON_SVGS.info;
    return `
        <span class="status-result__icon" aria-hidden="true">${icon}</span>
        <span class="status-result__text">${message}</span>
    `;
}

function showPaymentSuccess(response, userDetails, phone, price) {
    // Extract payment data from the new response format
    const paymentData = response.data || response;
    const paymentId = paymentData.payment_id;
    
    fireExoticSwal({
        title: 'Payment Request Sent!',
        html: `
            <div class="payment-success">
                <div class="success-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" width="32" height="32">
                        <rect x="6" y="2" width="12" height="20" rx="2"></rect>
                        <line x1="12" y1="18" x2="12" y2="18"></line>
                    </svg>
                </div>
                <p>Hello <strong>${userDetails.firstName}</strong>,</p>
                <p>A payment request for <strong>KES ${price}</strong> has been sent to your MPESA number <strong>${phone}</strong>.</p>
                <p>Please complete the payment on your phone to activate your account on <strong>${selectedPlatform.name}</strong>.</p>
                
                <div class="payment-details">
                    <h4>Payment Details:</h4>
                    <ul>
                        <li><strong>Platform:</strong> ${paymentData.platform_name || selectedPlatform.name}</li>
                        <li><strong>Service:</strong> ${paymentData.product_name || 'Subscription'}</li>
                        <li><strong>Duration:</strong> ${paymentData.duration || 'monthly'}</li>
                        <li><strong>Amount:</strong> KES ${paymentData.amount || price}</li>
                        <li><strong>Payment ID:</strong> #${paymentId}</li>
                    </ul>
                </div>
                
                <!--
                <div class="manual-instructions">
                    <h4>If you don't receive the MPESA prompt:</h4>
                    <ol>
                        <li>Go to your MPESA menu</li>
                        <li>Select "Lipa na M-PESA"</li>
                        <li>Select "Pay Bill"</li>
                        <li>Enter Business Number: <strong>400200</strong></li>
                        <li>Enter Account Number: <strong>${phone}</strong></li>
                        <li>Enter Amount: <strong>KES ${price}</strong></li>
                        <li>Enter your MPESA PIN</li>
                    </ol>
                </div>
                -->
                
                <div class="status-check">
                    ${crmStkRetryEnabled ? '<button id="resendStk" class="status-btn status-btn--primary">Resend STK Push</button>' : ''}
                    <button id="checkStatus" class="status-btn">Check Payment Status</button>
                    <div id="statusResult" class="status-result"></div>
                </div>
                
                ${paymentData.sandbox_note ? `<div class="sandbox-notice"><small><em>${paymentData.sandbox_note}</em></small></div>` : ''}
                ${paymentData.production_note ? `<div class="production-notice"><small><em>${paymentData.production_note}</em></small></div>` : ''}
            </div>
        `,
        confirmButtonText: 'Got it!',
        allowOutsideClick: false,
        didOpen: () => {
            // Add event listener for status check button
            const statusButton = document.getElementById('checkStatus');
            const resendButton = document.getElementById('resendStk');
            const statusResult = document.getElementById('statusResult');
            
            statusButton.addEventListener('click', () => {
                checkPaymentStatus(paymentId, statusButton, statusResult);
            });
            
            // Add event listener for resend STK button
            if (crmStkRetryEnabled && resendButton) {
                resendButton.addEventListener('click', () => {
                    resendStkPush(paymentId, resendButton, statusResult);
                });
            }
            
            // Auto-check status every 30 seconds
            const autoCheck = setInterval(() => {
                if (!document.getElementById('checkStatus')) {
                    clearInterval(autoCheck);
                    return;
                }
                checkPaymentStatus(paymentId, statusButton, statusResult, true);
            }, 30000);
        }
    });
}

// Function to resend STK push
function resendStkPush(paymentId, button, resultDiv) {
    if (!crmStkRetryEnabled) {
        fireExoticSwal({
            icon: 'info',
            title: 'Retry Unavailable',
            text: 'STK retry is not enabled on this site.'
        });
        return;
    }

    button.disabled = true;
    button.textContent = 'Resending...';
    resultDiv.style.display = 'none';

    jQuery.ajax({
        url: crmApiUrl('/api/billing/retry-stk'),
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ payment_id: paymentId }),
        success: function(response) {
            console.log('Resend STK response:', response);
            
            if (response.status === true) {
                resultDiv.className = 'status-result status-success';
                resultDiv.innerHTML = buildStatusMarkup('success', 'STK Push resent successfully! Check your phone.');
                resultDiv.style.display = 'flex';
                
                // Show success message
                fireExoticSwal({
                    title: 'STK Push Resent!',
                    text: 'A new payment request has been sent to your phone.',
                    icon: 'success'
                });
            } else {
                resultDiv.className = 'status-result status-failed';
                resultDiv.innerHTML = buildStatusMarkup('error', 'Failed to resend STK Push. Please try again.');
                resultDiv.style.display = 'flex';
            }
        },
        error: function(xhr) {
            console.error('Resend STK error:', xhr);
            resultDiv.className = 'status-result status-failed';
            resultDiv.innerHTML = buildStatusMarkup('error', 'Error resending STK Push. Please try again.');
            resultDiv.style.display = 'flex';
        },
        complete: function() {
            button.disabled = false;
            button.textContent = 'Resend STK Push';
        }
    });
}

function showPendingRequestError(response) {
    const instructions = response.error_details.instructions || [
        'Check your phone for pending M-PESA prompts',
        'Complete any existing payment requests',
        'Wait 5 minutes before trying again'
    ];
    
    fireExoticSwal({
        title: 'Pending Payment Request',
        html: `
            <div class="pending-error">
                <div class="pending-error__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" width="28" height="28">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12" y2="17"></line>
                    </svg>
                </div>
                <p class="pending-error__message">${response.error}</p>
                <div class="pending-error__instructions">
                    <h4>What to do:</h4>
                    <ul>
                        ${instructions.map(instruction => `<li>${instruction}</li>`).join('')}
                    </ul>
                </div>
                <div class="pending-error__retry">
                    <p><strong>Retry after:</strong> ${Math.floor((response.error_details.retry_after || 300) / 60)} minutes</p>
                </div>
            </div>
        `,
        confirmButtonText: 'Understood'
    });
}

function showPaymentError(errorMessage) {
    fireExoticSwal({
        title: 'Payment Failed',
        text: errorMessage,
        icon: 'error',
        confirmButtonText: 'Try Again'
    });
}

function checkPaymentStatus(paymentId, button, resultDiv, isAutoCheck = false) {
    if (!isAutoCheck) {
        button.disabled = true;
        button.textContent = 'Checking...';
        resultDiv.style.display = 'none';
    }

    jQuery.ajax({
        url: crmApiUrl('/api/check-payment-status'),
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ payment_id: paymentId }),
        success: function(response) {
            console.log('Status check response:', response);
            
            if (response.status === true) {
                const status = response.payment_status || response.data?.status;
                let statusClass, statusText, statusType;
                
                switch(status) {
                    case 'completed':
                    case 'success':
                        statusClass = 'status-success';
                        statusText = 'Payment successful! Your subscription is now active.';
                        statusType = 'success';
                        
                        // Show success modal and redirect to exoticnairobi.com
                        if (!isAutoCheck) {
                            setTimeout(() => {
                                fireExoticSwal({
                                    title: 'Payment Confirmed!',
                                    html: 'Your payment has been processed successfully. Your subscription is now active.<br><br>You will be redirected to login.',
                                    icon: 'success',
                                    confirmButtonText: 'Continue to Login'
                                }).then(() => {
                                    // Redirect to exoticnairobi.com
                                    window.location.href = 'https://www.exoticnairobi.com/escort/';
                                });
                            }, 1000);
                        }
                        break;
                        
                    case 'failed':
                        statusClass = 'status-failed';
                        statusText = 'Payment failed. Please try again.';
                        statusType = 'error';
                        break;
                        
                    case 'pending':
                    case 'initiated':
                        statusClass = 'status-pending';
                        statusText = 'Payment is still being processed. Please wait...';
                        statusType = 'info';
                        break;
                        
                    case 'expired':
                        statusClass = 'status-failed';
                        statusText = 'Payment request expired. Please try again.';
                        statusType = 'warning';
                        break;
                        
                    default:
                        statusClass = 'status-pending';
                        statusText = `Payment status: ${status}`;
                        statusType = 'question';
                }
                
                if (!isAutoCheck) {
                    resultDiv.className = `status-result ${statusClass}`;
                    resultDiv.innerHTML = buildStatusMarkup(statusType, statusText);
                    resultDiv.style.display = 'flex';
                }
                
            } else {
                if (!isAutoCheck) {
                    resultDiv.className = 'status-result status-failed';
                    resultDiv.innerHTML = buildStatusMarkup('error', 'Could not check payment status. Please try again.');
                    resultDiv.style.display = 'flex';
                }
            }
        },
        error: function(xhr) {
            console.error('Status check error:', xhr);
            if (!isAutoCheck) {
                resultDiv.className = 'status-result status-failed';
                resultDiv.innerHTML = buildStatusMarkup('error', 'Error checking payment status. Please try again.');
                resultDiv.style.display = 'flex';
            }
        },
        complete: function() {
            if (!isAutoCheck) {
                button.disabled = false;
                button.textContent = 'Check Payment Status';
            }
        }
    });
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/* ==========================================================================
   WALLET SYSTEM — CRM-backed wallet card, refresh, top-up, and wallet pay.
   ========================================================================== */
(function($) {
    'use strict';
    if (!$('#wallet-section').length) return;

    var walletFeature = window.exoticWalletFeature || { enabled: false, state: 'coming_soon' };
    var runtime = window.exoticWalletRuntime || {};
    var syncedConfig = runtime.syncedConfig || {};
    var walletState = {
        summary: null,
        config: null,
        mode: 'disabled'
    };

    function formatAmount(num) {
        return Number(num || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatAmountShort(num) {
        return Number(num || 0).toLocaleString('en-KE');
    }

    function timeAgo(dateStr) {
        if (!dateStr) return 'Waiting for sync';
        var diff = Date.now() - new Date(dateStr).getTime();
        var mins  = Math.floor(diff / 60000);
        var hours = Math.floor(diff / 3600000);
        var days  = Math.floor(diff / 86400000);
        if (mins < 1)   return 'Just now';
        if (mins < 60)  return mins + (mins === 1 ? ' min ago' : ' mins ago');
        if (hours < 24) return hours + (hours === 1 ? ' hour ago' : ' hours ago');
        if (days < 30)  return days + (days === 1 ? ' day ago' : ' days ago');
        return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'Pending';
        return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function walletErrorMessage(xhr) {
        var payload = xhr && xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : {};
        var nestedResponse = payload && payload.response && typeof payload.response === 'object' ? payload.response : {};
        return payload.message || nestedResponse.message || xhr.statusText || 'Wallet request failed.';
    }

    function enabledProviders() {
        var providers = walletState.config && walletState.config.providers ? walletState.config.providers : {};
        return Object.keys(providers).filter(function(key) {
            return providers[key] && providers[key].enabled;
        });
    }

    function minimumTopupAmount() {
        var providerKeys = enabledProviders();
        var minimums = providerKeys.map(function(key) {
            var provider = walletState.config && walletState.config.providers ? walletState.config.providers[key] : null;
            var amount = parseFloat(provider && provider.min_amount ? provider.min_amount : 0);
            return amount > 0 ? amount : null;
        }).filter(function(amount) {
            return amount !== null;
        });

        if (minimums.length) {
            return Math.min.apply(Math, minimums);
        }

        return 100;
    }

    function providerMeta(providerKey) {
        var meta = {
            mpesa_stk: {
                name: 'M-Pesa',
                description: 'Pay via STK push to your phone'
            },
            paystack: {
                name: 'Paystack',
                description: 'Visa / Mastercard via Paystack'
            },
            pesapal: {
                name: 'Pesapal',
                description: 'Card checkout via Pesapal'
            }
        };

        return meta[providerKey] || {
            name: providerKey,
            description: 'Wallet top-up provider'
        };
    }

    function normalizeConfig(config) {
        var source = config && typeof config === 'object' ? config : {};

        return {
            market: source.market || ((syncedConfig.config && syncedConfig.config.market) || { currency: 'KES' }),
            topup_presets: Array.isArray(source.topup_presets) ? source.topup_presets : (((syncedConfig.config && syncedConfig.config.topup_presets) || ['500.00', '1000.00', '2000.00', '5000.00'])),
            providers: source.providers || ((syncedConfig.config && syncedConfig.config.providers) || {}),
            show_refresh_button: typeof source.show_refresh_button === 'boolean'
                ? source.show_refresh_button
                : !!((syncedConfig.config && syncedConfig.config.show_refresh_button) || false),
            allow_combined_topup_subscribe: typeof source.allow_combined_topup_subscribe === 'boolean'
                ? source.allow_combined_topup_subscribe
                : !!((syncedConfig.config && syncedConfig.config.allow_combined_topup_subscribe) || false),
            recent_transactions_limit: parseInt(source.recent_transactions_limit || ((syncedConfig.config && syncedConfig.config.recent_transactions_limit) || 10), 10),
            wallet_refresh_rate_limit_seconds: parseInt(source.wallet_refresh_rate_limit_seconds || ((syncedConfig.config && syncedConfig.config.wallet_refresh_rate_limit_seconds) || 15), 10),
            wallet_refresh_timeout_seconds: parseInt(source.wallet_refresh_timeout_seconds || ((syncedConfig.config && syncedConfig.config.wallet_refresh_timeout_seconds) || 15), 10),
            topup_poll_interval_seconds: parseInt(source.topup_poll_interval_seconds || ((syncedConfig.config && syncedConfig.config.topup_poll_interval_seconds) || 10), 10),
            sandbox_badge: typeof source.sandbox_badge === 'boolean'
                ? source.sandbox_badge
                : !!((syncedConfig.config && syncedConfig.config.sandbox_badge) || false),
            business_name: source.business_name || ((syncedConfig.config && syncedConfig.config.business_name) || ''),
            description: source.description || ((syncedConfig.config && syncedConfig.config.description) || '')
        };
    }

    function normalizeWalletSummary(payload) {
        var source = payload && typeof payload === 'object' ? payload : {};

        return {
            balance: parseFloat(source.balance || 0),
            currency: String(source.currency || (source.config && source.config.market ? source.config.market.currency : 'KES') || 'KES'),
            mode: String(source.mode || syncedConfig.mode || 'disabled'),
            refreshed_at: source.refreshed_at || '',
            wallet_last_synced_at: source.wallet_last_synced_at || '',
            last_topup: source.last_topup && typeof source.last_topup === 'object' ? source.last_topup : null,
            transactions: Array.isArray(source.transactions) ? source.transactions : [],
            config: normalizeConfig(source.config || (syncedConfig.config || {}))
        };
    }

    function setWalletState(payload) {
        walletState.summary = normalizeWalletSummary(payload);
        walletState.config = walletState.summary.config;
        walletState.mode = walletState.summary.mode;
    }

    function renderWalletBalance(data) {
        var $amount = $('#wallet-balance-display .wallet-amount');
        var $currency = $('#wallet-balance-display .wallet-currency');
        var $sub = $('.wallet-balance-sub');
        var $badge = $('#wallet-mode-badge');
        var $updated = $('#wallet-last-updated');
        var balance = parseFloat(data.balance || 0);

        $amount.removeClass('wallet-amount--loading');
        $amount.text(formatAmount(balance));
        $amount.attr('data-wallet-amount', balance);
        $currency.text(data.currency);
        $updated.text(data.wallet_last_synced_at ? 'Last updated ' + timeAgo(data.wallet_last_synced_at) : 'Waiting for wallet sync');

        if (walletState.mode === 'sandbox' || walletState.config.sandbox_badge) {
            $badge.prop('hidden', false).text('Sandbox');
        } else {
            $badge.prop('hidden', true);
        }

        if (balance === 0) {
            $sub.text('Top up your wallet to pay for subscriptions');
            $sub.addClass('wallet-empty-hint');
        } else {
            $sub.text('Available to spend');
            $sub.removeClass('wallet-empty-hint');
        }

        if (data.last_topup && balance > 0) {
            $('#wallet-last-topup').show();
            $('#wallet-last-topup-amount').html(
                '<span class="wallet-currency">' + data.currency + '</span> ' + formatAmountShort(data.last_topup.amount)
            );
            $('#wallet-last-topup-meta').text(
                timeAgo(data.last_topup.created_at) + ' \u00B7 ' + (data.last_topup.description || 'Wallet top-up')
            );
        } else {
            $('#wallet-last-topup').hide();
        }

        $('#wallet-refresh-btn').toggle(!!walletState.config.show_refresh_button);
        $('#wallet-history-toggle').toggle(!!(data.transactions && data.transactions.length));
    }

    function renderTransactions(transactions) {
        var $list = $('#wallet-txn-list');
        $list.empty();

        if (!transactions || !transactions.length) {
            $list.html('<div style="padding:8px 0;color:var(--text-tertiary);font-size:13px;">No transactions yet</div>');
            return;
        }

        transactions.forEach(function(txn) {
            var isCredit = txn.type === 'credit';
            var sign = isCredit ? '+' : '\u2013';
            var amountClass = isCredit ? 'wallet-txn-amount--credit' : 'wallet-txn-amount--debit';
            var indicatorClass = isCredit ? 'wallet-txn-indicator--credit' : 'wallet-txn-indicator--debit';

            var row = '<div class="wallet-txn-row">' +
                '<div class="wallet-txn-left">' +
                    '<div class="wallet-txn-indicator ' + indicatorClass + '">' + sign + '</div>' +
                    '<div class="wallet-txn-desc">' + (txn.description || txn.desc || 'Wallet transaction') + '</div>' +
                '</div>' +
                '<div class="wallet-txn-right">' +
                    '<div class="wallet-txn-amount ' + amountClass + '">' +
                        sign + ' ' + (txn.currency || walletState.summary.currency || 'KES') + ' ' + formatAmountShort(txn.amount) +
                    '</div>' +
                    '<div class="wallet-txn-date">' + formatDate(txn.created_at || txn.date) + '</div>' +
                '</div>' +
            '</div>';

            $list.append(row);
        });
    }

    function setRefreshLoading(isLoading) {
        var $button = $('#wallet-refresh-btn');
        $button.toggleClass('is-loading', !!isLoading);
        $button.prop('disabled', !!isLoading);
        $button.find('.wallet-refresh-label').text(isLoading ? 'Refreshing...' : 'Refresh');
    }

    function walletAjax(action, data) {
        return $.ajax({
            url: runtime.ajaxUrl,
            method: 'POST',
            dataType: 'json',
            data: $.extend({
                action: action,
                nonce: runtime.nonce
            }, data || {})
        });
    }

    function refreshWallet(options) {
        options = options || {};

        if (!walletFeature.enabled) {
            return $.Deferred().reject({ responseJSON: { data: { message: 'Wallet is not enabled on this site.' } } }).promise();
        }

        if (!options.force && refreshWallet.lastRunAt) {
            var diff = (Date.now() - refreshWallet.lastRunAt) / 1000;
            if (diff < (walletState.config.wallet_refresh_rate_limit_seconds || 15)) {
                return $.Deferred().resolve({ success: true, data: { wallet: walletState.summary, message: 'Wallet is already up to date.' } }).promise();
            }
        }

        setRefreshLoading(true);

        return walletAjax('exotic_wallet_refresh').done(function(response) {
            if (!response || !response.success || !response.data || !response.data.wallet) {
                if (!options.silent) {
                    fireExoticSwal({
                        icon: 'error',
                        title: 'Wallet Error',
                        text: 'Wallet refresh returned an invalid response.'
                    });
                }
                return;
            }

            refreshWallet.lastRunAt = Date.now();
            setWalletState(response.data.wallet);
            renderWalletBalance(walletState.summary);
            renderTransactions(walletState.summary.transactions);

            if (!options.silent) {
                fireExoticSwal({
                    icon: 'success',
                    title: 'Wallet Updated',
                    text: response.data.message || 'Wallet refreshed.'
                });
            }
        }).fail(function(xhr) {
            if (!options.silent) {
                fireExoticSwal({
                    icon: 'error',
                    title: 'Wallet Error',
                    text: walletErrorMessage(xhr)
                });
            }
        }).always(function() {
            setRefreshLoading(false);
        });
    }

    function showWalletTopupModal(currencyHint, prefilledAmount, autoSubscribe) {
        var currency = walletState.summary.currency || (walletState.config.market && walletState.config.market.currency) || 'KES';
        var selectedAmount = typeof prefilledAmount === 'number' ? prefilledAmount : 0;
        var autoSubscribePayload = autoSubscribe && typeof autoSubscribe === 'object' ? autoSubscribe : null;
        var presets = walletState.config.topup_presets || ['500.00', '1000.00', '2000.00', '5000.00'];
        var minimumAmount = minimumTopupAmount();

        fireExoticSwal({
            title: 'Top Up Wallet',
            html:
                '<div class="wallet-topup__presets">' +
                    presets.map(function(preset) {
                        var amount = parseFloat(preset);
                        return '<button type="button" class="wallet-topup__preset" data-amount="' + amount + '">' + currency + ' ' + formatAmountShort(amount) + '</button>';
                    }).join('') +
                '</div>' +
                '<div class="wallet-topup__divider">or enter amount</div>' +
                '<div class="wallet-topup__custom-wrap">' +
                    '<span class="wallet-topup__custom-prefix">' + currency + '</span>' +
                    '<input type="number" class="wallet-topup__custom-input" id="wallet-custom-amount" min="' + minimumAmount + '" step="50" placeholder="Enter amount"' +
                        (prefilledAmount ? ' value="' + prefilledAmount + '"' : '') + ' />' +
                '</div>',
            showConfirmButton: true,
            confirmButtonText: 'Continue to Provider \u2192',
            showCloseButton: true,
            didOpen: function() {
                $(document).off('click.walletPreset').on('click.walletPreset', '.wallet-topup__preset', function() {
                    $('.wallet-topup__preset').removeClass('is-selected');
                    $(this).addClass('is-selected');
                    selectedAmount = parseInt($(this).data('amount'), 10);
                    $('#wallet-custom-amount').val('');
                });

                $('#wallet-custom-amount').off('input.walletCustom').on('input.walletCustom', function() {
                    var val = parseInt($(this).val(), 10);
                    if (val > 0) {
                        selectedAmount = val;
                        $('.wallet-topup__preset').removeClass('is-selected');
                    }
                });

                if (prefilledAmount) {
                    selectedAmount = prefilledAmount;
                    $('.wallet-topup__preset[data-amount="' + prefilledAmount + '"]').addClass('is-selected');
                }
            },
            preConfirm: function() {
                if (!selectedAmount || selectedAmount < minimumAmount) {
                    Swal.showValidationMessage('Please select or enter an amount (min ' + currency + ' ' + formatAmountShort(minimumAmount) + ')');
                    return false;
                }
                return selectedAmount;
            }
        }).then(function(result) {
            if (result.isConfirmed && result.value) {
                showWalletProviderModal(result.value, autoSubscribePayload);
            }
        });
    }

    function showWalletProviderModal(amount, autoSubscribePayload) {
        var currency = walletState.summary.currency || (walletState.config.market && walletState.config.market.currency) || 'KES';
        var providers = enabledProviders();

        if (!providers.length) {
            fireExoticSwal({
                icon: 'info',
                title: 'Wallet Top-Up Unavailable',
                text: 'No wallet top-up providers are enabled for this market.'
            });
            return;
        }

        fireExoticSwal({
            title: 'Pay ' + currency + ' ' + formatAmountShort(amount),
            html:
                '<div class="wallet-payment-methods">' +
                    providers.map(function(providerKey) {
                        var meta = providerMeta(providerKey);
                        var isMpesa = providerKey === 'mpesa_stk';
                        var iconClass = isMpesa ? 'wallet-payment-method__icon--mpesa' : 'wallet-payment-method__icon--card';
                        var icon = isMpesa
                            ? '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2" width="12" height="20" rx="2"></rect><line x1="12" y1="18" x2="12" y2="18"></line></svg>'
                            : '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>';
                        return '<div class="wallet-payment-method" data-wallet-provider="' + providerKey + '">' +
                            '<div class="wallet-payment-method__icon ' + iconClass + '">' + icon + '</div>' +
                            '<div class="wallet-payment-method__info">' +
                                '<div class="wallet-payment-method__name">' + meta.name + '</div>' +
                                '<div class="wallet-payment-method__desc">' + meta.description + '</div>' +
                            '</div>' +
                            '<div class="wallet-payment-method__arrow">\u203A</div>' +
                        '</div>';
                    }).join('') +
                '</div>',
            showConfirmButton: false,
            showCloseButton: true,
            didOpen: function() {
                $('.wallet-payment-method').off('click.walletProvider').on('click.walletProvider', function() {
                    initiateWalletTopup($(this).data('wallet-provider'), amount, autoSubscribePayload);
                });
            }
        });
    }

    function initiateWalletTopup(provider, amount, autoSubscribePayload) {
        fireExoticSwal({
            title: 'Preparing Payment',
            html: '<div class="exotic-modal__loader"><div class="exotic-loading-wave"><span></span><span></span><span></span><span></span></div><p class="exotic-modal__subtext">Creating your wallet top-up...</p></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            showCloseButton: false
        });

        var requestData = {
            provider: provider,
            amount: amount
        };

        if (autoSubscribePayload && autoSubscribePayload.enabled) {
            requestData.auto_subscribe_enabled = 1;
            requestData.auto_subscribe_product_id = autoSubscribePayload.product_id;
            requestData.auto_subscribe_duration = autoSubscribePayload.duration;
        }

        walletAjax('exotic_wallet_initiate_topup', requestData).done(function(response) {
            if (!response || !response.success || !response.data) {
                fireExoticSwal({
                    icon: 'error',
                    title: 'Top-Up Failed',
                    text: 'Wallet top-up returned an invalid response.'
                });
                return;
            }

            var data = response.data;
            var action = data.action || {};

            if (action.type === 'redirect' && action.url) {
                window.location.href = action.url;
                return;
            }

            if (action.type === 'stk_pending') {
                showWalletStkPending(data, amount);
                return;
            }

            fireExoticSwal({
                icon: 'success',
                title: 'Top-Up Started',
                text: data.message || 'Wallet top-up has been initiated.'
            });
        }).fail(function(xhr) {
            fireExoticSwal({
                icon: 'error',
                title: 'Top-Up Failed',
                text: walletErrorMessage(xhr)
            });
        });
    }

    function showWalletStkPending(data, amount) {
        var payment = data.payment || {};
        var action = data.action || {};
        var initialBalance = walletState.summary.balance;
        var pollEvery = parseInt(action.poll_after_seconds || walletState.config.topup_poll_interval_seconds || 10, 10) * 1000;
        var poller = null;

        fireExoticSwal({
            title: 'Check Your Phone',
            html:
                '<div class="payment-success">' +
                    '<p>A wallet top-up request for <strong>' + (walletState.summary.currency || 'KES') + ' ' + formatAmountShort(amount) + '</strong> has been sent to your phone.</p>' +
                    '<div class="status-check">' +
                        (crmStkRetryEnabled && action.retry_available ? '<button id="walletRetryStk" class="status-btn status-btn--primary">Resend STK Push</button>' : '') +
                        '<button id="walletRefreshNow" class="status-btn">Refresh Wallet</button>' +
                        '<div id="walletPendingStatus" class="status-result"></div>' +
                    '</div>' +
                '</div>',
            confirmButtonText: 'Close',
            showCloseButton: true,
            didOpen: function() {
                poller = setInterval(function() {
                    refreshWallet({ silent: true, force: true }).done(function() {
                        if (walletState.summary.balance > initialBalance) {
                            clearInterval(poller);
                            Swal.close();
                            fireExoticSwal({
                                icon: 'success',
                                title: 'Top-Up Received',
                                text: 'Your wallet balance has been updated.'
                            });
                        }
                    });
                }, pollEvery);

                $('#walletRefreshNow').on('click', function() {
                    refreshWallet({ force: true });
                });

                $('#walletRetryStk').on('click', function() {
                    walletAjax('exotic_wallet_retry_stk', { payment_id: payment.id }).done(function(retryResponse) {
                        $('#walletPendingStatus')
                            .removeClass('status-failed')
                            .addClass('status-success')
                            .html(buildStatusMarkup('success', (retryResponse.data && retryResponse.data.message) || 'A new STK push was sent.'))
                            .show();
                    }).fail(function(xhr) {
                        $('#walletPendingStatus')
                            .removeClass('status-success')
                            .addClass('status-failed')
                            .html(buildStatusMarkup('error', walletErrorMessage(xhr)))
                            .show();
                    });
                });
            },
            willClose: function() {
                if (poller) {
                    clearInterval(poller);
                }
            }
        });
    }

    $('#wallet-history-toggle').on('click', function() {
        var $history = $('#wallet-history');
        var isHidden = $history.prop('hidden');
        $history.prop('hidden', !isHidden);
        $(this).find('.wallet-chevron').toggleClass('is-open');
    });

    $('#wallet-topup-btn').on('click', function() {
        showWalletTopupModal();
    });

    $('#wallet-refresh-btn').on('click', function() {
        refreshWallet({ force: true });
    });

    function showInsufficientWalletPrompt(productId, name, price, currency, duration, deficit) {
        fireExoticSwal({
            title: 'Insufficient Balance',
            html: `
                <div class="wallet-insufficient">
                    <div class="wallet-insufficient__balance">
                        <span class="wallet-insufficient__label">Your wallet balance</span>
                        <span class="wallet-insufficient__value">${currency} ${formatAmountShort(walletState.summary.balance)}</span>
                    </div>
                    <div class="wallet-insufficient__needed">
                        <span class="wallet-insufficient__label">Amount needed</span>
                        <span class="wallet-insufficient__value">${currency} ${formatAmountShort(price)}</span>
                    </div>
                    <div class="wallet-insufficient__deficit">
                        <span class="wallet-insufficient__label">Top up at least</span>
                        <span class="wallet-insufficient__value wallet-insufficient__value--highlight">${currency} ${formatAmountShort(deficit)}</span>
                    </div>
                </div>
            `,
            confirmButtonText: 'Top Up ' + currency + ' ' + formatAmountShort(deficit),
            showCancelButton: true,
            cancelButtonText: 'Pay Directly Instead',
            showCloseButton: true
        }).then(function(result) {
            if (result.isConfirmed) {
                showWalletTopupModal(currency, Math.ceil(deficit), {
                    enabled: true,
                    product_id: parseInt(productId, 10),
                    duration: duration
                });
            }
        });
    }

    window.__crmWalletPayFromWallet = function(productId, name, price, currency, duration) {
        if (!walletFeature.enabled) {
            fireExoticSwal({
                icon: 'info',
                title: 'Wallet Unavailable',
                text: 'Wallet payments are not enabled on this site yet.'
            });
            return;
        }

        var walletBalance = parseFloat(walletState.summary.balance || 0);
        var deficit = price - walletBalance;
        var durationLabel = duration === 'monthly' ? 'Monthly' : duration === 'biweekly' ? 'Biweekly' : 'Weekly';

        if (walletBalance < price) {
            showInsufficientWalletPrompt(productId, name, price, currency, duration, deficit);
            return;
        }

        fireExoticSwal({
            title: 'Confirm Wallet Payment',
            html: `
                <div class="wallet-confirm">
                    <div class="wallet-confirm__package">
                        <span class="wallet-confirm__label">Plan</span>
                        <span class="wallet-confirm__value">${name} (${durationLabel})</span>
                    </div>
                    <div class="wallet-confirm__amount">
                        <span class="wallet-confirm__label">Amount</span>
                        <span class="wallet-confirm__value wallet-confirm__value--price">${currency} ${formatAmountShort(price)}</span>
                    </div>
                    <div class="wallet-confirm__remaining">
                        <span class="wallet-confirm__label">Remaining balance</span>
                        <span class="wallet-confirm__value">${currency} ${formatAmountShort(walletBalance - price)}</span>
                    </div>
                </div>
            `,
            confirmButtonText: 'Confirm Payment',
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            showCloseButton: true
        }).then(function(result) {
            if (result.isConfirmed) {
                window.executeWalletDebit(productId, name, price, currency, duration);
            }
        });
    };

    window.__crmWalletExecuteWalletDebit = function(productId, name, price, currency, duration) {
        fireExoticSwal({
            title: 'Processing Payment',
            html: `
                <div class="exotic-modal__loader">
                    <div class="exotic-loading-wave">
                        <span></span><span></span><span></span><span></span>
                    </div>
                    <p class="exotic-modal__subtext">Paying from your wallet...</p>
                </div>
            `,
            allowOutsideClick: false,
            showConfirmButton: false,
            showCloseButton: false
        });

        walletAjax('exotic_wallet_subscribe_from_wallet', {
            product_id: productId,
            duration: duration
        }).done(function(response) {
            if (!response || !response.success || !response.data) {
                fireExoticSwal({
                    icon: 'error',
                    title: 'Wallet Payment Failed',
                    text: 'Wallet subscription returned an invalid response.'
                });
                return;
            }

            setWalletState(response.data.wallet || walletState.summary);
            renderWalletBalance(walletState.summary);
            renderTransactions(walletState.summary.transactions);

            fireExoticSwal({
                icon: 'success',
                title: 'Payment Successful',
                html: `
                    <div style="text-align:center;margin-top:8px;">
                        <div style="font-size:15px;color:rgba(255,255,255,0.7);margin-bottom:6px;">
                            ${name} activated from your wallet
                        </div>
                        <div style="font-size:17px;font-weight:600;color:#E6C46A;">
                            Remaining balance: ${walletState.summary.currency} ${formatAmountShort(walletState.summary.balance)}
                        </div>
                    </div>
                `,
                confirmButtonText: 'Reload Account',
                showCloseButton: false
            }).then(function() {
                window.location.reload();
            });
        }).fail(function(xhr) {
            fireExoticSwal({
                icon: 'error',
                title: 'Wallet Payment Failed',
                text: walletErrorMessage(xhr)
            });
        });
    };

    window.payFromWallet = window.__crmWalletPayFromWallet;
    window.executeWalletDebit = window.__crmWalletExecuteWalletDebit;
    window.showWalletTopupModal = showWalletTopupModal;
    window.renderWalletBalance = function(payload) {
        setWalletState(payload);
        renderWalletBalance(walletState.summary);
    };
    window.renderTransactions = function(transactions) {
        renderTransactions(transactions || walletState.summary.transactions || []);
    };

    function initializeWallet() {
        if (!walletFeature.enabled) {
            var $amount = $('#wallet-balance-display .wallet-amount');
            $amount.removeClass('wallet-amount--loading');
            $amount.text('--');
            $amount.attr('data-wallet-amount', '0');

            $('.wallet-balance-sub')
                .text(String((window.exoticWalletFeature && window.exoticWalletFeature.reason) || 'Wallet payments are currently unavailable.'))
                .addClass('wallet-empty-hint');

            $('#wallet-last-topup').hide();
            $('#wallet-history-toggle').hide();
            $('#wallet-refresh-btn').hide();
            $('#wallet-history').prop('hidden', true);
            return;
        }

        setWalletState(runtime.summary || {});
        renderWalletBalance(walletState.summary);
        renderTransactions(walletState.summary.transactions);

        var params = new URLSearchParams(window.location.search || '');
        if (params.get('wallet_refresh') === '1') {
            refreshWallet({ force: true, silent: true }).always(function() {
                var status = params.get('wallet_payment_status');
                fireExoticSwal({
                    icon: status === 'completed' ? 'success' : 'info',
                    title: status === 'completed' ? 'Top-Up Received' : 'Payment Processing',
                    text: status === 'completed'
                        ? 'Your wallet balance has been updated.'
                        : 'Your payment is still processing. You can refresh again in a moment.'
                });
            });
        } else if (!walletState.summary.refreshed_at && runtime.userId) {
            refreshWallet({ silent: true, force: true });
        }
    }

    initializeWallet();

})(jQuery);
</script>
<?php if ( is_front_page() && ! wp_script_is('chat-init', 'done') ) : ?>
<script id="chat-init-loader">
(function (w, d) {
    var chatSrc = 'https://cloud.board.support/account/js/init.js?id=1369683147';
    var booted = false;

    function boot() {
        if (booted || d.getElementById('chat-init')) {
            return;
        }

        booted = true;

        var script = d.createElement('script');
        script.id = 'chat-init';
        script.async = true;
        script.setAttribute('data-cfasync', 'false');
        script.src = chatSrc;
        (d.body || d.documentElement).appendChild(script);
    }

    if ('requestIdleCallback' in w) {
        w.requestIdleCallback(boot, { timeout: 4000 });
    } else {
        w.addEventListener('load', function () {
            w.setTimeout(boot, 1500);
        }, { once: true });
    }

    ['pointerdown', 'keydown', 'touchstart'].forEach(function (eventName) {
        w.addEventListener(eventName, boot, { once: true, passive: true });
    });
})(window, document);
</script>
<?php endif; ?>
</body>
</html>
<!--
Lovers can see to do their amorous rites
By their own beauties; or, if love be blind,
It best agrees with night. Come, civil night,
-->
