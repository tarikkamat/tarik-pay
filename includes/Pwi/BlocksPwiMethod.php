<?php

namespace Iyzico\IyzipayWoocommerce\Pwi;

use Iyzico\IyzipayWoocommerce\Checkout\CheckoutSettings;
use Iyzico\IyzipayWoocommerce\Common\Abstracts\AbstractBlocksMethod;

/**
 * Class BlocksPwiMethod
 *
 * @extends AbstractBlocksMethod
 */
class BlocksPwiMethod extends AbstractBlocksMethod {
	protected $name = 'pwi';
	protected $pwiSettings;
	protected $checkoutSettings;

	public function initialize(): void {
		$this->settings = $this->pwiSettings->getSettings();
	}

	public function get_payment_method_data(): array {
		$title       = $this->settings['title'];
		$description = $this->settings['description'];
		$lang        = "TR";
		$image_path  = plugin_dir_url( PLUGIN_BASEFILE ) . 'assets/images/pwi_tr.png';

		if ( strlen( $this->checkoutSettings->findByKey( 'form_language' ) ) > 0 ) {
			$lang = $this->checkoutSettings->findByKey( 'form_language' );
		}

		if ( $lang == "EN" ) {
			$image_path = plugin_dir_url( PLUGIN_BASEFILE ) . 'assets/images/pwi_en.png';
		}

		return [
			'title'       => $title,
			'description' => $description,
			'icon'        => $image_path
		];
	}

	protected function initializeSettings() {
		$this->pwiSettings      = new PwiSettings();
		$this->checkoutSettings = new CheckoutSettings();
		$this->settings         = $this->pwiSettings->getSettings();
	}
}
