<?php
/**
 * CRM admin UI: menus, list columns, and contact meta boxes.
 *
 * @package Simple_CRM
 * @since 0.1.0
 */

namespace Simple_CRM;

defined( 'ABSPATH' ) || exit;

class Class_CRM_Admin {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_filter( 'manage_crm_contact_posts_columns', [ $this, 'contact_columns' ] );
		add_action( 'manage_crm_contact_posts_custom_column', [ $this, 'contact_column_content' ], 10, 2 );
		add_filter( 'manage_crm_deal_posts_columns', [ $this, 'deal_columns' ] );
		add_action( 'manage_crm_deal_posts_custom_column', [ $this, 'deal_column_content' ], 10, 2 );
		add_action( 'add_meta_boxes', [ $this, 'add_contact_meta_boxes' ] );
		add_action( 'save_post_crm_contact', [ $this, 'save_contact_meta' ] );
	}

	public function add_admin_menu() {
		add_menu_page( __( 'CRM', 'simple-crm' ), __( 'CRM', 'simple-crm' ), 'edit_posts', 'simple-crm', '', 'dashicons-contacts', 30 );
		add_submenu_page( 'simple-crm', __( 'Contacts', 'simple-crm' ), __( 'Contacts', 'simple-crm' ), 'edit_posts', 'edit.php?post_type=crm_contact' );
		add_submenu_page( 'simple-crm', __( 'Companies', 'simple-crm' ), __( 'Companies', 'simple-crm' ), 'edit_posts', 'edit.php?post_type=crm_company' );
		add_submenu_page( 'simple-crm', __( 'Deals', 'simple-crm' ), __( 'Deals', 'simple-crm' ), 'edit_posts', 'edit.php?post_type=crm_deal' );
		add_submenu_page( 'simple-crm', __( 'Activities', 'simple-crm' ), __( 'Activities', 'simple-crm' ), 'edit_posts', 'edit.php?post_type=crm_activity' );
	}

	public function contact_columns( $columns ) {
		$new = [];
		foreach ( $columns as $key => $value ) {
			$new[ $key ] = $value;
			if ( 'title' === $key ) {
				$new['crm_email']   = __( 'Email', 'simple-crm' );
				$new['crm_phone']   = __( 'Phone', 'simple-crm' );
				$new['crm_company'] = __( 'Company', 'simple-crm' );
			}
		}
		return $new;
	}

	public function contact_column_content( $column, $post_id ) {
		if ( 'crm_email' === $column || 'crm_phone' === $column ) {
			echo esc_html( get_post_meta( $post_id, $column, true ) );
			return;
		}

		if ( 'crm_company' === $column ) {
			$company_id = (int) get_post_meta( $post_id, 'crm_company_id', true );
			if ( $company_id ) {
				$company = get_post( $company_id );
				if ( $company ) {
					echo esc_html( $company->post_title );
				}
			}
		}
	}

	public function deal_columns( $columns ) {
		$new = [];
		foreach ( $columns as $key => $value ) {
			$new[ $key ] = $value;
			if ( 'title' === $key ) {
				$new['crm_value']   = __( 'Value', 'simple-crm' );
				$new['crm_stage']   = __( 'Stage', 'simple-crm' );
				$new['crm_contact'] = __( 'Contact', 'simple-crm' );
			}
		}
		return $new;
	}

	public function deal_column_content( $column, $post_id ) {
		if ( 'crm_value' === $column ) {
			$value = (float) get_post_meta( $post_id, 'crm_value', true );
			echo $value ? esc_html( number_format_i18n( $value, 2 ) ) . ' €' : esc_html__( '0.00 €', 'simple-crm' );
			return;
		}

		if ( 'crm_stage' === $column ) {
			$stage  = get_post_meta( $post_id, 'crm_stage', true );
			$stages = simple_crm_get_deal_stages();
			echo esc_html( $stages[ $stage ] ?? $stage );
			return;
		}

		if ( 'crm_contact' === $column ) {
			$contact_id = (int) get_post_meta( $post_id, 'crm_contact_id', true );
			if ( $contact_id ) {
				$contact = get_post( $contact_id );
				if ( $contact ) {
					echo esc_html( $contact->post_title );
				}
			}
		}
	}

	public function add_contact_meta_boxes() {
		add_meta_box( 'crm_contact_details', __( 'Contact Details', 'simple-crm' ), [ $this, 'render_contact_details_box' ], 'crm_contact', 'normal', 'high' );
	}

	public function render_contact_details_box( $post ) {
		wp_nonce_field( 'crm_contact_details_nonce', 'crm_contact_details_nonce' );
		$email      = get_post_meta( $post->ID, 'crm_email', true );
		$phone      = get_post_meta( $post->ID, 'crm_phone', true );
		$company_id = (int) get_post_meta( $post->ID, 'crm_company_id', true );
		$companies  = get_posts( [ 'post_type' => 'crm_company', 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids' ] );
		?>
		<p><label for="crm_email"><?php esc_html_e( 'Email', 'simple-crm' ); ?></label><br><input type="email" name="crm_email" id="crm_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text"></p>
		<p><label for="crm_phone"><?php esc_html_e( 'Phone', 'simple-crm' ); ?></label><br><input type="text" name="crm_phone" id="crm_phone" value="<?php echo esc_attr( $phone ); ?>" class="regular-text"></p>
		<p>
			<label for="crm_company_id"><?php esc_html_e( 'Company', 'simple-crm' ); ?></label><br>
			<select name="crm_company_id" id="crm_company_id">
				<option value=""><?php esc_html_e( '— None —', 'simple-crm' ); ?></option>
				<?php foreach ( $companies as $company_post_id ) : ?>
					<option value="<?php echo (int) $company_post_id; ?>" <?php selected( $company_id, $company_post_id ); ?>><?php echo esc_html( get_the_title( $company_post_id ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	public function save_contact_meta( $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['crm_contact_details_nonce'] ) || ! wp_verify_nonce( $_POST['crm_contact_details_nonce'], 'crm_contact_details_nonce' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}

		if ( isset( $_POST['crm_email'] ) ) {
			update_post_meta( $post_id, 'crm_email', sanitize_email( wp_unslash( $_POST['crm_email'] ) ) );
		}
		if ( isset( $_POST['crm_phone'] ) ) {
			update_post_meta( $post_id, 'crm_phone', sanitize_text_field( wp_unslash( $_POST['crm_phone'] ) ) );
		}
		if ( isset( $_POST['crm_company_id'] ) ) {
			update_post_meta( $post_id, 'crm_company_id', absint( $_POST['crm_company_id'] ) );
		}
	}
}
