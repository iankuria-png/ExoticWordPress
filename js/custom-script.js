jQuery(document).ready(function() {
          jQuery(".r-more").click(function() {
            jQuery(this).hide();
            jQuery(".top-mobile-expand p").show();
            jQuery(".top-mobile-expand .r-less").show();
            return false;
          })

          jQuery(".r-less").click(function() {
            jQuery(this).hide();
            jQuery(".top-mobile-expand p").hide();
            jQuery(".top-mobile-expand .r-more").show();
            return false;
          })
      
      
	
	jQuery(".mobile-menu-icon").click(function(){
		jQuery(".mobile-menu-div-content").show();
	})
	
	jQuery(".close-menu").click(function(){
		jQuery(".mobile-menu-div-content").hide();
	})
	
	jQuery(".mobile-login-icon").click(function(){
		jQuery(".mobile-login-div-content").toggle()
	})
	
	
	var w = jQuery(document).width();
	var showOverlay = function($card){
		$card.find(".vip-div").hide();
		$card.find(".premiumlabel").hide();
		$card.find(".model-info").hide();
		$card.find(".video-set").hide();
		$card.find(".girl-overlay").show();
	};
	var hideOverlay = function($card){
		$card.find(".vip-div").show();
		$card.find(".premiumlabel").show();
		$card.find(".model-info").show();
		$card.find(".video-set").show();
		$card.find(".girl-overlay").hide();
	};

	if(w > 640){
		jQuery(".bodybox .girl .thumbwrapper").on("mouseenter", function(){
			showOverlay(jQuery(this).closest(".girl"));
		});
		jQuery(".bodybox .girl .thumbwrapper").on("mouseleave", function(){
			hideOverlay(jQuery(this).closest(".girl"));
		});
	}else{
		jQuery(".bodybox .girl .thumbwrapper").on("click", function(){
			var $card = jQuery(this).closest(".girl");
			hideOverlay(jQuery(".bodybox .girl"));
			showOverlay($card);
		});
	}



jQuery(".open-country").click(function(){
		jQuery(".mobile-menu-div-content").hide();
		jQuery(".mobile-login-div-content").hide();
		jQuery(".slidercountries").show();
		return false;
	})



jQuery(".open-search").click(function(){
		jQuery(".mobile-menu-div-content").hide();
		jQuery(".quicksearch").show();
		return false;
	})



	jQuery(".close-country").click(function(){
		jQuery(".slidercountries").hide();
		return false;
	})



	jQuery(".close-search").click(function(){
		jQuery(".quicksearch").hide();
		return false;
	})

	// Ensure phone/tel links are clickable without triggering card overlay
	jQuery(document).on("click touchstart", ".phone-number-box, .call-now-box, .contact-btn", function(e){
		e.stopPropagation();
	});

	// Contact button now always shows number; no reveal behavior needed

	// Location sidebar controls (desktop + overlay)
	jQuery(".location-expand").on("click", function(e){
		e.preventDefault();
		jQuery(".sidebar-left .country-list li ul").show();
		jQuery(".sidebar-left .country-list .iconlocation.icon-angle-down")
			.removeClass("icon-angle-down")
			.addClass("icon-angle-up");
	});

	jQuery(".location-collapse").on("click", function(e){
		e.preventDefault();
		jQuery(".sidebar-left .country-list li ul").hide();
		jQuery(".sidebar-left .country-list .iconlocation.icon-angle-up")
			.removeClass("icon-angle-up")
			.addClass("icon-angle-down");

		// Keep current category path visible
		jQuery(".sidebar-left .country-list .current-cat").parentsUntil(".country-list").show();
		jQuery(".sidebar-left .country-list .current-cat > ul").show();
		jQuery(".sidebar-left .country-list .current-cat-parent > .icon-angle-down")
			.removeClass("icon-angle-down")
			.addClass("icon-angle-up");
		jQuery(".sidebar-left .country-list .current-cat > .icon-angle-down")
			.removeClass("icon-angle-down")
			.addClass("icon-angle-up");
	});




jQuery(".close-online-escort").click(function(){

 	jQuery(this).parent().hide();

var dataString =  'action=set-session';



var str_this = jQuery(this);



		jQuery.ajax({

		type: "POST",

		url: 'https://www.exoticethiopia.com/wp-content/themes/escortwp2022-child/get-online-escort.php',

		data: dataString,

		success: function(data){

		}		

		})

		

	

return false;

})





jQuery(".hide-all").click(function(){

jQuery(".fullPopup").css("display","none");

return false;

})



jQuery(".show-popup").click(function(){

jQuery(".fullPopup").css("display","block");

return false;

})



})



jQuery(window).scroll(function() {

    if (jQuery(this).scrollTop() > 150) { // this refers to window

        jQuery(".online-escort-counter-div").addClass("fixed-position");

       

    }

	

	if (jQuery(this).scrollTop() < 150) {

	 jQuery(".online-escort-counter-div").removeClass("fixed-position");

	}

});









var count_escort_call = function() {

 

var dataString =  'action=get-online-escrot-count';
var url = "https://www.exoticethiopia.com?count_online_escort=yes";


var str_this = jQuery(this);



		jQuery.ajax({

		type: "GET",
		
		url: url,

		success: function(data){

		if(data != 0 ){

		jQuery('.online-escort-counter-div .count').html("Chat " + data + "escort now!");

		}else{

		jQuery('.online-escort-counter-div .count').html("");

		}

		}		

		})

		

return false;





};



var interval = 1000 * 1 * 30;



//setInterval(count_escort_call, interval);

;

// Sidebar ads carousel (auto-advance, pause on hover/focus)
jQuery(function() {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    jQuery('.sidebar-ad-carousel').each(function() {
        var $carousel = jQuery(this);
        var $slides = $carousel.find('.widgetadbox');
        if ($slides.length <= 1) {
            return;
        }

        var index = 0;
        var timer = null;
        var resumeTimer = null;

        var goTo = function(i) {
            var el = $carousel.get(0);
            if (!el || !el.scrollTo) {
                return;
            }
            index = (i + $slides.length) % $slides.length;
            var target = index * el.clientWidth;
            el.scrollTo({ left: target, behavior: 'smooth' });
        };

        var start = function() {
            if (timer) return;
            timer = setInterval(function() {
                goTo(index + 1);
            }, 4500);
        };

        var stop = function() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        };

        $carousel.on('mouseenter focusin', stop);
        $carousel.on('mouseleave focusout', start);
        $carousel.on('scroll', function() {
            stop();
            if (resumeTimer) {
                clearTimeout(resumeTimer);
            }
            resumeTimer = setTimeout(start, 6000);
        });

        start();
    });
});
