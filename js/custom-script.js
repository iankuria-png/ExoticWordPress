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
	if(w > 640){
	jQuery(".bodybox .girl").mouseover(function(){
		jQuery(this).find(".vip-div").hide();
		jQuery(this).find(".premiumlabel").hide();
		jQuery(this).find(".model-info").hide();
		jQuery(this).find(".video-set").hide();
		jQuery(this).find(".girl-overlay").show();
	})
	
	jQuery(".bodybox .girl").mouseout(function(){
		jQuery(this).find(".vip-div").show();
		jQuery(this).find(".premiumlabel").show();
		jQuery(this).find(".model-info").show();
		jQuery(this).find(".video-set").show();
		jQuery(this).find(".girl-overlay").hide();
	})
	}else{
		jQuery(".bodybox .girl").click(function(){
			
		jQuery(".bodybox .girl").find(".vip-div").show();
		jQuery(".bodybox .girl").find(".premiumlabel").show();
		jQuery(".bodybox .girl").find(".model-info").show();
		jQuery(".bodybox .girl").find(".video-set").show();
		jQuery(".bodybox .girl").find(".girl-overlay").hide();
		
		
		jQuery(this).find(".vip-div").hide();
		jQuery(this).find(".premiumlabel").hide();
		jQuery(this).find(".model-info").hide();
		jQuery(this).find(".video-set").hide();
		jQuery(this).find(".girl-overlay").show();
	})
	
	
	}



	jQuery(".open-country").click(function(){
		jQuery(".slidercountries").show();
		return false;
	})



	jQuery(".open-search").click(function(){
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
	jQuery(document).on("click", ".phone-number-box, .call-now-box", function(e){
		e.stopPropagation();
	});

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
        jQuery(".mobile-menu-div").addClass("fixed-position");

       

    }

	

	if (jQuery(this).scrollTop() < 150) {

	 jQuery(".online-escort-counter-div").removeClass("fixed-position");
	 jQuery(".mobile-menu-div").removeClass("fixed-position");

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
