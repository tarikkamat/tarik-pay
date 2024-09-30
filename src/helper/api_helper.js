import apiFetch from '@wordpress/api-fetch';

const BASE_HEADERS = {
    'X-WP-Nonce': iyzicoRestApi.nonce
};

export const fetchData = async (url) => {
    try {
        const response = await apiFetch({
            path: url,
            method: 'GET',
            headers: BASE_HEADERS
        });
        return response;
    } catch (error) {
        console.error('Error: ', error);
        throw error;
    }
};

export const getOrders = async (page = 1, perPage = 50, search = '', status = '') => {
    try {
        const queryParams = new URLSearchParams({
            page: page.toString(),
            per_page: perPage.toString(),
        });

        if (search) {
            queryParams.append('search', search);
        }
        if (status && status !== 'all') {
            queryParams.append('status', status);
        }

        const response = await apiFetch({
            path: `${iyzicoRestApi.GetOrdersUrl}?${queryParams.toString()}`,
            method: 'GET',
            headers: BASE_HEADERS
        });
        return response;
    } catch (error) {
        console.error('Error fetching orders:', error);
        return {
            orders: [],
            total: 0,
            total_pages: 0,
            current_page: 1
        };
    }
};

export const getSettingsDashboardWidgets = async () => {
    try {
        const response = await apiFetch({
            path: iyzicoRestApi.SettingsDashboardWidgetsUrl,
            method: 'GET',
            headers: BASE_HEADERS
        });
        return response;
    } catch (error) {
        console.error('Error fetching dashboard widgets:', error);
        return null;
    }
};

export const getSettingsDashboardCharts = async () => {
    try {
        const response = await apiFetch({
            path: iyzicoRestApi.SettingsDashboardChartsUrl,
            method: 'GET',
            headers: BASE_HEADERS
        });
        return response;
    } catch (error) {
        console.error('Error fetching dashboard charts:', error);
        return null;
    }
};

export const getLast30DaysRevenue = () => fetchData(iyzicoRestApi.Last30DaysTotalBalanceUrl);
export const getLastMonthOrdersByStatus = () => fetchData(iyzicoRestApi.LastMonthOrdersByStatusUrl);
export const getLocalizations = () => fetchData(iyzicoRestApi.LocalizationsUrl);