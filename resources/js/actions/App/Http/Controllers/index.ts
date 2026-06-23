import ClientController from './ClientController'
import OrderController from './OrderController'
import InputController from './InputController'
import StoreController from './StoreController'
import Auth from './Auth'
import CartController from './CartController'
import CheckoutController from './CheckoutController'
import ClientProfileController from './ClientProfileController'
import DashboardController from './DashboardController'
import Inertia from './Inertia'
import Settings from './Settings'

const Controllers = {
    ClientController: Object.assign(ClientController, ClientController),
    OrderController: Object.assign(OrderController, OrderController),
    InputController: Object.assign(InputController, InputController),
    StoreController: Object.assign(StoreController, StoreController),
    Auth: Object.assign(Auth, Auth),
    CartController: Object.assign(CartController, CartController),
    CheckoutController: Object.assign(CheckoutController, CheckoutController),
    ClientProfileController: Object.assign(ClientProfileController, ClientProfileController),
    DashboardController: Object.assign(DashboardController, DashboardController),
    Inertia: Object.assign(Inertia, Inertia),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers