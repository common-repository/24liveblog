<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses {wp-blocks} for block type registration & related functions.
 * @uses {wp-element} for WP Element abstraction — structure of blocks.
 * @uses {wp-i18n} to internationalize the block's text.
 * @uses {wp-editor} for WP editor styles.
 * @since 1.0.0
 */
function liveblog24_live_blogging_tool_cgb_block_assets() { // phpcs:ignore
	// Register block styles for both frontend + backend.
	wp_register_style(
		'liveblog24_live_blogging_tool-cgb-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		is_admin() ? array( 'wp-editor' ) : null, // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
	);

	// Register block editor script for backend.
	wp_register_script(
		'liveblog24_live_blogging_tool-cgb-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
		null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);

	// Register block editor styles for backend.
	wp_register_style(
		'liveblog24_live_blogging_tool-cgb-block-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
	);

	// WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
	wp_localize_script(
		'liveblog24_live_blogging_tool-cgb-block-js',
		'cgbGlobal', // Array containing dynamic data for a JS Global.
		[
			'pluginDirPath' => plugin_dir_path( __DIR__ ),
			'pluginDirUrl'  => plugin_dir_url( __DIR__ ),
			// Add more data here that you want to access from `cgbGlobal` object.
		]
	);

	/**
	 * Register Gutenberg block on server-side.
	 *
	 * Register the block on server-side to ensure that the block
	 * scripts and styles for both frontend and backend are
	 * enqueued when the editor loads.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
	 * @since 1.16.0
	 */
	register_block_type(
		'cgb/block-liveblog24-live-blogging-tool', array(
			// Enqueue blocks.style.build.css on both frontend & backend.
			'style'         => 'liveblog24_live_blogging_tool-cgb-style-css',
			// Enqueue blocks.build.js in the editor only.
			'editor_script' => 'liveblog24_live_blogging_tool-cgb-block-js',
			// Enqueue blocks.editor.build.css in the editor only.
			'editor_style'  => 'liveblog24_live_blogging_tool-cgb-block-editor-css',
		)
	);
}

function lb24_block_category( $categories, $post ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug' => 'lb24-blocks',
				'title' => __( '24liveblog', 'lb24-blocks' ),
			),
		)
	);
}

function lb24_block_enqueue_scripts() {
  	$LB24_URL = esc_url('https://portal.24liveblog.com');
  	$LB24_SETTINGS = admin_url('options-general.php?page=lb24_plugin');
  	$LB24_LOGO_ICON = plugins_url( 'assets/lb24-logo.png' , dirname(__FILE__) );
  	$LB24_CLOSE_ICON = plugins_url( 'assets/lb24-close.svg' , dirname(__FILE__) );
  	$LB24_SEARCH_ICON = plugins_url( 'assets/lb24-search.svg' , dirname(__FILE__) );
  	$LB24_RELOAD_ICON = plugins_url( 'assets/lb24-reload.svg' , dirname(__FILE__) );
  	$LB24_LOADING_BLACK_ICON = plugins_url( 'assets/lb24-loading-black.svg' , dirname(__FILE__) );
  	$LB24_PREVIOUS_PAGE_ICON = plugins_url( 'assets/lb24-previous.svg' , dirname(__FILE__) );
  	$LB24_NEXT_PAGE_ICON = plugins_url( 'assets/lb24-next.svg' , dirname(__FILE__) );

  	$lb24_config = array(
  		'LB24_URL' 					=> $LB24_URL,
		'LB24_SETTINGS' 			=> $LB24_SETTINGS,
		'LB24_LOGO_ICON' 			=> $LB24_LOGO_ICON,
		'LB24_CLOSE_ICON' 			=> $LB24_CLOSE_ICON,
		'LB24_SEARCH_ICON' 			=> $LB24_SEARCH_ICON,
		'LB24_RELOAD_ICON' 			=> $LB24_RELOAD_ICON,
		'LB24_LOADING_BLACK_ICON' 	=> $LB24_LOADING_BLACK_ICON,
		'LB24_PREVIOUS_PAGE_ICON' 	=> $LB24_PREVIOUS_PAGE_ICON,
		'LB24_NEXT_PAGE_ICON' 		=> $LB24_NEXT_PAGE_ICON,
  	);

  	$current_user = wp_get_current_user();
    $user_id = sanitize_text_field($current_user->ID);
    $user_name = sanitize_text_field($current_user->user_login);
    $nonce = sanitize_text_field(wp_create_nonce('lb24'));
    if(current_user_can('manage_options')) {
    	$token_key = 'lb24_token';
	    $uid_key = 'lb24_uid';
	    $refresh_token_key = 'lb24_refresh_token';
    	$uname_key = 'lb24_uname';
	    $single = true;
	    $user_token = sanitize_text_field(get_user_meta($user_id, $token_key, $single));
	    $user_uid = sanitize_text_field(get_user_meta($user_id, $uid_key, $single));
	    $user_refresh_token = sanitize_text_field(get_user_meta($user_id, $refresh_token_key, $single));
    	$user_uname = sanitize_text_field(get_user_meta($user_id, $uname_key, $single));
    } else {
    	$user_token = sanitize_text_field(get_option('lb24_token'));
	    $user_uid = sanitize_text_field(get_option('lb24_uid'));
	    $user_refresh_token = sanitize_text_field(get_option('lb24_refresh_token'));
    	$user_uname = sanitize_text_field(get_option('lb24_uname'));
    }
    
    $dataToBlock = array(
    	'getLb24Config' 		   => $lb24_config,
        'getWpUserInfo'  	       => $current_user,
        'getWpUserId'    	       => $user_id,
        'getWpUserName'  	       => $user_name,
        'getLb24Token' 		       => $user_token,
        'getLb24Uid'   		       => $user_uid,
        'getLb24RefreshToken'      => $user_refresh_token,
        'getLb24Uname'             => $user_uname,
        'getNonce'                 => $nonce,
    );

  	wp_localize_script('liveblog24_live_blogging_tool-cgb-block-js', 'lb24BlockData', $dataToBlock);
}

add_filter('block_categories', 'lb24_block_category', 10, 2);
add_action('init', 'liveblog24_live_blogging_tool_cgb_block_assets' );
add_action('enqueue_block_editor_assets', 'lb24_block_enqueue_scripts');