<?php
/**
 * Plugin Name: 24liveblog - live blog tool
 * Plugin URI: https://www.24liveblog.com
 * Description: 24liveblog is a simple, functional, powerful and FREE live blogging tool. 24liveblog is the easiest way to live blogging. It is free to use and works with any type of website.
 * Author: 24liveblog
 * Author URI: https://www.24liveblog.com
 * Version: 2.2
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'src/init.php';

function lb24_add_menu(){
	add_options_page(
		__('24liveblog Settings', 'lb24'),
		__('24liveblog', 'lb24'),
		'manage_options',
		'lb24_plugin',
		'lb24_create_plugin_settings_page'
	);
}

function lb24_create_plugin_settings_page(){
    global $wpdb;

    if(!current_user_can('manage_options'))   {
        wp_die(__('You may not have permissions to access this page.'));
    }
	
    include(sprintf("%s/templates/lb24-settings.php", dirname(__FILE__)));
}

function lb24_plugin_notice() {
    global $hook_suffix;

    if ($hook_suffix == 'plugins.php' && !sanitize_text_field(get_user_meta(wp_get_current_user()->ID, 'lb24_token', true))) {
        include(sprintf("%s/templates/lb24-notice.php", dirname(__FILE__)));
    }
}

function lb24_settings_assets($hook){
    global $wpdb;
    wp_register_style('lb24_notice_style', plugins_url('assets/lb24-notice.css', __FILE__ ) );
    wp_enqueue_style('lb24_notice_style');

	if($hook != 'settings_page_lb24_plugin')
        return;
    
    wp_register_style('lb24_setting_style', plugins_url('assets/lb24-settings.css', __FILE__ ) );
	wp_enqueue_style('lb24_setting_style');
	wp_enqueue_script('lb24_setting_script', plugins_url( 'assets/lb24-settings.js' , __FILE__ ) );

    $login_url = esc_url('https://update.24liveplus.com/v1/update_server/login/');
    $current_user = wp_get_current_user();
    $user_id = sanitize_text_field($current_user->ID);
    $user_name = sanitize_text_field($current_user->user_login);
    $token_key = 'lb24_token';
    $uid_key = 'lb24_uid';
    $refresh_token_key = 'lb24_refresh_token';
    $uname_key = 'lb24_uname';
    $single = true;
    $user_token = sanitize_text_field(get_user_meta($user_id, $token_key, $single));
    $user_uid = sanitize_text_field(get_user_meta($user_id, $uid_key, $single));
    $user_refresh_token = sanitize_text_field(get_user_meta($user_id, $refresh_token_key, $single));
    $user_uname = sanitize_text_field(get_user_meta($user_id, $uname_key, $single));
    $nonce = sanitize_text_field(wp_create_nonce('lb24'));
    $dataToWp = array(
        'getLoginUrl'            => $login_url,
        'getWpUserInfo'          => $current_user,
        'getWpUserId'            => $user_id,
        'getWpUserName'          => $user_name,
        'getLb24Token'           => $user_token,
        'getLb24Uid'             => $user_uid,
        'getLb24RefreshToken'    => $user_refresh_token,
        'getLb24Uname'           => $user_uname,
        'getNonce'               => $nonce,
    );
    wp_localize_script('lb24_setting_script', 'lb24WpData', $dataToWp);
}

function update_lb24_token() {
    check_ajax_referer('lb24', '_ajax_nonce');
    $user_id = sanitize_text_field($_POST['user_id']);
    $user_token = sanitize_text_field($_POST['user_token']);
    $user_uid = sanitize_text_field($_POST['user_uid']);
    $user_refresh_token = sanitize_text_field($_POST['user_refresh_token']);
    $user_uname = sanitize_text_field($_POST['user_uname']);
    update_user_meta($user_id, 'lb24_token', $user_token);
    update_user_meta($user_id, 'lb24_uid', $user_uid);
    update_user_meta($user_id, 'lb24_refresh_token', $user_refresh_token);
    update_user_meta($user_id, 'lb24_uname', $user_uname);
    update_option('lb24_token', $user_token);
    update_option('lb24_uid', $user_uid);
    update_option('lb24_refresh_token', $user_refresh_token);
    update_option('lb24_uname', $user_uname);
    wp_die();
}

add_action('admin_menu', 'lb24_add_menu');
add_action('admin_notices', 'lb24_plugin_notice');
add_action('admin_enqueue_scripts', 'lb24_settings_assets');
add_action('wp_ajax_update_lb24_token', 'update_lb24_token');