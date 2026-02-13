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
if ($_COOKIE['tos18'] != "yes" && get_option("tos18") == "1") {
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script>
// Global variable to store selected platform
let selectedPlatform = null;

jQuery(document).ready(function($) {
    if (typeof escortwp_user_id !== 'undefined' && $('.dropdownlinks-dropdown h4:contains("My Account")').length > 0) {
        
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
                
                if (response.success && response.data.is_private) {
                    addActivateButton();
                }
            },
            error: function(xhr, status, error) {
                console.log('Could not check profile status:', error);
            }
        });
    }
});

function addActivateButton() {
    var activateBtn = `
        <div class="activate-account-btn-wrapper" style="margin: 15px 0;">
            <button id="activate-account-btn" class="premium-button" 
                data-user-id="${escortwp_user_id}">
                <span class="button-icon">✨</span>
                <span class="button-text">Activate Account</span>
            </button>
            <style>
                .premium-button {
                    width: 100%;
                    padding: 12px;
                    border: none;
                    border-radius: 30px;
                    background: linear-gradient(135deg, #28a745, #20c997);
                    color: white;
                    font-weight: bold;
                    font-size: 15px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
                }
                .premium-button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
                }
                .premium-button:active {
                    transform: translateY(0);
                }
                .button-icon {
                    font-size: 18px;
                }
            </style>
        </div>
    `;

    $('.dropdownlinks-dropdown h4:contains("My Account")').closest('.dropdownlinks-dropdown')
        .prepend(activateBtn);

    // Handle click
    $('#activate-account-btn').click(function(e) {
        e.preventDefault();
        detectAndSetPlatform();
    });
}

