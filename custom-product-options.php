<?php
/**
 * Plugin Name: Custom Product Options (Enhanced)
 * Description: افزودن آپشن‌های سفارشی (قیمت‌دار، توضیحات، نوع‌های متنوع، مدال) به محصولات ووکامرس و ثبت در سفارش.
 * Version: 1.2.1
 * Author: zaiko
 * Requires Plugins: woocommerce
 */

if ( ! defined('ABSPATH') ) exit;

define('CPO_BASE_FILE', __FILE__);
define('CPO_BASE_DIR', plugin_dir_path(__FILE__));
define('CPO_BASE_URL', plugin_dir_url(__FILE__));
define('CPO_META_KEY', '_cpo_options');

require_once CPO_BASE_DIR . 'includes/class-admin.php';
require_once CPO_BASE_DIR . 'includes/class-frontend.php';
require_once CPO_BASE_DIR . 'includes/class-order.php';

add_action('plugins_loaded', function () {
    if ( ! class_exists('WooCommerce') ) return;
    new CPO_Admin();
    new CPO_Frontend();
    new CPO_Order();
});
