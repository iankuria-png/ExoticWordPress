(function ($) {
  'use strict';

  var $form = $('.exotic-campaign-form');
  if (!$form.length) {
    return;
  }

  var $formatInputs = $form.find('input[name="_campaign_format"]');
  var $cardBlock = $form.find('.exotic-campaign-format[data-format="card"]');
  var $imageBlock = $form.find('.exotic-campaign-format[data-format="image"]');
  var $preview = $('#campaign-live-preview .ad-card');

  function getFormat() {
    return $formatInputs.filter(':checked').val() || 'card';
  }

  function syncFormatBlocks() {
    var format = getFormat();
    if (format === 'image') {
      $cardBlock.hide();
      $imageBlock.show();
    } else {
      $cardBlock.show();
      $imageBlock.hide();
    }
  }

  function updatePreview() {
    var title = $.trim($form.find('input[name="post_title"]').val());
    var badge = $.trim($form.find('input[name="_campaign_badge_text"]').val());
    var copy = $.trim($form.find('textarea[name="_campaign_description"]').val());
    var cta = $.trim($form.find('input[name="_campaign_cta_text"]').val());
    var primary = $.trim($form.find('input[name="_campaign_color_primary"]').val()) || '#ab1c2f';
    var secondary = $.trim($form.find('input[name="_campaign_color_secondary"]').val()) || primary;

    $form.find('[data-preview-title]').text(title || 'Campaign Title');
    $form.find('[data-preview-badge]').text(badge || 'Badge');
    $form.find('[data-preview-copy]').text(copy || 'Campaign description preview.');
    $form.find('[data-preview-cta] span').first().text(cta || 'Learn More');

    $preview.css({
      '--campaign-cta-start': primary,
      '--campaign-cta-end': secondary
    });

    $form.find('[data-preview-cta]').css('background', 'linear-gradient(135deg, ' + primary + ', ' + secondary + ')');
  }

  function initMediaPicker() {
    var frame;
    var $imageId = $('#campaign-image-id');
    var $previewBox = $('#campaign-image-preview');

    $('#campaign-image-select').on('click', function (event) {
      event.preventDefault();

      if (frame) {
        frame.open();
        return;
      }

      frame = wp.media({
        title: (window.exoticCampaignAdmin && window.exoticCampaignAdmin.chooseImage) || 'Choose Campaign Image',
        button: {
          text: (window.exoticCampaignAdmin && window.exoticCampaignAdmin.useImage) || 'Use this image'
        },
        multiple: false,
        library: { type: 'image' }
      });

      frame.on('select', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        $imageId.val(attachment.id || '');

        if (attachment.sizes && attachment.sizes.medium_large) {
          $previewBox.html('<img src="' + attachment.sizes.medium_large.url + '" alt="" />');
        } else if (attachment.url) {
          $previewBox.html('<img src="' + attachment.url + '" alt="" />');
        }
      });

      frame.open();
    });

    $('#campaign-image-remove').on('click', function (event) {
      event.preventDefault();
      $imageId.val('');
      $previewBox.empty();
    });
  }

  function initColorPickers() {
    var $pickers = $form.find('.campaign-color-picker');
    if (!$pickers.length || typeof $.fn.wpColorPicker !== 'function') {
      return;
    }

    $pickers.wpColorPicker({
      change: updatePreview,
      clear: updatePreview
    });
  }

  $formatInputs.on('change', function () {
    syncFormatBlocks();
    updatePreview();
  });

  $form.on('input change', 'input, textarea, select', updatePreview);

  syncFormatBlocks();
  initColorPickers();
  initMediaPicker();
  updatePreview();
})(jQuery);
