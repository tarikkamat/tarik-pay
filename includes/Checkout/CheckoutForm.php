<?php

namespace Iyzico\IyzipayWoocommerce\Checkout;

use Exception;
use Iyzico\IyzipayWoocommerce\Admin\SettingsPage;
use Iyzico\IyzipayWoocommerce\Common\Helpers\CookieManager;
use Iyzico\IyzipayWoocommerce\Common\Helpers\DataFactory;
use Iyzico\IyzipayWoocommerce\Common\Helpers\Logger;
use Iyzico\IyzipayWoocommerce\Common\Helpers\PaymentProcessor;
use Iyzico\IyzipayWoocommerce\Common\Helpers\PriceHelper;
use Iyzico\IyzipayWoocommerce\Common\Helpers\TlsVerifier;
use Iyzico\IyzipayWoocommerce\Common\Helpers\VersionChecker;
use Iyzico\IyzipayWoocommerce\Database\DatabaseManager;
use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Model\CheckoutForm as CheckoutFormModel;
use Iyzipay\Options;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use WC_Order;
use WC_Order_Item_Fee;
use WC_Payment_Gateway_CC;
use Iyzico\IyzipayWoocommerce\Common\Interfaces\PaymentGatewayInterface;

class CheckoutForm extends WC_Payment_Gateway_CC implements PaymentGatewayInterface {

	public $checkoutSettings;
	public $order;
	public $form_fields;
	public $supports = [];
	public $has_fields;
	public $cookieManager;
	public $versionChecker;
	public $tlsVerifier;
	public $logger;
	public $priceHelper;
	public $paymentProcessor;
	public $checkoutDataFactory;
	public $checkoutView;
	public $adminSettings;
	public $databaseManager;

