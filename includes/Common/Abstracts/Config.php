<?php

namespace Iyzico\IyzipayWoocommerce\Common\Abstracts;

abstract class Config {
	public $optionsTableKey;

	public function getSettings() {
		$settings = get_option( $this->optionsTableKey, array() );
		$settings = is_array( $settings ) ? $settings : array();

		foreach ( $this->getDefaultSettings() as $key => $value ) {
			if ( false === array_key_exists( $key, $settings ) ) {
				$settings[ $key ] = $value;
			}
		}

		return $settings;
	}

	public function findByKey( string $key ) {
		$settings = $this->getSettings();

		return array_key_exists( $key, $settings ) ? $settings[ $key ] : false;
	}


	public function setSettings( mixed $options ) {
		update_option( $this->optionsTableKey, $options );

		if ( method_exists( $this, 'settings_updated' ) ) {
			call_user_func( array( $this, 'settings_updated' ) );
		}

		return true;
	}

	private function getDefaultSettings() {
		$defaultSettings = apply_filters(
			'iyzico_default_settings',
			array(
				'woocommerce_iyzico_settings' => array(
					'enabled'        => 'yes',
					'title'          => __( 'iyzico Checkout', 'woocommerce-iyzico' ),
					'button_text'    => __( 'Pay With Card', 'woocommerce-iyzico' ),
					'description'    => __( 'Best Payment Solution', 'woocommerce-iyzico' ),
					'icon'           => PLUGIN_ASSETS_DIR_URL . '/images/cards.png',
					'success_status' => 'processing',
					'overlay_script' => 'bottomLeft',
					'form_class'     => 'responsive',
				),
			)
		);

		return array_key_exists( $this->optionsTableKey, (array) $defaultSettings ) ? $defaultSettings[ $this->optionsTableKey ] : array();
	}
}