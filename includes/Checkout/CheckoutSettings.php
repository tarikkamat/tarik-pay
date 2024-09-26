<?php
namespace Iyzico\IyzipayWoocommerce\Checkout;

use Iyzico\IyzipayWoocommerce\Common\Abstracts\Config;

class CheckoutSettings extends Config {
	public $optionsTableKey = 'woocommerce_iyzico_settings';
	public $form_fields = array();

	public function __construct() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __('Enable/Disable', 'woocommerce-iyzico'),
				'label'   => __('Enable iyzico checkout', 'woocommerce-iyzico'),
				'type'    => 'checkbox',
				'default' => 'no'
			),
		);

		$this->defaultSettings = array();
		foreach ($this->form_fields as $key => $field) {
			$this->defaultSettings[$key] = $field['default'] ?? '';
		}
	}

	public function getFormFields() {
		return $this->form_fields;
	}
}