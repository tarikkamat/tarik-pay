import CheckoutOptions from './checkout'
import PwiOptions from "./pwi";

const { registerPaymentMethod } = window.wc.wcBlocksRegistry

registerPaymentMethod(CheckoutOptions)
registerPaymentMethod(PwiOptions)