	public function __construct() {
		$this->id                 = "iyzico";
		$this->method_title       = __( 'iyzico Checkout', 'woocommerce-iyzico' );
		$this->method_description = __( 'Best Payment Solution', 'woocommerce-iyzico' );
		$this->checkoutSettings   = new CheckoutSettings();
		$this->form_fields        = $this->checkoutSettings->getFormFields();
		$this->init_settings();
		$settings = $this->checkoutSettings->getSettings();

		$this->enabled           = $settings['enabled'];
		$this->title             = $settings['title'];
		$this->description       = $settings['description'];
		$this->order_button_text = $settings['button_text'] ?? '';
		$this->icon              = $settings['icon'] ?? '';
		$this->has_fields        = true;
		$this->supports          = [
			'products',
			'refunds'
		];

		$this->logger         = new Logger();
		$this->cookieManager  = new CookieManager();
		$this->versionChecker = new VersionChecker( $this->logger );
		$this->tlsVerifier    = new TlsVerifier();
		$this->priceHelper    = new PriceHelper();
		$this->databaseManager = new DatabaseManager();

		$this->paymentProcessor = new PaymentProcessor(
			$this->logger,
			$this->priceHelper,
			$this->cookieManager,
			$this->versionChecker,
			$this->tlsVerifier,
			$this->checkoutSettings,
			$this->databaseManager
		);

		$this->checkoutDataFactory = new DataFactory( $this->priceHelper, $this->checkoutSettings );
		$this->checkoutView        = new CheckoutView( $this->checkoutSettings );
		$this->adminSettings       = new SettingsPage();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'woocommerce_receipt_iyzico', [ $this, 'load_form' ] );
		add_action( 'woocommerce_receipt_iyzico', [ $this, 'checkout_form' ] );
		add_action( 'woocommerce_api_request', [ $this, 'handle_api_request' ] );
		add_action( 'woocommerce_before_checkout_form', [ $this, 'display_errors' ] );
	}

	public function handle_api_request() {
		if ( isset( $_GET['wc-api'] ) && $_GET['wc-api'] === 'CheckoutForm' ) {
			$this->paymentProcessor->processCallback();
			exit;
		}
	}

	public function process_payment( $order_id ) {
		try {
			$this->order = wc_get_order( $order_id );
			$formType    = $this->checkoutSettings->findByKey( 'form_class' );

			if ( $formType === 'redirect' ) {
				$this->order->add_order_note( __( "This order will be processed on the iyzico payment page.", "woocommerce-iyzico" ) );
				$checkoutFormInitialize = $this->create_payment( $order_id );
				$paymentPageUrl         = $checkoutFormInitialize->getPaymentPageUrl();

				return $this->redirect_to_iyzico( $paymentPageUrl );
			}

			return [
				'result'   => 'success',
				'redirect' => $this->order->get_checkout_payment_url( true )
			];

		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );

			return [ 'result' => 'failure' ];
		}
	}

	protected function create_payment( $orderId ) {
		$this->versionChecker->check();
		$this->cookieManager->setWooCommerceSessionCookie();

		global $woocommerce;

		$order    = wc_get_order( $orderId );
		$cart     = $woocommerce->cart->get_cart();
		$language = $this->checkoutSettings->findByKey( 'form_language' ) ?? "tr";
		$customer = wp_get_current_user();

		$woocommerce->session->set( 'conversationId', $orderId );
		$woocommerce->session->set( 'customerId', $customer->ID );
		$woocommerce->session->set( 'totalAmount', $order->get_total() );

		$currency = get_woocommerce_currency();

		// Create Request
		$request = new CreateCheckoutFormInitializeRequest();
		$request->setLocale( $language );
		$request->setConversationId( $orderId );
		$request->setPrice( $this->priceHelper->subTotalPriceCalc( $cart, $order ) );
		$request->setPaidPrice( $this->priceHelper->priceParser( round( $order->get_total(), 2 ) ) );
		$request->setCurrency( $currency );
		$request->setBasketId( $orderId );
		$request->setPaymentGroup( "PRODUCT" );
		$request->setCallbackUrl( add_query_arg( 'wc-api', 'CheckoutForm', $order->get_checkout_order_received_url() ) );
		$request->setForceThreeDS( "0" );

		// Prepare Checkout Data
		$checkoutData = $this->checkoutDataFactory->prepareCheckoutData( $customer, $order, $cart );
		$request->setBuyer( $checkoutData['buyer'] );
		$request->setBillingAddress( $checkoutData['billingAddress'] );
		$request->setShippingAddress( $checkoutData['shippingAddress'] );
		$request->setBasketItems( $checkoutData['basketItems'] );

		// Create Options
		$options = $this->create_options();

		return CheckoutFormInitialize::create( $request, $options );
	}

	public function checkout_form( $orderId ) {
		$checkoutFormInitialize = $this->create_payment( $orderId );
		$this->checkoutView->renderCheckoutForm( $checkoutFormInitialize );
	}

	public function display_errors() {
		if ( isset( $_GET['payment'] ) && $_GET['payment'] === 'failed' ) {
			$error = WC()->session->get( 'iyzico_error' );
			if ( $error ) {
				wc_add_notice( $error, 'error' );
				WC()->session->set( 'iyzico_error', null );
			} else {
				wc_add_notice( __( "An unknown error occurred during the payment process. Please try again.", "woocommerce-iyzico" ), 'error' );
			}
		}
	}

	public function admin_options() {
		$this->adminSettings->renderAdminOptions();
	}

	protected function set_fee() {
		$fee_data = new WC_Order_Item_Fee();
		$fee_data->set_tax_status( 'none' );
		$fee_data->save();
		$this->order->add_meta_data( "iyzico_fee", true );
		$this->order->add_item( $fee_data );
		$this->order->calculate_totals();
		$this->order->save();
	}

	public function load_form() {
		wp_enqueue_style( 'iyzico-loading-style', plugin_dir_url( PLUGIN_BASEFILE ) . 'assets/css/iyzico-loading.css' );
		$this->checkoutView->renderLoadingHtml();
	}

	public function redirect_to_iyzico( string $paymentPageUrl ) {
		return [
			'result'   => 'success',
			'redirect' => $paymentPageUrl
		];
	}

	public function notify_process( $response ) {
		$this->order = wc_get_order( $response->getConversationId() );

		if ( $response->isSuccess() && $response->getPaymentId() && $this->order->needs_payment() ) {
			$this->set_fee();
			$this->order->payment_complete( $response->getPaymentId() );
		} elseif ( ! $this->order->get_transaction_id() ) {
			$this->order->update_status( 'failed' );
		}

		$this->order->add_order_note( $response->getIyziEventType() . $response->isSuccess() );
	}
    
	protected function create_options(): Options {
		$options = new Options();
		$options->setApiKey( $this->checkoutSettings->findByKey( 'api_key' ) );
		$options->setSecretKey( $this->checkoutSettings->findByKey( 'secret_key' ) );
		$options->setBaseUrl( $this->checkoutSettings->findByKey( 'api_type' ) );

		return $options;
	}
}
