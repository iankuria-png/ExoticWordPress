jQuery(document).ready(function($) {
	var height = $(window).height();
	$('.tosoverlay').css('height', height);

	$(window).scroll(function() {
    	$('.tosoverlay').css('top', $(this).scrollTop() + "px");
		var height = $(window).height();
		$('.tosoverlay').css('height', height);
	});
});