import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\LoginClientController::store
* @see app/Http/Controllers/Auth/LoginClientController.php:19
* @route '/login_cli'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/login_cli',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\LoginClientController::store
* @see app/Http/Controllers/Auth/LoginClientController.php:19
* @route '/login_cli'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\LoginClientController::store
* @see app/Http/Controllers/Auth/LoginClientController.php:19
* @route '/login_cli'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::store
* @see app/Http/Controllers/Auth/LoginClientController.php:19
* @route '/login_cli'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::store
* @see app/Http/Controllers/Auth/LoginClientController.php:19
* @route '/login_cli'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const client = {
    store: Object.assign(store, store),
}

export default client