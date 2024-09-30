import { SelectControl, TextControl } from '@wordpress/components'

const ConnectionSettings = ({ environment, setEnvironment, apiKey, setApiKey, secretKey, setSecretKey }) => {
    return (
        <div className="border-b border-gray-900/10 pb-12">
            <h2 className="text-base font-semibold leading-7 text-gray-900">
                Bağlantı Ayarları
            </h2>
            <p className="mt-1 text-sm leading-6 text-gray-600">
                Bu kısımdan iyzico hesabınızı bağlayabilir ve ödeme ayarlarınızı yönetebilirsiniz.
            </p>
            <div className="settings-form mt-5">
                <SelectControl
                    label="Ortam Seçimi"
                    value={environment}
                    options={[
                        { label: 'Seçiniz', value: '' },
                        { label: 'Canlı', value: 'live' },
                        { label: 'Sandbox', value: 'sandbox' },
                    ]}
                    onChange={(value) => setEnvironment(value)}
                />
                <TextControl
                    value={apiKey}
                    label="API KEY"
                    type="text"
                    onChange={(value) => setApiKey(value)}
                />
                <TextControl
                    value={secretKey}
                    label="SECRET KEY"
                    type="password"
                    onChange={(value) => setSecretKey(value)}
                />
            </div>
        </div>
    )
}

export default ConnectionSettings