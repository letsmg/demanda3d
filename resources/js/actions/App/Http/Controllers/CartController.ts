import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\CartController::show
* @see app/Http/Controllers/CartController.php:68
* @route '/cart'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/cart',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CartController::show
* @see app/Http/Controllers/CartController.php:68
* @route '/cart'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::show
* @see app/Http/Controllers/CartController.php:68
* @route '/cart'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CartController::show
* @see app/Http/Controllers/CartController.php:68
* @route '/cart'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CartController::show
* @see app/Http/Controllers/CartController.php:68
* @route '/cart'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CartController::show
* @see app/Http/Controllers/CartController.php:68
* @route '/cart'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CartController::show
* @see app/Http/Controllers/CartController.php:68
* @route '/cart'
*/
showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\CartController::index
* @see app/Http/Controllers/CartController.php:59
* @route '/cart/items'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/cart/items',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CartController::index
* @see app/Http/Controllers/CartController.php:59
* @route '/cart/items'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::index
* @see app/Http/Controllers/CartController.php:59
* @route '/cart/items'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CartController::index
* @see app/Http/Controllers/CartController.php:59
* @route '/cart/items'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\CartController::index
* @see app/Http/Controllers/CartController.php:59
* @route '/cart/items'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CartController::index
* @see app/Http/Controllers/CartController.php:59
* @route '/cart/items'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\CartController::index
* @see app/Http/Controllers/CartController.php:59
* @route '/cart/items'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\CartController::store
* @see app/Http/Controllers/CartController.php:77
* @route '/cart'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/cart',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CartController::store
* @see app/Http/Controllers/CartController.php:77
* @route '/cart'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::store
* @see app/Http/Controllers/CartController.php:77
* @route '/cart'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CartController::store
* @see app/Http/Controllers/CartController.php:77
* @route '/cart'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CartController::store
* @see app/Http/Controllers/CartController.php:77
* @route '/cart'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\CartController::update
* @see app/Http/Controllers/CartController.php:106
* @route '/cart/{cartItem}'
*/
export const update = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/cart/{cartItem}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\CartController::update
* @see app/Http/Controllers/CartController.php:106
* @route '/cart/{cartItem}'
*/
update.url = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { cartItem: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { cartItem: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            cartItem: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        cartItem: typeof args.cartItem === 'object'
        ? args.cartItem.id
        : args.cartItem,
    }

    return update.definition.url
            .replace('{cartItem}', parsedArgs.cartItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::update
* @see app/Http/Controllers/CartController.php:106
* @route '/cart/{cartItem}'
*/
update.put = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\CartController::update
* @see app/Http/Controllers/CartController.php:106
* @route '/cart/{cartItem}'
*/
const updateForm = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CartController::update
* @see app/Http/Controllers/CartController.php:106
* @route '/cart/{cartItem}'
*/
updateForm.put = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\CartController::destroy
* @see app/Http/Controllers/CartController.php:129
* @route '/cart/{cartItem}'
*/
export const destroy = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/cart/{cartItem}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\CartController::destroy
* @see app/Http/Controllers/CartController.php:129
* @route '/cart/{cartItem}'
*/
destroy.url = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { cartItem: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { cartItem: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            cartItem: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        cartItem: typeof args.cartItem === 'object'
        ? args.cartItem.id
        : args.cartItem,
    }

    return destroy.definition.url
            .replace('{cartItem}', parsedArgs.cartItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::destroy
* @see app/Http/Controllers/CartController.php:129
* @route '/cart/{cartItem}'
*/
destroy.delete = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\CartController::destroy
* @see app/Http/Controllers/CartController.php:129
* @route '/cart/{cartItem}'
*/
const destroyForm = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CartController::destroy
* @see app/Http/Controllers/CartController.php:129
* @route '/cart/{cartItem}'
*/
destroyForm.delete = (args: { cartItem: number | { id: number } } | [cartItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

/**
* @see \App\Http\Controllers\CartController::clear
* @see app/Http/Controllers/CartController.php:144
* @route '/cart/clear'
*/
export const clear = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

clear.definition = {
    methods: ["post"],
    url: '/cart/clear',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\CartController::clear
* @see app/Http/Controllers/CartController.php:144
* @route '/cart/clear'
*/
clear.url = (options?: RouteQueryOptions) => {
    return clear.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CartController::clear
* @see app/Http/Controllers/CartController.php:144
* @route '/cart/clear'
*/
clear.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CartController::clear
* @see app/Http/Controllers/CartController.php:144
* @route '/cart/clear'
*/
const clearForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: clear.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\CartController::clear
* @see app/Http/Controllers/CartController.php:144
* @route '/cart/clear'
*/
clearForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: clear.url(options),
    method: 'post',
})

clear.form = clearForm

const CartController = { show, index, store, update, destroy, clear }

export default CartController