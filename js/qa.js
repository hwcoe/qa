jQuery(function ($) {
	jQuery.fn.quicksearch = function (opt) {
		
		function is_empty(i) 
		{
			return (i === null || i === undefined || i === false) ? true: false;
		}
		
		function strip_html(input)
		{
			var regexp = new RegExp(/\<[^\<]+\>/g);
			var output = input.replace(regexp, "");
			output = $.trim(output.toLowerCase().replace(/\n/, '').replace(/\s{2,}/, ' '));
			return output;
		}
		
		function get_key()
		{
			var input = strip_html($('input[id="' + options.randomElement + '"]').val());
			
			if (input.indexOf(' ') === -1)
			{
				return input;
			}
			else
			{
				return input.split(" ");
			}
		}
		
		function test_key(k, value, type)
		{
			if (type === "string")
			{
				return test_key_string(k, value);
			}
			else
			{
				return test_key_arr(k, value);
			}
		}
		
		function test_key_string(k, value)
		{
			return (value.indexOf(k) > -1);
		}
		
		function test_key_arr(k, value)
		{
			for (var i = 0; i < k.length; i++) {
				var test = value.indexOf(k[i]);	
				if (test === -1) {	
					return false;
				}
			}			
			return true;
		}
		
		function select_element(el) 
		{
			if (options.hideElement === "grandparent") 
			{
				return $(el).parent().parent();
			} 
			else if (options.hideElement === "parent") 
			{
				return $(el).parent();
			} 
			else
			{
				return $(el);
			}
		}
		
		function stripe(el)
		{
			if (doStripe)
			{
				var i = 0;
				select_element(el).filter(':visible').each(function () {
					
					for (var j = 0; j < stripeRowLength; j++)
					{
						if (i === j)
						{
							$(this).addClass(options.stripeRowClass[i]);
							
						}
						else
						{
							$(this).removeClass(options.stripeRowClass[j]);
						}
					}
					i = (i + 1) % stripeRowLength;
				});
			}
		}
		
		function fix_widths(el)
		{
			jQuery(el).find('td').each(function () {
				jQuery(this).attr('width', parseInt($(this).css('width')));
			});
		}
		
		function loader(o) {
			if (options.loaderId) 
			{
				var l = $('input[rel="' + options.randomElement + '"]').parent().find('.loader');
				if (o === 'hide') 
				{
					l.hide();
				} 
				else 
				{
					l.show();
				}
			}			
		}
		
		function place_form() {
			var formPosition = options.position;
			var formAttached = options.attached;

			if (formPosition === 'before') {
				jQuery(formAttached).before(make_form());
			} else if (formPosition === 'prepend') {
				jQuery(formAttached).prepend(make_form());
			} else if (formPosition === 'append') {
				jQuery(formAttached).append(make_form());
			} else {
				jQuery(formAttached).after(make_form());
			}
		}
				
		function make_form_label()
		{
			if (!is_empty(options.labelText)) {
				return '<label for="' + options.randomElement + '" '+
							'class="' + options.labelClass + '">'
							+ options.labelText
							+ '</label> ';	
			}
			return '';
		}
		
		function make_form_input()
		{
			var val = (!is_empty(options.inputText)) ? options.inputText : ""
			return '<input type="text" value="' + val + '" rel="' + options.randomElement  + '" class="' + options.inputClass + '" id="' + options.randomElement + '" /> ';
		}
		
		function make_form_loader()
		{
			if (!is_empty(options.loaderImg)) {
				return '<img src="' + options.loaderImg + '" alt="Loading" id="' + options.loaderId + '" class="' + options.loaderClass + '" />';
			} else {
				return '<span id="' + options.loaderId + '" class="' + options.loaderClass + '">' + options.loaderText + '</span>';
			}
		}
		
		function make_form()
		{
			var f = (!options.isFieldset) ? 'form' : 'fieldset';
			return '<' + f + ' action="#" ' + 'id="'+ options.formId + '" ' + 'class="quicksearch">' +
						make_form_label() +	make_form_input() + make_form_loader() +
					'</' + f + '>';
		}
			
		function focus_on_load()
		{
			jQuery('input[rel="' + options.randomElement + '"]').get(0).focus();
		}
		
		function toggle_text() {
			jQuery('input[rel="' + options.randomElement + '"]').focus(function () {
				if ($(this).val() === options.inputText) {
					$(this).val('');
				}
			});
			jQuery('input[rel="' + options.randomElement + '"]').blur(function () {
				if ($(this).val() === "") {
					$(this).val(options.inputText);
				}
			});
		}
		
		function get_cache(el) 
		{
			return $(el).map(function(){
				return strip_html(this.innerHTML);
			});
		}
		
		function init()
		{
			place_form();
			if (options.fixWidths) fix_widths(el);
			if (options.focusOnLoad) focus_on_load();
			if (options.inputText != "" && options.inputText != null) toggle_text();
			
			cache = get_cache(el);
			
			stripe(el);
			loader('hide');
		}
		
		function qs() 
		{
			clearTimeout(timeout);
			timeout = setTimeout(function () {
				
				loader('show');
				
				setTimeout(function () {
					options.onBefore();
					
					var k = get_key();
					var k_type = (typeof k);
					var i = 0;
					
					k = options.filter(k);
					
					if (k != "")
					{
						if (typeof score[k] === "undefined")
						{
							score[k] = new Array();
							cache.each(function (i) {
								if (test_key(k, cache[i], k_type))
								{
									score[k][i] = true;
								}
							});
						}
						
						if (score[k].length === 0)
						{
							select_element(el).hide();
						}
						else
						{
							$(el).each(function (i) {
								if (score[k][i])
								{
									select_element(this).show();
								}
								else
								{
									select_element(this).hide();
								}
							});
							
						}
					}
					else
					{
						select_element(el).show();
					}
				
					stripe(el);
				}, options.delay/2);
				
				setTimeout( function () { 
					loader('hide');
				}, options.delay/2);
				
				options.onAfter();
				
			}, options.delay/2);
		}
		
		var options = $.extend({
			position: 'prepend',
			attached: 'body',
			formId: 'quicksearch',
			labelText: 'Quick Search',
			labelClass: 'qs_label',
			inputText: null,
			inputClass: 'qs_input',
			loaderId: 'loader',
			loaderClass: 'loader',
			loaderImg: null,
			loaderText: 'Loading...',
			stripeRowClass: null,
			hideElement: null,
			delay: 500,
			focusOnLoad: false,
			onBefore: function () { },
			onAfter: function () { },
			filter: function (i) { 
				return i;
			},
			randomElement: 'qs' + Math.floor(Math.random() * 1000000),
			isFieldset: false,
			fixWidths: false
		}, opt);
		
		var timeout;
		var score = {};
		var stripeRowLength = (!is_empty(options.stripeRowClass)) ? options.stripeRowClass.length : 0;
		var doStripe = (stripeRowLength > 0) ? true : false;
		var el = this;
		var cache;
		var selector = $(this).selector;
		
		jQuery.fn.extend({
			reset_cache: function () {
				el = $(selector);
				cache = get_cache(el);
			}
		});
		
		init();
		
		jQuery('input[rel="' + options.randomElement + '"]').keydown(function (e) {
			var keycode = e.keyCode;
			if (!(keycode === 9 || keycode === 13 || keycode === 16 || keycode === 17 || keycode === 18 || keycode === 38 || keycode === 40 || keycode === 224))
			{
				qs();
			}
		});
		
		jQuery('form.quicksearch, fieldset.quicksearch').submit( function () { return false; });
		
		return this;
	};
});

