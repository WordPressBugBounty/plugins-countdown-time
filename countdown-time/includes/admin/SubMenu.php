<?php
namespace CTB\Admin;

if ( !defined( 'ABSPATH' ) ) { exit; }

class SubMenu {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
	}

	function adminMenu(){
		add_submenu_page(
			'tools.php',
			__('Countdown Timer - bPlugins', 'countdown-time'),
			__('Countdown Timer', 'countdown-time'),
			'manage_options',
			'countdown-time',
			[ \CTBPlugin::class, 'renderDashboard' ]
		);
	}
}
new SubMenu();