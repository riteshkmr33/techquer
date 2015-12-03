$(document).ready(function() {
	$('ul.ticker').bxSlider({
	  mode: 'vertical',
	  slideMargin: 5,
	  pager:false,
	  controls:false,
	  auto:true,
	  pause:3000,
	});
	$('ul.slider-content').bxSlider({
	  slideMargin:0,
	  pager:false,
	  controls:false,
	  auto:true,
	  pause:3000,
	});
	$('ul.news-carousel').bxSlider({
	  minSlides: 2,
	  maxSlides: 2,
	  slideWidth: 270,
	  slideMargin: 10,
	  pager:false,
	});
	$('.footer-nav-scroll').click(function() {
    	$('body,html').animate({scrollTop:0},500);
  	});
  	$('.info_box .close').click(function(e){
		e.preventDefault();
		$(this).parent().slideUp();
	});
});
$(document).on('mousemove', '.user-rate-active' , function (e) {
	var rated = $(this);
	if( rated.hasClass('rated-done') ){
		return false;
	}
	if (!e.offsetX){
		e.offsetX = e.clientX - $(e.target).offset().left;
	}
	var offset = e.offsetX + 4;
	if (offset > 100) {
		offset = 100;
	}
	rated.find('.user-rate-image span').css('width', offset + '%');
	var score = Math.floor(((offset / 10) * 5)) / 10;
	if (score > 5) {
		score = 5;
	}
});
$(document).on('mouseout', '.user-rate-active' , function (e) {
	var rated = $(this);
	if( rated.hasClass('rated-done') ){
		return false;
	}
	var post_rate = rated.attr('data-rate');
	rated.find(".user-rate-image span").css('width', post_rate + '%');
});