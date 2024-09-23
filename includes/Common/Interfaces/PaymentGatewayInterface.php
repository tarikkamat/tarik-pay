<?php

namespace Iyzico\IyzipayWoocommerce\Common\Interfaces;

use Iyzico\IyzipayWoocommerce\Common\Hooks\IyzicoResponse;

/**
 * Interface PaymentGatewayInterface
 *
 * @package Iyzico\IyzipayWoocommerce\Common\Interfaces
 */
interface PaymentGatewayInterface {

	/**
	 * Get properties
	 *
	 * @param IyzicoResponse $response
	 * @param bool $onCheckout
	 *
	 * @return mixed
	 */

	public function success_process( IyzicoResponse $response, bool $onCheckout );

	/**
	 * Notify process
	 *
	 * @param IyzicoResponse $response
	 *
	 * @return mixed
	 */
	public function notify_process( IyzicoResponse $response );

	/**
	 * Error process
	 *
	 * @param IyzicoResponse $response
	 * @param bool $onCheckout
	 *
	 * @return mixed
	 */

	public function error_process( IyzicoResponse $response, bool $onCheckout );

	/**
	 * Process callback
	 *
	 * @param string $transactionId
	 *
	 * @return mixed
	 */
	public function process_callback( string $transactionId );

	/**
	 * Transaction success process
	 *
	 * @param $response
	 *
	 * @return mixed
	 */
	public function transaction_success_process( $response );

	public function redirect_payment_form(string $url);

	public function response_filter(mixed $variable);

	/**
	 * Transaction error process
	 *
	 * @param $response
	 *
	 * @return mixed
	 */
	public function transaction_error_process( $response );
}
