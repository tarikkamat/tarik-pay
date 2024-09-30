<?php

namespace Iyzico\IyzipayWoocommerce\Rest;

use Iyzico\IyzipayWoocommerce\Common\Helpers\AdminHelper;

class RestAPI {
	private $adminHelper;

	public function __construct() {
		$this->adminHelper = new AdminHelper();
	}

	public function localizeScript(): void {
		wp_localize_script(
			'AdminScript',  // This should match the handle used in wp_enqueue_script
			'iyzicoRestApi',
			[
				'SettingsDashboardWidgetsUrl' => esc_url_raw( rest_url( 'iyzico/v1/SettingsDashboardWidgetsRoute' ) ),
				'SettingsDashboardChartsUrl'  => esc_url_raw( rest_url( 'iyzico/v1/SettingsDashboardChartsRoute' ) ),
				'GetOrdersUrl'                => esc_url_raw( rest_url( 'iyzico/v1/getOrdersRoute' ) ),
				'SaveSettingsUrl'             => esc_url_raw( rest_url( 'iyzico/v1/saveSettingsRoute' ) ),
				'SettingsUrl'                 => esc_url_raw( rest_url( 'iyzico/v1/getSettingsRoute' ) ),
				'LocalizationsUrl'            => esc_url_raw( rest_url( 'iyzico/v1/GetLocalizationsRoute' ) ),
				'nonce'                       => wp_create_nonce( 'wp_rest' )
			]
		);
	}

	public function addRestRoutes(): void {
		register_rest_route( 'iyzico/v1', '/SettingsDashboardWidgetsRoute', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getSettingsDashboardWidgetsMethod' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			}
		] );

		register_rest_route( 'iyzico/v1', '/SettingsDashboardChartsRoute', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getSettingsDashboardChartsMethod' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			}
		] );

		register_rest_route( 'iyzico/v1', '/getOrdersRoute', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getOrdersMethod' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			}
		] );

		register_rest_route( 'iyzico/v1', '/saveSettingsRoute', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'saveSettingsMethod' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			}
		] );


		register_rest_route( 'iyzico/v1', '/getSettingsRoute', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getSettingsMethod' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			}
		] );

		register_rest_route( 'iyzico/v1', '/GetLocalizationsRoute', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'getLocalizationsMethod' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			}
		] );
	}

	public function getSettingsDashboardWidgetsMethod() {
		return rest_ensure_response( $this->adminHelper->getSettingsDashboardWidgets() );
	}

	public function getSettingsDashboardChartsMethod() {
		return rest_ensure_response( $this->adminHelper->getSettingsDashboardCharts() );
	}

	public function getSettingsMethod() {
		return rest_ensure_response( $this->adminHelper->getSettings() );
	}

	public function saveSettingsMethod( $request ) {
		return rest_ensure_response( $this->adminHelper->saveSettings( $request ) );
	}

	public function getOrdersMethod( $request ) {
		return rest_ensure_response( $this->adminHelper->getOrders( $request ) );
	}


	public function getLocalizationsMethod() {
		$locale    = get_locale();
		$file_path = PLUGIN_DIR_PATH . 'i18n/languages/localizations.json';
		$data      = file_get_contents( $file_path );
		$json_data = json_decode( $data, true );

		if ( isset( $json_data[ $locale ] ) ) {
			return rest_ensure_response( $json_data[ $locale ] );
		} else {
			return rest_ensure_response( $json_data['tr_TR'] );
		}

	}


}