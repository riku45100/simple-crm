<?php
/**
 * Plugin Name: Simple CRM
 * Description: Custom WordPress CRM plugin with Elementor integration, Lark Base sync, Kanban pipeline, exports, and email.
 * Version: 0.1.0
 * Author: Your Name
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'SIMPLE_CRM_VERSION', '0.1.0' );
define( 'SIMPLE_CRM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIMPLE_CRM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Autoload includes (simple manual autoload)
spl_autoload_register(
	function ( $class ) {
		$prefix = 'Simple_CRM\\';
		$base   = SIMPLE_CRM_PLUGIN_DIR . 'includes/';

		if ( strpos( $class, $prefix ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$file     = $base . str_replace( '_', '-', strtolower( $relative ) ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

// Stages helper
function simple_crm_get_deal_stages() {
	return [
		'lead'      => 'Lead',
		'qualified' => 'Qualified',
		'proposal'  => 'Proposal',
		'won'       => 'Won',
		'lost'      => 'Lost',
	];
}

// Bootstrap
function simple_crm_init() {
	new \Simple_CRM\Class_CRM_Post_Types();
	new \Simple_CRM\Class_CRM_Admin();
	new \Simple_CRM\Class_CRM_Forms();

	if ( did_action( 'elementor_pro/init' ) ) {
		new \Simple_CRM\Class_CRM_Elementor();
	}

	new \Simple_CRM\Class_CRM_Export();
	new \Simple_CRM\Class_CRM_Emails();
	new \Simple_CRM\Class_CRM_Lark();
	new \Simple_CRM\Class_CRM_Kanban();
}
add_action( 'plugins_loaded', 'simple_crm_init' );