jQuery(document).ready(function($) {
	//Searchable FAQ code below
	jQuery('.qa-faqs .faqs dl dt').each(function() {
		$(this).nextUntil('dt').andSelf().wrapAll('<div class="entry"></div>');
		$(this).nextUntil('dt').andSelf().wrapAll('<div class="entry"></div>');
	});

	jQuery('.faqs div.entry dd').hide();

	jQuery('.entry').quicksearch({
		position: 'prepend',
		attached: '.qa-faqs',
		labelText: 'Filter FAQs',
		loaderText: '',
		delay: 100
	});

	jQuery('.expand-collapse').click(function() {
		if($(this).text() == "Expand All") {
			$(this).text("Collapse All");
			$(this).addClass('open');
			$(this).attr('aria-pressed', 'true');
			$('.faqs div.entry dd').slideDown();
			$('.faqs div.entry dd').attr('aria-hidden', 'false');
			$('.faqs div.entry button').addClass('open');
			$('.faqs div.entry button').attr('aria-expanded', 'true');
		}
		else if($(this).text() == "Collapse All") {
			$(this).text("Expand All");
			$(this).removeClass('open');
			$(this).attr('aria-pressed', 'false');
			$('.faqs div.entry dd').slideUp();
			$('.faqs div.entry dd').attr('aria-hidden', 'true');
			$('.faqs div.entry button').removeClass('open');
			$('.faqs div.entry button').attr('aria-expanded', 'false');
		}
		return true;
	});

	jQuery('.faqs div.entry button').click(function() {
		$(this).closest('dt').next('dd').slideToggle();
		$(this).toggleClass('open');
		if ($(this).hasClass('open')) {
			$(this).attr('aria-expanded', 'true');
			$(this).closest('dt').next('dd').attr('aria-hidden', 'false');
		} else {
			$(this).attr('aria-expanded', 'false');
			$(this).closest('dt').next('dd').attr('aria-hidden', 'true');
		}
	
		if ($('.faqs button.open').length == $('.faqs button').length) {
			$('.expand-collapse').text("Collapse All");
			$('.expand-collapse').addClass('open');
		} else { 
			$('.expand-collapse').text("Expand All"); 
			$('.expand-collapse').removeClass('open'); 
		}
		return true;
	});
	
	if(jQuery('.qa-faqs').height() < 600) {jQuery('.faqs .small').hide();}
	
	jQuery('.qs_input').focus();
});