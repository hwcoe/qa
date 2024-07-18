Plugin Name: HWCOE Q and A  
Description: Create, categorize, and reorder FAQs and insert them into a page with a shortcode. Adapted from FAQ plugin by http://madebyraygun.com
Version: 1.0.5
License: GPL  
Author: Herbert Wertheim College of Engineering  
Author URI: http://www.eng.ufl.edu  

Create, categorize, and reorder FAQs and insert them into a page with a shortcode.

== Description ==

Create, categorize, and reorder FAQs and insert them into a page with a shortcode. Questions are shown/hidden with a simple jQuery animation; users without javascript enabled will click through to the single question page. Uses the native Custom Post Type functionality in WordPress 3.0. and above.

The plugin also includes functionality from the <a href="http://geekyweekly.com/mypageorder">My Page Order</a> plugin by Andrew Charlton.

== Installation ==

Extract the zip file and upload the contents to the wp-content/plugins/ directory of your WordPress installation and then activate the plugin from plugins page. 

The plugin registers a new custom post type, so you'll want to update your permalinks. No need to change your permalink structure, just go to "Settings->Permalinks" and click "Save Changes" without making any modifications.

Use the shortcode [qa] to insert your FAQs into a page.

If you want to sort your FAQs into categories, you can optionally use the cat attribute with the category slug. Example: [qa cat="cheese"] will return only FAQs in the "Cheese" category. You can find the category slug on the FAQ Categories page.

You can also insert a single FAQ with the format [aq id="1234"] where 1234 is the post ID.

* Note: the cat & the id attributes are mutually exclusive. Don't use both in the same shortcode.

Use the shortcode [search-qa] to insert a search form that will search only the FAQs.
		

== Frequently Asked Questions ==

= With Javascript disabled, clicking on FAQ titles causes a 404 error.

Did you update your permalinks? (See installation).


== Changelog ==

1.0.5
- Bugfix: implode() for PHP 8
- Update CSS for Mercury theme

1.0.4
- Change faqpageorder_Widget constructor method name to avoid warnings on PHP 7 and newer

1.0.3
- Bugfix: Prevent filtering input field in quicksearch form from automatically gaining focus on page load

1.0.2
- Fix "Trying to get property of non-object" notice 

1.0.1 
* Fix "undefined index" PHP notice for faq_category

1.0.0
* Change plugin name
* Make FAQs keyboard navigable
* Add ARIA attributes for accessibility
* Update styles to be more theme agnostic

0.2.8

* Properly filter the FAQ content, which preserves formatting and allows shortcodes to be entered in the FAQ entry.

0.2.7 

* Fix for undefined variable qa_shortcode

* Workaround for themes that try to limit number of FAQs returned.

0.2.6

* Properly enqueue styles to they can be deregistered.

* Support for custom WP-CONTENT directories

0.2.5

* Added support for revisions of faq posts

* Updated colums displayed on main faq page

* Added ability to filter by category on main faq page

* Added ability to filter by category on reorder page

* Category is shown in each line item on the reorder page

0.2.4

* Added support for Qtranslate plugin.

0.2.3

* Changed method of shortcode content filtering for more compatibility.

0.2.2

* Plugin now filters the FAQ content for other shortcodes (such as captions & audio players)

0.2.1 

* Fixed small bug in search shortcode that resulted in no results under some configurations.

0.2.0

* Including WordPress version of jQuery instead of Google CDN version to limit SSL errors.


* Changed categories to hierarchical for clarity.

* Added shortcode for search form that can be added anywhere on your site.


= 0.1.3 = 

* You can now grab single FAQs by ID and insert them into a page.

= 0.1.2 =

* Changed permalinks to FAQs to a more compatible format in case javascript is disabled.

= 0.1.1 =

* Added category titles to displayed FAQs

= 0.1 =

* First version