<?php
/*
Plugin Name: HWCOE Q and A
Description: Create and categorize FAQs and insert them into a page with a shortcode.
Author: HWCOE modified version originally from UF and Raygun
Author URI: https://www.eng.ufl.edu
Version: 1.0.5
*/ 

require_once(dirname(__FILE__).'/reorder.php');

$qa_version = "1.0.3";
// add our default options if they're not already there:
if (get_option('qa_version')  != $qa_version) {
    update_option('qa_version', $qa_version);}
   
// now let's grab the options table data
$qa_version = get_option('qa_version');

add_action( 'init', 'create_qa_post_types' );
function create_qa_post_types() {
	 $labels = array(
		'name' => _x( 'FAQ Categories', 'taxonomy general name' ),
		'singular_name' => _x( 'FAQ Category', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search FAQ Categories' ),
		'all_items' => __( 'All FAQ Categories' ),
		'parent_item' => __( 'Parent FAQ Category' ),
		'parent_item_colon' => __( 'Parent FAQ Category:' ),
		'edit_item' => __( 'Edit FAQ Category' ), 
		'update_item' => __( 'Update FAQ Category' ),
		'add_new_item' => __( 'Add New FAQ Category' ),
		'new_item_name' => __( 'New FAQ Category Name' ),
  ); 	
  	register_taxonomy('faq_category',array('qa_faqs'), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'faq-category' ),
  ));
	if( !wp_count_terms ('faq_category') > 0 ) {
		wp_insert_term(
			'Uncategorized',
			'faq_category',
			array('slug' => 'uncategorized')
		);
	}

	register_post_type( 'qa_faqs',
		array(
			'labels' => array(
				'name' => __( 'FAQs' ),
				'singular_name' => __( 'FAQ' ),
				'edit_item'	=>	__( 'Edit FAQ'),
				'add_new_item'	=>	__( 'Add FAQ')
			),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'post',
			'rewrite' => array( 'slug' => 'faq', 'with_front' => false ),
			'taxonomies' => array( 'FAQs '),
			'supports' => array('title','editor','revisions')	
		)
	);
}	


add_action('restrict_manage_posts','restrict_faq_listings_by_categories');
function restrict_faq_listings_by_categories() {
    global $typenow;
    global $wp_query;
    if ($typenow=='qa_faqs') {
        
		$tax_slug = 'faq_category';
        
		// retrieve the taxonomy object
		$tax_obj = get_taxonomy($tax_slug);
		$tax_name = $tax_obj->labels->name;
		// retrieve array of term objects per taxonomy
		$terms = get_terms($tax_slug);

		// output html for taxonomy dropdown filter
		echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
		echo "<option value=''>Show All $tax_name</option>";
		foreach ($terms as $term) {			
			// output each select option line, check against the last $_GET to show the current option selected
			echo '<option value='. $term->slug, isset( $_GET[$tax_slug] ) && $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>';
		}
		echo "</select>";
    }
}

function faq_cat_sort($a, $b) {
	return strcmp($a->description, $b->description);
}

function extract_ids($object){
	$res = array();
	foreach($object as $k=>$v) {
		$res[]= $v->term_id;
	}
	return $res;
}

function qa_shortcode($atts) {
	extract(shortcode_atts(array(
		'cat'	=> ''
	), $atts));
		
	// stuff that loads when the shortcode is called goes here
	global $listing_output;
	$termID = get_term_by('slug', $cat, 'faq_category');

	if ( !empty( $cat ) ) {
		$termchildren = get_term_children( $termID->term_id, 'faq_category' );
	} else {
		$termchildren = get_terms( 'faq_category', 'parent=0&hide_empty=0' ); 
		$termchildren = extract_ids($termchildren);
	}
		
	if ( empty ( $termchildren ) ) { 
		$termchildren[0] = $termID->term_id; 
	}
		
	foreach($termchildren as $child) :
		$term_order[] = get_term_by( 'id', $child, 'faq_category' );
	endforeach;
	usort($term_order, "faq_cat_sort");
	$termchildren = $term_order;
	
	if ( count($termchildren) > 1 ) {
		
		$listing_output .= '<ul class="qa-nav">';
		foreach($termchildren as $child) :
			$term = get_term_by( 'id', $child->term_id, 'faq_category' );
			if( $term->count > 0 ) {
				$listing_output .= '<li><a href="#' . $term->slug. '">'. $term->name.'</a></li>';
			}
		endforeach;
		$listing_output .= '</ul>';
	}
	
	$listing_output .= '<div class="qa-faqs" id="qa-faqs"><button class="expand-collapse" aria-pressed="false">Expand All</button>';

	foreach($termchildren as $child) :
		$term = get_term_by( 'id', $child->term_id, 'faq_category' );
		
		if( $term->count > 0 ):
		
			$listing_output .= '<div id="' .$term->slug.'" class="faqs">';
			if ( count($termchildren) > 1 ) { $listing_output .= '<h3>'. $term->name. '</h3>'; }
			$listing_output .= '<dl>'; 
		
			$q = new WP_Query(array(
				'order'          => 'ASC',
				'orderby' 		 => 'menu_order ID',
				'post_type'      => 'qa_faqs',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'faq_category'	 => $term->slug,
			));
		
			// Output term list		
			
			foreach ($q->posts as $item) :

				$listing_output .= "<dt>\n\t<button id=\"$item->ID\" aria-controls=\"$item->ID-content\" aria-expanded=\"false\">$item->post_title</button>\n</dt>\n\t<dd id=\"$item->ID-content\" aria-hidden=\"true\">" . apply_filters( 'the_content', $item->post_content );
					if ( is_user_logged_in() ) { 
						$edit_link = get_edit_post_link( $item->ID );
						$listing_output .= '<br><a class="edit-link" href="'. $edit_link . '">&raquo; Edit this FAQ</a>';
						}
				$listing_output .= "</dd>\n";

			endforeach;
		
			$listing_output .= "\t</dl>\t<a href=\"#qa-faqs\" class=\"small\">Back to Top</a>\t</div>\n";
	
		endif;
	endforeach;
	
	$listing_output .= "</div>\n";
	
	//echo $listing_output;
	return $listing_output;
	wp_reset_query();
	
	$qa_shortcode = do_shortcode( $qa_shortcode );
	return (__($qa_shortcode));
}//ends the qa_shortcode function

