<?php
/**
 * Plugin Name: Countdown Timer
 * Description: Display your events date into a timer to your visitor with countdown time block
 * Version: 1.3.4
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * Plugin URI: https://bplugins.com/products/countdown-time/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: countdown-time
 * Requires at least: 6.5
 * Tested up to: 7.1
 * Requires PHP: 7.4
 */

// ABS PATH
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( function_exists( 'ctb_fs' ) ) {
	ctb_fs()->set_basename( true, __FILE__ );
}else{
	define( 'CTB_VERSION', ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : '1.3.4' );
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
				add_action( 'wp_ajax_ctb_dismiss_bfcm_banner', [$this, 'ajaxDismissBFCM'] );
				add_filter( 'default_title', [$this, 'defaultTitle'], 10, 2 );
				add_filter( 'default_content', [$this, 'defaultContent'], 10, 2 );
			}
			
			/**
			 * Filter the default title for newly created pages.
			 *
			 * @param string	$title	Default page title.
			 * @param \WP_Post	$post	Post object.
			 * @return string	Filtered page title.
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
			 * @param string	$content	Default page content.
			 * @param \WP_Post	$post		Post object.
			 * @return string	Filtered page content.
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
			 * Checks whether the current user has dismissed the BFCM promotion in ctb_options.
			 *
			 * @param int $user_id
			 * @return bool
			 */
			public static function isBFCMPromotionDismissed( $user_id = 0 ) {
				if ( ! $user_id ) {
					$user_id = get_current_user_id();
				}
				if ( ! $user_id ) {
					return false;
				}
				$options   = get_option( 'ctb_options', [] );
				$dismissed = isset( $options['bfcm_2026_dismissed'] ) && is_array( $options['bfcm_2026_dismissed'] )
					? $options['bfcm_2026_dismissed']
					: [];
				return in_array( (int) $user_id, array_map( 'intval', $dismissed ), true );
			}

			/**
			 * Records the user dismissal inside the ctb_options array in wp_options.
			 *
			 * @param int $user_id
			 * @return bool
			 */
			public static function dismissBFCMPromotion( $user_id = 0 ) {
				if ( ! $user_id ) {
					$user_id = get_current_user_id();
				}
				if ( ! $user_id ) {
					return false;
				}
				$options = get_option( 'ctb_options', [] );
				if ( ! is_array( $options ) ) {
					$options = [];
				}
				$dismissed = isset( $options['bfcm_2026_dismissed'] ) && is_array( $options['bfcm_2026_dismissed'] )
					? array_map( 'intval', $options['bfcm_2026_dismissed'] )
					: [];
				$user_id = (int) $user_id;
				if ( ! in_array( $user_id, $dismissed, true ) ) {
					$dismissed[] = $user_id;
					$options['bfcm_2026_dismissed'] = array_values( array_unique( $dismissed ) );
					update_option( 'ctb_options', $options );
				}
				return true;
			}

			/**
			 * Handles AJAX request to dismiss the BFCM promotional banner.
			 *
			 * @return void
			 */
			public function ajaxDismissBFCM() {
				check_ajax_referer( 'ctb_dismiss_bfcm', 'nonce' );
				self::dismissBFCMPromotion();
				wp_send_json_success();
			}

			/**
			 * Add custom links to the plugin action links on the plugins page.
			 *
			 * @param array		$links	Action links array.
			 * @param string	$file	Plugin filename relative to plugins directory.
			 * @return array	Modified action links.
			 */
			public function pluginActionLinks( $links, $file ) {
				if( plugin_basename( __FILE__ ) === $file ) {
					$helpDemosLink = admin_url( 'tools.php?page=countdown-time#/welcome' );

					$links['help-and-demos'] = sprintf( '<a href="%s" style="%s">%s</a>', $helpDemosLink, 'color:#FF7A00;font-weight:bold', __( 'Help & Demos', 'countdown-time' ) );

					$isExpired = time() > strtotime( '2026-12-05 23:59:59' );
					$isPremium = function_exists( 'ctb_fs' ) && ctb_fs()->can_use_premium_code();

					if ( ! $isExpired && ! $isPremium ) {
						$bfcmUrl = 'https://bplugins.com/products/countdown-time/pricing/?utm_source=admin&utm_medium=plugins&utm_campaign=bfcm2026';
						$links['bfcm-deal'] = sprintf(
							'<a href="%s" style="%s" target="_blank" rel="noopener noreferrer">%s</a>',
							esc_url( $bfcmUrl ),
							'color: #dc2626; font-weight: 700;',
							__( 'Upgrade to Pro & Save 45% This BFCM!', 'countdown-time' )
						);
					}
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

				// Core registers block.json scripts without a path, so local JSONs need this explicitly
				wp_set_script_translations( 'ctb-countdown-time-editor-script', 'countdown-time', CTB_DIR_PATH . 'languages' );
				wp_set_script_translations( 'ctb-countdown-time-view-script', 'countdown-time', CTB_DIR_PATH . 'languages' );
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
					'title'	=> __( 'Countdown Time', 'countdown-time' )
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
						'isPremium' => function_exists( 'ctb_fs' ) && ctb_fs()->can_use_premium_code(),
						'isBFCMDismissed' => self::isBFCMPromotionDismissed(),
						'dismissNonce' => wp_create_nonce( 'ctb_dismiss_bfcm' ),
						'hasPro' => false,
						'adminUrl' => admin_url(),
						'currentUser' => [
							'name' => wp_get_current_user()->display_name,
						],
						'startUrl' => admin_url( 'post-new.php?post_type=page&title=Countdown&content=' . rawurlencode( '<!-- wp:ctb/countdown-time /-->' ) . '&nonce=' . wp_create_nonce( 'ctbCreatePage' ) )
					] ) ); ?>'
				></div>
			<?php }
		}
		new CTBPlugin;
	}
}