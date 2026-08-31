<?php
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
		add_menu_page(
			'CRM',
			'CRM',
			'edit_posts',
			'simple-crm',
			'',
			'dashicons-contacts',
			30
		);

		add_submenu_page( 'simple-crm', 'Contacts', 'Contacts', 'edit_posts', 'edit.php?post_type=crm_contact' );
		add_submenu_page( 'simple-crm', 'Companies', 'Companies', 'edit_posts', 'edit.php?post_type=crm_company' );
		add_submenu_page( 'simple-crm', 'Deals', 'Deals', 'edit_posts', 'edit.php?post_type=crm_deal' );
		add_submenu_page( 'simple-crm', 'Activities', 'Activities', 'edit_posts', 'edit.php?post_type=crm_activity' );
	}

	public function contact_columns( $columns ) {
		$new = [];
		foreach ( $columns as $key => $value ) {
			if ( $key === 'title' ) {
				$new[ $key ] = $value;
				$new['crm_email'] = 'Email';
				$new['crm_phone'] = 'Phone';
				$new['crm_company'] = 'Company';
			} else {
				$new[ $key ] = $value;
			}
		}
		return $new;
	}

	public function contact_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'crm_email':
				echo esc_html( get_post_meta( $post_id, 'crm_email', true ) );
				break;
			case 'crm_phone':
				echo esc_html( get_post_meta( $post_id, 'crm_phone', true ) );
				break;
			case 'crm_company':
				$comp_id = (int) get_post_meta( $post_id, 'crm_company_id', true );
				if ( $comp_id ) {
					$comp = get_post( $comp_id );
					if ( $comp ) {
						echo esc_html( $comp->post_title );
					}
				}
				break;
		}
	}

	public function deal_columns( $columns ) {
		$new = [];
		foreach ( $columns as $key => $value ) {
			if ( $key === 'title' ) {
				$new[ $key ] = $value;
				$new['crm_value'] = 'Value';
				$new['crm_stage'] = 'Stage';
				$new['crm_contact'] = 'Contact';
			} else {
				$new[ $key ] = $value;
			}
		}
		return $new;
	}

	public function deal_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'crm_value':
				$value = (float) get_post_meta( $post_id, 'crm_value', true );
				echo $value ? esc_html( number_format( $value, 2, ',', ' ' ) ) . ' €' : '0,00 €';
				break;
			case 'crm_stage':
				$stage = get_post_meta( $post_id, 'crm_stage', true );
				$stages = simple_crm_get_deal_stages();
				echo esc_html( $stages[ $stage ] ?? $stage );
				break;
			case 'crm_contact':
				$contact_id = (int) get_post_meta( $post_id, 'crm_contact_id', true );
				if ( $contact_id ) {
					$contact = get_post( $contact_id );
					if ( $contact ) {
						echo esc_html( $contact->post_title );
					}
				}
				break;
		}
	}

	public function add_contact_meta_boxes() {
		add_meta_box(
			'crm_contact_details',
			'Contact Details',
			[ $this, 'render_contact_details_box' ],
			'crm_contact',
			'normal',
			'high'
		);
	}

	public function render_contact_details_box( $post ) {
		wp_nonce_field( 'crm_contact_details_nonce', 'crm_contact_details_nonce' );

		$email  = get_post_meta( $post->ID, 'crm_email', true );
		$phone  = get_post_meta( $post->ID, 'crm_phone', true );
		$comp_id = (int) get_post_meta( $post->ID, 'crm_company_id', true );

		$companies = get_posts( [
			'post_type'      => 'crm_company',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		] );
		?>
		<p>
			<label for="crm_email">Email</label><br>
			<input type="email" name="crm_email" id="crm_email"
			       value="<?php echo esc_attr( $email ); ?>" class="regular-text">
		</p>
		<p>
			<label for="crm_phone">Phone</label><br>
			<input type="text" name="crm_phone" id="crm_phone"
			       value="<?php echo esc_attr( $phone ); ?>" class="regular-text">
		</p>
		<p>
			<label for="crm_company_id">Company</label><br>
			<select name="crm_company_id" id="crm_company_id">
				<option value="">— None —</option>
				<?php foreach ( $companies as $cid ) : 
					$title = get_the_title( $cid );
					?>
					<option value="<?php echo (int) $cid; ?>" <?php selected( $comp_id, $cid ); ?>>
						<?php echo esc_html( $title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	public function save_contact_meta( $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['crm_contact_details_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['crm_contact_details_nonce'], 'crm_contact_details_nonce' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST['crm_email'] ) ) {
			update_post_meta( $post_id, 'crm_email', sanitize_email( $_POST['crm_email'] ) );
		}
		if ( isset( $_POST['crm_phone'] ) ) {
			update_post_meta( $post_id, 'crm_phone', sanitize_text_field( $_POST['crm_phone'] ) );
		}
		if ( isset( $_POST['crm_company_id'] ) ) {
			update_post_meta( $post_id, 'crm_company_id', absint( $_POST['crm_company_id'] ) );
		}
	}
}
