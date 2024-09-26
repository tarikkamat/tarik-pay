<?php

namespace Iyzico\IyzipayWoocommerce\Pwi;

use Iyzico\IyzipayWoocommerce\Common\Abstracts\Config;

class PwiSettings extends Config {
	public $optionsTableKey = 'woocommerce_pwi_settings';
	public $form_fields = array();

	public function __construct() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-iyzico' ),
				'label'   => __( 'Enable Pay with iyzico', 'woocommerce-iyzico' ),
				'type'    => 'checkbox',
				'default' => 'no'
			)
		);

		$this->defaultSettings = array();

		foreach ( $this->form_fields as $key => $field ) {
			$this->defaultSettings[ $key ] = $field['default'] ?? '';
		}
	}

	public function getFormFields() {
		return $this->form_fields;
	}
}