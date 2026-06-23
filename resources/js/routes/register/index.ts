import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import client992f46 from './client'
/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::store
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:53
* @route '/register'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::store
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:53
* @route '/register'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::store
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:53
* @route '/register'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::store
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:53
* @route '/register'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\RegisteredUserController::store
* @see vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:53
* @route '/register'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::client
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
export const client = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: client.url(options),
    method: 'get',
})

client.definition = {
    methods: ["get","head"],
    url: '/register_cli',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::client
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
client.url = (options?: RouteQueryOptions) => {
    return client.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::client
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
client.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: client.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::client
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
client.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: client.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::client
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
const clientForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: client.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::client
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
clientForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: client.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::client
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
clientForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: client.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

client.form = clientForm

const register = {
    store: Object.assign(store, store),
    client: Object.assign(client, client992f46),
}

export default register