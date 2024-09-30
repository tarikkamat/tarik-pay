<?php

namespace Iyzico\IyzipayWoocommerce\Common\Hooks;

use Iyzico\IyzipayWoocommerce\Admin\SettingsPage;

class AdminHooks {

	private $page;

	public function __construct() {
		$this->page = new SettingsPage();
	}

	public function register(): void
	{
		add_action('admin_menu', [$this->page, 'addAdminMenu']);
		add_action('admin_enqueue_scripts', [$this->page, 'enqueueAdminAssets']);
	}


}