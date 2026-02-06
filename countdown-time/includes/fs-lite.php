<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'ctb_fs' ) ) {
	function ctb_fs() {
		global $ctb_fs;

		if ( !isset( $ctb_fs ) ) {
			require_once CTB_DIR_PATH . '/vendor/freemius-lite/start.php';

			$ctb_fs = fs_lite_dynamic_init( [
				'id'					=> '14562',
				'slug'					=> 'countdown-time',
				'__FILE__'				=> CTB_DIR_PATH . 'plugin.php',
				'premium_slug'			=> 'countdown-time-pro',
				'type'					=> 'plugin',
				'public_key'			=> 'pk_7f62446a2a53154c56c36346db2fa',
				'is_premium'			=> false,
				'premium_suffix'		=> 'Pro',
				'has_premium_version'	=> true,
				'has_addons'			=> false,
				'has_paid_plans'		=> true,
				'menu'					=> [
					'slug'			=> 'countdown-time',
					'first-path'	=> 'tools.php?page=countdown-time',
					'parent'		=> [
						'slug'	=> 'tools.php'
					],
					'contact'		=> false,
					'support'		=> false
				]
			] );
		}

		return $ctb_fs;
	}

	ctb_fs();
	do_action( 'ctb_fs_loaded' );
}
