<?php
/*
Plugin Name: WooCommerce Draft Product Publisher
Description: Detects and publishes draft products with custom images.
Version: 1.0
Author: Pablo Sánchez
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

// Include admin page
require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';

// Enqueue scripts and styles
function wcdpp_enqueue_scripts($hook) {
    if ('woocommerce_page_wc-draft-product-publisher' !== $hook) {
        return;
    }
    wp_enqueue_style('wcdpp-styles', plugin_dir_url(__FILE__) . 'assets/css/styles.css', array(), '1.0.0');
    wp_enqueue_script('wcdpp-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0.0', true);
    wp_localize_script('wcdpp-script', 'wcdpp_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wcdpp_ajax_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'wcdpp_enqueue_scripts');

// AJAX handler for publishing products
function wcdpp_publish_products() {
    check_ajax_referer('wcdpp_ajax_nonce', 'nonce');

    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $products = wcdpp_get_draft_products_with_custom_image();
    $published_count = 0;

    foreach ($products as $product_id) {
        $result = wp_update_post(array(
            'ID' => $product_id,
            'post_status' => 'publish'
        ), true);

        if (!is_wp_error($result)) {
            $published_count++;
        }
    }

    wp_send_json_success(array('count' => $published_count));
}
add_action('wp_ajax_wcdpp_publish_products', 'wcdpp_publish_products');

// Function to get draft products with custom images
function wcdpp_get_draft_products_with_custom_image() {
    $args = array(
        'post_type' => 'product',
        'post_status' => 'draft',
        'posts_per_page' => -1,
    );

    $products = get_posts($args);
    $custom_image_products = array();

    foreach ($products as $product) {
        $product_image_id = get_post_thumbnail_id($product->ID);
        if ($product_image_id) {
            $image_url = wp_get_attachment_image_src($product_image_id, 'full');
            if ($image_url) {
                $image_url = $image_url[0];
                $default_image_url = wc_placeholder_img_src();
                $custom_placeholder_url = wcdpp_get_custom_placeholder_url();

                if ($image_url !== $default_image_url && $image_url !== $custom_placeholder_url) {
                    $custom_image_products[] = $product->ID;
                }
            }
        }
    }

    return $custom_image_products;
}

// Function to get custom placeholder URL
function wcdpp_get_custom_placeholder_url() {
    $upload_dir = wp_upload_dir();
    $uploads = untrailingslashit($upload_dir['baseurl']);
    return $uploads . '/2024/11/cropped-logo-yupi-party-compress.png';
}

function wcdpp_load_textdomain() {
    load_plugin_textdomain('wc-draft-product-publisher', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('init', 'wcdpp_load_textdomain');

