import { decodeEntities } from '@wordpress/html-entities'

const Content = () => {
    return decodeEntities(settings.description || '')
}

const Label = (props) => {
    const { PaymentMethodLabel } = props.components
    return <PaymentMethodLabel text={label} />
}

const { getSetting } = window.wc.wcSettings
const settings = getSetting('iyzico_data', {})
const label = decodeEntities(settings.title)

const CheckoutOptions = {
    name: "iyzico",
    label: <Label />,
    content: <Content />,
    edit: <Content />,
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    }
}

export default CheckoutOptions