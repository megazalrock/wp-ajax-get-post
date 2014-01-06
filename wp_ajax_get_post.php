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

register_activation_hook( __FILE__, array( 'WP_Ajax_Get_Post', 'on_activation' ) );
register_deactivation_hook( __FILE__, array( 'WP_Ajax_Get_Post', 'on_deactivation' ) );
register_uninstall_hook( __FILE__, array( 'WP_Ajax_Get_Post', 'on_uninstall' ) );

add_action( 'plugins_loaded', array( 'WP_Ajax_Get_Post', 'init' ) );
class WP_Ajax_Get_Post {
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
		$this->plugin_url = plugin_dir_url( __FILE__ );
		$this->plugin_path = dirname( __FILE__ ) . '/';

		$locale = apply_filters( 'plugin_locale', get_locale(), 'wpagp' );
		load_textdomain( 'wpagp', WP_LANG_DIR . '/wpagp/wpagp-' . $locale . '.mo' );
		load_plugin_textdomain( 'wpagp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		// add action
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

	/*
	 * 引数をもとにカスタム投稿タイプを生成します。
	 * @param string $en 英語名（スラッグ）
	 * @param string $ja 日本語名（表示用）
	 * @param number $menu_position メニュー位置
	 * @param array $taxonomies カスタムタクソノミーの設定
	 */
	public static function create_custom_post_type($en,$ja,$menu_position,$taxonomies = null,$show_in_menu = true){
		if(!is_null($taxonomies) && is_array($taxonomies)){
			$this->create_custom_taxonomies($taxonomies['en'],$taxonomies['ja'],$en);
		}

		register_post_type( $en,
			array(
			'labels' => array(
			'name' => __( $ja ),
			'singular_name' => __( $ja ),
			'add_new_item' => __( $ja.'を新規追加' ),
			'add_new' => __( $ja.'を新規追加' ),
			'new_item' => __('新しい'.$ja),
			'view_item' => __($ja.'を表示'),
			'not_found' => __($ja.'はありません'),
			'not_found_in_trash' => __('ゴミ箱に'.$ja.'はありません。'),
			'search_items' => __($ja.'を検索'),
			'description' => __($ja.'の管理をします。')
			),
			'has_archive' => true,
			'public' => true,
			'menu_position' => $menu_position,
			'supports' => array('title','editor','author','custom-fields','revisions'),
			'rewrite' => true,
			'show_in_menu' => $show_in_menu
			)
		);
	}

	/**
	 * カスタムタクソノミーを生成
	 * @param string $en 英語名（スラッグ）
	 * @param string $ja 日本語名（表示用）
	 * @param number $post_type 追加するカスタム投稿スラッグ
	 */
	public static function create_custom_taxonomies($en,$ja,$post_type){
		register_taxonomy(
			$en, 
			$post_type, 
			array(
				'hierarchical' => true, 
				'update_count_callback' => '_update_post_term_count',
				'label' => $ja,
				'singular_label' => $ja,
				'public' => true,
				'show_ui' => true,
				'rewrite' => true
			)
		);
	}

	/**
	 * prefix付きでoptionを取得
	 */
	private static function get_option($key,$default = false){
		return get_option($this->add_prefix($key),$default);
	}

	/**
	 * prefix付きでoptionを保存
	 */
	private function update_option($key,$value){
		return update_option($this->add_prefix($key),$value);
	}

	/**
	 * prefixをつける
	 */
	private function add_prefix($str){
		return self::$prefix .'_'. $str;
	}
}
