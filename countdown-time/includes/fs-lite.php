<?php
if ( !defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'ctb_fs' ) ) {
	/**
	 * Freemius Lite SDK initialization and helper function.
	 *
	 * Checks if Freemius is set up, initializes it if necessary with the proper keys and plan configurations,
	 * and returns the global Freemius instance.
	 *
	 * @return \Freemius The global Freemius instance.
	 */
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
				'menu'					=> [
					'slug'			=> 'countdown-time',
					'first-path'	=> 'tools.php?page=countdown-time',
					'parent'		=> [
						'slug'	=> 'tools.php'
					]
				]
			] );
		}

		return $ctb_fs;
	}

	ctb_fs();
	do_action( 'ctb_fs_loaded' );
}
