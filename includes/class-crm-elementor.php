<?php
namespace Simple_CRM;

defined( 'ABSPATH' ) || exit;

class Class_CRM_Elementor {

	public function __construct() {
		add_action( 'elementor_pro/forms/new_record', [ $this, 'handle_form_submission' ], 10, 2 );
	}

	public function handle_form_submission( $record, $handler ) {
		$form_settings = $record->get_form_settings();
		$form_name     = $form_settings['form_name'] ?? '';

		switch ( $form_name ) {
			case 'Contact Form':
				$this->handle_contact_form( $record );
				break;

			case 'Deal Request Form':
				$this->handle_deal_request_form( $record );
				break;

			default:
				return;
		}
	}

	private function handle_contact_form( $record ) {
		$full_name = $this->get_field_value( $record, 'full_name' );
		$email     = $this->get_field_value( $record, 'email' );
		$phone     = $this->get_field_value( $record, 'phone' );
		$company   = $this->get_field_value( $record, 'company' );

		if ( ! $email ) {
			return;
		}

		$parts       = preg_split( '/\s+/', trim( $full_name ), 2 );
		$first_name  = $parts[0] ?? '';
		$last_name   = $parts[1] ?? '';

		$company_id = $company ? $this->get_or_create_company( $company ) : 0;

		$contact_id = $this->create_or_update_contact( [
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'email'      => $email,
			'phone'      => $phone,
			'company_id' => $company_id,
		] );

		$this->log_activity( $contact_id, 'Contact form submitted', 'contact_form' );
	}

	private function handle_deal_request_form( $record ) {
		$first_name   = $this->get_field_value( $record, 'first_name' );
		$last_name    = $this->get_field_value( $record, 'last_name' );
		$email        = $this->get_field_value( $record, 'email' );
		$phone        = $this->get_field_value( $record, 'phone' );
		$company      = $this->get_field_value( $record, 'company' );
		$budget       = $this->get_field_value( $record, 'budget' );
		$service_type = $this->get_field_value( $record, 'service_type' );
		$notes        = $this->get_field_value( $record, 'notes' );

		if ( ! $email ) {
			return;
		}

		$company_id = $company ? $this->get_or_create_company( $company ) : 0;

		$contact_id = $this->create_or_update_contact( [
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'email'      => $email,
			'phone'      => $phone,
			'company_id' => $company_id,
		] );

		$deal_title = trim( $first_name . ' ' . $last_name );
		if ( $service_type ) {
			$deal_title .= ' - ' . $service_type;
		} elseif ( $company ) {
			$deal_title .= ' - ' . $company;
		}

		// Map stage from form if present
		$stage_raw = $this->get_field_value( $record, 'stage' );
		$stage_map = [
			'Lead'        => 'lead',
			'Qualified'   => 'qualified',
			'Proposal'    => 'proposal',
			'Won'         => 'won',
			'Lost'        => 'lost',
		];
		$stage = $stage_map[ $stage_raw ] ?? 'lead';

		$deal_id = $this->create_deal( [
			'title'        => $deal_title ?: 'Deal',
			'contact_id'   => $contact_id,
			'company_id'   => $company_id,
			'value'        => floatval( $budget ),
			'stage'        => $stage,
		] );

		if ( $deal_id ) {
			update_post_meta( $deal_id, 'crm_service_type', sanitize_text_field( $service_type ) );
			update_post_meta( $deal_id, 'crm_notes', sanitize_textarea_field( $notes ) );
		}

		$this->log_activity( $contact_id, 'Deal request form submitted', 'deal_request_form' );
	}

	private function get_field_value( $record, $field_id ) {
		$field = $record->get_field( [ 'id' => $field_id ] );
		return $field['value'] ?? '';
	}

