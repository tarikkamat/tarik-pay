<?php

namespace Iyzico\IyzipayWoocommerce\Checkout;

use Exception;
use Iyzico\IyzipayWoocommerce\Common\Helpers\CookieManager;
use Iyzico\IyzipayWoocommerce\Common\Helpers\Logger;
use Iyzico\IyzipayWoocommerce\Common\Helpers\PriceHelper;
use Iyzico\IyzipayWoocommerce\Common\Helpers\TlsVerifier;
use Iyzico\IyzipayWoocommerce\Common\Helpers\VersionChecker;
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

class CheckoutForm extends WC_Payment_Gateway_CC implements PaymentGatewayInterface
{

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

	public function __construct()
	{
		$this->id = "iyzico";
		$this->method_title = __('iyzico Checkout', 'woocommerce-iyzico');
		$this->method_description = __('Best Payment Solution', 'woocommerce-iyzico');
		$this->checkoutSettings = new CheckoutSettings();
		$this->form_fields = $this->checkoutSettings->getFormFields();
		$this->init_settings();
		$settings = $this->checkoutSettings->getSettings();

		$this->enabled = $settings['enabled'];
		$this->title = $settings['title'];
		$this->description = $settings['description'];
		$this->order_button_text = $settings['button_text'] ?? '';
		$this->icon = $settings['icon'] ?? '';
		$this->has_fields = true;
		$this->supports = [
			'products',
			'refunds'
		];

		$this->logger = new Logger();
		$this->cookieManager = new CookieManager();
		$this->versionChecker = new VersionChecker($this->logger);
		$this->tlsVerifier = new TlsVerifier();
		$this->priceHelper = new PriceHelper();

		add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
		add_action('woocommerce_receipt_iyzico', [$this, 'load_form']);
		add_action('woocommerce_receipt_iyzico', [$this, 'checkout_form']);
		add_action('woocommerce_api_request', [$this, 'handle_api_request']);
		add_action('woocommerce_before_checkout_form', [$this, 'display_errors']);
	}

	public function handle_api_request()
	{
		if (isset($_GET['wc-api']) && $_GET['wc-api'] === 'CheckoutForm') {
			$this->process_callback();
			exit;
		}
	}

	public function process_payment($orderId)
	{
		try {
			$this->order = wc_get_order($orderId);
			$formType = $this->checkoutSettings->findByKey('form_class');

			if ($formType === 'redirect') {
				$this->order->add_order_note(__("This order will be processed on the iyzico payment page.", "woocommerce-iyzico"));
                $checkoutFormInitialize = $this->create_payment($orderId);
                $paymentPageUrl = $checkoutFormInitialize->getPaymentPageUrl();
                return $this->redirect_to_iyzico($paymentPageUrl);
			}

			return [
				'result' => 'success',
				'redirect' => $this->order->get_checkout_payment_url(true)
			];

		} catch (Exception $e) {
			wc_add_notice($e->getMessage(), 'error');
			return ['result' => 'failure'];
		}
	}

	private function process_callback(): void {
		try {
			$this->validate_token();
			$checkoutFormResult = $this->retrieve_checkout_form();
			$order = $this->get_order($checkoutFormResult->getBasketId());
			$this->ensure_payment_method($order);
			if ($this->is_payment_successful($checkoutFormResult)) {
				$this->complete_order($order);
				$this->redirect_to_order_received($order);
			} else {
				$this->handle_payment_failure($order, $checkoutFormResult->getErrorMessage());
			}
		} catch (Exception $e) {
			$this->handle_exception($e);
		}
	}

	/**
	 * @throws Exception
	 */
	private function validate_token(): void {
		if (empty($_POST['token'])) {
			throw new Exception(__("Payment token is missing. Please try again or contact the store owner if the problem persists.", "woocommerce-iyzico"));
		}
	}

