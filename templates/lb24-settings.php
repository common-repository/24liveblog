<?php

/**
 * @package 24Liveblog Widget for Wordpress
 * @author 24Liveblog
 * @copyright (C) 2020- 24Liveblog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
    <div class="lb24-login">
        <div class="lb24-wp-login-header">
            <img src="<?php echo plugins_url( 'assets/lb24-logo.png' , dirname(__FILE__) ) ?>" class="lb24-wp-setting-login-logo">
        </div>
<?php
if (!sanitize_text_field(get_user_meta(wp_get_current_user()->ID, 'lb24_token', true))) {
?>
        <div class="lb24-wp-body">
            <div class="lb24-wp-body-title">
                <?php _e('Sign in to your 24LIVEBLOG account', 'lb24'); ?>
            </div>
            <div class="lb24-wp-login-body">
                <span class="lb24-wp-login-email-label"><?php _e('Email', 'lb24'); ?></span>
                <input class="lb24-wp-login-email" onchange="window.lb24WpFunc.lb24GetEmailInputValue(event)"/>
                <span class="lb24-wp-login-password-label"><?php _e('Password', 'lb24'); ?></span>
                <input class="lb24-wp-login-password" type="password" onchange="window.lb24WpFunc.lb24GetPasswordInputValue(event)"/>
                <span id="lb24-wp-login-error-message" class="lb24-wp-login-error-message"></span>
                <button 
                    class="lb24-wp-login-btn"
                    onclick="window.lb24WpFunc.lb24Login()"
                >
                <img id="lb24-login-loading" src="<?php echo plugins_url( 'assets/lb24-loading-white.svg' , dirname(__FILE__) ) ?>">
                <?php _e('Log in', 'lb24'); ?>
                </button>
                <span class="lb24-wp-setting-login-desc"><?php _e('No account yet? Go to', 'lb24'); ?>
                <a class="lb24-wp-setting-login-portal" href="https://portal.24liveblog.com" target="_blank"> 24liveblog </a>
                    <?php _e('and create new one', 'lb24'); ?></span>
            </div>
<?php
} else {
?>
            <div class="lb24-wp-logout-body">
                <div class="lb24-wp-username">
                    <?php _e('Welcome, ', 'lb24'); ?><?php echo sanitize_text_field(get_user_meta(wp_get_current_user()->ID, 'lb24_uname', true)) ?>
                </div>
                <button 
                    class="lb24-wp-logout-btn"
                    onclick="window.lb24WpFunc.lb24Logout()"
                >
                <?php _e('LOGOUT', 'lb24'); ?>
                </button>
            </div>
<?php
}
?>
        </div>
    </div>