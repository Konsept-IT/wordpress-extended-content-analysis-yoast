<?php
/**
 * @link              http://www.ressourcenmangel.de/
 * @since             1.0.1
 * @package           extended-content-analysis-yoastseo
 *
 * @wordpress-plugin
 * Plugin Name:       Extended Content Analysis for YOAST SEO
 * Plugin URI:        https://github.com/Basbee
 * Description:       Plugin extends the "Content Analysis" Features of YOAST SEO while it fetches the complete Post or Page content manually and even gets the data out of layout building tools like Enfolds Avia Layout Builder.
 * Version:           1.0.1
 * Author:            Sebastian Kulahs
 * Author URI:        https://github.com/Basbee
 * Author:            Alexander Sagen <alexander@konsept-it.no>
 * Author URI:        https://github.com/Konsept-IT
 * Text Domain:       extended-content-analysis-yoastseo
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

// manually require plugin.php in order to use is_plugin_active
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

/**
 * Check if YOAST SEO Plugin is installed and activated
 */
if (!is_plugin_active('wordpress-seo/wp-seo.php')) {
	/*
	 *   display message if YOAST SEO is not activated
	 */
	function extend_content_yoast_admin_notices() {
		echo '<div class="updated"><p>YOAST SEO Plugin ( min v.3.0 ) must be activated in order to get <strong>"Extended Content Analysis for YOAST SEO"</strong> work correctly.</p></div>';
	}
	add_action('admin_notices', 'extend_content_yoast_admin_notices');
	return;
}

/**
 * Register scripts - add child theme javascripts to admin area only
 */
function extend_content_yoast_scripts() {
	wp_register_script('extend-content-analysis', plugins_url( '/admin/js/extend-content-yoast.js', __FILE__ ));
	wp_enqueue_script('extend-content-analysis', plugins_url( '/admin/js/extend-content-yoast.js', __FILE__ ), array('yoast-seo'), '1.0.1', true);
}
add_action('admin_enqueue_scripts', 'extend_content_yoast_scripts');

/**
 * Add AJAX method
 */
function extend_content_yoast_ajax_get_post_content() {
	$post_id = intval($_POST['postID']);

	// Check user access to edit post (this plugin is only used when editing posts)
	if (!current_user_can('edit_post', $post_id)) wp_die('0', 403);

	// Get post by ID
	$post = get_page($post_id);
	if (empty($post) || !is_object($post)) wp_die('0', 404);

	// Get post HTML
	$content = apply_filters('the_content', $post->post_content);

	// Replace characters to get correct number of words count
	// - Remove HTML entities from content string
	$content = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $content);
	// - Remove whitespaces after html tags from content string
	$content = preg_replace("/>\s+/", '>', $content);

	// return post content in json format
	header("Content-type: application/json; charset=utf-8");
	echo json_encode(array('content' =>  $content), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	wp_die();
}
add_action('wp_ajax_extend-content-yoast-get-post-content', 'extend_content_yoast_ajax_get_post_content');