<?php
namespace Simple_CRM;

defined( 'ABSPATH' ) || exit;

class Class_CRM_Post_Types {

	public function __construct() {
		add_action( 'init', [ $this, 'register_post_types' ] );
	}

	public function register_post_types() {
		$this->register_contact();
		$this->register_company();
		$this->register_deal();
		$this->register_activity();
	}

	private function register_contact() {
		$labels = [
			'name'          => 'Contacts',
			'singular_name' => 'Contact',
			'menu_name'     => 'Contacts',
			'all_items'     => 'All Contacts',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add New Contact',
			'edit_item'     => 'Edit Contact',
			'view_item'     => 'View Contact',
		];

		$args = [
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => 'simple-crm',
			'show_in_rest'  => true,
			'supports'      => [ 'title', 'editor', 'custom-fields' ],
			'capability_type' => 'post',
			'map_meta_cap'  => true,
			'rewrite'       => false,
		];

		register_post_type( 'crm_contact', $args );

		register_post_meta( 'crm_contact', 'crm_email', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'sanitize_callback' => 'sanitize_email',
		] );

		register_post_meta( 'crm_contact', 'crm_phone', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		register_post_meta( 'crm_contact', 'crm_company_id', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'integer',
			'sanitize_callback' => 'absint',
		] );

		register_post_meta( 'crm_contact', 'crm_first_name', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		register_post_meta( 'crm_contact', 'crm_last_name', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		] );
	}

	private function register_company() {
		$labels = [
			'name'          => 'Companies',
			'singular_name' => 'Company',
			'menu_name'     => 'Companies',
			'all_items'     => 'All Companies',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add New Company',
			'edit_item'     => 'Edit Company',
		];

		$args = [
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => 'simple-crm',
			'show_in_rest'  => true,
			'supports'      => [ 'title', 'editor', 'custom-fields' ],
			'capability_type' => 'post',
			'map_meta_cap'  => true,
			'rewrite'       => false,
		];

		register_post_type( 'crm_company', $args );

		register_post_meta( 'crm_company', 'crm_website', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'sanitize_callback' => 'esc_url_raw',
		] );

		register_post_meta( 'crm_company', 'crm_industry', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		] );
	}

	private function register_deal() {
		$labels = [
			'name'          => 'Deals',
			'singular_name' => 'Deal',
			'menu_name'     => 'Deals',
			'all_items'     => 'All Deals',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add New Deal',
			'edit_item'     => 'Edit Deal',
		];

		$args = [
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => 'simple-crm',
			'show_in_rest'  => true,
			'supports'      => [ 'title', 'editor', 'custom-fields' ],
			'capability_type' => 'post',
			'map_meta_cap'  => true,
			'rewrite'       => false,
		];

		register_post_type( 'crm_deal', $args );

		register_post_meta( 'crm_deal', 'crm_value', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'number',
			'sanitize_callback' => 'floatval',
		] );

		register_post_meta( 'crm_deal', 'crm_stage', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		register_post_meta( 'crm_deal', 'crm_contact_id', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'integer',
			'sanitize_callback' => 'absint',
		] );

		register_post_meta( 'crm_deal', 'crm_company_id', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'integer',
			'sanitize_callback' => 'absint',
		] );

		register_post_meta( 'crm_deal', 'crm_service_type', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		register_post_meta( 'crm_deal', 'crm_notes', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
		] );
	}

	private function register_activity() {
		$labels = [
			'name'          => 'Activities',
			'singular_name' => 'Activity',
			'menu_name'     => 'Activities',
			'all_items'     => 'All Activities',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add New Activity',
			'edit_item'     => 'Edit Activity',
		];

		$args = [
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => 'simple-crm',
			'show_in_rest'  => true,
			'supports'      => [ 'title', 'editor', 'custom-fields' ],
			'capability_type' => 'post',
			'map_meta_cap'  => true,
			'rewrite'       => false,
		];

		register_post_type( 'crm_activity', $args );

		register_post_meta( 'crm_activity', 'crm_activity_type', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		register_post_meta( 'crm_activity', 'crm_related_contact_id', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'integer',
			'sanitize_callback' => 'absint',
		] );
	}
}