function detectAndSetPlatform() {
    // Show loading state
    Swal.fire({
        title: 'Loading Platform Information',
        html: `
            <div style="margin: 20px 0;">
                <div class="loading-wave" style="display: flex; justify-content: center; gap: 5px; height: 30px;">
                    <div style="background: #28a745; height: 100%; width: 6px; animation: wave 1s ease-in-out infinite; animation-delay: 0.1s; border-radius: 3px;"></div>
                    <div style="background: #20c997; height: 100%; width: 6px; animation: wave 1s ease-in-out infinite; animation-delay: 0.2s; border-radius: 3px;"></div>
                    <div style="background: #28a745; height: 100%; width: 6px; animation: wave 1s ease-in-out infinite; animation-delay: 0.3s; border-radius: 3px;"></div>
                    <div style="background: #20c997; height: 100%; width: 6px; animation: wave 1s ease-in-out infinite; animation-delay: 0.4s; border-radius: 3px;"></div>
                </div>
                <style>
                    @keyframes wave {
                        0%, 60%, 100% { transform: scaleY(0.6); }
                        30% { transform: scaleY(1); }
                    }
                </style>
            </div>
            <p style="color: #666; margin-top: 15px;">Detecting your platform...</p>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });

    // Get current domain
    const currentDomain = window.location.hostname;
    
    // Fetch platforms from API
    $.ajax({
        url: 'https://testing.exotic-ads.com/api/platforms',
        method: 'GET',
        success: function(response) {
            // Check if response is valid
            if (!response || !response.platforms || !Array.isArray(response.platforms)) {
                console.error('Invalid platforms API response format:', response);
                Swal.fire({
                    icon: 'error',
                    title: 'Data Error',
                    text: response?.message || 'Received invalid platforms data format from server',
                    confirmButtonColor: '#28a745',
                    background: '#ffffff',
                    backdrop: 'rgba(40, 167, 69, 0.1)'
                });
                return;
            }
            
            const platforms = response.platforms;
            
            // Check if platforms array is empty
            if (platforms.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No platforms available',
                    text: response.message || 'Please contact support for assistance.',
                    confirmButtonColor: '#28a745',
                    background: '#ffffff',
                    backdrop: 'rgba(40, 167, 69, 0.1)'
                });
                return;
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
                Swal.fire({
                    icon: 'error',
                    title: 'Platform Not Found',
                    text: 'Could not identify your platform. Please contact support.',
                    confirmButtonColor: '#28a745',
                    background: '#ffffff',
                    backdrop: 'rgba(40, 167, 69, 0.1)'
                });
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
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: xhr.responseJSON?.message || 'Could not load platform information. Please check your connection and try again.',
                confirmButtonColor: '#28a745',
                background: '#ffffff',
                backdrop: 'rgba(40, 167, 69, 0.1)'
            });
        }
    });
}

function showPackageSelection() {
    // Show animated loading state
    Swal.fire({
        title: 'Loading Premium Packages',
        html: `
            <div style="margin: 20px 0;">
                <div class="loading-wave" style="display: flex; justify-content: center; gap: 5px; height: 30px;">
                    <div style="background: #28a745; height: 100%; width: 6px; animation: wave 1s ease-in-out infinite; animation-delay: 0.1s; border-radius: 3px;"></div>
                    <div style="background: #20c997; height: 100%; width: 6px; animation: wave 1s ease-in-out infinite; animation-delay: 0.2s; border-radius: 3px;"></div>
                    <div style="background: #28a745; height: 100%; width: 6px; animation: wave 1s ease-in-out infinite; animation-delay: 0.3s; border-radius: 3px;"></div>
                    <div style="background: #20c997; height: 100%; width: 6px; animation: wave 1s ease-in-out infinite; animation-delay: 0.4s; border-radius: 3px;"></div>
                </div>
                <style>
                    @keyframes wave {
                        0%, 60%, 100% { transform: scaleY(0.6); }
                        30% { transform: scaleY(1); }
                    }
                </style>
            </div>
            <p style="color: #666; margin-top: 15px;">Preparing exclusive offers for you...</p>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });

    // Fetch products from Laravel API
    $.ajax({
        url: 'https://testing.exotic-ads.com/api/products',
        method: 'GET',
        success: function(response) {
            Swal.close();
            
            // Check if response is valid and contains products array
            if (!response || !response.products || !Array.isArray(response.products)) {
                console.error('Invalid API response format:', response);
                Swal.fire({
                    icon: 'error',
                    title: 'Data Error',
                    text: response?.message || 'Received invalid data format from server',
                    confirmButtonColor: '#28a745',
                    background: '#ffffff',
                    backdrop: 'rgba(40, 167, 69, 0.1)'
                });
                return;
            }
            
            const products = response.products;
            
            // Check if products array is empty
            if (products.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No packages available',
                    text: response.message || 'Please contact support for assistance.',
                    confirmButtonColor: '#28a745',
                    background: '#ffffff',
                    backdrop: 'rgba(40, 167, 69, 0.1)'
                });
                return;
            }

            // Since product_id is null, show ALL available products
            // Build product list HTML with all products
            let productOptions = products.map(product => {
                // Format prices clearly
                const monthlyPrice = parseFloat(product.monthly_price).toLocaleString();
                const biweeklyPrice = parseFloat(product.biweekly_price).toLocaleString();
                const weeklyPrice = parseFloat(product.weekly_price).toLocaleString();
                
                return `
                <div class="package-card">
                    <div class="package-name">${product.name}</div>
                    
                    <div class="pricing-options">
                        <div class="pricing-option active" data-price="${product.monthly_price}" data-duration="monthly" data-product-id="${product.id}">
                            <div class="option-name">Monthly</div>
                            <div class="option-price">
                                <span class="currency">${product.currency}</span>
                                <span class="amount">${monthlyPrice}</span>
                            </div>
                        </div>
                        <div class="pricing-option" data-price="${product.biweekly_price}" data-duration="biweekly" data-product-id="${product.id}">
                            <div class="option-name">2 Weeks</div>
                            <div class="option-price">
                                <span class="currency">${product.currency}</span>
                                <span class="amount">${biweeklyPrice}</span>
                            </div>
                        </div>
                        <div class="pricing-option" data-price="${product.weekly_price}" data-duration="weekly" data-product-id="${product.id}">
                            <div class="option-name">Weekly</div>
                            <div class="option-price">
                                <span class="currency">${product.currency}</span>
                                <span class="amount">${weeklyPrice}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="package-features">
                        ${(product.description || 'Premium account features').split('\n').map(feature => 
                            `<div class="feature-item">
                                <span class="feature-icon">✓</span>
                                <span class="feature-text">${feature.trim()}</span>
                            </div>`
                        ).join('')}
                    </div>
                    <button class="select-package-btn" 
                        onclick="selectPackage(event, '${product.id}', '${product.name.replace(/'/g, "\\'")}', ${product.monthly_price}, ${product.biweekly_price}, ${product.weekly_price}, '${product.currency}')">
                        Select Plan
                    </button>
                </div>
            `}).join('');

            Swal.fire({
                title: 'Available Premium Plans',
                html: `
                    <div class="package-selection-header">
                        <div class="header-icon">🏆</div>
                        <h3>Unlock Exclusive Features for ${selectedPlatform.name}</h3>
                        <p>Select your preferred plan and billing option</p>
                        <div class="currency-display">
                            <small>Displaying prices in: ${products[0]?.currency || 'KES'}</small>
                        </div>
                    </div>
                    <div class="horizontal-scroll-container">
                        <div class="package-horizontal-container">
                            ${productOptions}
                        </div>
                    </div>
                    <style>
                        .package-selection-header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .package-selection-header .header-icon {
                            font-size: 40px;
                            margin-bottom: 10px;
                        }
                        .package-selection-header h3 {
                            margin: 5px 0;
                            color: #333;
                            font-size: 18px;
                        }
                        .package-selection-header p {
                            color: #666;
                            margin: 0;
                        }
                        .currency-display {
                            margin-top: 8px;
                            color: #888;
                            font-style: italic;
                        }
                        .horizontal-scroll-container {
                            width: 100%;
                            overflow-x: auto;
                            padding-bottom: 20px;
                            margin-top: 20px;
                        }
                        .package-horizontal-container {
                            display: inline-flex;
                            gap: 20px;
                            padding: 0 10px;
                        }
                        .package-card {
                            position: relative;
                            border: 1px solid #e0e0e0;
                            border-radius: 15px;
                            padding: 25px;
                            min-width: 280px;
                            transition: all 0.3s ease;
                            background: white;
                            overflow: hidden;
                            border-top: 3px solid #28a745;
                        }
                        .package-card:hover {
                            transform: translateY(-5px);
                            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                            border-color: #20c997;
                        }
                        .package-name {
                            font-size: 20px;
                            font-weight: bold;
                            margin-bottom: 15px;
                            color: #333;
                            text-align: center;
                        }
                        .pricing-options {
                            display: flex;
                            gap: 10px;
                            margin-bottom: 20px;
                            background: #f8f9ff;
                            padding: 10px;
                            border-radius: 10px;
                        }
                        .pricing-option {
                            flex: 1;
                            padding: 10px;
                            border-radius: 8px;
                            text-align: center;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        }
                        .pricing-option.active {
                            background: white;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                            border: 1px solid #20c997;
                        }
                        .pricing-option:not(.active):hover {
                            background: rgba(255,255,255,0.7);
                        }
                        .option-name {
                            font-size: 14px;
                            font-weight: bold;
                            color: #666;
                            margin-bottom: 5px;
                        }
                        .pricing-option.active .option-name {
                            color: #28a745;
                        }
                        .option-price {
                            font-size: 18px;
                            font-weight: bold;
                            color: #333;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            gap: 3px;
                            flex-direction: column;
                        }
                        .pricing-option.active .option-price {
                            color: #20c997;
                        }
                        .option-price .currency {
                            font-size: 12px;
                            margin-bottom: 2px;
                        }
                        .option-price .amount {
                            font-size: 16px;
                            font-weight: 800;
                        }
                        .package-features {
                            font-size: 14px;
                            color: #666;
                            line-height: 1.6;
                            margin-bottom: 20px;
                        }
                        .feature-item {
                            display: flex;
                            align-items: flex-start;
                            margin-bottom: 10px;
                        }
                        .feature-icon {
                            color: #28a745;
                            margin-right: 8px;
                            font-weight: bold;
                        }
                        .select-package-btn {
                            background: linear-gradient(135deg, #28a745, #20c997);
                            color: white;
                            border: none;
                            width: 100%;
                            padding: 12px;
                            border-radius: 30px;
                            font-weight: bold;
                            cursor: pointer;
                            transition: all 0.3s ease;
                        }
                        .select-package-btn:hover {
                            background: linear-gradient(135deg, #218838, #1aa179);
                        }
                        .swal2-popup {
                            max-width: 900px !important;
                            border-radius: 15px !important;
                        }
                        .swal2-title {
                            color: #333;
                        }
                        /* Scrollbar styling */
                        .horizontal-scroll-container::-webkit-scrollbar {
                            height: 8px;
                        }
                        .horizontal-scroll-container::-webkit-scrollbar-track {
                            background: #f1f1f1;
                            border-radius: 10px;
                        }
                        .horizontal-scroll-container::-webkit-scrollbar-thumb {
                            background: #28a745;
                            border-radius: 10px;
                        }
                        .horizontal-scroll-container::-webkit-scrollbar-thumb:hover {
                            background: #218838;
                        }
                    </style>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                width: 'auto'
            });

            // Add click handler for pricing options
            $('.pricing-option').click(function() {
                $(this).siblings().removeClass('active');
                $(this).addClass('active');
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: xhr.responseJSON?.message || 'Could not load packages. Please check your connection and try again.',
                confirmButtonColor: '#28a745',
                background: '#ffffff',
                backdrop: 'rgba(40, 167, 69, 0.1)'
            });
        }
    });
}

function selectPackage(event, id, name, monthlyPrice, biweeklyPrice, weeklyPrice, currency) {
    // Get the parent package card element
    const packageCard = $(event.target).closest('.package-card');
    
    // Get selected pricing option within this card
    const selectedOption = packageCard.find('.pricing-option.active');
    const price = parseFloat(selectedOption.data('price'));
    const duration = selectedOption.data('duration');
    
    Swal.fire({
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
                <div class="payment-method" onclick="showMobileMoneyOptions('${id}', '${name}', ${price}, '${currency}', '${duration}')">
                    <div class="payment-icon">📱</div>
                    <div class="payment-content">
                        <div class="payment-name">Mobile Money</div>
                        <div class="payment-desc">MPESA</div>
                    </div>
                    <div class="payment-arrow">→</div>
                </div>
                
                <div class="payment-method" onclick="initiateCardPayment('${id}', '${name}', ${price}, '${currency}', '${duration}')">
                    <div class="payment-icon">💳</div>
                    <div class="payment-content">
                        <div class="payment-name">Credit/Debit Card</div>
                        <div class="payment-desc">Visa, Mastercard, etc.</div>
                    </div>
                    <div class="payment-arrow">→</div>
                </div>
                
            </div>
            <style>
                .payment-header {
                    background: #f8f9ff;
                    padding: 15px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                    text-align: center;
                }
                .selected-package {
                    display: inline-flex;
                    align-items: center;
                    gap: 15px;
                    background: white;
                    padding: 10px 20px;
                    border-radius: 30px;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
                }
                .selected-platform {
                    margin-top: 10px;
                    color: #666;
                }
                .package-name {
                    font-weight: bold;
                    color: #333;
                }
                .package-price {
                    font-weight: bold;
                    color: #28a745;
                }
                .payment-method-container {
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    margin-top: 15px;
                }
                .payment-method {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    border: 1px solid #e0e0e0;
                    border-radius: 12px;
                    padding: 15px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    background: white;
                }
                .payment-method:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                    border-color: #20c997;
                }
                .payment-icon {
                    font-size: 28px;
                    width: 50px;
                    height: 50px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #f5f9ff;
                    border-radius: 50%;
                    color: #28a745;
                }
                .payment-content {
                    flex: 1;
                }
                .payment-name {
                    font-weight: bold;
                    font-size: 16px;
                    margin-bottom: 3px;
                    color: #333;
                }
                .payment-desc {
                    font-size: 13px;
                    color: #666;
                }
                .payment-arrow {
                    color: #999;
                    font-size: 20px;
                    padding-right: 5px;
                }
            </style>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        width: '500px'
    });
}

function showMobileMoneyOptions(id, name, price, currency, duration) {
    Swal.fire({
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
            <style>
                .mobile-money-header {
                    background: #f8f9ff;
                    padding: 15px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                }
                .package-info {
                    display: inline-flex;
                    align-items: center;
                    gap: 15px;
                    background: white;
                    padding: 10px 20px;
                    border-radius: 30px;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
                }
                .package-name {
                    font-weight: bold;
                    color: #333;
                }
                .package-price {
                    font-weight: bold;
                    color: #28a745;
                }
                .provider-prompt {
                    text-align: center;
                    color: #666;
                    margin: 15px 0;
                }
                .mobile-money-options {
                    display: flex;
                    justify-content: center;
                    gap: 30px;
                    margin: 20px 0;
                }
                .mobile-option {
                    cursor: pointer;
                    transition: all 0.3s ease;
                    padding: 20px;
                    border-radius: 12px;
                    text-align: center;
                    width: 150px;
                    background: white;
                    border: 1px solid #e0e0e0;
                }
                .mobile-option:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                    border-color: #20c997;
                }
                .provider-logo {
                    height: 50px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-bottom: 10px;
                }
                .provider-logo img {
                    max-height: 100%;
                    max-width: 100%;
                }
                .provider-name {
                    font-weight: bold;
                    color: #333;
                }
            </style>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        width: '500px'
    });
}

function requestPhoneNumber(id, name, price, currency, provider, duration) {
    Swal.fire({
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
            <style>
                .payment-summary {
                    background: #f8f9ff;
                    padding: 15px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                    text-align: center;
                }
                .package-name {
                    font-weight: bold;
                    color: #333;
                }
                .package-price {
                    font-weight: bold;
                    color: #28a745;
                    font-size: 18px;
                    margin-top: 5px;
                }
                .phone-input-container {
                    margin-top: 20px;
                }
                .phone-input-container label {
                    display: block;
                    text-align: left;
                    margin-bottom: 8px;
                    color: #555;
                    font-weight: 500;
                }
                .input-wrapper {
                    display: flex;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    overflow: hidden;
                }
                .country-code {
                    padding: 10px 12px;
                    background: #f5f5f5;
                    color: #666;
                    font-weight: bold;
                }
                .phone-input {
                    flex: 1;
                    padding: 10px 12px;
                    border: none;
                    outline: none;
                    font-size: 15px;
                }
                .phone-input:focus {
                    background: #f8f9ff;
                }
                .input-hint {
                    display: block;
                    text-align: left;
                    margin-top: 8px;
                    color: #888;
                    font-size: 13px;
                }
            </style>
        `,
        showCancelButton: true,
        confirmButtonText: 'Proceed to Payment',
        confirmButtonColor: '#28a745',
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
    Swal.fire({
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
            <style>
                .customer-info-form { text-align: left; }
                .form-group { margin-bottom: 15px; }
                .form-group label { 
                    display: block; 
                    margin-bottom: 5px; 
                    font-weight: 600; 
                    color: #333; 
                }
                .swal2-input { 
                    margin: 0 !important; 
                    width: 100% !important;
                    box-sizing: border-box;
                }
            </style>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Continue to Payment',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
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
    Swal.fire({
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
    fetch('https://testing.exotic-ads.com/api/initiate-card-payment', {
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
        Swal.fire({
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
        Swal.fire({
            icon: 'error',
            title: 'Payment Error',
            html: `
                <div style="text-align: left;">
                    <p>${error.message || 'Failed to initiate payment. Please try again.'}</p>
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; color: #666;">Technical Details</summary>
                        <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; font-size: 12px; overflow: auto; margin-top: 10px;">
${error.stack || 'No stack trace available'}
                        </pre>
                    </details>
                </div>
            `,
            confirmButtonColor: '#28a745'
        });
    });
}

// Alternative version without customer info collection (if you want to skip the form)
function initiateCardPaymentDirect(id, name, price, currency, duration = null, firstName = 'Customer', lastName = 'User', phone = '254700000000') {
    // Show loading indicator
    Swal.fire({
        title: 'Preparing Payment',
        html: `
            <div class="processing-payment">
                <div class="loading-spinner"><div class="spinner"></div></div>
                <p class="processing-text">Setting up secure payment gateway...</p>
            </div>
            <style>
                .processing-payment { text-align: center; }
                .loading-spinner { margin: 20px auto; width: 60px; height: 60px; position: relative; }
                .spinner {
                    width: 100%; height: 100%;
                    border: 5px solid #f3f3f3; border-top: 5px solid #28a745;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }
                @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                .processing-text { color: #666; margin-top: 15px; }
            </style>
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
    fetch('https://testing.exotic-ads.com/api/initiate-card-payment', {
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
        Swal.fire({
            icon: 'error',
            title: 'Payment Error',
            text: error.message || 'Failed to initiate payment. Please try again.',
            confirmButtonColor: '#28a745'
        });
    });
}


function showPaypalPayment(id, name, price, currency, duration) {
    Swal.fire({
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
            <style>
                .paypal-payment-container {
                    text-align: center;
                }
                .payment-summary {
                    background: #f8f9ff;
                    padding: 15px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                }
                .package-name {
                    font-weight: bold;
                    color: #333;
                }
                .package-price {
                    font-weight: bold;
                    color: #28a745;
                    font-size: 18px;
                    margin-top: 5px;
                }
                .paypal-logo {
                    margin: 20px 0;
                }
                .paypal-logo img {
                    height: 40px;
                }
                .paypal-description {
                    color: #666;
                    line-height: 1.5;
                </style>
            `,
        showCancelButton: true,
        confirmButtonText: 'Continue to PayPal',
        confirmButtonColor: '#28a745',
        icon: 'info',
        focusConfirm: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Show animated redirect
            Swal.fire({
                title: 'Redirecting to PayPal',
                html: `
                    <div class="redirect-animation">
                        <div class="loading-dots">
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                        <p>Please wait while we connect you to PayPal...</p>
                    </div>
                    <style>
                        .redirect-animation {
                            text-align: center;
                        }
                        .loading-dots {
                            display: flex;
                            justify-content: center;
                            gap: 8px;
                            margin: 20px 0;
                        }
                        .loading-dots div {
                            width: 12px;
                            height: 12px;
                            background: #28a745;
                            border-radius: 50%;
                            animation: bounce 1.4s infinite ease-in-out;
                        }
                        .loading-dots div:nth-child(1) {
                            animation-delay: -0.32s;
                        }
                        .loading-dots div:nth-child(2) {
                            animation-delay: -0.16s;
                        }
                        @keyframes bounce {
                            0%, 80%, 100% { transform: scale(0); }
                            40% { transform: scale(1); }
                        }
                    </style>
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
    Swal.fire({
        title: 'Complete Your Details',
        html: `
            <div class="user-details-form">
                <div class="form-group">
                    <label for="firstName">First Name *</label>
                    <input type="text" id="firstName" class="swal2-input" placeholder="Enter your first name" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name *</label>
                    <input type="text" id="lastName" class="swal2-input" placeholder="Enter your last name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email (Optional)</label>
                    <input type="email" id="email" class="swal2-input" placeholder="Enter your email">
                </div>
            </div>
            <style>
                .user-details-form {
                    text-align: left;
                }
                .form-group {
                    margin-bottom: 15px;
                }
                .form-group label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: bold;
                    color: #333;
                }
                .swal2-input {
                    width: 100% !important;
                    margin: 0 !important;
                    box-sizing: border-box;
                }
            </style>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Proceed to Payment',
        confirmButtonColor: '#28a745',
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
    Swal.fire({
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
            <style>
                .processing-payment {
                    text-align: center;
                }
                .loading-spinner {
                    margin: 20px auto;
                    width: 60px;
                    height: 60px;
                    position: relative;
                }
                .spinner {
                    width: 100%;
                    height: 100%;
                    border: 5px solid #f3f3f3;
                    border-top: 5px solid #28a745;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .processing-text {
                    color: #666;
                    margin-top: 15px;
                    margin-bottom: 10px;
                }
                .amount-text, .phone-text {
                    color: #333;
                    font-weight: bold;
                    margin: 5px 0;
                }
            </style>
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
        url: 'https://testing.exotic-ads.com/api/initiate-stk-payment',
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

function showPaymentSuccess(response, userDetails, phone, price) {
    // Extract payment data from the new response format
    const paymentData = response.data || response;
    const paymentId = paymentData.payment_id;
    
    Swal.fire({
        title: 'Payment Request Sent!',
        html: `
            <div class="payment-success">
                <div class="success-icon">📱</div>
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
                    <button id="resendStk" class="status-btn" style="background: #28a745; margin-right: 10px;">Resend STK Push</button>
                    <button id="checkStatus" class="status-btn">Check Payment Status</button>
                    <div id="statusResult" class="status-result" style="display: none; margin-top: 15px;"></div>
                </div>
                
                ${paymentData.sandbox_note ? `<div class="sandbox-notice"><small><em>${paymentData.sandbox_note}</em></small></div>` : ''}
                ${paymentData.production_note ? `<div class="production-notice"><small><em>${paymentData.production_note}</em></small></div>` : ''}
            </div>
            <style>
                .payment-success {
                    text-align: center;
                }
                .success-icon {
                    font-size: 50px;
                    margin-bottom: 15px;
                }
                .payment-details {
                    background: #e8f5e8;
                    padding: 15px;
                    border-radius: 10px;
                    margin: 20px 0;
                    text-align: left;
                }
                .payment-details ul {
                    list-style: none;
                    padding: 0;
                    margin: 10px 0 0 0;
                }
                .payment-details li {
                    padding: 5px 0;
                    border-bottom: 1px solid #d4edda;
                }
                .payment-details li:last-child {
                    border-bottom: none;
                }
                .manual-instructions {
                    background: #f8f9ff;
                    padding: 15px;
                    border-radius: 10px;
                    margin-top: 20px;
                    text-align: left;
                }
                .manual-instructions h4 {
                    margin-top: 0;
                    color: #333;
                }
                .manual-instructions ol {
                    padding-left: 20px;
                    margin-bottom: 0;
                }
                .manual-instructions li {
                    margin-bottom: 8px;
                }
                .status-check {
                    margin-top: 20px;
                    display: flex;
                    justify-content: center;
                    flex-wrap: wrap;
                    gap: 10px;
                }
                .status-btn {
                    background: #28a745;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                .status-btn:hover {
                    opacity: 0.9;
                }
                .status-btn:disabled {
                    background: #ccc;
                    cursor: not-allowed;
                }
                .status-result {
                    padding: 10px;
                    border-radius: 5px;
                    font-weight: bold;
                    width: 100%;
                    text-align: center;
                }
                .status-success {
                    background: #d4edda;
                    color: #155724;
                }
                .status-pending {
                    background: #fff3cd;
                    color: #856404;
                }
                .status-failed {
                    background: #f8d7da;
                    color: #721c24;
                }
                .sandbox-notice, .production-notice {
                    margin-top: 15px;
                    padding: 10px;
                    background: #e2e3e5;
                    border-radius: 5px;
                    font-style: italic;
                }
            </style>
        `,
        icon: 'success',
        confirmButtonColor: '#28a745',
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
            resendButton.addEventListener('click', () => {
                resendStkPush(paymentId, resendButton, statusResult);
            });
            
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
    button.disabled = true;
    button.textContent = 'Resending...';
    resultDiv.style.display = 'none';

    jQuery.ajax({
        url: 'https://testing.exotic-ads.com/api/resend-stk-push',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ payment_id: paymentId }),
        success: function(response) {
            console.log('Resend STK response:', response);
            
            if (response.status === true) {
                resultDiv.className = 'status-result status-success';
                resultDiv.innerHTML = '✅ STK Push resent successfully! Check your phone.';
                resultDiv.style.display = 'block';
                
                // Show success message
                Swal.fire({
                    title: 'STK Push Resent!',
                    text: 'A new payment request has been sent to your phone.',
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                });
            } else {
                resultDiv.className = 'status-result status-failed';
                resultDiv.innerHTML = '❌ Failed to resend STK Push. Please try again.';
                resultDiv.style.display = 'block';
            }
        },
        error: function(xhr) {
            console.error('Resend STK error:', xhr);
            resultDiv.className = 'status-result status-failed';
            resultDiv.innerHTML = '❌ Error resending STK Push. Please try again.';
            resultDiv.style.display = 'block';
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
    
    Swal.fire({
        title: 'Pending Payment Request',
        html: `
            <div class="pending-error">
                <div class="warning-icon">⚠️</div>
                <p>${response.error}</p>
                <div class="instructions">
                    <h4>What to do:</h4>
                    <ul>
                        ${instructions.map(instruction => `<li>${instruction}</li>`).join('')}
                    </ul>
                </div>
                <div class="retry-info">
                    <p><strong>Retry after:</strong> ${Math.floor((response.error_details.retry_after || 300) / 60)} minutes</p>
                </div>
            </div>
            <style>
                .pending-error {
                    text-align: center;
                }
                .warning-icon {
                    font-size: 50px;
                    margin-bottom: 15px;
                }
                .instructions {
                    background: #fff3cd;
                    padding: 15px;
                    border-radius: 10px;
                    margin: 20px 0;
                    text-align: left;
                }
                .instructions h4 {
                    margin-top: 0;
                    color: #856404;
                }
                .instructions ul {
                    margin-bottom: 0;
                    padding-left: 20px;
                }
                .instructions li {
                    margin-bottom: 8px;
                }
                .retry-info {
                    background: #e2e3e5;
                    padding: 10px;
                    border-radius: 5px;
                    margin-top: 15px;
                }
            </style>
        `,
        icon: 'warning',
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Understood'
    });
}

function showPaymentError(errorMessage) {
    Swal.fire({
        title: 'Payment Failed',
        text: errorMessage,
        icon: 'error',
        confirmButtonColor: '#28a745',
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
        url: 'https://testing.exotic-ads.com/api/check-payment-status',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ payment_id: paymentId }),
        success: function(response) {
            console.log('Status check response:', response);
            
            if (response.status === true) {
                const status = response.payment_status || response.data?.status;
                let statusClass, statusText, statusIcon;
                
                switch(status) {
                    case 'completed':
                    case 'success':
                        statusClass = 'status-success';
                        statusText = '✅ Payment Successful! Your subscription is now active.';
                        statusIcon = 'success';
                        
                        // Show success modal and redirect to exoticnairobi.com
                        if (!isAutoCheck) {
                            setTimeout(() => {
                                Swal.fire({
                                    title: 'Payment Confirmed!',
                                    html: 'Your payment has been processed successfully. Your subscription is now active.<br><br>You will be redirected to login.',
                                    icon: 'success',
                                    confirmButtonColor: '#28a745',
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
                        statusText = '❌ Payment Failed. Please try again.';
                        statusIcon = 'error';
                        break;
                        
                    case 'pending':
                    case 'initiated':
                        statusClass = 'status-pending';
                        statusText = '⏳ Payment is still being processed. Please wait...';
                        statusIcon = 'info';
                        break;
                        
                    case 'expired':
                        statusClass = 'status-failed';
                        statusText = '⏰ Payment request expired. Please try again.';
                        statusIcon = 'warning';
                        break;
                        
                    default:
                        statusClass = 'status-pending';
                        statusText = `📄 Payment status: ${status}`;
                        statusIcon = 'question';
                }
                
                if (!isAutoCheck) {
                    resultDiv.className = `status-result ${statusClass}`;
                    resultDiv.innerHTML = statusText;
                    resultDiv.style.display = 'block';
                }
                
            } else {
                if (!isAutoCheck) {
                    resultDiv.className = 'status-result status-failed';
                    resultDiv.innerHTML = '❌ Could not check payment status. Please try again.';
                    resultDiv.style.display = 'block';
                }
            }
        },
        error: function(xhr) {
            console.error('Status check error:', xhr);
            if (!isAutoCheck) {
                resultDiv.className = 'status-result status-failed';
                resultDiv.innerHTML = '❌ Error checking payment status. Please try again.';
                resultDiv.style.display = 'block';
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
</script>
<?php if ( is_front_page() ) : ?>
<!-- Support Board -->
<script id="chat-init" src="https://cloud.board.support/account/js/init.js?id=1369683147"></script>
<?php endif; ?>
</body>
</html>
<!--
Lovers can see to do their amorous rites
By their own beauties; or, if love be blind,
It best agrees with night. Come, civil night,
-->
