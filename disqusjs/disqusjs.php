<?php
/*
Plugin Name: DisqusJs
Plugin URI: https://edlinus.cn
Description: Remove the default WordPress comments features and replace with Disqus.
Author: Edward Linus & Credit to Sukka
Author URI: https://edlinus.cn
Version: 1.0.0
Text Domain: disqusjs
*/

if (!defined('ABSPATH')) die;

function disqusjs_admin_notice() {
	$options = get_option('disqusjs_settings');
	if (!current_user_can('manage_options') || !empty($options['disqus_apikey'])) {
		return;
	}
	?>
	<div class="notice notice-warning is-dismissible">
		<h3>Howdy!</h3>
		<p>The disqusjs plugin is active, but you have not yet setup your Disqus.</p>
		<p>You can do that on <a href="<?php echo admin_url('options-general.php?page=disqusjs'); ?>">this page</a>.</p>
	</div>
	<?php
}
add_action('admin_notices', 'disqusjs_admin_notice');

// replace comments section
function disqusjs_comments_template() {
	$options = get_option('disqusjs_settings');
	if (empty($options['disqus_apikey'])) {
		return;
	}
	global $post;
	if ( !(is_singular() && (have_comments() || 'open' == $post->comment_status)) ) {
		return;
	}
	return dirname(__FILE__).'/comments_template.php';
}
add_filter('comments_template', 'disqusjs_comments_template');


// add count script to footer
/*
function disqusjs_count_script() {
	
	// add the following to your theme's functions/php file to disable the comment count script - add_filter('disqusjs_show_counter', '__return_false');
	$show_counter = apply_filters('disqusjs_show_counter', true);
	if (!$show_counter) {
		return;
	}
	
	if (get_theme_mod('hide_comments_link')) {
		return;
	}
	
	$options = get_option('disqusjs_settings');
	if (empty($options['disqus_apikey'])) {
		return;
	}
	
	$disqus_count = 'https://'.trim($options['disqus_apikey']).'.disqus.com/count.js';
	?>
	<script id="dsq-count-scr" src="<?php echo esc_url($disqus_count); ?>" async defer></script>
	<?php
}
add_action('wp_footer', 'disqusjs_count_script', 999999);
*/


// add #disqus_thread to end of comment links
/*
function disqusjs_filter_comment_link($url) {
	return $link.'#disqus_thread';
}
add_filter('comment_link', 'disqusjs_filter_comment_link');
*/


// remove rss link to default WP comments feed
add_filter('feed_links_show_comments_feed', '__return_false');



// Remove default comments link from Adminbar
function disqusjs_remove_comments_adminbar() {
	$options = get_option('disqusjs_settings');
	if (empty($options['disqus_apikey'])) {
		return;
	}
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('comments');
}
add_action('wp_before_admin_bar_render', 'disqusjs_remove_comments_adminbar');



// Remove comments link from main menu
function disqusjs_remove_comments_menu(){
	$options = get_option('disqusjs_settings');
	if (empty($options['disqus_apikey'])) {
		return;
	}
	remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'disqusjs_remove_comments_menu');


// Add link to Disqus in Adminbar
function disqusjs_adminbar($admin_bar){
	
	$options = get_option('disqusjs_settings');
	if (!current_user_can('edit_posts') || empty($options['disqus_apikey'])) {
		return;
	}
	
	$disqus_url = 'https://'.trim($options['disqus_apikey']).'.disqus.com/admin/moderate/';
	
	$admin_bar->add_menu( array(
		'id'    => 'pipdig-mod-comments',
		'title' => __('Moderate Disqus Comments', 'disqusjs'),	
		'href'  => esc_url($disqus_url),
		'meta'  => array(
			'title' => __('Moderate Comments on Disqus', 'disqusjs'),	
			'target' => '_blank',			
		),
	));
}
add_action('admin_bar_menu', 'disqusjs_adminbar', 100);



// Admin Settings page
function disqusjs_add_admin_menu() { 
	add_options_page('disqusjs', 'disqusjs', 'edit_posts', 'disqusjs', 'disqusjs_options_page');
}
add_action('admin_menu', 'disqusjs_add_admin_menu');


function disqusjs_settings_init() { 

	register_setting('disqusjs_pluginPage', 'disqusjs_settings');

	add_settings_section(
		'disqusjs_pluginPage_section', 
		'', //section description 
		'disqusjs_settings_section_callback', 
		'disqusjs_pluginPage'
	);

	add_settings_field( 
		'disqus_shortname', 
		__('Disqus Shortname', 'disqusjs').' (<a href="https://help.disqus.com/customer/portal/articles/466208" rel="noopener" target="_blank">?</a>)', 
		'disqus_shortname_render', 
		'disqusjs_pluginPage', 
		'disqusjs_pluginPage_section' 
	);

	add_settings_field( 
		'disqus_apikey', 
		__('Disqus API Key', 'disqusjs').' (<a href="https://github.com/SukkaW/DisqusJS" rel="noopener" target="_blank">?</a>)', 
		'disqus_apikey_render', 
		'disqusjs_pluginPage', 
		'disqusjs_pluginPage_section' 
	);

	add_settings_field( 
		'disqus_apiendpoint', 
		__('Disqus API EndPoint', 'disqusjs').' (<a href="https://github.com/SukkaW/DisqusJS" rel="noopener" target="_blank">?</a>)', 
		'disqus_apiendpoint_render', 
		'disqusjs_pluginPage', 
		'disqusjs_pluginPage_section' 
	);

	add_settings_field( 
		'disqus_nesting', 
		__('Disqus Max Comment Nesting Level', 'disqusjs').' (<a href="https://github.com/SukkaW/DisqusJS" rel="noopener" target="_blank">?</a>)', 
		'disqus_nesting_render', 
		'disqusjs_pluginPage', 
		'disqusjs_pluginPage_section' 
	);

	add_settings_field( 
		'disqus_admin', 
		__('Disqus Admin UserName', 'disqusjs').' (<a href="https://github.com/SukkaW/DisqusJS" rel="noopener" target="_blank">?</a>)', 
		'disqus_admin_render', 
		'disqusjs_pluginPage', 
		'disqusjs_pluginPage_section' 
	);

	add_settings_field( 
		'disqus_badge', 
		__('Disqus Admin Badge Name', 'disqusjs').' (<a href="https://github.com/SukkaW/DisqusJS" rel="noopener" target="_blank">?</a>)', 
		'disqus_badge_render', 
		'disqusjs_pluginPage', 
		'disqusjs_pluginPage_section' 
	);

	add_settings_field( 
		'disqus_customcss', 
		__('Disqus Custom Css', 'disqusjs'), 
		'disqus_customcss_render', 
		'disqusjs_pluginPage', 
		'disqusjs_pluginPage_section' 
	);

}
add_action('admin_init', 'disqusjs_settings_init');

