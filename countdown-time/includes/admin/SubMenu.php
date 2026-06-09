<?php
namespace CTB\Admin;

if ( !defined( 'ABSPATH' ) ) { exit; }

/**
 * Class SubMenu
 *
 * Handles admin submenu page registration for tools section in free version.
 *
 * @package CTB\Admin
 */
class SubMenu {
	/**
	 * Constructor. Sets up action hook for admin menu.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'adminMenu' ] );
	}

	/**
	 * Add Countdown Timer submenu under WordPress Tools.
	 *
	 * @return void
	 */
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