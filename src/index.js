import PwiOptions from "./pwi";
import CheckoutOptions from "./checkout";

const {registerPaymentMethod} = window.wc.wcBlocksRegistry

if (PwiOptions.ariaLabel !== undefined) {
    registerPaymentMethod(PwiOptions)
}

if (CheckoutOptions.ariaLabel !== undefined) {
    registerPaymentMethod(CheckoutOptions)
}
