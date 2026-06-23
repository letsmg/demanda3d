import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import client992f46 from './client'
/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::store
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:58
* @route '/login'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::store
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:58
* @route '/login'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::store
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:58
* @route '/login'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::store
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:58
* @route '/login'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::store
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:58
* @route '/login'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
export const client = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: client.url(options),
    method: 'get',
})

client.definition = {
    methods: ["get","head"],
    url: '/login_cli',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
client.url = (options?: RouteQueryOptions) => {
    return client.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
client.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: client.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
client.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: client.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
const clientForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: client.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
clientForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: client.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::client
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
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

const login = {
    store: Object.assign(store, store),
    client: Object.assign(client, client992f46),
}

export default login