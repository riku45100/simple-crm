<?php
namespace Simple_CRM;

defined( 'ABSPATH' ) || exit;

class Class_CRM_Lark {

	private $app_token;
	private $api_base = 'https://open.larksuite.com/open-apis';

	public function __construct() {
		$this->app_token = get_option( 'simple_crm_lark_base_token' );

		add_action( 'simple_crm_contact_saved', [ $this, 'sync_contact_to_lark' ], 10, 2 );
		add_action( 'simple_crm_deal_saved', [ $this, 'sync_deal_to_lark' ], 10, 2 );
	}

	private function get_access_token() {
		$cache_key = 'simple_crm_lark_tenant_token';
		$token     = get_transient( $cache_key );
		if ( $token ) {
			return $token;
		}

		$app_id     = get_option( 'simple_crm_lark_app_id' );
		$app_secret = get_option( 'simple_crm_lark_app_secret' );

		if ( ! $app_id || ! $app_secret ) {
			return false;
		}

		$response = wp_remote_post( $this->api_base . '/auth/v3/tenant_access_token/internal', [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode( [
				'app_id'     => $app_id,
				'app_secret' => $app_secret,
			] ),
		] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$token = $body['tenant_access_token'] ?? false;

		if ( ! $token ) {
			return false;
		}

		$expires_in = (int) ( $body['expire'] ?? 3600 );
		set_transient( $cache_key, $token, $expires_in - 60 );

		return $token;
	}

	public function sync_contact_to_lark( $contact_id, $data ) {
		$token = $this->get_access_token();
		if ( ! $token || ! $this->app_token ) {
			return;
		}

		$table_id = get_option( 'simple_crm_lark_contacts_table_id' );
		if ( ! $table_id ) {
			return;
		}

		$fields = [
			'Name'          => trim( ( $data['first_name'] ?? '' ) . ' ' . ( $data['last_name'] ?? '' ) ),
			'First Name'    => $data['first_name'] ?? '',
			'Last Name'     => $data['last_name'] ?? '',
			'Email'         => $data['email'] ?? '',
			'Phone'         => $data['phone'] ?? '',
			'Company'       => $data['company'] ?? '',
			'WP Contact ID' => (int) $contact_id,
		];

		$existing_record_id = $this->find_contact_record_by_wp_id( $contact_id, $table_id, $token );

		if ( $existing_record_id ) {
			wp_remote_post(
				$this->api_base . '/bitable/v1/apps/' . $this->app_token . '/tables/' . $table_id . '/records/' . $existing_record_id,
				[
					'method'  => 'PUT',
					'headers' => [
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $token,
					],
					'body'    => wp_json_encode( [ 'fields' => $fields ] ),
				]
			);
		} else {
			wp_remote_post(
				$this->api_base . '/bitable/v1/apps/' . $this->app_token . '/tables/' . $table_id . '/records',
				[
					'headers' => [
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $token,
					],
					'body'    => wp_json_encode( [ 'fields' => $fields ] ),
				]
			);
		}
	}

	public function sync_deal_to_lark( $deal_id, $data ) {
		$token = $this->get_access_token();
		if ( ! $token || ! $this->app_token ) {
			return;
		}

		$table_id = get_option( 'simple_crm_lark_deals_table_id' );
		if ( ! $table_id ) {
			return;
		}

		$fields = [
			'Deal Title'    => $data['title'] ?? 'Deal',
			'Value'         => floatval( $data['value'] ?? 0 ),
			'Stage'         => $data['stage'] ?? 'lead',
			'Contact'       => $data['contact_name'] ?? '',
			'Company'       => $data['company'] ?? '',
			'Service Type'  => $data['service_type'] ?? '',
			'Notes'         => $data['notes'] ?? '',
			'WP Deal ID'    => (int) $deal_id,
		];

		$existing_record_id = $this->find_deal_record_by_wp_id( $deal_id, $table_id, $token );

		if ( $existing_record_id ) {
			wp_remote_post(
				$this->api_base . '/bitable/v1/apps/' . $this->app_token . '/tables/' . $table_id . '/records/' . $existing_record_id,
				[
					'method'  => 'PUT',
					'headers' => [
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $token,
					],
					'body'    => wp_json_encode( [ 'fields' => $fields ] ),
				]
			);
		} else {
			wp_remote_post(
				$this->api_base . '/bitable/v1/apps/' . $this->app_token . '/tables/' . $table_id . '/records',
				[
					'headers' => [
						'Content-Type'  => 'application/json',
						'Authorization' => 'Bearer ' . $token,
					],
					'body'    => wp_json_encode( [ 'fields' => $fields ] ),
				]
			);
		}
	}

	private function find_contact_record_by_wp_id( $contact_id, $table_id, $token ) {
		return $this->find_record_by_field( $table_id, $token, 'WP Contact ID', (string) $contact_id );
	}

	private function find_deal_record_by_wp_id( $deal_id, $table_id, $token ) {
		return $this->find_record_by_field( $table_id, $token, 'WP Deal ID', (string) $deal_id );
	}

	private function find_record_by_field( $table_id, $token, $field_name, $value ) {
		$response = wp_remote_post(
			$this->api_base . '/bitable/v1/apps/' . $this->app_token . '/tables/' . $table_id . '/records/search',
			[
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $token,
				],
				'body'    => wp_json_encode( [
					'filter' => [
						'conjunction' => 'and',
						'conditions'  => [
							[
								'field_name' => $field_name,
								'operator'   => 'is',
								'value'      => $value,
							],
						],
					],
					'page_size' => 1,
				] ),
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$items = $body['data']['items'] ?? [];

		if ( empty( $items ) ) {
			return false;
		}

		return $items[0]['record_id'] ?? false;
	}
}