	/**
	 * @throws Exception
	 */
	private function retrieve_checkout_form() {
		$request = new RetrieveCheckoutFormRequest();
		$locale = $this->checkoutSettings->findByKey('form_language') ?? "tr";
		$request->setLocale($locale);
		$request->setToken($_POST['token']);

		$checkoutFormResult = CheckoutFormModel::retrieve($request, $this->create_options());

		if (!$checkoutFormResult || $checkoutFormResult->getStatus() !== 'success') {
			throw new Exception(__("Payment process failed. Please try again or choose a different payment method.", "woocommerce-iyzico"));
		}

		return $checkoutFormResult;
	}

	/**
	 * @throws Exception
	 */
	private function get_order($basketId) {
		$order = wc_get_order($basketId);

		if (!$order) {
			throw new Exception(__("Order not found.", "woocommerce-iyzico"));
		}

		return $order;
	}

	private function ensure_payment_method($order): void {
		if ($order->get_payment_method_title() !== 'iyzico') {
			$order->set_payment_method('iyzico');
		}
	}

	private function is_payment_successful($checkoutFormResult): bool {
		return $checkoutFormResult->getPaymentStatus() === 'SUCCESS';
	}

	private function complete_order($order): void {
		$order->payment_complete();
		$order->save();

		$orderStatus = $this->checkoutSettings->findByKey('order_status');

		if ($orderStatus !== 'default' && !empty($orderStatus)) {
			$order->update_status($orderStatus);
		}
	}

	private function redirect_to_order_received($order): void {
		$checkoutOrderUrl = $order->get_checkout_order_received_url();
		$redirectUrl = add_query_arg(['msg' => 'Thank You', 'type' => 'woocommerce-message'], $checkoutOrderUrl);

		wp_redirect($redirectUrl);
		exit;
	}

	/**
	 * @throws Exception
	 */
	private function handle_payment_failure($order, $errorMessage): void {
		$order->add_order_note($errorMessage);
		throw new Exception($errorMessage);
	}

	private function handle_exception(Exception $e): void {
		$this->logger->error('CheckoutForm.php:139: ' . $e->getMessage());
		WC()->session->set('iyzico_error', $e->getMessage());
		wp_redirect(wc_get_checkout_url() . '?payment=failed');
		exit;
	}

	public function display_errors()
	{
		if (isset($_GET['payment']) && $_GET['payment'] === 'failed') {
			$error = WC()->session->get('iyzico_error');
			if ($error) {
				wc_add_notice($error, 'error');
				WC()->session->set('iyzico_error', null);
			} else {
				wc_add_notice(__("An unknown error occurred during the payment process. Please try again.", "woocommerce-iyzico"), 'error');
			}
		}
	}

    private function create_payment($orderId)
    {
	    $this->versionChecker->check();
	    $this->cookieManager->setWooCommerceSessionCookie();

	    global $woocommerce;

	    $order = wc_get_order($orderId);
	    $cart = $woocommerce->cart->get_cart();
	    $language = $this->checkoutSettings->findByKey('form_language') ?? "tr";
	    $customer = wp_get_current_user();

	    $woocommerce->session->set('conversationId', $orderId);
	    $woocommerce->session->set('customerId', $customer->ID);
	    $woocommerce->session->set('totalAmount', $order->get_total());

	    $currency = get_woocommerce_currency();

	    // Create Request
	    $request = new CreateCheckoutFormInitializeRequest();
	    $request->setLocale($language);
	    $request->setConversationId($orderId);
	    $request->setPrice($this->priceHelper->subTotalPriceCalc($cart, $order));
	    $request->setPaidPrice($this->priceHelper->priceParser(round($order->get_total(), 2)));
	    $request->setCurrency($currency);
	    $request->setBasketId($orderId);
	    $request->setPaymentGroup("PRODUCT");
	    $request->setCallbackUrl(add_query_arg('wc-api', 'CheckoutForm', $order->get_checkout_order_received_url()));
	    $request->setForceThreeDS("0");

	    // Create Buyer
	    $buyer = $this->create_buyer($customer, $order);
	    $request->setBuyer($buyer);

	    // Create Billing Address
	    $billingAddress = $this->create_address($order, "billing");
	    $request->setBillingAddress($billingAddress);

	    // Create Shipping Address
	    $shippingAddress = $this->create_address($order, "shipping");
	    $request->setShippingAddress($shippingAddress);

	    // Create Basket
	    $basketItems = $this->create_basket($cart);
	    $request->setBasketItems($basketItems);

	    // Create Options
	    $options = $this->create_options();

        return CheckoutFormInitialize::create($request, $options);
    }

