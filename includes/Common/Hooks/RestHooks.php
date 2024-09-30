<?php

namespace Iyzico\IyzipayWoocommerce\Common\Hooks;

use Iyzico\IyzipayWoocommerce\Rest\RestAPI;

class RestHooks
{
	private $restApiHandler;

	public function __construct()
	{
		$this->restApiHandler = new RestAPI();
	}

	public function register(): void
	{
		add_action('rest_api_init', [$this->restApiHandler, 'addRestRoutes']);
	}

}