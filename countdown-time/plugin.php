<?php
/**
 * Plugin Name: Countdown Timer
 * Description: Display your events date into a timer to your visitor with countdown time block
 * Version: 1.3.3
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * Plugin URI: https://bplugins.com/products/countdown-time
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: countdown-time
 * Requires at least: 6.5
 * Tested up to: 7.0
 * Requires PHP: 7.4
 */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( function_exists( 'ctb_fs' ) ) {
	ctb_fs()->set_basename( true, __FILE__ );
}else{
	define( 'CTB_VERSION', ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : '1.3.3' );
	define( 'CTB_DIR_URL', plugin_dir_url( __FILE__ ) );
	define( 'CTB_DIR_PATH', plugin_dir_path( __FILE__ ) );

	require_once CTB_DIR_PATH . 'includes/fs-lite.php';
	require_once CTB_DIR_PATH . 'includes/admin/SubMenu.php';
	require_once CTB_DIR_PATH . 'includes/Patterns.php';

	if( !class_exists( 'CTBPlugin' ) ){
		/**
		 * Main plugin class for Countdown Timer.
		 */
		class CTBPlugin{
			/**
			 * Constructor. Sets up action and filter hooks.
			 */
			public function __construct(){
				add_action( 'init', [ $this, 'onInit' ] );
				add_filter( 'block_categories_all', [$this, 'blockCategories'] );
				add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
				add_action( 'enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets'] );

				add_filter( 'plugin_action_links', [$this, 'pluginActionLinks'], 10, 2 );
				add_filter( 'default_title', [$this, 'defaultTitle'], 10, 2 );
				add_filter( 'default_content', [$this, 'defaultContent'], 10, 2 );
			}
			
			/**
			 * Filter the default title for newly created pages.
			 *
			 * @param string  $title Default page title.
			 * @param \WP_Post $post  Post object.
			 * @return string Filtered page title.
			 */
			public function defaultTitle( $title, $post ) {
				if ( 'page' === $post->post_type && isset( $_GET['title'] ) ) {
					$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';

					if ( wp_verify_nonce( $nonce, 'ctbCreatePage' ) ) {
						return sanitize_text_field( wp_unslash( $_GET['title'] ) );
					}
				}
				return $title;
			}

			/**
			 * Filter the default content for newly created pages.
			 *
			 * @param string  $content Default page content.
			 * @param \WP_Post $post    Post object.
			 * @return string Filtered page content.
			 */
			public function defaultContent( $content, $post ) {
				if ( 'page' === $post->post_type && isset( $_GET['content'] ) ) {
					$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';

					if ( wp_verify_nonce( $nonce, 'ctbCreatePage' ) ) {
						return wp_kses_post( wp_unslash( $_GET['content'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					}
				}
				return $content;
			}

			/**
			 * Add custom links to the plugin action links on the plugins page.
			 *
			 * @param array  $links Action links array.
			 * @param string $file  Plugin filename relative to plugins directory.
			 * @return array Modified action links.
			 */
			public function pluginActionLinks( $links, $file ) {
				if( plugin_basename( __FILE__ ) === $file ) {
					$helpDemosLink = admin_url( 'tools.php?page=countdown-time#/welcome' );

					$links['help-and-demos'] = sprintf( '<a href="%s" style="%s">%s</a>', $helpDemosLink, 'color:#FF7A00;font-weight:bold', __( 'Help & Demos', 'countdown-time' ) );
				}
	
				return $links;
			}

			/**
			 * Initialize the block and register block type.
			 *
			 * @return void
			 */
			public function onInit(){
				register_block_type( __DIR__ . '/build' );
			}

			/**
			 * Add custom block category for Countdown Time.
			 *
			 * @param array $categories Current block categories.
			 * @return array Modified block categories.
			 */
			public function blockCategories( $categories ){
				return array_merge( [ [
					'slug'	=> 'CTBlock',
					'title'	=> 'Countdown Time'
				] ], $categories );
			}

			/**
			 * Enqueue stylesheets and scripts in the admin dashboard page.
			 *
			 * @param string $hook The current admin page hook.
			 * @return void
			 */
			public function adminEnqueueScripts( $hook ) {
				if( strpos( $hook, 'countdown-time' ) ){
					wp_enqueue_style( 'ctb-admin-dashboard', CTB_DIR_URL . 'build/admin/dashboard.css', [], CTB_VERSION );

					$asset_file = include CTB_DIR_PATH . 'build/admin/dashboard.asset.php';
					wp_enqueue_script( 'ctb-admin-dashboard', CTB_DIR_URL . 'build/admin/dashboard.js', array_merge( $asset_file['dependencies'], [ 'wp-util' ] ), CTB_VERSION, true );
					wp_set_script_translations( 'ctb-admin-dashboard', 'countdown-time', CTB_DIR_PATH . 'languages' );
				}
			}

			/**
			 * Add inline script for the block editor.
			 *
			 * @return void
			 */
			public function enqueueBlockEditorAssets(){
				wp_add_inline_script( 'ctb-countdown-time-editor-script', 'const ctbpricingurl = "'. admin_url( 'tools.php?page=countdown-time#/pricing' ) .'";', 'before' );
			}

			/**
			 * Render the admin dashboard wrapper div.
			 *
			 * @return void
			 */
			public static function renderDashboard(){ ?>
				<div
					id='ctbDashboard'
					data-info='<?php echo esc_attr( wp_json_encode( [
						'version' => CTB_VERSION,
						'isPremium' => false,
						'hasPro' => false,
						'adminUrl' => admin_url(),
						'startUrl' => admin_url( 'post-new.php?post_type=page&title=Countdown&content=' . rawurlencode( '<!-- wp:ctb/countdown-time /-->' ) . '&nonce=' . wp_create_nonce( 'ctbCreatePage' ) )
					] ) ); ?>'
				></div>
			<?php }
		}
		new CTBPlugin;
	}
}