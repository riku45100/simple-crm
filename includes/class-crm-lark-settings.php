<?php
namespace Simple_CRM;

defined( 'ABSPATH' ) || exit;

class Class_CRM_Lark_Settings {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_settings_page() {
		add_submenu_page(
			'simple-crm',
			'Lark Settings',
			'Lark Settings',
			'manage_options',
			'simple-crm-lark-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	public function register_settings() {
		register_setting( 'simple_crm_lark_group', 'simple_crm_lark_app_id' );
		register_setting( 'simple_crm_lark_group', 'simple_crm_lark_app_secret' );
		register_setting( 'simple_crm_lark_group', 'simple_crm_lark_base_token' );
		register_setting( 'simple_crm_lark_group', 'simple_crm_lark_contacts_table_id' );
		register_setting( 'simple_crm_lark_group', 'simple_crm_lark_deals_table_id' );
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		?>
		<div class="wrap">
			<h1>Lark Base Settings</h1>
			<p>Configure integration with your Lark Base "CRM".</p>

			<form method="post" action="options.php">
				<?php settings_fields( 'simple_crm_lark_group' ); ?>
				<?php do_settings_sections( 'simple_crm_lark_group' ); ?>

				<table class="form-table" style="max-width:600px;">
					<tr>
						<th scope="row"><label for="simple_crm_lark_app_id">Lark App ID</label></th>
						<td>
							<input type="text" id="simple_crm_lark_app_id"
							       name="simple_crm_lark_app_id"
							       value="<?php echo esc_attr( get_option( 'simple_crm_lark_app_id' ) ); ?>"
							       class="regular-text">
							<p class="description">From your Lark Open Platform app.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="simple_crm_lark_app_secret">Lark App Secret</label></th>
						<td>
							<input type="password" id="simple_crm_lark_app_secret"
							       name="simple_crm_lark_app_secret"
							       value="<?php echo esc_attr( get_option( 'simple_crm_lark_app_secret' ) ); ?>"
							       class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="simple_crm_lark_base_token">Lark Base Token</label></th>
						<td>
							<input type="text" id="simple_crm_lark_base_token"
							       name="simple_crm_lark_base_token"
							       value="<?php echo esc_attr( get_option( 'simple_crm_lark_base_token' ) ); ?>"
							       class="regular-text">
							<p class="description">The Base token from your "CRM" Base URL.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="simple_crm_lark_contacts_table_id">Contacts Table ID</label></th>
						<td>
							<input type="text" id="simple_crm_lark_contacts_table_id"
							       name="simple_crm_lark_contacts_table_id"
							       value="<?php echo esc_attr( get_option( 'simple_crm_lark_contacts_table_id' ) ); ?>"
							       class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="simple_crm_lark_deals_table_id">Deals Table ID</label></th>
						<td>
							<input type="text" id="simple_crm_lark_deals_table_id"
							       name="simple_crm_lark_deals_table_id"
							       value="<?php echo esc_attr( get_option( 'simple_crm_lark_deals_table_id' ) ); ?>"
							       class="regular-text">
						</td>
					</tr>
				</table>

				<?php submit_button( 'Save Settings' ); ?>
			</form>

			<h2>How to get these values</h2>
			<ol>
				<li>Create an app in Lark Open Platform and get App ID & App Secret.</li>
				<li>Open your "CRM" Base in Lark; the Base token is in the URL.</li>
				<li>Get table IDs for Contacts and Deals tables via Lark API or dev tools.</li>
			</ol>
		</div>
		<?php
	}
}
