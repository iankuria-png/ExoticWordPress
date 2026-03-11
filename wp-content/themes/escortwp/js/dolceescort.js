jQuery(document).ready(function($) {
	if ($.fn && typeof $.fn.checkator === 'function') {
		$(':checkbox,:radio').not('.ios-checkbox').checkator();
	}

	var start_mobile_size = 960;

	var isphone = false;
	if($('body').hasClass('isphone')) { var isphone = true; }

	vcenter = function() {
		$('.vcenter').each(function(index, el) {
			$(this).css('margin-top', ($(this).parent().height() - $(this).height()) / 2);
		});
	}
	vcenter();

	$('.hamburger-menu').on('click', function(event) {
		$(this).hide();
		$('header .header-nav').slideDown();
	});

	if($('.all-header-slider .sliderall .slider').length) {
		var owlcarouselautoplay = false;
		if($('.all-header-slider').data('autoscroll') == "yes") {
			owlcarouselautoplay = true;
		}

		$('.all-header-slider .sliderall .slider').owlCarousel({
			items: 6,
			loop: false,
			nav: true,
			dots: false,
			center: false,
			autoplay: owlcarouselautoplay,
			autoplayTimeout: 4000,
			autoplaySpeed: 600,
			margin: 10,
			autoplayHoverPause: true,
			navText: ['<span class="icon-left-open"></span>','<span class="icon-right-open"></span>'],
			stagePadding: 0,
			responsive : {
				0 : {
					items: 2,
				},
				// breakpoint from 500 up
				500 : {
					items: 4,
				},
				// breakpoint from 700 up
				700 : {
					items: 5,
				},
				// breakpoint from 1150 up
				1150 : {
					items: 6,
				}
			}
		});
	} // if($('.all-header-slider .sliderall .slider').length) {

	$('.headerlang').change(function() {
		var lang = $(this).val();
		Cookies.set('sitelang', lang);
		location.reload();
	});

	if($('.country-list li').length) {
		$('.country-list li').each(function(index, el) {
			if($(this).find('ul').length) {
				$(this).prepend('<span class="iconlocation icon-angle-down"></span>')
			} else {
				$(this).prepend('<span class="iconlocation icon-minus"></span>')
			}
		});
		
		$('.country-list li ul').hide();
	}
	$('.country-list .current-cat').parentsUntil('.country-list').show();
	$('.country-list .current-cat > ul').show();
	$('.country-list .current-cat-parent > .icon-angle-down').removeClass("icon-angle-down").addClass("icon-angle-up");
	$('.country-list .current-cat > .icon-angle-down').removeClass("icon-angle-down").addClass("icon-angle-up");

	$('.country-list li .iconlocation').on('click', function(){
		if($(this).hasClass('icon-angle-down')) {
			$(this).parent().find("ul").first().show();
			$(this).removeClass("icon-angle-down").addClass("icon-angle-up");
		} else {
			$(this).parent().find("ul").first().hide();
			$(this).removeClass("icon-angle-up").addClass("icon-angle-down");
		}
	});

	function prepare_responsive() {
		var headerwidth = $(window).width();
		if(isphone || headerwidth <= start_mobile_size) {
			window.isMobile = true;
		} else {
			window.isMobile = false;
		}

		if(isphone || headerwidth <= start_mobile_size) {
			if($('.select2').length) {
				$('select.select2-hidden-accessible').select2('destroy');
			}
		} else {
			if($('.select2').length) {
				$('.select2').select2({minimumResultsForSearch: 20, width: 'auto', dropdownAutoWidth : true});
			}
		}

		if(isphone || headerwidth <= start_mobile_size) {
			// responsive images in profile page
			// only show the images if they are in viewport
			function show_profile_images() {
				if($('.mobile-ready-img').length) {
					$('.mobile-ready-img').each(function() {
						if(isElementInViewport($(this)) && (!$(this).attr('src') || $(this).attr('src') != $(this).data('responsive-img-url'))) {
							$(this).css('opacity', '0').attr('src', $(this).data('responsive-img-url')).fadeTo('slow', 1);
						}
					});
				}
			}
			show_profile_images();
			$(window).scroll(function(event){
				if(isphone || headerwidth <= start_mobile_size) {
					show_profile_images();
				}
			});


			$('a[rel=profile-photo]').on('click', function(event) {
				event.preventDefault();
			});
			var mobile_text = $('.profile-page-no-photos .for-browsers').data('mobile-text');
			$('.profile-page-no-photos .for-browsers').data('original-text', $('.profile-page-no-photos .for-browsers').html()).find('p').text(mobile_text);

			var mobile_text_v = $('.profile-page-no-videos .for-browsers').data('mobile-text');
			$('.profile-page-no-videos .for-browsers').data('original-text', $('.profile-page-no-videos .for-browsers').html()).find('p').text(mobile_text_v);

			$('.header-nav').on('click', 'a', function(event) { if($(this).siblings('ul').length == "1") event.preventDefault(); });
		} else {
			if($('.bigimage').length && $('.lockedsection').length) {
				$('.bigimage').removeClass('col100').show();
			}
			if($('.mobile-ready-img').length) {
				$('.mobile-ready-img').each(function() {
					if($(this).attr('src') != $(this).data('original-url')) {
						$(this).attr('src', $(this).data('original-url'));
					}
				});
			};
			$('.profile-page-no-photos .for-browsers').html($('.profile-page-no-photos .for-browsers').data('original-text'));
			$('.profile-page-no-videos .for-browsers').html($('.profile-page-no-videos .for-browsers').data('original-text'));
		} // responsive images in profile page

		if ($.isFunction($.fn.fancybox)) {
			// open image viewer on image click
			if(isphone || headerwidth <= start_mobile_size) {
				$("a[data-fancybox=\"profile-photo\"]").fancybox({
					loop: true,
					infobar: false,
					thumbs : {
						autoStart : false,
					},
					buttons: [
						"zoom",
						"fullScreen",
						"thumbs",
						"close"
					],
				});
			} else {
				$("a[data-fancybox=\"profile-photo\"]").fancybox({
					loop: true,
					infobar: false,
					thumbs : {
						autoStart : true,
					},
					buttons: [
						"zoom",
						"fullScreen",
						"thumbs",
						"close"
					],
				});
			}

			// open video player on video click
			$('a[data-fancybox=\"profile-video\"]').fancybox({
				infobar: false,
				thumbs : {
					autoStart : false,
				},
				beforeClose: function() {
					$("video").each(function() {
						$(this).get(0).pause();
					});
				}
			});
		}

		if(isphone || headerwidth <= start_mobile_size) {
			$(".sidebar-left .countries h4").on('click', function(){
				if($(this).parent().find('ul.country-list').hasClass('active')) {
					$(this).parent().find('ul.country-list').slideUp('fast', function() {
						$(this).removeClass('active');
					});
				} else {
					$(this).parent().find('ul.country-list').slideDown('fast', function() {
						$(this).addClass('active');
					});
				}
			});
		}

		$('.bodybox-homepage .see-more-button').on('click', function(event) {
			event.preventDefault();
			window.location = $(this).siblings('.see-all-bottom').attr('href');
		});

		if($('header .header-nav').length) {
			$('header').removeClass('header-mobile1 header-mobile2');
			if($('header .header-nav').offset().top < $('header .subnav-menu-wrapper').offset().top) {
				$('header').addClass('header-mobile1');
				if($('header .header-nav').offset().top < $('header .subnav-menu-wrapper').offset().top) {
					$('header').addClass('header-mobile2');
					$('header .subnav-menu').attr('style', '');
				}
			}
		}

		if($('.single-profile-page .profile-header').length) {
			var profile_header = $('.single-profile-page .profile-header');
			if($('.single-profile-page .girlsingle .phone-box').length) {
				var profile_header_name = $('.single-profile-page .girlsingle .profile-header-name');
				var phone_box = $('.single-profile-page .girlsingle .phone-box');
				var available_on_box;
				if($('.single-profile-page .girlsingle .available-on').length) {
					available_on_box = $('.single-profile-page .girlsingle .available-on');
				}
				profile_header.removeClass('profile-header-mobile1 profile-header-mobile2');
				if(phone_box.offset().top > profile_header_name.offset().top ||
				(available_on_box && available_on_box.offset().top > profile_header_name.offset().top)) {
					profile_header.addClass('profile-header-mobile1');
					if($('.single-profile-page .girlsingle .profile-header-name-info').length) {
						var profile_header_name_info = $('.single-profile-page .girlsingle .profile-header-name-info');
						if(phone_box.offset().top > profile_header_name_info.offset().top || 
							(available_on_box && available_on_box.offset().top > profile_header_name_info.offset().top)) {
							// profile_header.addClass('profile-header-mobile2');
						}
					}
				}
			} else if($('.single-profile-page .girlsingle .profile-header-name-info').length) {
				profile_header.removeClass('profile-header-mobile1 profile-header-mobile2');
				if($('.single-profile-page .girlsingle .profile-header-name-info').offset().top > $('.single-profile-page .girlsingle .profile-header-name').offset().top) {
					// profile_header.addClass('profile-header-mobile1 profile-header-mobile2');
				}
			}
		}
	} // function prepare_responsive()

	var headerwidth = $(window).width();
	prepare_responsive();
	$(window).resize(function(){
		prepare_responsive();
		vcenter();
	});

	//right-sidebar js START
	//escort js
	$(".sidebar-right .dropdownlinks-dropdown h4").on('click', function(){
		$(this).parent().find('ul').stop().slideToggle('fast');
	});

	$(".sidebar-right .buypremium").on('click', function(){
		$('.buypremium_details').slideToggle("fast");
	});
	$(".buypremium_details .closebtn_box").on('click', function(){
		$('.buypremium_details').slideUp("fast");
	});

	$(".sidebar-right .buyfeatured").on('click', function(){
		$('.buyfeatured_details').slideToggle('fast');
	});
	$(".buyfeatured_details .closebtn_box").on('click', function(){
		$('.buyfeatured_details').slideUp("fast");
	});

	// mobile expiration text
	$('.sidebar-expire-notice-mobile').on('click', function(event) {
		var payment_plan = $(this).data('payment-plan')
		$(this).siblings('.sidebar-expire-notice[data-payment-plan="'+payment_plan+'"]').slideToggle('fast');
	});

	// admin menu js
	$(".manuallyactivatetour").on('click', function(){
		$('.manuallyactivatetour_div').slideDown('fast');
		$('.manuallyactivatevip_div').slideUp('fast');
	});
	$(".manuallyactivatevip").on('click', function(){
		$('.manuallyactivatevip_div').slideDown('fast');
		$('.manuallyactivatetour_div').slideUp('fast');
	});

	// escort menu shown to admin
	$('.upgradebutton').on('click', function(){
		$('.upgradebuttons').slideDown();
		$('.upgradeescortbox').slideUp();
		$(this).parent().siblings('.upgradeescortbox').slideDown('fast');
		$(this).parent().slideUp('fast');
	});
	$('.upgradeescortbox .closebtn_box').on('click', function(){
		if($(this).parent().siblings('.upgradebuttons')) {
			$(this).parent().siblings('.upgradebuttons').slideDown('fast');
		}
		$(this).parent().slideUp('fast');
	});

	//agency js
	$('.addescort').on('click', function(){
		$('.addnewescortform').slideToggle("slow");
		$('.addescort span').toggle();
	});
	if (location.hash === "#edit") {
		show_profile_section("editprofile");
	}
	if (location.hash === "#delete-account") {
		show_profile_section("delete");
	}
	$('.agencyeditbuttons a').on('click', function(){
		show_profile_section($(this).attr("class"));
	});
	$('.dropdownlinks .delete').on('click', function(){
		show_profile_section('delete');
	});
	function show_profile_section(class_name) {
		$('.agency_options_dropdowns').slideUp();
		$('.agency_options_'+class_name).slideDown();
		$('.girlsingle').slideUp();
		$('.select2').select2();
	}
	$('.agency_options_dropdowns .closebtn').on('click', function(){
		$(this).parent().slideUp();
		$('.girlsingle').slideDown();
	});
	//right-sidebar js END

	//animate loader div
	loader=function(l, nohtml){
		if(!nohtml) {
			$(l).html('<div class="loader rad3"><span class="rad3"></span></div>');
		}
		if($(l).find('.loader').is(':visible')){
			$(l).find('.loader span').css('left', '-100%').animate({ 'left': '100%'}, 2000, function(){
				loader(l, "no");
			});
		}
	}

	function isElementInViewport(el) {
	    var docViewTop = $(window).scrollTop();
	    var docViewBottom = docViewTop + $(window).height();

	    var elemTop = $(el).offset().top;
	    var elemBottom = elemTop + $(el).height();
	    //return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
	    return ((elemTop >= docViewTop) && (elemTop <= docViewBottom) || (elemBottom >= docViewTop) && (elemBottom <= docViewBottom));
	}

	if($('.woocommerce .woocommerce-orders-table__cell-order-number').length) {
			var text = "";
		$('.woocommerce .woocommerce-orders-table__cell-order-number').each(function(index, el) {
			text = $(el).find('a').text();
			$(el).text(text);
		});
	}
	if($('.woocommerce .woocommerce-orders-table__cell-order-total').length) {
		var total_div = $('.woocommerce .woocommerce-orders-table__cell-order-total');
		var total_html = total_div.find('.amount').get(0).outerHTML;
		total_div.html(total_html);
	}
	if($('.woocommerce #order_review .shop_table .product-quantity').length) {
		$('.woocommerce #order_review .shop_table .product-quantity').text('');
	}
	if($('.woocommerce #order_review .shop_table tfoot tr').length) {
		$('.woocommerce #order_review .shop_table tfoot tr').first().remove();
	}
	if($('.woocommerce form#order_review').length) {
		$('.woocommerce form#order_review').addClass('checkout woocommerce-checkout');
	}

    $('.registrationcomplete .change-email-button').on('click', function(event) {
    	$('.registrationcomplete .change-email-address').slideUp();
    	$('.registrationcomplete .change-email-address-form').css('display', 'inline-block');
    });	
});

function getCookie(c_name) {
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0; i<ARRcookies.length;i++) {
		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		x=x.replace(/^\s+|\s+$/g,"");
		if (x==c_name) {
			return unescape(y);
		}
	}
}
