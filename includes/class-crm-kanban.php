<?php
namespace Simple_CRM;

defined( 'ABSPATH' ) || exit;

class Class_CRM_Kanban {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_kanban_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_kanban_assets' ] );
		add_action( 'wp_ajax_simple_crm_update_deal_stage', [ $this, 'ajax_update_deal_stage' ] );
		add_action( 'admin_post_simple_crm_new_deal', [ $this, 'handle_new_deal' ] );
	}

	public function add_kanban_page() {
		add_submenu_page(
			'simple-crm',
			'Deals Kanban',
			'Deals Kanban',
			'edit_posts',
			'simple-crm-kanban',
			[ $this, 'render_kanban_page' ]
		);
	}

	public function render_kanban_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}

		$stages = simple_crm_get_deal_stages();
		?>
		<div class="wrap">
			<h1>Deals Kanban</h1>
			<p>Drag deals between stages. Changes are saved automatically.</p>

			<h2>New Deal</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
			      style="max-width:600px; margin-bottom:20px;">
				<?php wp_nonce_field( 'simple_crm_new_deal_nonce', 'simple_crm_new_deal_nonce' ); ?>
				<input type="hidden" name="action" value="simple_crm_new_deal">

				<p>
					<label for="deal_title">Deal Title</label><br>
					<input type="text" name="deal_title" id="deal_title" required class="regular-text">
				</p>
				<p>
					<label for="deal_value">Value (€)</label><br>
					<input type="number" step="0.01" name="deal_value" id="deal_value"
					       class="regular-text" value="0">
				</p>
				<p>
					<label for="deal_stage">Stage</label><br>
					<select name="deal_stage" id="deal_stage">
						<?php foreach ( $stages as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, 'lead' ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<label for="deal_contact">Contact (optional)</label><br>
					<select name="deal_contact" id="deal_contact">
						<option value="">— None —</option>
						<?php
						$contacts = get_posts( [
							'post_type'      => 'crm_contact',
							'post_status'    => 'any',
							'posts_per_page' => -1,
							'fields'         => 'ids',
						] );
						foreach ( $contacts as $cid ) :
							$title = get_the_title( $cid );
							?>
							<option value="<?php echo (int) $cid; ?>">
								<?php echo esc_html( $title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<label for="deal_company">Company (optional)</label><br>
					<select name="deal_company" id="deal_company">
						<option value="">— None —</option>
						<?php
						$companies = get_posts( [
							'post_type'      => 'crm_company',
							'post_status'    => 'any',
							'posts_per_page' => -1,
							'fields'         => 'ids',
						] );
						foreach ( $companies as $cid ) :
							$title = get_the_title( $cid );
							?>
							<option value="<?php echo (int) $cid; ?>">
								<?php echo esc_html( $title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<?php submit_button( 'Create Deal', 'primary', 'submit_new_deal' ); ?>
				</p>
			</form>

			<div id="simple-crm-kanban-board" style="display:flex; gap:12px; overflow-x:auto; padding-bottom:12px;">
				<?php foreach ( $stages as $stage_key => $stage_label ) : ?>
					<div class="simple-crm-kanban-column" data-stage="<?php echo esc_attr( $stage_key ); ?>"
					     style="min-width:260px; max-width:260px; background:#f5f5f5; border-radius:6px; padding:8px;">
						<h3 style="margin:4px 0 8px; font-size:14px;">
							<?php echo esc_html( $stage_label ); ?>
							<span class="simple-crm-stage-count" style="font-weight:normal; color:#666;">
								<?php echo $this->count_deals_by_stage( $stage_key ); ?>
							</span>
						</h3>
						<div class="simple-crm-kanban-cards" data-stage="<?php echo esc_attr( $stage_key ); ?>"
						     style="min-height:200px;">
							<?php $this->render_deal_cards( $stage_key ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	private function count_deals_by_stage( $stage ) {
		$query = new \WP_Query( [
			'post_type'      => 'crm_deal',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => [
				[
					'key'   => 'crm_stage',
					'value' => $stage,
				],
			],
		] );
		return (int) $query->found_posts;
	}

	private function render_deal_cards( $stage ) {
		$deals = get_posts( [
			'post_type'      => 'crm_deal',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'meta_query'     => [
				[
					'key'   => 'crm_stage',
					'value' => $stage,
				],
			],
		] );

		foreach ( $deals as $deal ) {
			$value   = (float) get_post_meta( $deal->ID, 'crm_value', true );
			$contact_id = (int) get_post_meta( $deal->ID, 'crm_contact_id', true );
			$contact_name = '';
			if ( $contact_id ) {
				$c = get_post( $contact_id );
				if ( $c ) {
					$contact_name = $c->post_title;
				}
			}

			$company_id = (int) get_post_meta( $deal->ID, 'crm_company_id', true );
			$company_name = '';
			if ( $company_id ) {
				$comp = get_post( $company_id );
				if ( $comp ) {
					$company_name = $comp->post_title;
				}
			}

			$edit_url = get_edit_post_link( $deal->ID, false );
			?>
			<div class="simple-crm-kanban-card"
			     data-deal-id="<?php echo (int) $deal->ID; ?>"
			     draggable="true"
			     style="background:#fff; border:1px solid #ddd; border-radius:6px; padding:8px; margin-bottom:8px; cursor:move;">
				<div style="font-weight:600; font-size:13px; margin-bottom:4px;">
					<a href="<?php echo esc_url( $edit_url ); ?>" target="_blank" style="text-decoration:none; color:#2271bb;">
						<?php echo esc_html( $deal->post_title ); ?>
					</a>
				</div>
				<div style="font-size:12px; color:#444;">
					<?php if ( $value ) : ?>
						<div><?php echo esc_html( number_format( $value, 2, ',', ' ' ) ) . ' €'; ?></div>
					<?php endif; ?>
					<?php if ( $contact_name ) : ?>
						<div><?php echo esc_html( $contact_name ); ?></div>
					<?php endif; ?>
					<?php if ( $company_name ) : ?>
						<div><?php echo esc_html( $company_name ); ?></div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
	}

	public function enqueue_kanban_assets( $hook ) {
		if ( $hook !== 'toplevel_page_simple-crm-kanban' ) {
			return;
		}

		wp_enqueue_script(
			'simple-crm-kanban',
			plugin_dir_url( __FILE__ ) . '../assets/js/kanban.js',
			[],
			SIMPLE_CRM_VERSION,
			true
		);

		wp_localize_script( 'simple-crm-kanban', 'simpleCrmKanban', [
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'simple_crm_kanban_nonce' ),
			'action'    => 'simple_crm_update_deal_stage',
		] );
	}

	public function ajax_update_deal_stage() {
		check_ajax_referer( 'simple_crm_kanban_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ] );
		}

		$deal_id = absint( $_POST['deal_id'] ?? 0 );
		$stage   = sanitize_text_field( $_POST['stage'] ?? '' );

		if ( ! $deal_id || ! $stage ) {
			wp_send_json_error( [ 'message' => 'Invalid data' ] );
		}

		$deal = get_post( $deal_id );
		if ( ! $deal || $deal->post_type !== 'crm_deal' ) {
			wp_send_json_error( [ 'message' => 'Deal not found' ] );
		}

		$valid_stages = array_keys( simple_crm_get_deal_stages() );
		if ( ! in_array( $stage, $valid_stages, true ) ) {
			wp_send_json_error( [ 'message' => 'Invalid stage' ] );
		}

		update_post_meta( $deal_id, 'crm_stage', $stage );

		wp_send_json_success( [ 'message' => 'Stage updated' ] );
	}

	public function handle_new_deal() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}

		if ( ! isset( $_POST['simple_crm_new_deal_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['simple_crm_new_deal_nonce'], 'simple_crm_new_deal_nonce' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$title       = sanitize_text_field( $_POST['deal_title'] ?? '' );
		$value       = floatval( $_POST['deal_value'] ?? 0 );
		$stage       = sanitize_text_field( $_POST['deal_stage'] ?? 'lead' );
		$contact_id  = absint( $_POST['deal_contact'] ?? 0 );
		$company_id  = absint( $_POST['deal_company'] ?? 0 );

		if ( ! $title ) {
			wp_die( 'Title is required' );
		}

		$valid_stages = array_keys( simple_crm_get_deal_stages() );
		if ( ! in_array( $stage, $valid_stages, true ) ) {
			$stage = 'lead';
		}

		$deal_id = wp_insert_post( [
			'post_type'   => 'crm_deal',
			'post_title'  => $title,
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $deal_id ) ) {
			wp_die( 'Could not create deal' );
		}

		update_post_meta( $deal_id, 'crm_value', $value );
		update_post_meta( $deal_id, 'crm_stage', $stage );
		update_post_meta( $deal_id, 'crm_contact_id', $contact_id );
		update_post_meta( $deal_id, 'crm_company_id', $company_id );

		// Trigger Lark sync
		do_action( 'simple_crm_deal_saved', $deal_id, [
			'title'        => $title,
			'value'        => $value,
			'stage'        => $stage,
			'contact_name' => $contact_id ? get_the_title( $contact_id ) : '',
			'company'      => $company_id ? get_the_title( $company_id ) : '',
		] );

		wp_safe_redirect( admin_url( 'admin.php?page=simple-crm-kanban&deal_created=1' ) );
		exit;
	}
}
