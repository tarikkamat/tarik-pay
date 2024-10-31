<?php

namespace Iyzico\IyzipayWoocommerce\Checkout;

use Iyzico\IyzipayWoocommerce\Common\Abstracts\AbstractBlocksMethod;

/**
 * Class BlocksCheckoutMethod
 *
 * @extends AbstractBlocksMethod
 */
class BlocksCheckoutMethod extends AbstractBlocksMethod {
	protected $name = 'iyzico';
	protected $checkoutSettings;

	public function initialize(): void {
		$this->settings = $this->checkoutSettings->getSettings();
	}

	protected function initializeSettings() {
		$this->checkoutSettings = new CheckoutSettings();
		$this->settings         = $this->checkoutSettings->getSettings();
	}
}
