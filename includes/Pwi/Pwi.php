<?php

namespace Iyzico\IyzipayWoocommerce\Pwi;

use Exception;
use Iyzico\IyzipayWoocommerce\Common\Hooks\IyzicoResponse;
use JetBrains\PhpStorm\NoReturn;
use WC_Order;
use WC_Order_Item_Fee;
use WC_Payment_Gateway_CC;
use Iyzico\IyzipayWoocommerce\Common\Interfaces\PaymentGatewayInterface;

class Pwi extends WC_Payment_Gateway_CC implements PaymentGatewayInterface {

	public $pwiSettings;
	public WC_Order|null $order;

	public function __construct() {
		$this->id                 = "pwi";
		$this->pwiSettings        = new PwiSettings();
		$this->method_title       = __( 'Pay with iyzico', 'woocommerce-iyzico' );
		$this->method_description = __( 'Best Payment Solution', 'woocommerce-iyzico' );
		$this->enabled            = $this->pwiSettings->findByKey( 'enabled' );
		$this->title              = apply_filters( 'pwi_woocommerce_gateway_title_text', $this->pwiSettings->findByKey( 'title' ) );
		$this->description        = apply_filters( 'pwi_woocommerce_gateway_description_text', $this->pwiSettings->findByKey( 'description' ) );
		$this->order_button_text  = apply_filters( 'pwi_woocommerce_gateway_button_text', $this->pwiSettings->findByKey( 'button_text' ) );
		$this->has_fields         = true;
		$this->supports           = [
			'products',
			'refunds'
		];
		$this->init_settings();
	}

	public function process_payment( $order_id ) {
		try {
			$this->order = wc_get_order( $order_id );
			$response    = $this->response_filter( $_POST );

			// Ödeme işlemi başarılı olursa
			if ( $this->pwiSettings->findByKey( 'form_class' ) == 'redirect' ) {
				// TODO: some code here
			}

			$this->transaction_error_process( $response );
			$this->error_process( $response, true );

		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );
		}
	}

	public function success_process( IyzicoResponse $response, $onCheckout ) {
		$this->order  = wc_get_order( $response->getConversationId() );
		$received_url = $this->order->get_checkout_order_received_url();
		$this->set_fee();

		if ( $response->getPaymentId() && $this->order->needs_payment() ) {
			$this->order->payment_complete( $response->getPaymentId() );
			$this->order->add_order_note( $response->isSuccess() );
		}

		if ( $onCheckout ) {
			return array(
				'result'   => 'success',
				'redirect' => $received_url,
			);
		}

		if ( $this->pwiSettings->findByKey( 'form_class' ) ) {
			$this->redirect_payment_form( $received_url );
		}

		wp_safe_redirect( $received_url );
		exit;
	}

	/**
	 * Ödeme işleminin hatayla karşılaşması sonucunda yapılacak işlemlerin hepsini barındırır.
	 *
	 * @param IyzicoResponse $response Ödeme geçidi cevabı
	 * @param bool $onCheckout Ödeme sayfasında mı ?
	 *
	 * @return void
	 * @throws Exception Ödemede hata
	 *
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 */
	public function error_process( IyzicoResponse $response, bool $onCheckout ) {
		if ( ! $this->order instanceof WC_Order ) {
			$this->order = wc_get_order( $response->getConversationId() );
		}

		if ( ! $this->order->get_transaction_id() ) {
			$this->order->add_order_note( $response->getToken() );
		}

		if ( false === $onCheckout ) {
			$checkout_url = add_query_arg(
				array(
					"iyzico-error" => bin2hex( $response->getErrorMessage() ),
				),
				wc_get_checkout_url()
			);

			if ( $this->pwiSettings->findByKey( 'use_iframe' ) ) {
				$this->redirect_payment_form( $checkout_url );
			}

			wp_safe_redirect( $checkout_url );
			exit;
		}

		throw new Exception( esc_html( $response->getErrorMessage() ) );
	}

	/**
	 * Ödeme işleminin bildirim tarafından gelen cevaba istinaden yapılacak aksiyonları organzie eder.
	 *
	 * @param IyzicoResponse $response Ödeme geçidi cevabı
	 *
	 * @return void
	 */
	public function notify_process( IyzicoResponse $response ) {
		$this->order = wc_get_order( $response->getConversationId() );

		if ( $response->isSuccess() && $response->getPaymentId() && $this->order->needs_payment() ) {
			$this->set_fee();
			$this->order->payment_complete( $response->getPaymentId() );
		} elseif ( ! $this->order->get_transaction_id() ) {
			$this->order->update_status( 'failed' );
		}

		$this->order->add_order_note( $response->getIyziEventType() . $response->isSuccess() );
	}


	/**
	 * WooCommerce -> Ayarlar -> Ödemeler sekmesi altındaki ayarları yönlendirir.
	 *
	 * @return void
	 */
	public function admin_options() {
		?>
        <style>
            .woocommerce-save-button {
                display: none !important;
            }
        </style>
        <h3>
			<?php esc_html_e( 'These payment method settings are made through the admin menu.', 'woocommerce-iyzico' ); ?>
            <a
                    href="<?php echo esc_url( admin_url( 'admin.php?page=iyzico' ) ); ?>"><?php esc_html_e( 'Click to go to settings.', 'woocommerce-iyzico' ); ?></a>
        </h3>
		<?php
	}

	protected function set_fee() {
		$fee_data = new WC_Order_Item_Fee();
		//$fee_data->set_amount( (string) $fee->get_total() );
		//$fee_data->set_total( (string) $fee->get_total() );
		//$fee_data->set_name( $fee->get_name() );
		$fee_data->set_tax_status( 'none' );
		$fee_data->save();
		$this->order->add_meta_data( "iyzico_fee", true );
		$this->order->add_item( $fee_data );
		$this->order->calculate_totals();
		$this->order->save();

	}

	public function response_filter( mixed $variable ) {
		if ( is_array( $variable ) ) {
			return array_map( 'response_filter', $variable );
		}

		return is_scalar( $variable ) ? sanitize_text_field( wp_unslash( $variable ) ) : $variable;
	}

	#[NoReturn]
	public function redirect_payment_form( $redirect_url ) {
		?>
        <script>
            window.parent.location.href = '<?php echo esc_url_raw( $redirect_url ); ?>';
        </script>
		<?php
		exit;
	}

	public function transaction_error_process( $response ) {
		// TODO: Implement transaction_error_process() method.
	}

	public function process_callback( string $transactionId ) {
		// TODO: Implement process_callback() method.
	}

	public function transaction_success_process( $response ) {
		// TODO: Implement transaction_success_process() method.
	}
}
