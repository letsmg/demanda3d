import LoginClientController from './LoginClientController'
import RegisterClientController from './RegisterClientController'

const Auth = {
    LoginClientController: Object.assign(LoginClientController, LoginClientController),
    RegisterClientController: Object.assign(RegisterClientController, RegisterClientController),
}

export default Auth