<?php

/**
 * Plugin Name: Countdown Timer
 * Description: Display your events date into a timer to your visitor with countdown time block
 * Version: 1.3.0
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: countdown-time
 */
// ABS PATH
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'ctb_fs' ) ) {
    ctb_fs()->set_basename( false, __FILE__ );
} else {
    define( 'CTB_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.3.0' ) );
    define( 'CTB_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'CTB_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'CTB_HAS_PRO', file_exists( dirname( __FILE__ ) . '/vendor/freemius/start.php' ) );
    function ctb_fs() {
        global $ctb_fs;
        if ( !isset( $ctb_fs ) ) {
            if ( CTB_HAS_PRO ) {
                require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
            } else {
                require_once dirname( __FILE__ ) . '/vendor/freemius-lite/start.php';
            }
            $ctbConfig = [
                'id'                  => '14562',
                'slug'                => 'countdown-time',
                'premium_slug'        => 'countdown-time-pro',
                'type'                => 'plugin',
                'public_key'          => 'pk_7f62446a2a53154c56c36346db2fa',
                'is_premium'          => false,
                'premium_suffix'      => 'Pro',
                'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'trial'               => [
                    'days'               => 7,
                    'is_require_payment' => false,
                ],
                'menu'                => ( CTB_HAS_PRO ? [
                    'slug'       => 'countdown-time',
                    'first-path' => 'admin.php?page=countdown-time',
                    'contact'    => false,
                    'support'    => false,
                ] : [
                    'slug'           => 'countdown-time',
                    'override_exact' => true,
                    'first-path'     => 'tools.php?page=countdown-time',
                    'contact'        => false,
                    'support'        => false,
                    'parent'         => [
                        'slug' => 'tools.php',
                    ],
                ] ),
            ];
            $ctb_fs = ( CTB_HAS_PRO ? fs_dynamic_init( $ctbConfig ) : fs_lite_dynamic_init( $ctbConfig ) );
        }
        return $ctb_fs;
    }

    ctb_fs();
    do_action( 'ctb_fs_loaded' );
    function ctbIsPremium() {
        return ( CTB_HAS_PRO ? ctb_fs()->can_use_premium_code() : false );
    }

    // Require Files
    require_once CTB_DIR_PATH . '/includes/pattern.php';
    if ( CTB_HAS_PRO ) {
        require_once CTB_DIR_PATH . 'includes/admin/Menu.php';
    } else {
        require_once CTB_DIR_PATH . 'includes/admin/SubMenu.php';
    }
    if ( CTB_HAS_PRO && ctbIsPremium() ) {
        require_once CTB_DIR_PATH . 'includes/admin/CPT.php';
    }
    class CTBPlugin {
        function __construct() {
            add_filter( 'block_categories_all', [$this, 'blockCategories'] );
            add_action( 'init', [$this, 'onInit'] );
            add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
            add_action( 'enqueue_block_editor_assets', [$this, 'enqueueBlockEditorAssets'] );
        }

        function blockCategories( $categories ) {
            return array_merge( [[
                'slug'  => 'CTBlock',
                'title' => 'Countdown Time',
            ]], $categories );
        }

        function onInit() {
            register_block_type( __DIR__ . '/build' );
        }

        function adminEnqueueScripts( $hook ) {
            if ( strpos( $hook, 'countdown-time' ) ) {
                wp_enqueue_style(
                    'ctb-admin-dashboard',
                    CTB_DIR_URL . 'build/admin-dashboard.css',
                    [],
                    CTB_VERSION
                );
                wp_enqueue_script(
                    'ctb-admin-dashboard',
                    CTB_DIR_URL . 'build/admin-dashboard.js',
                    ['react', 'react-dom'],
                    CTB_VERSION,
                    true
                );
                wp_set_script_translations( 'ctb-admin-dashboard', 'countdown-time', CTB_DIR_PATH . 'languages' );
            }
        }

        function enqueueBlockEditorAssets() {
            wp_add_inline_script( 'ctb-countdown-time-editor-script', 'const ctppipecheck = ' . wp_json_encode( ctbIsPremium() ) . ';', 'before' );
        }

    }

    new CTBPlugin();
}