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
			[$this, 'renderDashboardPage']
		);
	}

	function renderDashboardPage(){ ?>
		<div
			id='ctbDashboard'
			data-info='<?php echo esc_attr( wp_json_encode( [
				'version' => CTB_VERSION,
				'isPremium' => ctbIsPremium(),
				'hasPro' => CTB_HAS_PRO
			] ) ); ?>'
		></div>
	<?php }
}
new SubMenu();