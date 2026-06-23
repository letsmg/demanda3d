import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\CheckoutController::store
* @see app/Http/Controllers/CheckoutController.php:18
* @route '/checkout'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/checkout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CheckoutController::store
* @see app/Http/Controllers/CheckoutController.php:18
* @route '/checkout'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CheckoutController::store
* @see app/Http/Controllers/CheckoutController.php:18
* @route '/checkout'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CheckoutController::store
* @see app/Http/Controllers/CheckoutController.php:18
* @route '/checkout'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CheckoutController::store
* @see app/Http/Controllers/CheckoutController.php:18
* @route '/checkout'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\CheckoutController::success
* @see app/Http/Controllers/CheckoutController.php:73
* @route '/checkout/success'
*/
export const success = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(options),
    method: 'get',
})

success.definition = {
    methods: ["get","head"],
    url: '/checkout/success',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CheckoutController::success
* @see app/Http/Controllers/CheckoutController.php:73
* @route '/checkout/success'
*/
success.url = (options?: RouteQueryOptions) => {
    return success.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CheckoutController::success
* @see app/Http/Controllers/CheckoutController.php:73
* @route '/checkout/success'
*/
success.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CheckoutController::success
* @see app/Http/Controllers/CheckoutController.php:73
* @route '/checkout/success'
*/
success.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: success.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CheckoutController::success
* @see app/Http/Controllers/CheckoutController.php:73
* @route '/checkout/success'
*/
const successForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: success.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CheckoutController::success
* @see app/Http/Controllers/CheckoutController.php:73
* @route '/checkout/success'
*/
successForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: success.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CheckoutController::success
* @see app/Http/Controllers/CheckoutController.php:73
* @route '/checkout/success'
*/
successForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: success.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

success.form = successForm

/**
* @see \App\Http\Controllers\CheckoutController::cancel
* @see app/Http/Controllers/CheckoutController.php:103
* @route '/checkout/cancel'
*/
export const cancel = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cancel.url(options),
    method: 'get',
})

cancel.definition = {
    methods: ["get","head"],
    url: '/checkout/cancel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CheckoutController::cancel
* @see app/Http/Controllers/CheckoutController.php:103
* @route '/checkout/cancel'
*/
cancel.url = (options?: RouteQueryOptions) => {
    return cancel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CheckoutController::cancel
* @see app/Http/Controllers/CheckoutController.php:103
* @route '/checkout/cancel'
*/
cancel.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cancel.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CheckoutController::cancel
* @see app/Http/Controllers/CheckoutController.php:103
* @route '/checkout/cancel'
*/
cancel.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: cancel.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CheckoutController::cancel
* @see app/Http/Controllers/CheckoutController.php:103
* @route '/checkout/cancel'
*/
const cancelForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: cancel.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CheckoutController::cancel
* @see app/Http/Controllers/CheckoutController.php:103
* @route '/checkout/cancel'
*/
cancelForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: cancel.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CheckoutController::cancel
* @see app/Http/Controllers/CheckoutController.php:103
* @route '/checkout/cancel'
*/
cancelForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: cancel.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

cancel.form = cancelForm

const checkout = {
    store: Object.assign(store, store),
    success: Object.assign(success, success),
    cancel: Object.assign(cancel, cancel),
}

export default checkout