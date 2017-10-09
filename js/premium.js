jQuery( document ).ready( function() {
	jQuery( '#lightslider' ).lightSlider({
		item: 1,
		autoWidth: false,
		slideMove: 1, // slidemove will be 1 if loop is true
		slideMargin: 10,

		addClass: '',
		mode: "slide",
		useCSS: true,
		cssEasing: 'ease', //'cubic-bezier(0.25, 0, 0.25, 1)',//
		easing: 'linear', //'for jquery animation',////

		speed: 400, //ms'
		auto: true,
		loop: true,
		slideEndAnimation: true,
		pause: 5000,

		keyPress: false,
		controls: true,
		prevHtml: '<span class="dashicons dashicons-arrow-left-alt2">',
		nextHtml: '<span class="dashicons dashicons-arrow-right-alt2">',

		rtl:false,
		adaptiveHeight:false,

		vertical:false,
		verticalHeight:500,
		vThumbWidth:100,

		thumbItem:10,
		pager: true,
		gallery: false,
		galleryMargin: 5,
		thumbMargin: 5,
		currentPagerPosition: 'middle',

		enableTouch:true,
		enableDrag:true,
		freeMove:true,
		swipeThreshold: 40,

		responsive : [],

		onBeforeStart: function (el) {},
		onSliderLoad: function (el) {},
		onBeforeSlide: function (el) {},
		onAfterSlide: function (el) {},
		onBeforeNextSlide: function (el) {},
		onBeforePrevSlide: function (el) {}
	});
});