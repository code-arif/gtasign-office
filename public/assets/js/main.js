(function ($) {
	"use strict";

	$(document).ready(function () {

		$(".menu_trigger").click(function(){
			$(".popup-menu, .overlay-bg").addClass("active");
		});
		$(".menu-x, .mobile-nav ul li a").click(function(){
			$(".popup-menu, .overlay-bg").removeClass("active");
		});


		$(".search-btn button").click(function(){
			$(".popup-search, .overlay-bg").addClass("active");
		});
		$(".search-x, .overlay-bg").click(function(){
			$(".popup-search, .overlay-bg").removeClass("active");
		});



		// Hero Slider (Owl Carousel)
		$(".hero-slider").owlCarousel({
			items: 1,
			nav: true,
			dots: false,
			loop: true,
			margin: 0,
			autoplay: true,
			autoplayTimeout: 5000,
			smartSpeed: 1500,
			navText: [
				"<img src='assets/img/angle-left.svg'>",
				"<img src='assets/img/angle-right.svg'>"
			],
			responsive : {
				0 : {
					dots:true
				},
				768 : {
					dots:false
				}
			}
		});
		// Hero Slider (Owl Carousel)
		$(".setup-slider").owlCarousel({
			items: 1,
			nav: true,
			dots: false,
			loop: true,
			margin: 0,
			autoplay: true,
			autoplayTimeout: 5000,
			smartSpeed: 1500,
			navText: [
				"<img src='assets/img/angle-left.svg'>",
				"<img src='assets/img/angle-right.svg'>"
			]
		});

		// Featherlight Gallery Init
		$('.gallery a').featherlightGallery({
			previousIcon: '«',
			nextIcon: '»',
			galleryFadeIn: 300,
			galleryFadeOut: 300,
			openSpeed: 200,
			closeSpeed: 200
		});

		
		

		// ===============================
		// Wholesale Products Quantity Control
		// ===============================

	

	}); // document.ready end


	// Increase button
		$(document).on("click", ".increase-v", function () {
			let $input = $(this).closest(".choice-item").find("input");
			let current = parseInt($input.val()) || 0;
			$input.val(current + 1);
		});

		// Decrease button
		$(document).on("click", ".decrease-v", function () {
			let $input = $(this).closest(".choice-item").find("input");
			let current = parseInt($input.val()) || 0;
			if (current > 0) {
				$input.val(current - 1);
			}
		});


	$(window).on("load", function () {
		// Future window load scripts here
	});

})(jQuery);
