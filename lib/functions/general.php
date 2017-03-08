<?php
/**
 * General
 *
 * This file contains any general functions
 *
 * @package   Core_Functionality
 * @since        1.0.0
 * @link					https://github.com/billerickson/Core-Functionality
 * @author			Cap Web Solutions
 * @copyright  Copyright (c) 2016, Cap Web Solutions
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

add_filter( 'http_request_args', 'cws_core_functionality_hidden', 5, 2 );
/**
 * Do Not Update Plugin
 * @since 1.0.0
 *
 * This prevents you being prompted to update if there is a public plugin
 * with the same name.
 *
 * @author Mark Jaquith
 * @link http://markjaquith.wordpress.com/2009/12/14/excluding-your-plugin-or-theme-from-update-checks/
 *
 * @param array $r, request arguments
 * @param string $url, request url
 * @return array request arguments
 */

function cws_core_functionality_hidden( $r, $url ) {
	if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) )
		return $r; // Not a plugin update request. Bail immediately.
	$plugins = unserialize( $r['body']['plugins'] );
	unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
	unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
	$r['body']['plugins'] = serialize( $plugins );
	return $r;
}

// Use shortcodes in widgets
add_filter( 'widget_text', 'do_shortcode' );

//Remove theme and plugin editor links
add_action('admin_init','cws_hide_editor_and_tools');
function cws_hide_editor_and_tools() {
	remove_submenu_page('themes.php','theme-editor.php');
	remove_submenu_page('plugins.php','plugin-editor.php');
}

/*
 * Prevent the Jetpack publicize connections from being auto-selected,
 * Source: http://jetpack.me/2013/10/15/ever-accidentally-publicize-a-post-that-you-didnt/
 */
add_filter( 'publicize_checkbox_default', '__return_false' );

add_action( 'admin_menu', 'cws_remove_menus' );
/**
 * Remove Menu Items
 * @since 1.0.0
 *
 * Remove unused menu items by adding them to the array.
 * See the commented list of menu items for reference.
 *
 */
function cws_remove_menus () {
	global $menu;
	$restricted = array(__('Links'));
	// Example: $restricted = array(__('Dashboard'), __('Posts'), __('Media'), __('Links'), __('Pages'), __('Appearance'), __('Tools'), __('Users'), __('Settings'), __('Comments'), __('Plugins'));
	end ($menu);
	while (prev($menu)){
		$value = explode(' ',$menu[key($menu)][0]);
		if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){unset($menu[key($menu)]);}
	}
}

add_action( 'wp_before_admin_bar_render', 'cws_admin_bar_items' );
/**
 * Customize Admin Bar Items
 * @since 1.0.0
 * @link http://wp-snippets.com/addremove-wp-admin-bar-links/
 */
function cws_admin_bar_items() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'new-link', 'new-content' );
}

add_filter( 'menu_order', 'cws_custom_menu_order' );
add_filter( 'custom_menu_order', 'cws_custom_menu_order' );
/**
 * Customize Menu Order
 * @since 1.0.0
 *
 * @param array $menu_ord. Current order.
 * @return array $menu_ord. New order.
 *
 */
function cws_custom_menu_order( $menu_ord ) {
	if ( !$menu_ord ) return true;
	return array(
		'index.php', // this represents the dashboard link
		'edit.php?post_type=page', //the page tab
		'edit.php', //the posts tab
		'edit-comments.php', // the comments tab
		'upload.php', // the media manager
    );
}

// recipe CPT Specific Stuff =======================================

add_filter( 'et_builder_post_types', 'cap_web_cws_et_builder_post_types' );
/**
 * Adding Support for  CPT to Divi
 */
function cap_web_cws_et_builder_post_types( $post_types ) {
    $post_types[] = 'cws_recipe';
    $post_types[] = 'product';
    // $post_types[] = 'ANOTHER_CPT_HERE';

    return $post_types;
}

/*
 * Ref: https://gist.github.com/lots0logs/d6e8ff16beec201eb3ec
 */

function cap_web_remove_default_et_pb_custom_search() {
  remove_action( 'pre_get_posts', 'et_pb_custom_search' );
  add_action( 'pre_get_posts', 'cap_web_et_pb_custom_search' );
}
add_action( 'wp_loaded', 'cap_web_remove_default_et_pb_custom_search' );

function cap_web_et_pb_custom_search( $query = false ) {
  if ( is_admin() || ! is_a( $query, 'WP_Query' ) || ! $query->is_search ) {
    return;
  }
  if ( isset( $_GET['et_pb_searchform_submit'] ) ) {
    $postTypes = array();
    if ( ! isset($_GET['et_pb_include_posts'] ) && ! isset( $_GET['et_pb_include_pages'] ) ) $postTypes = array( 'post' );
    if ( isset( $_GET['et_pb_include_pages'] ) ) $postTypes = array( 'page' );
    if ( isset( $_GET['et_pb_include_posts'] ) ) $postTypes[] = 'post';
    
    /* BEGIN Add custom post types */
    $postTypes[] = 'cws_recipe';
    /* END Add custom post types */
    
    $query->set( 'post_type', $postTypes );
    if ( ! empty( $_GET['et_pb_search_cat'] ) ) {
      $categories_array = explode( ',', $_GET['et_pb_search_cat'] );
      $query->set( 'category__not_in', $categories_array );
    }
    if ( isset( $_GET['et-posts-count'] ) ) {
      $query->set( 'posts_per_page', (int) $_GET['et-posts-count'] );
    }
  }
}


// Gravity Forms Specific Stuff =======================================

/**
 * Fix Gravity Form Tabindex Conflicts
 * http://gravitywiz.com/fix-gravity-form-tabindex-conflicts/
 */
add_filter( 'gform_tabindex', 'cws_gform_tabindexer', 10, 2 );
function cws_gform_tabindexer( $tab_index, $form = false ) {
    $starting_index = 1000; // if you need a higher tabindex, update this number
    if( $form )
        add_filter( 'gform_tabindex_' . $form['id'], 'cws_gform_tabindexer' );
    return GFCommon::$tab_index >= $starting_index ? GFCommon::$tab_index : $starting_index;
}

// Enable Gravity Forms Visibility Setting
// Ref: https://www.gravityhelp.com/gravity-forms-v1-9-placeholders/
add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );

// End of Gravity Forms Specific Stuff ================================

/*
 * Custom widgets with "ACF Widget"
 */

class Example_Widget extends WP_Widget
{
  function Example_Widget() 
  {
    parent::WP_Widget(false, "Example Widget");
  }
 
  function update($new_instance, $old_instance) 
  {  
    return $new_instance;  
  }  
 
  function form($instance)
  {  
    $title = esc_attr($instance["title"]);  
    echo "<br />";
  }
 
  function widget($args, $instance) 
  {
    $widget_id = "widget_" . $args["widget_id"];
  }
}