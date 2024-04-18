define([
    'jquery',
    ], function ($) {
	"use strict";

	function setCookie(name, value, days) {
		var expires = "";
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = "; expires=" + date.toUTCString();
		}
		document.cookie = name + "=" + (value || "") + expires + "; path=/";
	}
	
	function getCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
		}
		return null;
	}
	
	/* notifySlider */
	(function ($) {
	    "use strict";
	    $.fn.notifySlider = function (options) {
	      	var defaults = {
		        autoplay   : true,
		        firsttime  : 3000,
		        speed      : 9000
	      	};

			var settings    = $.extend(defaults, options);
			var firsttime   = parseInt(settings.firsttime);
			var speed    	= parseInt(settings.speed);
			var autoplay    = settings.autoplay;

	      	var methods = {
		        init : function() {
					var closedNoti = getCookie('hgati_noti_closed');
					if (closedNoti) return;

			        return this.each(function() {
			        	methods.suggestLoad($(this));
			        });
		        },
		        
		        suggestLoad: function(suggest){
		            var el  = suggest.find('.notify-slider-wrapper');
		            suggest.find('.x-close').click(function() {
		                suggest.addClass('close');
						setCookie('hgati_noti_closed', 'true', 1); // 1 day expired
		            });
		            var slideCount    = suggest.find('.slider >.item').length;
		            var slideWidth    = suggest.find('.slider >.item').width();
		            var slideHeight   = suggest.find('.slider >.item').height();
		            var sliderUlWidth = slideCount * slideWidth;
		            /*suggest.find('.notify-slider').css({ width: slideWidth, height: slideHeight });*/
		            suggest.find('.notify-slider .slider').css({ width: sliderUlWidth});
		            suggest.find('.notify-slider .slider >.item:last-child').prependTo('.notify-slider .slider');
		            setTimeout(function(){
		            	el.slideDown('slow'); 
			            if(!autoplay) return;
			            setInterval(function () {
			                el.slideUp({
			                        duration:'slow', 
			                        easing: 'swing',
			                        complete: function(){
			                            methods.moveRight(suggest, slideWidth);
			                            setTimeout(function(){ el.slideDown('slow'); }, speed/2);
			                        }
			                    });

			            }, speed);
		            }, firsttime);
		        },

		        moveRight: function(suggest, slideWidth){
		            suggest.find('.notify-slider .slider').animate({
		                left: - slideWidth
		            }, 0, function () {
		                var slider = suggest.find('.notify-slider .slider');
		                suggest.find('.notify-slider .slider >.item:first-child').appendTo(slider);
		                slider.css('left', '');
		            })
		        }

	      	};

	      	if (methods[options]) {
	        	return methods[options].apply(this, Array.prototype.slice.call(arguments, 1));
	      	} else if (typeof options === 'object' || !options) {
	        	return methods.init.apply(this);
	      	} else {
	        	$.error('Method "' + method + '" does not exist in timer plugin!');
	      	}
	    }

	    $(document).ready(function($) {
		    $('.suggest-slider').each(function() {
		    	if($(this).hasClass('autoplay')){
		    		var config = $(this).data();
		    		$(this).notifySlider(config);
		    	}
		    });  
	    });
	    
  	})($);

	/* End notifySlider */
});