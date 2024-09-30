import { ToggleControl } from '@wordpress/components'

const DashboardSettings = ({ environment, setEnvironment, apiKey, setApiKey, secretKey, setSecretKey }) => {
    return (
        <div className="border-b border-gray-900/10 pb-12">
            <h2 className="text-base font-semibold leading-7 text-gray-900">
                Gösterge Paneli Ayarları
            </h2>
            <p className="mt-1 text-sm leading-6 text-gray-600">
                Bu kısımdan eklentinizin gösterge paneli ayarlarınızı yönetebilirsiniz.
            </p>
            <div className="settings-form mt-5">
                <ToggleControl
                    label="Fixed Background"
                    help={true ? 'Has fixed background.' : 'No fixed background.'}
                    checked={true}
                    onChange={(e) => console.log(e)}
                />
            </div>
        </div>
    )
}

export default DashboardSettings