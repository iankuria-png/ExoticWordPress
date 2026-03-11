<?php
if (!defined('ABSPATH')) {
    exit;
}

$chat_id = isset($chat_context['chat_id']) ? (string) $chat_context['chat_id'] : '1369683147';
$department_id = isset($chat_context['department_id']) ? (int) $chat_context['department_id'] : 0;
$country_code = isset($chat_context['country_code']) ? (string) $chat_context['country_code'] : '';
$chat_host = isset($chat_context['host']) ? (string) $chat_context['host'] : '';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <title><?php echo esc_html(get_bloginfo('name') . ' | Chat Support'); ?></title>
    <?php wp_head(); ?>
</head>
<body class="exotic-chat-landing-page">
    <main class="exotic-chat-landing" aria-live="polite">
        <section class="exotic-chat-card">
            <p class="exotic-chat-eyebrow">Support</p>
            <h1>Welcome to Exotic Chat</h1>
            <p class="exotic-chat-copy">We are preparing your support session.</p>
            <div class="exotic-chat-loader" aria-hidden="true">
                <span class="exotic-chat-loader-dot"></span>
                <span class="exotic-chat-loader-track"></span>
            </div>
            <ul id="exotic-chat-badges" class="exotic-chat-badges" aria-label="Support highlights">
                <li class="exotic-chat-badge">
                    <span class="exotic-chat-badge-dot"></span>
                    Privacy
                </li>
                <li class="exotic-chat-badge">
                    <span class="exotic-chat-badge-dot"></span>
                    24/7
                </li>
                <li class="exotic-chat-badge exotic-chat-badge--live">
                    <span class="exotic-chat-badge-dot"></span>
                    Live
                </li>
            </ul>
            <p id="exotic-chat-status" class="exotic-chat-status">Initializing chat...</p>
            <button id="exotic-chat-focus-fallback" class="exotic-chat-fallback" type="button" hidden>
                Tap to start typing
            </button>
        </section>
    </main>
    <script>
    window.exoticChatLandingRuntime = {
        chatId: <?php echo wp_json_encode($chat_id); ?>,
        departmentId: <?php echo (int) $department_id; ?>,
        countryCode: <?php echo wp_json_encode($country_code); ?>,
        host: <?php echo wp_json_encode($chat_host); ?>
    };
    if (window.exoticChatLandingRuntime.departmentId > 0) {
        window.SB_DEFAULT_DEPARTMENT = window.exoticChatLandingRuntime.departmentId;
    }
    </script>
    <script id="chat-init" data-cfasync="false" src="https://cloud.board.support/account/js/init.js?id=<?php echo esc_attr($chat_id); ?>"></script>
    <?php wp_footer(); ?>
</body>
</html>