	private function get_or_create_company( $name ) {
		$name = trim( $name );
		if ( ! $name ) {
			return 0;
		}

		$existing = get_posts( [
			'post_type'      => 'crm_company',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'title'          => $name,
			'fields'         => 'ids',
		] );

		if ( ! empty( $existing ) ) {
			return (int) $existing[0];
		}

		$post_id = wp_insert_post( [
			'post_type'   => 'crm_company',
			'post_title'  => $name,
			'post_status' => 'publish',
		] );

		return is_wp_error( $post_id ) ? 0 : $post_id;
	}

	private function create_or_update_contact( array $data ) {
		$email = sanitize_email( $data['email'] ?? '' );
		if ( ! $email ) {
			return 0;
		}

		$existing = get_posts( [
			'post_type'      => 'crm_contact',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'meta_key'       => 'crm_email',
			'meta_value'     => $email,
			'fields'         => 'ids',
		] );

		if ( ! empty( $existing ) ) {
			$contact_id = (int) $existing[0];
			$first_name = $data['first_name'] ?? '';
			$last_name  = $data['last_name'] ?? '';
			$name       = trim( $first_name . ' ' . $last_name );

			if ( $name ) {
				wp_update_post( [
					'ID'         => $contact_id,
					'post_title' => $name,
				] );
			}

			update_post_meta( $contact_id, 'crm_first_name', sanitize_text_field( $first_name ) );
			update_post_meta( $contact_id, 'crm_last_name', sanitize_text_field( $last_name ) );
			update_post_meta( $contact_id, 'crm_email', $email );
			update_post_meta( $contact_id, 'crm_phone', sanitize_text_field( $data['phone'] ?? '' ) );
			update_post_meta( $contact_id, 'crm_company_id', absint( $data['company_id'] ?? 0 ) );

			do_action( 'simple_crm_contact_saved', $contact_id, $data );

			return $contact_id;
		}

		$first_name = $data['first_name'] ?? '';
		$last_name  = $data['last_name'] ?? '';
		$name       = trim( $first_name . ' ' . $last_name ) ?: $email;

		$post_id = wp_insert_post( [
			'post_type'   => 'crm_contact',
			'post_title'  => $name,
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $post_id ) ) {
			return 0;
		}

		update_post_meta( $post_id, 'crm_first_name', sanitize_text_field( $first_name ) );
		update_post_meta( $post_id, 'crm_last_name', sanitize_text_field( $last_name ) );
		update_post_meta( $post_id, 'crm_email', $email );
		update_post_meta( $post_id, 'crm_phone', sanitize_text_field( $data['phone'] ?? '' ) );
		update_post_meta( $post_id, 'crm_company_id', absint( $data['company_id'] ?? 0 ) );

		do_action( 'simple_crm_contact_saved', $post_id, $data );

		return $post_id;
	}

	private function create_deal( array $data ) {
		$post_id = wp_insert_post( [
			'post_type'   => 'crm_deal',
			'post_title'  => $data['title'] ?? 'Deal',
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $post_id ) ) {
			return 0;
		}

		update_post_meta( $post_id, 'crm_value', floatval( $data['value'] ?? 0 ) );
		update_post_meta( $post_id, 'crm_stage', sanitize_text_field( $data['stage'] ?? 'lead' ) );
		update_post_meta( $post_id, 'crm_contact_id', absint( $data['contact_id'] ?? 0 ) );
		update_post_meta( $post_id, 'crm_company_id', absint( $data['company_id'] ?? 0 ) );

		do_action( 'simple_crm_deal_saved', $post_id, $data );

		return $post_id;
	}

	private function log_activity( $contact_id, $message, $type = 'general' ) {
		if ( ! $contact_id ) {
			return;
		}

		$post_id = wp_insert_post( [
			'post_type'   => 'crm_activity',
			'post_title'  => $type . ' - ' . current_time( 'mysql' ),
			'post_content'=> $message,
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, 'crm_activity_type', sanitize_text_field( $type ) );
		update_post_meta( $post_id, 'crm_related_contact_id', absint( $contact_id ) );
	}
}
