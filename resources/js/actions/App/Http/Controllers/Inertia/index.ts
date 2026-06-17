import ClientController from './ClientController';
import OrderController from './OrderController';
import InputController from './InputController';

const Inertia = {
    ClientController: Object.assign(ClientController, ClientController),
    OrderController: Object.assign(OrderController, OrderController),
    InputController: Object.assign(InputController, InputController),
};

export default Inertia;
