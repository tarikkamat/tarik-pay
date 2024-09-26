<?php

namespace Iyzico\IyzipayWoocommerce\Core;

use Iyzico\IyzipayWoocommerce\Admin\SettingsPage;
use Iyzico\IyzipayWoocommerce\Common\Helpers\BlocksSupport;
use Iyzico\IyzipayWoocommerce\Checkout\CheckoutForm;
use Iyzico\IyzipayWoocommerce\Common\Helpers\Logger;
use Iyzico\IyzipayWoocommerce\Common\Traits\PluginLoader;
use Iyzico\IyzipayWoocommerce\Database\DatabaseManager;
use Iyzico\IyzipayWoocommerce\Pwi\Pwi;

class Plugin {

	use PluginLoader;

	public function run(): void {
		$this->loadDependencies();
		$this->setLocale();
		$this->defineAdminHooks();
		$this->initPaymentGateway();
		$this->generateWebhookKey();

		BlocksSupport::init();
		HighPerformanceOrderStorageSupport::init();

		if ( is_admin() ) {
			new SettingsPage();
		}
	}

	private function loadDependencies(): void {
		require_once PLUGIN_PATH . '/includes/Common/Helpers/BlocksSupport.php';
		require_once PLUGIN_PATH . '/includes/Common/Helpers/HighPerformanceOrderStorageSupport.php';

		require_once PLUGIN_PATH . '/includes/Admin/SettingsPage.php';

		require_once PLUGIN_PATH . '/includes/Common/Hooks/IyzicoResponse.php';
		require_once PLUGIN_PATH . '/includes/Common/Interfaces/PaymentGatewayInterface.php';

		require_once PLUGIN_PATH . '/includes/Checkout/CheckoutForm.php';
		require_once PLUGIN_PATH . '/includes/Checkout/BlocksCheckoutMethod.php';

		require_once PLUGIN_PATH . '/includes/Pwi/Pwi.php';
		require_once PLUGIN_PATH . '/includes/Pwi/BlocksPwiMethod.php';
	}

	private function setLocale(): void {
		load_plugin_textdomain( 'woocommerce-iyzico', false, PLUGIN_LANG_PATH );
	}

	private function defineAdminHooks(): void {
		if ( is_admin() ) {
			add_filter(
				'plugin_action_links_' . plugin_basename( PLUGIN_BASEFILE ),
				[ $this, 'actionLinks' ]
			);
		}
	}

	private function initPaymentGateway(): void {
		add_filter( 'woocommerce_payment_gateways', [ $this, 'addGateways' ] );
	}

	public function addGateways( $methods ) {
		$methods[] = CheckoutForm::class;
		$methods[] = Pwi::class;

		return $methods;
	}

	public function actionLinks( $links ): array {
		$custom_links   = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=iyzico' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		$custom_links[] = '<a target="_blank" href="https://docs.iyzico.com/">' . __( 'Docs', 'woocommerce' ) . '</a>';
		$custom_links[] = '<a target="_blank" href="https://iyzico.com/destek/iletisim">' . __( 'Support', 'woocommerce-iyzico' ) . '</a>';

		return array_merge( $custom_links, $links );
	}

	private function generateWebhookKey(): void {
		$uniqueUrlId = substr( base64_encode( time() . mt_rand() ), 15, 6 );
		$iyziUrlId   = get_option( "iyzicoWebhookUrlKey" );
		if ( ! $iyziUrlId ) {
			add_option( "iyzicoWebhookUrlKey", $uniqueUrlId, '', false );
		}
	}

	public static function activate(): void {
		DatabaseManager::createTables();
	}

	public static function deactivate(): void {
		global $wpdb;
		$logger = new Logger();
		DatabaseManager::init( $wpdb, $logger, PLUGIN_VERSION );
		DatabaseManager::dropTables();

		delete_option( 'iyzico_overlay_token' );
		delete_option( 'iyzico_overlay_position' );
		delete_option( 'iyzico_thank_you' );
		delete_option( 'init_active_webhook_url' );

		flush_rewrite_rules();
	}
}