function disqus_shortname_render() { 
	$options = get_option('disqusjs_settings');
	$disqus_shortname = '';
	if (isset($options['disqus_shortname'])) {
		$disqus_shortname = sanitize_text_field($options['disqus_shortname']);
	}
	?>
	<input type="text" name="disqusjs_settings[disqus_shortname]" value="<?php echo $disqus_shortname; ?>">
	<?php
}

function disqus_apiendpoint_render() { 
	$options = get_option('disqusjs_settings');
	$disqus_apiendpoint = 'https://disqus.edlinus.cn/api/';
	if (isset($options['disqus_apiendpoint'])) {
		$disqus_apiendpoint = sanitize_text_field($options['disqus_apiendpoint']);
	}
	?>
	<input type="text" name="disqusjs_settings[disqus_apiendpoint]" value="<?php echo $disqus_apiendpoint; ?>">
	<?php
}

function disqus_apikey_render() { 
	$options = get_option('disqusjs_settings');
	$disqus_apikey = '';
	if (isset($options['disqus_apikey'])) {
		$disqus_apikey = sanitize_text_field($options['disqus_apikey']);
	}
	?>
	<input type="text" name="disqusjs_settings[disqus_apikey]" value="<?php echo $disqus_apikey; ?>">
	<?php
}

function disqus_nesting_render() { 
	$options = get_option('disqusjs_settings');
	$disqus_nesting = '4';
	if (isset($options['disqus_nesting'])) {
		$disqus_nesting = sanitize_text_field($options['disqus_nesting']);
	}
	?>
	<input type="text" name="disqusjs_settings[disqus_nesting]" value="<?php echo $disqus_nesting; ?>">
	<?php
}

function disqus_nocomment_render() { 
	$options = get_option('disqusjs_settings');
	$disqus_nocomment = 'Be the first to comment.';
	if (isset($options['disqus_nocomment'])) {
		$disqus_nocomment = sanitize_text_field($options['disqus_nocomment']);
	}
	?>
	<input type="text" name="disqusjs_settings[disqus_nocomment]" value="<?php echo $disqus_nocomment; ?>">
	<?php
}

function disqus_admin_render() { 
	$options = get_option('disqusjs_settings');
	$disqus_admin = '';
	if (isset($options['disqus_admin'])) {
		$disqus_admin = sanitize_text_field($options['disqus_admin']);
	}
	?>
	<input type="text" name="disqusjs_settings[disqus_admin]" value="<?php echo $disqus_admin; ?>">
	<?php
}

function disqus_badge_render() { 
	$options = get_option('disqusjs_settings');
	$disqus_badge = '';
	if (isset($options['disqus_badge'])) {
		$disqus_badge = sanitize_text_field($options['disqus_badge']);
	}
	?>
	<input type="text" name="disqusjs_settings[disqus_badge]" value="<?php echo $disqus_badge; ?>">
	<?php
}

function disqus_customcss_render() { 
	$options = get_option('disqusjs_settings');
	$disqus_customcss = '';
	if (isset($options['disqus_customcss'])) {
		$disqus_customcss = sanitize_textarea_field($options['disqus_customcss']);
	}
	?>
	<textarea rows="10" cols="50" name="disqusjs_settings[disqus_customcss]"><?php echo $disqus_customcss; ?></textarea>
	<?php
}


function disqusjs_settings_section_callback() {
	?>
	<style>.wp-core-ui .notice.is-dismissible{display:none}</style>
	<?php
}


function disqusjs_options_page() { 
	if (!current_user_can('manage_options')) {
		wp_die();
	}
	?>
	<div class="wrap">
	
	<form action='options.php' method='post'>
		
		<h1>Disqus Settings</h1>
		
		<div class="card">
		<p>To use Disqus comments on your blog posts, enter your Disqus <a href="https://disqus.com/api/applications/" target="_blank">API Key</a> and <a href="https://help.disqus.com/customer/portal/articles/466208" target="_blank">Shortname</a> below. Tutorial can be found on <a href="https://github.com/SukkaW/DisqusJS" target="_blank">Github</a></p>
		<?php
			settings_fields('disqusjs_pluginPage');
			do_settings_sections('disqusjs_pluginPage');
			submit_button();
		?>
		<h3>Already have some WordPress comments?</h3>
		<p>Please note, this plugin does not import any old WordPress comments. To do that, please see <a href="https://help.disqus.com/customer/portal/articles/466255-importing-comments-from-wordpress#manual" target="_blank">this guide</a>.</p>
		</div>
		
	</form>

	</div>
	<?php
}
