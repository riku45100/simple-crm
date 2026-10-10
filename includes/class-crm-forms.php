<?php
/**
 * Basic front-end contact form shortcode.
 *
 * @package Simple_CRM
 * @since 0.1.0
 */

namespace Simple_CRM;

defined( 'ABSPATH' ) || exit;

class Class_CRM_Forms {

	public function __construct() {
		add_shortcode( 'crm_contact_form', [ $this, 'render_contact_form' ] );
		add_action( 'admin_post_submit_crm_contact', [ $this, 'handle_contact_form' ] );
		add_action( 'admin_post_nopriv_submit_crm_contact', [ $this, 'handle_contact_form' ] );
	}

	public function render_contact_form() {
		ob_start();
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'crm_contact_form_nonce', 'crm_contact_form_nonce' ); ?>
			<input type="hidden" name="action" value="submit_crm_contact">

			<p>
				<label>Name<br>
					<input type="text" name="crm_name" required class="regular-text">
				</label>
			</p>
			<p>
				<label>Email<br>
					<input type="email" name="crm_email" required class="regular-text">
				</label>
			</p>
			<p>
				<label>Phone<br>
					<input type="text" name="crm_phone" class="regular-text">
				</label>
			</p>
			<p>
				<button type="submit">Submit</button>
			</p>
		</form>
		<?php
		return ob_get_clean();
	}

	public function handle_contact_form() {
		if ( ! isset( $_POST['crm_contact_form_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['crm_contact_form_nonce'], 'crm_contact_form_nonce' ) ) {
			wp_die( 'Invalid form' );
		}

		$name  = sanitize_text_field( $_POST['crm_name'] ?? '' );
		$email = sanitize_email( $_POST['crm_email'] ?? '' );
		$phone = sanitize_text_field( $_POST['crm_phone'] ?? '' );

		if ( ! $name || ! $email ) {
			wp_die( 'Name and email are required' );
		}

		$post_id = wp_insert_post( [
			'post_type'   => 'crm_contact',
			'post_title'  => $name,
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $post_id ) ) {
			wp_die( 'Could not create contact' );
		}

		update_post_meta( $post_id, 'crm_email', $email );
		update_post_meta( $post_id, 'crm_phone', $phone );

		wp_safe_redirect( home_url( '/thank-you/' ) );
		exit;
	}
}
