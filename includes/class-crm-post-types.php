<?php
/**
 * CRM post types registration.
 *
 * @package Simple_CRM
 * @since 0.1.0
 */

namespace Simple_CRM;

defined( 'ABSPATH' ) || exit;

class Class_CRM_Post_Types {

	public function __construct() {
		add_action( 'init', [ $this, 'register_post_types' ] );
	}

	public function register_post_types() {
		$this->register_post_type( 'crm_contact', __( 'Contacts', 'simple-crm' ), __( 'Contact', 'simple-crm' ) );
		$this->register_post_type( 'crm_company', __( 'Companies', 'simple-crm' ), __( 'Company', 'simple-crm' ) );
		$this->register_post_type( 'crm_deal', __( 'Deals', 'simple-crm' ), __( 'Deal', 'simple-crm' ) );
		$this->register_post_type( 'crm_activity', __( 'Activities', 'simple-crm' ), __( 'Activity', 'simple-crm' ) );

		$this->register_meta();
	}

	private function register_post_type( $post_type, $plural, $singular ) {
		$labels = [
			'name'          => $plural,
			'singular_name' => $singular,
			'menu_name'     => $plural,
			'all_items'     => sprintf( __( 'All %s', 'simple-crm' ), $plural ),
			'add_new'       => __( 'Add New', 'simple-crm' ),
			'add_new_item'  => sprintf( __( 'Add New %s', 'simple-crm' ), $singular ),
			'edit_item'     => sprintf( __( 'Edit %s', 'simple-crm' ), $singular ),
			'view_item'     => sprintf( __( 'View %s', 'simple-crm' ), $singular ),
		];

		register_post_type(
			$post_type,
			[
				'labels'          => $labels,
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'simple-crm',
				'show_in_rest'    => true,
				'supports'        => [ 'title', 'editor', 'custom-fields' ],
				'capability_type' => 'post',
				'map_meta_cap'    => true,
				'rewrite'         => false,
			]
		);
	}

	private function register_meta() {
		$this->register_meta_field( 'crm_contact', 'crm_email', 'string', 'sanitize_email' );
		$this->register_meta_field( 'crm_contact', 'crm_phone', 'string', 'sanitize_text_field' );
		$this->register_meta_field( 'crm_contact', 'crm_company_id', 'integer', 'absint' );
		$this->register_meta_field( 'crm_contact', 'crm_first_name', 'string', 'sanitize_text_field' );
		$this->register_meta_field( 'crm_contact', 'crm_last_name', 'string', 'sanitize_text_field' );
		$this->register_meta_field( 'crm_company', 'crm_website', 'string', 'esc_url_raw' );
		$this->register_meta_field( 'crm_company', 'crm_industry', 'string', 'sanitize_text_field' );
		$this->register_meta_field( 'crm_deal', 'crm_value', 'number', 'floatval' );
		$this->register_meta_field( 'crm_deal', 'crm_stage', 'string', 'sanitize_text_field' );
		$this->register_meta_field( 'crm_deal', 'crm_contact_id', 'integer', 'absint' );
		$this->register_meta_field( 'crm_deal', 'crm_company_id', 'integer', 'absint' );
		$this->register_meta_field( 'crm_deal', 'crm_service_type', 'string', 'sanitize_text_field' );
		$this->register_meta_field( 'crm_deal', 'crm_notes', 'string', 'sanitize_textarea_field' );
		$this->register_meta_field( 'crm_activity', 'crm_activity_type', 'string', 'sanitize_text_field' );
		$this->register_meta_field( 'crm_activity', 'crm_related_contact_id', 'integer', 'absint' );
	}

	private function register_meta_field( $post_type, $meta_key, $type, $sanitize_callback ) {
		register_post_meta(
			$post_type,
			$meta_key,
			[
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => $type,
				'sanitize_callback' => $sanitize_callback,
			]
		);
	}
}
