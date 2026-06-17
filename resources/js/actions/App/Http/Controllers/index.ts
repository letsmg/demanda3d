import ClientController from './ClientController'
import OrderController from './OrderController'
import InputController from './InputController'
import DashboardController from './DashboardController'
import Inertia from './Inertia'
import Settings from './Settings'

const Controllers = {
    ClientController: Object.assign(ClientController, ClientController),
    OrderController: Object.assign(OrderController, OrderController),
    InputController: Object.assign(InputController, InputController),
    DashboardController: Object.assign(DashboardController, DashboardController),
    Inertia: Object.assign(Inertia, Inertia),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers