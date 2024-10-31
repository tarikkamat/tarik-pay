import { decodeEntities } from '@wordpress/html-entities'

const Content = () => {
    return decodeEntities(settings.description)
}

const Label = () => {
    return (
        <span style={{ width: '100%' }}>
            {label}
            <Icon />
        </span>
    )
}

const Icon = () => {
    return settings.icon
        ? <img src={settings.icon} style={{ float: 'right', marginRight: '20px' }} />
        : ''
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