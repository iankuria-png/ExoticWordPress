<?php
if (!defined('ABSPATH')) {
    exit;
}

$chat_id = isset($chat_context['chat_id']) ? (string) $chat_context['chat_id'] : '1369683147';
$department_id = isset($chat_context['department_id']) ? (int) $chat_context['department_id'] : 0;
$country_code = isset($chat_context['country_code']) ? (string) $chat_context['country_code'] : '';
$chat_host = isset($chat_context['host']) ? (string) $chat_context['host'] : '';
$language_code = isset($chat_context['language_code']) ? (string) $chat_context['language_code'] : Exotic_Chat_Language_Registry::DEFAULT_LANGUAGE;
$widget_language_code = isset($chat_context['widget_language_code']) ? (string) $chat_context['widget_language_code'] : Exotic_Chat_Language_Registry::DEFAULT_LANGUAGE;
$html_lang = isset($chat_context['html_lang']) ? (string) $chat_context['html_lang'] : Exotic_Chat_Language_Registry::DEFAULT_LANGUAGE;
$support_board_lang = isset($chat_context['support_board_lang']) ? (string) $chat_context['support_board_lang'] : Exotic_Chat_Language_Registry::DEFAULT_LANGUAGE;
$strings = isset($chat_context['strings']) && is_array($chat_context['strings']) ? $chat_context['strings'] : Exotic_Chat_Language_Registry::packaged_strings('en');
$chat_script_url = 'https://cloud.board.support/account/js/init.js?id=' . rawurlencode($chat_id) . '&lang=' . rawurlencode($support_board_lang);
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr($html_lang); ?>" dir="ltr">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@600;700&display=swap" rel="stylesheet">
    <title><?php echo esc_html(get_bloginfo('name') . ' | ' . ($strings['page_title_suffix'] ?? 'Chat Support')); ?></title>
    <?php wp_head(); ?>
</head>
<body class="exotic-chat-landing-page">
    <main class="exotic-chat-landing" aria-live="polite">
        <section class="exotic-chat-card">
            <p class="exotic-chat-eyebrow"><?php echo esc_html($strings['eyebrow'] ?? 'Support'); ?></p>
            <h1><?php echo esc_html($strings['title'] ?? 'Welcome to Exotic Chat'); ?></h1>
            <p class="exotic-chat-copy"><?php echo esc_html($strings['intro'] ?? 'We are preparing your support session.'); ?></p>
            <div class="exotic-chat-loader" aria-hidden="true">
                <span class="exotic-chat-loader-dot"></span>
                <span class="exotic-chat-loader-track"></span>
            </div>
            <ul id="exotic-chat-badges" class="exotic-chat-badges" aria-label="<?php echo esc_attr($strings['eyebrow'] ?? 'Support'); ?>">
                <li class="exotic-chat-badge">
                    <span class="exotic-chat-badge-dot"></span>
                    <?php echo esc_html($strings['badge_privacy'] ?? 'Privacy'); ?>
                </li>
                <li class="exotic-chat-badge">
                    <span class="exotic-chat-badge-dot"></span>
                    <?php echo esc_html($strings['badge_hours'] ?? '24/7'); ?>
                </li>
                <li class="exotic-chat-badge exotic-chat-badge--live">
                    <span class="exotic-chat-badge-dot"></span>
                    <?php echo esc_html($strings['badge_live'] ?? 'Live'); ?>
                </li>
            </ul>
            <p id="exotic-chat-status" class="exotic-chat-status"><?php echo esc_html($strings['status_initializing'] ?? 'Initializing chat...'); ?></p>
            <button id="exotic-chat-focus-fallback" class="exotic-chat-fallback" type="button" hidden>
                <?php echo esc_html($strings['cta_focus'] ?? 'Tap to start typing'); ?>
            </button>
        </section>
    </main>
    <script>
    window.exoticChatLandingRuntime = {
        chatId: <?php echo wp_json_encode($chat_id); ?>,
        departmentId: <?php echo (int) $department_id; ?>,
        countryCode: <?php echo wp_json_encode($country_code); ?>,
        host: <?php echo wp_json_encode($chat_host); ?>,
        languageCode: <?php echo wp_json_encode($language_code); ?>,
        widgetLanguageCode: <?php echo wp_json_encode($widget_language_code); ?>
    };
    if (window.exoticChatLandingRuntime.departmentId > 0) {
        window.SB_DEFAULT_DEPARTMENT = window.exoticChatLandingRuntime.departmentId;
    }
    </script>
    <script id="chat-init" data-cfasync="false" src="<?php echo esc_url($chat_script_url); ?>"></script>
    <?php wp_footer(); ?>
</body>
</html>
