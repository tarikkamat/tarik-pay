import { useState, useEffect } from '@wordpress/element'
import ConnectionSettings from './ConnectionSettings'
import DashboardSettings from './DashboardSettings'

const Settings = () => {

    const [environment, setEnvironment] = useState('')
    const [apiKey, setApiKey] = useState('')
    const [secretKey, setSecretKey] = useState('')

    useEffect(() => {
        if (environment === 'sandbox') {
            setApiKey('sandbox-')
            setSecretKey('sandbox-')
        }
    }, [environment])

    return (
        <form>
            <div class="space-y-12">
                <ConnectionSettings
                    environment={environment}
                    setEnvironment={setEnvironment}
                    apiKey={apiKey}
                    setApiKey={setApiKey}
                    secretKey={secretKey}
                    setSecretKey={setSecretKey}
                />
                <DashboardSettings
                    environment={environment}
                    setEnvironment={setEnvironment}
                    apiKey={apiKey}
                    setApiKey={setApiKey}
                    secretKey={secretKey}
                    setSecretKey={setSecretKey}
                />
            </div>

            <div class="mt-6 flex items-center justify-end gap-x-6">
                <button type="button" class="text-sm font-semibold leading-6 text-gray-900">Cancel</button>
                <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
            </div>
        </form>
    )
}

export default Settings