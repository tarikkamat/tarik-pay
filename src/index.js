import CheckoutOptions from './checkout'

const { registerPaymentMethod } = window.wc.wcBlocksRegistry

registerPaymentMethod(CheckoutOptions)
console.log('iyzico-woocommerce script loaded successfully.');
