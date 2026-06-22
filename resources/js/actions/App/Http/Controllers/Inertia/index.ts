import ClientController from './ClientController'
import OrderController from './OrderController'
import InputController from './InputController'
import ProductController from './ProductController'

const Inertia = {
    ClientController: Object.assign(ClientController, ClientController),
    OrderController: Object.assign(OrderController, OrderController),
    InputController: Object.assign(InputController, InputController),
    ProductController: Object.assign(ProductController, ProductController),
}

export default Inertia