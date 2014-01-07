<?php
/**
 * Plugin Name: WP Ajax Get Post
 * Plugin URI:  http://wordpress.org/plugins
 * Description: The best WordPress extension ever made!
 * Version:     0.1.0
 * Author:      Otto Kamiya
 * Author URI:  
 * License:     GPLv2+
 * Text Domain: wpagp
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2014 Otto Kamiya (email : otto@mgzl.jp)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using grunt-wp-plugin
 * Copyright (c) 2013 10up, LLC
 * https://github.com/10up/grunt-wp-plugin
 */

register_activation_hook( __FILE__, array( 'WP_Ajax_Get_Posts', 'on_activation' ) );
register_deactivation_hook( __FILE__, array( 'WP_Ajax_Get_Posts', 'on_deactivation' ) );
register_uninstall_hook( __FILE__, array( 'WP_Ajax_Get_Posts', 'on_uninstall' ) );

add_action( 'plugins_loaded', array( 'WP_Ajax_Get_Posts', 'init' ) );
class WP_Ajax_Get_Posts {
	protected static $instance;
	protected static $version = '0.1.0';
	protected static $plugin_name = 'WP Ajax Get Post';
	protected static $prefix = 'wpagp';
	protected $plugin_url;
	protected $plugin_path;

	public static function init() {
		is_null( self::$instance ) AND self::$instance = new self;
		return self::$instance;
	}

	public static function on_activation() {
		if ( ! current_user_can( 'activate_plugins' ) ){
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "activate-plugin_{$plugin}" );

		# Uncomment the following line to see the function in action
		# exit( var_dump( $_GET ) );
	}

	public static function on_deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ){
			return;
		}
		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );

		# Uncomment the following line to see the function in action
		# exit( var_dump( $_GET ) );
	}

	public static function on_uninstall() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		check_admin_referer( 'bulk-plugins' );

		// Important: Check if the file is the one
		// that was registered during the uninstall hook.
		if ( __FILE__ != WP_UNINSTALL_PLUGIN ) {
			return;
		}

		# Uncomment the following line to see the function in action
		# exit( var_dump( $_GET ) );
	}

	public function __construct() {
		add_action('wp_ajax_nopriv_'.$this->add_prefix('get_posts'), array($this,'ajax_get_posts') );
		add_action('wp_ajax_'.$this->add_prefix('get_posts'), array($this,'ajax_get_posts') );
	}

	public function ajax_get_posts(){
		header("Content-Type: application/javascript; charset=utf-8",true,200);
		header("Access-Control-Allow-Origin: *");

		$callback_name = $_REQUEST['callback'];
		$callback_template = $callback_name.'(%s)';
		$query = wp_parse_args(preg_replace('/^\?/', '', strstr($_SERVER["REQUEST_URI"], '?')));
		$posts = get_posts($query);
		$posts = $this->get_cf_data($posts);
		apply_filters($this->add_prefix('ajax_get_posts'),$posts);
		echo sprintf($callback_template,json_encode($posts));
		die;
	}

	/**
	 * get all custom field data;
	 */
	public static function get_cf_data($post){
		$cf_array = get_post_custom($post->ID);
		foreach ($cf_array as $key => $value){
			$cf_array[$key] = $value[0];
		}
		$post -> cf = $cf_array;
		return $post;
	}

	/**
	 * prefixをつける
	 */
	private function add_prefix($str){
		return self::$prefix .'_'. $str;
	}
}
