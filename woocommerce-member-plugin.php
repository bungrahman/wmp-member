<?php
/**
 * Plugin Name: WooCommerce Member Plugin
 * Plugin URI: https://jejakkreasi.com
 * Description: This plugin adds member functionality to WooCommerce.
 * Version: 1.0.0
 * Author: bungrahman
 * Author URI: https://jejakkreasi.com
 * Text Domain: woocommerce-member-plugin
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Load plugin files
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/user-roles.php';
require_once plugin_dir_path(__FILE__) . 'includes/product-role.php';
require_once plugin_dir_path(__FILE__) . 'includes/email-notification.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'wmp_activate');
register_deactivation_hook(__FILE__, 'wmp_deactivate');

// Activation hook
function wmp_activate()
{
    // Add any necessary activation code here
}

// Deactivation hook
function wmp_deactivate()
{
    // Add any necessary deactivation code here
}



// WooCommerce not active notice
function wmp_woocommerce_notice()
{
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e('WooCommerce is required for the WooCommerce Member Plugin to work properly.', 'woocommerce-member-plugin'); ?></p>
    </div>
    <?php
}
