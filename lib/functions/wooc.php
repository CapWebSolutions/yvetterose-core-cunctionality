<?php
/**
 * WooC
 *
 * This file contains functions related to WooCommerce customizations
 *
 * @package   Core_Functionality
 * @since        1.0.0
 * @Plugin URI: https://github.com/mattry/selflove-core-cunctionality
 * @author			Matt Ryan [Cap Web Solutions] <matt@mattryan.co>
 * @copyright  Copyright (c) 2016, Cap Web Solutions
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


/**
 * @snippet       Remove Product Tabs & Echo Long Description
 * @how-to        Watch tutorial @ http://businessbloomer.com/?p=19055
 * @sourcecode    http://businessbloomer.com/?p=19940
 * @author        Rodolfo Melogli
 * @testedwith    WooCommerce 2.5.2
 */

add_action( 'woocommerce_after_single_product_summary' ,'bbloomer_wc_output_long_description',10);
function bbloomer_wc_output_long_description() {
  ?>
  <div class="woocommerce-tabs"><?php the_content(); ?></div>
<?php
}


/**
 * @snippet       WooCommerce Hide Prices on the Shop Page
 * @how-to        Watch tutorial @ http://businessbloomer.com/?p=19055
 * @sourcecode    http://businessbloomer.com/?p=406
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 2.4.7
 */

// Remove prices
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );


// First, let's remove related products from their original position
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

// Second, let's add a new tab

add_filter( 'woocommerce_product_tabs', 'woo_new_product_tab' );
function woo_new_product_tab( $tabs ) {
  $tabs['related_products'] = array(
    'title'     => __( 'Try it with', 'woocommerce' ),
    'priority'  => 50,
    'callback'  => 'woo_new_product_tab_content'
  );
  return $tabs;
}

// Third, let's put the related products inside

function woo_new_product_tab_content() {
  woocommerce_related_products();
}

/**
 * The do-action for cws_output_subtitle is called in the single-product template file.
 */
add_action( 'cws_output_subtitle', 'mycws_output_product_subtitle' );
function mycws_output_product_subtitle ( $my_text ) {
  echo '<div class="allura">' . $my_text . '</div>';
  return;
}
