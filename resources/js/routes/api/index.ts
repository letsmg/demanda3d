import clients from './clients';
import orders from './orders';
import api from './api';
import inputs from './inputs';

const apiNamespace = {
    clients: Object.assign(clients, clients),
    orders: Object.assign(orders, orders),
    api: Object.assign(api, api),
    inputs: Object.assign(inputs, inputs),
};

export default apiNamespace;
