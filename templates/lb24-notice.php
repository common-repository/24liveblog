<?php

/**
 * @package 24Liveblog Notice
 * @author 24Liveblog
 * @copyright (C) 2020- 24Liveblog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div class="updated lb24-notice">
    <a href="<?php echo admin_url('options-general.php?page=lb24_plugin'); ?>" class="lb24-notice-info-button">
        <?php _e('SET UP YOUR 24LIVEBLOG ACCOUNT', 'lb24'); ?>
    </a>
    <span class="lb24-notice-info-text">
        <?php _e('Almost done! Login to 24Liveblog and set all your events on wordpress', 'lb24'); ?>
    </span>
    <img class="lb24-wp-notice-login-logo" src="<?php echo plugins_url( 'assets/lb24-logo-white.png' , dirname(__FILE__) ) ?>">
</div>