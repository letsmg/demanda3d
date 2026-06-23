import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\RegisterClientController::create
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/register_cli',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::create
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::create
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::create
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::create
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::create
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::create
* @see app/Http/Controllers/Auth/RegisterClientController.php:16
* @route '/register_cli'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::store
* @see app/Http/Controllers/Auth/RegisterClientController.php:21
* @route '/register_cli'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/register_cli',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::store
* @see app/Http/Controllers/Auth/RegisterClientController.php:21
* @route '/register_cli'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::store
* @see app/Http/Controllers/Auth/RegisterClientController.php:21
* @route '/register_cli'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::store
* @see app/Http/Controllers/Auth/RegisterClientController.php:21
* @route '/register_cli'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\RegisterClientController::store
* @see app/Http/Controllers/Auth/RegisterClientController.php:21
* @route '/register_cli'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const RegisterClientController = { create, store }

export default RegisterClientController