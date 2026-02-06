<?php
/**
 * Plugin Name: Countdown Timer
 * Description: Display your events date into a timer to your visitor with countdown time block
 * Version: 1.3.1
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: countdown-time
   */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( function_exists( 'ctb_fs' ) ) {
	ctb_fs()->set_basename( false, __FILE__ );
}else{
	define( 'CTB_VERSION', isset( $_SERVER['HTTP_HOST'] ) && ( 'localhost' === $_SERVER['HTTP_HOST'] || 'plugins.local' === $_SERVER['HTTP_HOST'] ) ? time() : '1.3.1' );
	define( 'CTB_DIR_URL', plugin_dir_url( __FILE__ ) );
	define( 'CTB_DIR_PATH', plugin_dir_path( __FILE__ ) );
	define( 'CTB_HAS_PRO', file_exists( CTB_DIR_PATH . 'vendor/freemius/start.php' ) );

	if ( CTB_HAS_PRO ) {
		require_once CTB_DIR_PATH . 'includes/fs.php';
		require_once CTB_DIR_PATH . 'includes/admin/CPT.php';
	}else{
		require_once CTB_DIR_PATH . 'includes/fs-lite.php';
		require_once CTB_DIR_PATH . 'includes/admin/SubMenu.php';
	}

	require_once CTB_DIR_PATH . 'includes/Patterns.php';

	function ctbIsPremium(){
		return CTB_HAS_PRO ? ctb_fs()->can_use_premium_code() : false;
	}

	class CTBPlugin{
		function __construct(){
			add_action( 'init', [ $this, 'onInit' ] );
			add_filter( 'block_categories_all', [$this, 'blockCategories'] );
			add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
			add_action( 'enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets'] );
		}

		function onInit(){
			register_block_type( __DIR__ . '/build' );
		}

		function blockCategories( $categories ){
			return array_merge( [ [
				'slug'	=> 'CTBlock',
				'title'	=> 'Countdown Time'
			] ], $categories );
		}

		function adminEnqueueScripts( $hook ) {
			if( strpos( $hook, 'countdown-time' ) ){
				wp_enqueue_style( 'ctb-admin-dashboard', CTB_DIR_URL . 'build/admin/dashboard.css', [], CTB_VERSION );
				wp_enqueue_script( 'ctb-admin-dashboard', CTB_DIR_URL . 'build/admin/dashboard.js', [ 'react', 'react-dom' ], CTB_VERSION, true );
				wp_set_script_translations( 'ctb-admin-dashboard', 'countdown-time', CTB_DIR_PATH . 'languages' );
			}
		}

		function enqueueBlockEditorAssets(){
			wp_add_inline_script( 'ctb-countdown-time-editor-script', 'const ctppipecheck = ' . wp_json_encode( ctbIsPremium() ) .'; const ctbpricingurl = "'. admin_url( CTB_HAS_PRO ? 'edit.php?post_type=ctb&page=countdown-time#/pricing' : 'tools.php?page=countdown-time#/pricing' ) .'";', 'before' );
		}

		static function renderDashboard(){ ?>
			<div
				id='ctbDashboard'
				data-info='<?php echo esc_attr( wp_json_encode( [
					'version'	=> CTB_VERSION,
					'isPremium'	=> ctbIsPremium(),
					'hasPro'	=> CTB_HAS_PRO
				] ) ); ?>'
			></div>
		<?php }
	}
	new CTBPlugin;
}