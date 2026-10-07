<?php
/**
 * Lark Base integration settings.
 *
 * @package Simple_CRM
 * @since 0.1.0
 */

namespace Simple_CRM;

defined( 'ABSPATH' ) || exit;

class Class_CRM_Lark_Settings {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_settings_page() {
		add_submenu_page( 'simple-crm', __( 'Lark Settings', 'simple-crm' ), __( 'Lark Settings', 'simple-crm' ), 'manage_options', 'simple-crm-lark-settings', [ $this, 'render_settings_page' ] );
	}

	public function register_settings() {
		register_setting( 'simple_crm_lark_group', 'simple_crm_lark_app_id', [ 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'simple_crm_lark_group', 'simple_crm_lark_app_secret', [ 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'simple_crm_lark_group', 'simple_crm_lark_base_token', [ 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'simple_crm_lark_group', 'simple_crm_lark_contacts_table_id', [ 'sanitize_callback' => 'sanitize_text_field' ] );
		register_setting( 'simple_crm_lark_group', 'simple_crm_lark_deals_table_id', [ 'sanitize_callback' => 'sanitize_text_field' ] );
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'simple-crm' ) );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Lark Base Settings', 'simple-crm' ); ?></h1>
			<p><?php esc_html_e( 'Configure integration with your Lark Base "CRM".', 'simple-crm' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'simple_crm_lark_group' ); ?>
				<table class="form-table">
					<tr><th scope="row"><label for="simple_crm_lark_app_id"><?php esc_html_e( 'Lark App ID', 'simple-crm' ); ?></label></th><td><input type="text" id="simple_crm_lark_app_id" name="simple_crm_lark_app_id" value="<?php echo esc_attr( get_option( 'simple_crm_lark_app_id' ) ); ?>" class="regular-text"><p class="description"><?php esc_html_e( 'From your Lark Open Platform app.', 'simple-crm' ); ?></p></td></tr>
					<tr><th scope="row"><label for="simple_crm_lark_app_secret"><?php esc_html_e( 'Lark App Secret', 'simple-crm' ); ?></label></th><td><input type="password" id="simple_crm_lark_app_secret" name="simple_crm_lark_app_secret" value="<?php echo esc_attr( get_option( 'simple_crm_lark_app_secret' ) ); ?>" class="regular-text"></td></tr>
					<tr><th scope="row"><label for="simple_crm_lark_base_token"><?php esc_html_e( 'Lark Base Token', 'simple-crm' ); ?></label></th><td><input type="text" id="simple_crm_lark_base_token" name="simple_crm_lark_base_token" value="<?php echo esc_attr( get_option( 'simple_crm_lark_base_token' ) ); ?>" class="regular-text"><p class="description"><?php esc_html_e( 'The Base token from your CRM Base URL.', 'simple-crm' ); ?></p></td></tr>
					<tr><th scope="row"><label for="simple_crm_lark_contacts_table_id"><?php esc_html_e( 'Contacts Table ID', 'simple-crm' ); ?></label></th><td><input type="text" id="simple_crm_lark_contacts_table_id" name="simple_crm_lark_contacts_table_id" value="<?php echo esc_attr( get_option( 'simple_crm_lark_contacts_table_id' ) ); ?>" class="regular-text"></td></tr>
					<tr><th scope="row"><label for="simple_crm_lark_deals_table_id"><?php esc_html_e( 'Deals Table ID', 'simple-crm' ); ?></label></th><td><input type="text" id="simple_crm_lark_deals_table_id" name="simple_crm_lark_deals_table_id" value="<?php echo esc_attr( get_option( 'simple_crm_lark_deals_table_id' ) ); ?>" class="regular-text"></td></tr>
				</table>
				<?php submit_button( __( 'Save Settings', 'simple-crm' ) ); ?>
			</form>
		</div>
		<?php
	}
}