	public function checkout_form($orderId)
	{
		$checkoutFormInitialize = $this->create_payment($orderId);

		$className = $this->checkoutSettings->findByKey('form_class');
		$message = '<p id="infoBox" style="display:none;">' . $this->checkoutSettings->findByKey('payment_checkout_value') . '</p>';
		echo '<script>jQuery(window).on("load", function(){document.getElementById("loadingBar").style.display="none",document.getElementById("infoBox").style.display="block",document.getElementById("iyzipay-checkout-form").style.display="block"});</script>';

		if ($checkoutFormInitialize->getStatus() === "success") {
			echo $message;
			echo ' <div style="display:none" id="iyzipay-checkout-form" class=' . $className . '>' . $checkoutFormInitialize->getCheckoutFormContent() . '</div>';
		} else {
			echo $checkoutFormInitialize->getErrorMessage();
		}
	}

	protected function create_buyer($customer, $order): Buyer
	{
		$buyer = new Buyer();
		$buyer->setId($customer->ID);
		$buyer->setName($order->get_billing_first_name());
		$buyer->setSurname($order->get_billing_last_name());
		$buyer->setIdentityNumber("11111111111");
		$buyer->setEmail($order->get_billing_email());
		$buyer->setRegistrationDate(date('Y-m-d H:i:s'));
		$buyer->setLastLoginDate(date('Y-m-d H:i:s'));
		$buyer->setRegistrationAddress($order->get_billing_address_1() . $order->get_billing_address_2());
		$buyer->setCity($order->get_billing_city());
		$buyer->setCountry($order->get_billing_country());
		$buyer->setZipCode($order->get_billing_postcode());
		$buyer->setIp($_SERVER['REMOTE_ADDR']);
		$buyer->setGsmNumber($order->get_billing_phone());

		return $buyer;
	}

	protected function create_address($order, $type): Address
	{
		$isTypeBilling = $type === "billing";

		$firstName = $isTypeBilling ? $order->get_billing_first_name() : $order->get_shipping_first_name();
		$lastName = $isTypeBilling ? $order->get_billing_last_name() : $order->get_shipping_last_name();
		$contactName = $firstName . ' ' . $lastName;

		$city = $isTypeBilling ? $order->get_billing_city() : $order->get_shipping_city();
		$country = $isTypeBilling ? $order->get_billing_country() : $order->get_shipping_country();
		$address1 = $isTypeBilling ? $order->get_billing_address_1() : $order->get_shipping_address_1();
		$address2 = $isTypeBilling ? $order->get_billing_address_2() : $order->get_shipping_address_2();
		$fullAddress = $address1 . $address2;
		$zipCode = $isTypeBilling ? $order->get_billing_postcode() : $order->get_shipping_postcode();

		$address = new Address();
		$address->setContactName($contactName);
		$address->setCity($city);
		$address->setCountry($country);
		$address->setAddress($fullAddress);
		$address->setZipCode($zipCode);

		return $address;
	}

	protected function create_basket($cart): array
	{
		$basketItems = [];
		foreach ($cart as $item) {
			$product = $item['data'];
			$basketItem = new BasketItem();
			$basketItem->setId($item['product_id']);
			$basketItem->setName($product->get_name());

			$categories = get_the_terms($product->get_id(), 'product_cat');
			if ($categories && !is_wp_error($categories)) {
				$category_names = wp_list_pluck($categories, 'name');
				$basketItem->setCategory1(implode(', ', $category_names));
			}

			$basketItem->setItemType(BasketItemType::PHYSICAL);
			$basketItem->setPrice($product->get_price());
			$basketItems[] = $basketItem;
		}

		return $basketItems;
	}
    
