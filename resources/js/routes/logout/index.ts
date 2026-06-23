import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:43
* @route '/logout_cli'
*/
export const client = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: client.url(options),
    method: 'post',
})

client.definition = {
    methods: ["post"],
    url: '/logout_cli',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:43
* @route '/logout_cli'
*/
client.url = (options?: RouteQueryOptions) => {
    return client.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:43
* @route '/logout_cli'
*/
client.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: client.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:43
* @route '/logout_cli'
*/
const clientForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: client.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:43
* @route '/logout_cli'
*/
clientForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: client.url(options),
    method: 'post',
})

client.form = clientForm

const logout = {
    client: Object.assign(client, client),
}

export default logout