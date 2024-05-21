<?php
/**
 * Plugin Name: This Is It Product Schema
 * Plugin Description: Adds JSON-LD product schema markup to WooCommerce product pages.
 * Plugin URI: https://webgrrrl.net/archives/this-is-it-product-schema-plugin-woocommerce-google-shopping.htm
 * Author: webgrrrl
 * Author URI: http://webgrrrl.net
 * Version: 1.0.0
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Check for WooCommerce
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

  add_action( 'wp_head', 'tis_product_schema' ); // Updated function name

  function tis_product_schema() {
    if ( is_singular( 'product' ) ) {
      global $product;

      $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->get_name(),
        'description' => $product->get_description(),
        'url' => get_permalink(),
        'image' => get_the_post_thumbnail_url( get_the_ID(), 'full' ),
        $brand = get_post_meta( $product->get_id(), '_custom_brand', true );
        // Add brand information to schema if available
        if ( $brand ) {
          $product_schema['brand'] = array(
            '@type' => 'Brand',
            'name' => $brand,
          );
        }    
      );

      if ( $product->is_type( 'variable' ) ) {
        $variations = $product->get_available_variations();
        $schema['offers'] = array();

        foreach ( $variations as $variation ) {
          $variation_id = $variation->get_id();
          $variation_data = wc_get_product_variation_attributes( $variation_id );

          $color = ''; // Assuming color data is stored in an attribute named 'color'
          if ( isset( $variation_data['color'] ) ) {
            $color = $variation_data['color'];
          }

          $schema['offers'][] = array(
            '@type' => 'Offer',
            'price' => $variation->get_price(),
            'priceCurrency' => get_woocommerce_currency(),
            'availability' => $variation->is_in_stock() ? 'InStock' : 'OutStock',
            'sku' => $variation->get_sku(),
            'url' => get_permalink( $variation_id ), // Variation URL
            'name' => $product->get_name() . ' - ' . $color, // Include color in product name
          );
        }
      } else {
        $schema['sku'] = $product->get_sku();
        $schema['offers'] = array(
          '@type' => 'Offer',
          'price' => $product->get_price(),
          'priceCurrency' => get_woocommerce_currency(),
          'availability' => $product->is_in_stock() ? 'InStock' : 'OutStock',
        );
      }

      echo '<script type="application/ld+json">' . json_encode( $schema ) . '</script>';
    }
  }
}
