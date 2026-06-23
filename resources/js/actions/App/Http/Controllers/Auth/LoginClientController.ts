import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\LoginClientController::destroy
* @see app/Http/Controllers/Auth/LoginClientController.php:43
* @route '/logout_cli'
*/
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: destroy.url(options),
    method: 'post',
})

destroy.definition = {
    methods: ["post"],
    url: '/logout_cli',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\LoginClientController::destroy
* @see app/Http/Controllers/Auth/LoginClientController.php:43
* @route '/logout_cli'
*/
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\LoginClientController::destroy
* @see app/Http/Controllers/Auth/LoginClientController.php:43
* @route '/logout_cli'
*/
destroy.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: destroy.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::destroy
* @see app/Http/Controllers/Auth/LoginClientController.php:43
* @route '/logout_cli'
*/
const destroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::destroy
* @see app/Http/Controllers/Auth/LoginClientController.php:43
* @route '/logout_cli'
*/
destroyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(options),
    method: 'post',
})

destroy.form = destroyForm

/**
* @see \App\Http\Controllers\Auth\LoginClientController::create
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/login_cli',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\LoginClientController::create
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\LoginClientController::create
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::create
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::create
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::create
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Auth\LoginClientController::create
* @see app/Http/Controllers/Auth/LoginClientController.php:14
* @route '/login_cli'
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

const LoginClientController = { destroy, create, store }

export default LoginClientController