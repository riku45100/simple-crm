<?php
namespace Simple_CRM;

defined( 'ABSPATH' ) || exit;

class Class_CRM_Emails {

	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_email_meta_box' ] );
		add_action( 'admin_post_send_crm_email', [ $this, 'handle_send_email' ] );
	}

	public function add_email_meta_box() {
		add_meta_box(
			'crm_send_email_box',
			'Send Email',
			[ $this, 'render_email_box' ],
			'crm_contact',
			'side',
			'high'
		);
	}

	public function render_email_box( $post ) {
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			echo 'You do not have permission to send emails.';
			return;
		}

		wp_nonce_field( 'crm_send_email_nonce', 'crm_send_email_nonce' );

		$email = get_post_meta( $post->ID, 'crm_email', true );
		$name  = $post->post_title;
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="send_crm_email">
			<input type="hidden" name="contact_id" value="<?php echo (int) $post->ID; ?>">
			<?php wp_nonce_field( 'crm_send_email_nonce', 'crm_send_email_nonce' ); ?>

			<p>
				<label for="crm_email_to">To</label><br>
				<input type="email" name="crm_email_to" id="crm_email_to"
				       value="<?php echo esc_attr( $email ); ?>" class="regular-text" required>
			</p>
			<p>
				<label for="crm_email_subject">Subject</label><br>
				<input type="text" name="crm_email_subject" id="crm_email_subject"
				       class="regular-text" required>
			</p>
			<p>
				<label for="crm_email_message">Message</label><br>
				<textarea name="crm_email_message" id="crm_email_message"
				          class="large-text" rows="6" required></textarea>
			</p>
			<p>
				<?php submit_button( 'Send Email', 'primary', 'crm_send_email_submit' ); ?>
			</p>
			<p class="description">
				Email will be sent using WordPress' default mail function. Configure SMTP via a plugin or your server for better deliverability.
			</p>
		</form>
		<?php
	}

	public function handle_send_email() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}

		if ( ! isset( $_POST['crm_send_email_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['crm_send_email_nonce'], 'crm_send_email_nonce' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$contact_id = absint( $_POST['contact_id'] ?? 0 );
		$to         = sanitize_email( $_POST['crm_email_to'] ?? '' );
		$subject    = sanitize_text_field( $_POST['crm_email_subject'] ?? '' );
		$message    = sanitize_textarea_field( $_POST['crm_email_message'] ?? '' );

		if ( ! $contact_id || ! $to || ! $subject || ! $message ) {
			wp_die( 'Missing required fields' );
		}

		$headers = [
			'Content-Type: text/plain; charset=UTF-8',
		];

		$sent = wp_mail( $to, $subject, $message, $headers );

		if ( $sent ) {
			$post_id = wp_insert_post( [
				'post_type'   => 'crm_activity',
				'post_title'  => 'Email sent - ' . current_time( 'mysql' ),
				'post_content'=> "To: {$to}\nSubject: {$subject}\n\n{$message}",
				'post_status' => 'publish',
			] );

			if ( ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, 'crm_activity_type', 'email_sent' );
				update_post_meta( $post_id, 'crm_related_contact_id', $contact_id );
			}

			wp_safe_redirect( add_query_arg( [
				'post'      => $contact_id,
				'action'    => 'edit',
				'email_sent'=> '1',
			], admin_url( 'post.php' ) ) );
			exit;
		} else {
			wp_die( 'Failed to send email. Check your server/mail configuration.' );
		}
	}
}