	protected function create_options(): Options
	{
		$options = new Options();
		$options->setApiKey($this->checkoutSettings->findByKey('api_key'));
		$options->setSecretKey($this->checkoutSettings->findByKey('secret_key'));
		$options->setBaseUrl($this->checkoutSettings->findByKey('api_type'));

		return $options;
	}

	public function redirect_to_iyzico(string $paymentPageUrl)
	{
		return [
			'result' => 'success',
			'redirect' => $paymentPageUrl
		];
	}

	public function success_process($response, $onCheckout)
	{
		$this->order = wc_get_order($response->getConversationId());
		$received_url = $this->order->get_checkout_order_received_url();
		$this->set_fee();

		if ($response->getPaymentId() && $this->order->needs_payment()) {
			$this->order->payment_complete($response->getPaymentId());
			$this->order->add_order_note($response->isSuccess());
		}

		if ($onCheckout) {
			return [
				'result' => 'success',
				'redirect' => $received_url,
			];
		}

		if ($this->checkoutSettings->findByKey('form_class')) {
			// $this->redirect_payment_form($received_url);
		}

		wp_safe_redirect($received_url);
		exit;
	}

	/**
	 * @throws Exception
	 */
	public function error_process($response, bool $onCheckout)
	{
		if (!$this->order instanceof WC_Order) {
			$this->order = wc_get_order($response->getConversationId());
		}

		if (!$this->order->get_transaction_id()) {
			$this->order->add_order_note($response->getToken());
		}

		if (false === $onCheckout) {
			$checkout_url = add_query_arg(
				array(
					"iyzico-error" => bin2hex($response->getErrorMessage()),
				),
				wc_get_checkout_url()
			);

			if ($this->checkoutSettings->findByKey('use_iframe')) {
				//$this->redirect_payment_form($checkout_url);
			}

			wp_safe_redirect($checkout_url);
			exit;
		}

		throw new Exception(esc_html($response->getErrorMessage()));
	}

	public function notify_process($response)
	{
		$this->order = wc_get_order($response->getConversationId());

		if ($response->isSuccess() && $response->getPaymentId() && $this->order->needs_payment()) {
			$this->set_fee();
			$this->order->payment_complete($response->getPaymentId());
		} elseif (!$this->order->get_transaction_id()) {
			$this->order->update_status('failed');
		}

		$this->order->add_order_note($response->getIyziEventType() . $response->isSuccess());
	}

	public function admin_options()
	{
		?>
		<style>
			.woocommerce-save-button {
				display: none !important;
			}
		</style>
		<h3>
			<?php esc_html_e('These payment method settings are made through the admin menu.', 'woocommerce-iyzico'); ?>
			<a
				href="<?php echo esc_url(admin_url('admin.php?page=iyzico')); ?>"><?php esc_html_e('Click to go to settings.', 'woocommerce-iyzico'); ?></a>
		</h3>
		<?php
	}

	protected function set_fee()
	{
		$fee_data = new WC_Order_Item_Fee();
		//$fee_data->set_amount( (string) $fee->get_total() );
		//$fee_data->set_total( (string) $fee->get_total() );
		//$fee_data->set_name( $fee->get_name() );
		$fee_data->set_tax_status('none');
		$fee_data->save();
		$this->order->add_meta_data("iyzico_fee", true);
		$this->order->add_item($fee_data);
		$this->order->calculate_totals();
		$this->order->save();

	}

	public function load_form()
	{
		wp_enqueue_style('iyzico-loading-style', plugin_dir_url(PLUGIN_BASEFILE) . 'assets/css/iyzico-loading.css');
		$html = $this->get_loading_html();
		echo $html;
	}

	private function get_loading_html()
	{
		ob_start();
		?>
		<div id="loadingBar">
			<div class="loading"></div>
			<div class="brand">
				<p>iyzico</p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
