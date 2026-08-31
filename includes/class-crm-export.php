<?php
namespace Simple_CRM;

defined( 'ABSPATH' ) || exit;

class Class_CRM_Export {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_export_page' ] );
		add_action( 'admin_post_export_crm_contacts', [ $this, 'handle_export_contacts' ] );
		add_action( 'admin_post_export_crm_companies', [ $this, 'handle_export_companies' ] );
		add_action( 'admin_post_export_crm_deals', [ $this, 'handle_export_deals' ] );
		add_action( 'admin_post_export_crm_activities', [ $this, 'handle_export_activities' ] );
	}

	public function add_export_page() {
		add_submenu_page(
			'simple-crm',
			'Export Data',
			'Export',
			'edit_posts',
			'simple-crm-export',
			[ $this, 'render_export_page' ]
		);
	}

	public function render_export_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}
		?>
		<div class="wrap">
			<h1>Export CRM Data</h1>
			<p>Download your CRM data as CSV files.</p>

			<h2>Contacts</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'export_crm_contacts_nonce', 'export_crm_contacts_nonce' ); ?>
				<input type="hidden" name="action" value="export_crm_contacts">
				<?php submit_button( 'Export Contacts CSV', 'primary', 'submit-contacts' ); ?>
			</form>

			<h2>Companies</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'export_crm_companies_nonce', 'export_crm_companies_nonce' ); ?>
				<input type="hidden" name="action" value="export_crm_companies">
				<?php submit_button( 'Export Companies CSV', 'primary', 'submit-companies' ); ?>
			</form>

			<h2>Deals</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'export_crm_deals_nonce', 'export_crm_deals_nonce' ); ?>
				<input type="hidden" name="action" value="export_crm_deals">
				<?php submit_button( 'Export Deals CSV', 'primary', 'submit-deals' ); ?>
			</form>

			<h2>Activities</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'export_crm_activities_nonce', 'export_crm_activities_nonce' ); ?>
				<input type="hidden" name="action" value="export_crm_activities">
				<?php submit_button( 'Export Activities CSV', 'primary', 'submit-activities' ); ?>
			</form>
		</div>
		<?php
	}

	private function output_csv( $filename, $headers, $rows ) {
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$out = fopen( 'php://output', 'w' );
		if ( ! $out ) {
			wp_die( 'Cannot open output stream' );
		}

		fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		fputcsv( $out, $headers );

		foreach ( $rows as $row ) {
			fputcsv( $out, $row );
		}

		fclose( $out );
		exit;
	}

	public function handle_export_contacts() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}
		if ( ! isset( $_POST['export_crm_contacts_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['export_crm_contacts_nonce'], 'export_crm_contacts_nonce' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$posts = get_posts( [
			'post_type'      => 'crm_contact',
			'post_status'    => 'any',
			'posts_per_page' => -1,
		] );

		$headers = [
			'ID',
			'Name',
			'First Name',
			'Last Name',
			'Email',
			'Phone',
			'Company ID',
			'Company Name',
			'Created',
		];

		$rows = [];
		foreach ( $posts as $post ) {
			$company_id   = (int) get_post_meta( $post->ID, 'crm_company_id', true );
			$company_name = '';
			if ( $company_id ) {
				$comp = get_post( $company_id );
				if ( $comp ) {
					$company_name = $comp->post_title;
				}
			}

			$rows[] = [
				$post->ID,
				$post->post_title,
				get_post_meta( $post->ID, 'crm_first_name', true ),
				get_post_meta( $post->ID, 'crm_last_name', true ),
				get_post_meta( $post->ID, 'crm_email', true ),
				get_post_meta( $post->ID, 'crm_phone', true ),
				$company_id,
				$company_name,
				$post->post_date,
			];
		}

		$this->output_csv( 'crm-contacts.csv', $headers, $rows );
	}

	public function handle_export_companies() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}
		if ( ! isset( $_POST['export_crm_companies_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['export_crm_companies_nonce'], 'export_crm_companies_nonce' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$posts = get_posts( [
			'post_type'      => 'crm_company',
			'post_status'    => 'any',
			'posts_per_page' => -1,
		] );

		$headers = [
			'ID',
			'Company Name',
			'Website',
			'Industry',
			'Created',
		];

		$rows = [];
		foreach ( $posts as $post ) {
			$rows[] = [
				$post->ID,
				$post->post_title,
				get_post_meta( $post->ID, 'crm_website', true ),
				get_post_meta( $post->ID, 'crm_industry', true ),
				$post->post_date,
			];
		}

		$this->output_csv( 'crm-companies.csv', $headers, $rows );
	}

	public function handle_export_deals() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}
		if ( ! isset( $_POST['export_crm_deals_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['export_crm_deals_nonce'], 'export_crm_deals_nonce' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$posts = get_posts( [
			'post_type'      => 'crm_deal',
			'post_status'    => 'any',
			'posts_per_page' => -1,
		] );

		$headers = [
			'ID',
			'Deal Title',
			'Value',
			'Stage',
			'Contact ID',
			'Contact Name',
			'Company ID',
			'Company Name',
			'Service Type',
			'Notes',
			'Created',
		];

		$rows = [];
		foreach ( $posts as $post ) {
			$contact_id   = (int) get_post_meta( $post->ID, 'crm_contact_id', true );
			$contact_name = '';
			if ( $contact_id ) {
				$c = get_post( $contact_id );
				if ( $c ) {
					$contact_name = $c->post_title;
				}
			}

			$company_id   = (int) get_post_meta( $post->ID, 'crm_company_id', true );
			$company_name = '';
			if ( $company_id ) {
				$comp = get_post( $company_id );
				if ( $comp ) {
					$company_name = $comp->post_title;
				}
			}

			$value = (float) get_post_meta( $post->ID, 'crm_value', true );

			$rows[] = [
				$post->ID,
				$post->post_title,
				$value,
				get_post_meta( $post->ID, 'crm_stage', true ),
				$contact_id,
				$contact_name,
				$company_id,
				$company_name,
				get_post_meta( $post->ID, 'crm_service_type', true ),
				get_post_meta( $post->ID, 'crm_notes', true ),
				$post->post_date,
			];
		}

		$this->output_csv( 'crm-deals.csv', $headers, $rows );
	}

	public function handle_export_activities() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}
		if ( ! isset( $_POST['export_crm_activities_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['export_crm_activities_nonce'], 'export_crm_activities_nonce' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$posts = get_posts( [
			'post_type'      => 'crm_activity',
			'post_status'    => 'any',
			'posts_per_page' => -1,
		] );

		$headers = [
			'ID',
			'Title',
			'Content',
			'Type',
			'Related Contact ID',
			'Created',
		];

		$rows = [];
		foreach ( $posts as $post ) {
			$rows[] = [
				$post->ID,
				$post->post_title,
				$post->post_content,
				get_post_meta( $post->ID, 'crm_activity_type', true ),
				get_post_meta( $post->ID, 'crm_related_contact_id', true ),
				$post->post_date,
			];
		}

		$this->output_csv( 'crm-activities.csv', $headers, $rows );
	}
}
