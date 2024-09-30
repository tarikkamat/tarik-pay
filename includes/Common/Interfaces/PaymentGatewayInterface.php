<?php

namespace Iyzico\IyzipayWoocommerce\Common\Interfaces;

/**
 * Interface PaymentGatewayInterface
 *
 * @package Iyzico\IyzipayWoocommerce\Common\Interfaces
 */
interface PaymentGatewayInterface
{
	public function success_process($response, bool $onCheckout);

	public function error_process($response, bool $onCheckout);

	public function notify_process($response);

}
