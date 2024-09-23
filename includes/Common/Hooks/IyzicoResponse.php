<?php

namespace Iyzico\IyzipayWoocommerce\Common\Hooks;

/**
 * Class IyzicoResponse
 *
 * @package Iyzico\IyzipayWoocommerce\Common\Hooks
 */
class IyzicoResponse {

	public string $gateway;
	public bool $success = false;
	public string $errorMessage;
	public string $errorCode = '';
	public string $paymentId;
	public string $conversationId;
	public string $iyziEventTime;
	public string $iyziEventType;
	public string $iyziReferenceCode;
	public string $token;

	/**
	 * IyzicoResponse constructor.
	 *
	 * @param string $gateway
	 */
	public function __construct( string $gateway ) {
		$this->setGateway( $gateway );
	}

	/**
	 * @return string
	 */
	public function getIyziEventTime(): string {
		return $this->iyziEventTime;
	}

	/**
	 * @param string $iyziEventTime
	 */
	public function setIyziEventTime( string $iyziEventTime ): void {
		$this->iyziEventTime = $iyziEventTime;
	}

	/**
	 * @return string
	 */
	public function getIyziEventType(): string {
		return $this->iyziEventType;
	}

	/**
	 * @param string $iyziEventType
	 */
	public function setIyziEventType( string $iyziEventType ): void {
		$this->iyziEventType = $iyziEventType;
	}

	/**
	 * @return string
	 */
	public function getIyziReferenceCode(): string {
		return $this->iyziReferenceCode;
	}

	/**
	 * @param string $iyziReferenceCode
	 */
	public function setIyziReferenceCode( string $iyziReferenceCode ): void {
		$this->iyziReferenceCode = $iyziReferenceCode;
	}

	/**
	 * @return string
	 */
	public function getToken(): string {
		return $this->token;
	}

	/**
	 * @param string $token
	 */
	public function setToken( string $token ): void {
		$this->token = $token;
	}

	/**
	 * @var bool
	 */
	public bool $isPendingPayment = false;

	/**
	 * @param bool $success
	 *
	 * @return $this
	 */
	public function setSuccess( bool $success ): self {
		$this->success = $success;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSuccess(): bool {
		return $this->success;
	}

	/**
	 * @param string $gateway
	 *
	 * @return $this
	 */
	public function setGateway( string $gateway ): self {
		$this->gateway = $gateway;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getGateway(): string {
		return $this->gateway;
	}

	/**
	 * @param string $message
	 *
	 * @return $this
	 */
	public function setErrorMessage( string $message ): self {
		$this->errorMessage = $message;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getErrorMessage(): string {
		return $this->errorMessage ? $this->errorMessage : __( 'Unknown error please contact admin', 'woocommerce-iyzico' );
	}

	/**
	 * @param string $code
	 *
	 * @return $this
	 */
	public function setErrorCode( string $code ): self {
		$this->errorCode = $code;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getErrorCode(): string {
		return $this->errorCode ? $this->errorCode : '';
	}

	/**
	 * @param string $paymentId
	 *
	 * @return $this
	 */
	public function setPaymentId( string $paymentId ): self {
		$this->paymentId = $paymentId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPaymentId(): string {
		return $this->paymentId;
	}

	/**
	 * @param string $conversationId
	 *
	 * @return $this
	 */
	public function setConversationId( string $conversationId ): self {
		$this->paymentId = $conversationId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getConversationId(): string {
		return $this->conversationId;
	}

	/**
	 * @param bool $isPendingPayment
	 *
	 * @return $this
	 */
	public function setIsPendingPayment( bool $isPendingPayment ): self {
		$this->isPendingPayment = $isPendingPayment;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isPendingPayment(): bool {
		return $this->isPendingPayment;
	}
}
