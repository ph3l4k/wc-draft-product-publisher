<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add menu item
function wcdpp_add_menu_item() {
    add_submenu_page(
        'woocommerce',
        'Draft Product Publisher',
        'Draft Product Publisher',
        'manage_woocommerce',
        'wc-draft-product-publisher',
        'wcdpp_admin_page'
    );
}
add_action('admin_menu', 'wcdpp_add_menu_item');

// Admin page content
function wcdpp_admin_page() {
    if (!current_user_can('manage_woocommerce')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Get the count in real-time
    $draft_products = wcdpp_get_draft_products_with_custom_image();
    $count = count($draft_products);

    ?>
    <div class="wrap">
        <h1>WooCommerce Draft Product Publisher</h1>
        <p>This tool allows you to publish draft products that have custom images (not the default WooCommerce placeholder or the custom placeholder).</p>
        <p>Number of draft products with custom images: <strong id="wcdpp-count"><?php echo esc_html($count); ?></strong></p>
        <button id="wcdpp-publish-button" class="button button-primary">Publish Products</button>
        <div id="wcdpp-result" style="margin-top: 20px;"></div>
    </div>
    <?php
}

// AJAX handler for getting the current count
function wcdpp_get_current_count() {
    check_ajax_referer('wcdpp_ajax_nonce', 'nonce');

    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }

    $draft_products = wcdpp_get_draft_products_with_custom_image();
    $count = count($draft_products);

    wp_send_json_success(array('count' => $count));
}
add_action('wp_ajax_wcdpp_get_current_count', 'wcdpp_get_current_count');