add_shortcode('qa', 'qa_shortcode');
// define the shortcode function

add_filter('manage_edit-qa_faqs_columns', 'qa_columns');
function qa_columns($columns) {
    $columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Question' ),
		'faq_category' => __( 'Categories' ),
		'date' => __( 'Date' )
	);
    return $columns;
}

add_action('manage_posts_custom_column',  'qa_show_columns');
function qa_show_columns($name) {
    global $post;
    switch ($name) {
        case 'faq_category':
            $faq_cats = get_the_terms(0, "faq_category");
			$cats_html = array();
			if(is_array($faq_cats)){
				foreach ($faq_cats as $term)
						array_push($cats_html, '<a href="edit.php?post_type=qa_faqs&faq_category='.$term->slug.'">' . $term->name . '</a>');

				echo implode(", ", $cats_html);
			}
			break;
		default :
			break;	
	}
}

add_shortcode('search-qa', 'qasearch_shortcode');
// define the shortcode function
function qasearch_shortcode($atts) {
		global $qasearch_shortcode;
		$qasearch_shortcode .= '<div class="search-qa"><form role="search" method="get" id="searchform" action="';
		$qasearch_shortcode .= get_bloginfo ( 'siteurl' ); 
		$qasearch_shortcode .='">
    <div><label class="screen-reader-text" for="s">Search FAQs:</label>
        <input type="text" value="" name="s" id="s" />
        <input type="hidden" name="post_type" value="qa_faqs" />
        <input type="submit" id="searchsubmit" value="Search" />
    </div>
</form></div>';
		
	return $qasearch_shortcode;
}//ends the qa-search_shortcode function

// scripts to go in the header and/or footer

function qa_init() {
	global $qa_version;
	if( ! is_admin() ) {
		wp_enqueue_script('jquery');
	}
 
  	wp_enqueue_script('qa',  plugins_url('js/qa.js', __FILE__), false, $qa_version, true); 
    wp_enqueue_style('qa',  plugins_url('q-and-a.css', __FILE__), false, $qa_version, 'screen'); 
}

add_action('init', 'qa_init');

// create the admin menu
// hook in the action for the admin options page
add_action('admin_menu', 'add_qa_option_page');

function add_qa_option_page() {
	// hook in the options page function
	add_options_page('FAQ/Q and A', 'FAQ/Q and A', 'manage_options', __FILE__, 'qa_options_page');

}

function qa_options_page() { 	// Output the options page
	global $qa_version ?>
	<div class="wrap">
	<?php screen_icon(); ?>
		<h2>Plugin Reference</h2>
		<p>You're using Q and A v. <?php echo $qa_version;?>.</p>
		<h3>Installation</h3>
		<p>Extract the zip file and upload the contents to the wp-content/plugins/ directory of your WordPress installation and then activate the plugin from plugins page. </p>
		<p>The plugin registers a new custom post type, so you'll want to update your permalinks. No need to change your permalink structure, just go to "Settings->Permalinks" and click "Save Changes" without making any modifications.</p>

		<h3>Use</h3>
		<p>Use shortcode <code>[qa]</code> to insert your FAQs into a page.</p>
		
		<p>If you want to sort your FAQs into categories, you can optionally use the <code>cat="category-slug"</code> attribute. Example: <code>[qa cat="cheese"]</code> will return only FAQs in the "Cheese" category. You can find the category slug in the <a href="<?php bloginfo('wpurl');?>/wp-admin/edit-tags.php?taxonomy=faq_category&post_type=qa_faqs">FAQ Categories page</a>.
			
		<p>Use the shortcode [search-qa] to insert a search form that will search only your FAQs.</p>
		
		<h3>Troubleshooting</h3>
		<dl>
			<dt>With Javascript disabled, clicking on FAQ titles causes a 404 error.</dt>
			<dd>Did you update your permalinks? (See installation).</dd>
		</dl>
		
	</div><!--//wrap div-->
<?php } ?>