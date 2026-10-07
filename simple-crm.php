<?php
/**
 * Plugin Name: Simple CRM
 * Description: Custom WordPress CRM plugin with Elementor integration, Lark Base sync, Kanban pipeline, exports, and email.
 * Version: 0.1.0
 * Author: Richard McGrath/FourZero.work
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'SIMPLE_CRM_VERSION', '0.1.0' );
define( 'SIMPLE_CRM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIMPLE_CRM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load text domain
function simple_crm_load_textdomain() {
	load_plugin_textdomain( 'simple-crm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'simple_crm_load_textdomain' );

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
		'lead'      => __( 'Lead', 'simple-crm' ),
		'qualified' => __( 'Qualified', 'simple-crm' ),
		'proposal'  => __( 'Proposal', 'simple-crm' ),
		'won'       => __( 'Won', 'simple-crm' ),
		'lost'      => __( 'Lost', 'simple-crm' ),
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
	new \Simple_CRM\Class_CRM_Lark_Settings();
	new \Simple_CRM\Class_CRM_Kanban();
}
add_action( 'plugins_loaded', 'simple_crm_init' );
