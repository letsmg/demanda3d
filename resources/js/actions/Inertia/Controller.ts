import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
const Controller980bb49ee7ae63891f1d891d2fbcf1c9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})

Controller980bb49ee7ae63891f1d891d2fbcf1c9.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
Controller980bb49ee7ae63891f1d891d2fbcf1c9.url = (options?: RouteQueryOptions) => {
    return Controller980bb49ee7ae63891f1d891d2fbcf1c9.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
Controller980bb49ee7ae63891f1d891d2fbcf1c9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
Controller980bb49ee7ae63891f1d891d2fbcf1c9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
const Controller980bb49ee7ae63891f1d891d2fbcf1c9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
Controller980bb49ee7ae63891f1d891d2fbcf1c9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
Controller980bb49ee7ae63891f1d891d2fbcf1c9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controller980bb49ee7ae63891f1d891d2fbcf1c9.form = Controller980bb49ee7ae63891f1d891d2fbcf1c9Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients'
*/
const Controller5c268bd1cbc5dd5a03424a10190e8c18 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller5c268bd1cbc5dd5a03424a10190e8c18.url(options),
    method: 'get',
})

Controller5c268bd1cbc5dd5a03424a10190e8c18.definition = {
    methods: ["get","head"],
    url: '/clients',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients'
*/
Controller5c268bd1cbc5dd5a03424a10190e8c18.url = (options?: RouteQueryOptions) => {
    return Controller5c268bd1cbc5dd5a03424a10190e8c18.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients'
*/
Controller5c268bd1cbc5dd5a03424a10190e8c18.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller5c268bd1cbc5dd5a03424a10190e8c18.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients'
*/
Controller5c268bd1cbc5dd5a03424a10190e8c18.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller5c268bd1cbc5dd5a03424a10190e8c18.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients'
*/
const Controller5c268bd1cbc5dd5a03424a10190e8c18Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller5c268bd1cbc5dd5a03424a10190e8c18.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients'
*/
Controller5c268bd1cbc5dd5a03424a10190e8c18Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller5c268bd1cbc5dd5a03424a10190e8c18.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients'
*/
Controller5c268bd1cbc5dd5a03424a10190e8c18Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller5c268bd1cbc5dd5a03424a10190e8c18.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controller5c268bd1cbc5dd5a03424a10190e8c18.form = Controller5c268bd1cbc5dd5a03424a10190e8c18Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/create'
*/
const Controller55205497ab781e0c0bcb42db60644e8e = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller55205497ab781e0c0bcb42db60644e8e.url(options),
    method: 'get',
})

Controller55205497ab781e0c0bcb42db60644e8e.definition = {
    methods: ["get","head"],
    url: '/clients/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/create'
*/
Controller55205497ab781e0c0bcb42db60644e8e.url = (options?: RouteQueryOptions) => {
    return Controller55205497ab781e0c0bcb42db60644e8e.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/create'
*/
Controller55205497ab781e0c0bcb42db60644e8e.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller55205497ab781e0c0bcb42db60644e8e.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/create'
*/
Controller55205497ab781e0c0bcb42db60644e8e.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller55205497ab781e0c0bcb42db60644e8e.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/create'
*/
const Controller55205497ab781e0c0bcb42db60644e8eForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller55205497ab781e0c0bcb42db60644e8e.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/create'
*/
Controller55205497ab781e0c0bcb42db60644e8eForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller55205497ab781e0c0bcb42db60644e8e.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/create'
*/
Controller55205497ab781e0c0bcb42db60644e8eForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller55205497ab781e0c0bcb42db60644e8e.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controller55205497ab781e0c0bcb42db60644e8e.form = Controller55205497ab781e0c0bcb42db60644e8eForm
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/{client}/edit'
*/
const Controllerc095ebdaaeefca9ea0ff052b43500226 = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerc095ebdaaeefca9ea0ff052b43500226.url(args, options),
    method: 'get',
})

Controllerc095ebdaaeefca9ea0ff052b43500226.definition = {
    methods: ["get","head"],
    url: '/clients/{client}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/{client}/edit'
*/
Controllerc095ebdaaeefca9ea0ff052b43500226.url = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { client: args }
    }

    if (Array.isArray(args)) {
        args = {
            client: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        client: args.client,
    }

    return Controllerc095ebdaaeefca9ea0ff052b43500226.definition.url
            .replace('{client}', parsedArgs.client.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/{client}/edit'
*/
Controllerc095ebdaaeefca9ea0ff052b43500226.get = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerc095ebdaaeefca9ea0ff052b43500226.url(args, options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/{client}/edit'
*/
Controllerc095ebdaaeefca9ea0ff052b43500226.head = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerc095ebdaaeefca9ea0ff052b43500226.url(args, options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/{client}/edit'
*/
const Controllerc095ebdaaeefca9ea0ff052b43500226Form = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerc095ebdaaeefca9ea0ff052b43500226.url(args, options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/{client}/edit'
*/
Controllerc095ebdaaeefca9ea0ff052b43500226Form.get = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerc095ebdaaeefca9ea0ff052b43500226.url(args, options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/clients/{client}/edit'
*/
Controllerc095ebdaaeefca9ea0ff052b43500226Form.head = (args: { client: string | number } | [client: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerc095ebdaaeefca9ea0ff052b43500226.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controllerc095ebdaaeefca9ea0ff052b43500226.form = Controllerc095ebdaaeefca9ea0ff052b43500226Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders'
*/
const Controller46d571d7fe903e8a2eecb1a2ccbb23f8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller46d571d7fe903e8a2eecb1a2ccbb23f8.url(options),
    method: 'get',
})

Controller46d571d7fe903e8a2eecb1a2ccbb23f8.definition = {
    methods: ["get","head"],
    url: '/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders'
*/
Controller46d571d7fe903e8a2eecb1a2ccbb23f8.url = (options?: RouteQueryOptions) => {
    return Controller46d571d7fe903e8a2eecb1a2ccbb23f8.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders'
*/
Controller46d571d7fe903e8a2eecb1a2ccbb23f8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller46d571d7fe903e8a2eecb1a2ccbb23f8.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders'
*/
Controller46d571d7fe903e8a2eecb1a2ccbb23f8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller46d571d7fe903e8a2eecb1a2ccbb23f8.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders'
*/
const Controller46d571d7fe903e8a2eecb1a2ccbb23f8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller46d571d7fe903e8a2eecb1a2ccbb23f8.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders'
*/
Controller46d571d7fe903e8a2eecb1a2ccbb23f8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller46d571d7fe903e8a2eecb1a2ccbb23f8.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders'
*/
Controller46d571d7fe903e8a2eecb1a2ccbb23f8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller46d571d7fe903e8a2eecb1a2ccbb23f8.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controller46d571d7fe903e8a2eecb1a2ccbb23f8.form = Controller46d571d7fe903e8a2eecb1a2ccbb23f8Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/create'
*/
const Controllerfbd9cd50a33bebda657ed84438529dd7 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerfbd9cd50a33bebda657ed84438529dd7.url(options),
    method: 'get',
})

Controllerfbd9cd50a33bebda657ed84438529dd7.definition = {
    methods: ["get","head"],
    url: '/orders/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/create'
*/
Controllerfbd9cd50a33bebda657ed84438529dd7.url = (options?: RouteQueryOptions) => {
    return Controllerfbd9cd50a33bebda657ed84438529dd7.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/create'
*/
Controllerfbd9cd50a33bebda657ed84438529dd7.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerfbd9cd50a33bebda657ed84438529dd7.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/create'
*/
Controllerfbd9cd50a33bebda657ed84438529dd7.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerfbd9cd50a33bebda657ed84438529dd7.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/create'
*/
const Controllerfbd9cd50a33bebda657ed84438529dd7Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerfbd9cd50a33bebda657ed84438529dd7.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/create'
*/
Controllerfbd9cd50a33bebda657ed84438529dd7Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerfbd9cd50a33bebda657ed84438529dd7.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/create'
*/
Controllerfbd9cd50a33bebda657ed84438529dd7Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerfbd9cd50a33bebda657ed84438529dd7.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controllerfbd9cd50a33bebda657ed84438529dd7.form = Controllerfbd9cd50a33bebda657ed84438529dd7Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/{order}/edit'
*/
const Controller998309f13d1ce56f3f663df6f2bdf54c = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller998309f13d1ce56f3f663df6f2bdf54c.url(args, options),
    method: 'get',
})

Controller998309f13d1ce56f3f663df6f2bdf54c.definition = {
    methods: ["get","head"],
    url: '/orders/{order}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/{order}/edit'
*/
Controller998309f13d1ce56f3f663df6f2bdf54c.url = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

    if (Array.isArray(args)) {
        args = {
            order: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        order: args.order,
    }

    return Controller998309f13d1ce56f3f663df6f2bdf54c.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/{order}/edit'
*/
Controller998309f13d1ce56f3f663df6f2bdf54c.get = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller998309f13d1ce56f3f663df6f2bdf54c.url(args, options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/{order}/edit'
*/
Controller998309f13d1ce56f3f663df6f2bdf54c.head = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller998309f13d1ce56f3f663df6f2bdf54c.url(args, options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/{order}/edit'
*/
const Controller998309f13d1ce56f3f663df6f2bdf54cForm = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller998309f13d1ce56f3f663df6f2bdf54c.url(args, options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/{order}/edit'
*/
Controller998309f13d1ce56f3f663df6f2bdf54cForm.get = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller998309f13d1ce56f3f663df6f2bdf54c.url(args, options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/orders/{order}/edit'
*/
Controller998309f13d1ce56f3f663df6f2bdf54cForm.head = (args: { order: string | number } | [order: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller998309f13d1ce56f3f663df6f2bdf54c.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controller998309f13d1ce56f3f663df6f2bdf54c.form = Controller998309f13d1ce56f3f663df6f2bdf54cForm
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs'
*/
const Controller298d02a4992deb0bda3143d379b71802 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller298d02a4992deb0bda3143d379b71802.url(options),
    method: 'get',
})

Controller298d02a4992deb0bda3143d379b71802.definition = {
    methods: ["get","head"],
    url: '/inputs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs'
*/
Controller298d02a4992deb0bda3143d379b71802.url = (options?: RouteQueryOptions) => {
    return Controller298d02a4992deb0bda3143d379b71802.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs'
*/
Controller298d02a4992deb0bda3143d379b71802.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller298d02a4992deb0bda3143d379b71802.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs'
*/
Controller298d02a4992deb0bda3143d379b71802.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller298d02a4992deb0bda3143d379b71802.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs'
*/
const Controller298d02a4992deb0bda3143d379b71802Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller298d02a4992deb0bda3143d379b71802.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs'
*/
Controller298d02a4992deb0bda3143d379b71802Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller298d02a4992deb0bda3143d379b71802.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs'
*/
Controller298d02a4992deb0bda3143d379b71802Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller298d02a4992deb0bda3143d379b71802.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controller298d02a4992deb0bda3143d379b71802.form = Controller298d02a4992deb0bda3143d379b71802Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/create'
*/
const Controllerbb328c9fbe5806069cb785c4c114f7a7 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerbb328c9fbe5806069cb785c4c114f7a7.url(options),
    method: 'get',
})

Controllerbb328c9fbe5806069cb785c4c114f7a7.definition = {
    methods: ["get","head"],
    url: '/inputs/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/create'
*/
Controllerbb328c9fbe5806069cb785c4c114f7a7.url = (options?: RouteQueryOptions) => {
    return Controllerbb328c9fbe5806069cb785c4c114f7a7.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/create'
*/
Controllerbb328c9fbe5806069cb785c4c114f7a7.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerbb328c9fbe5806069cb785c4c114f7a7.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/create'
*/
Controllerbb328c9fbe5806069cb785c4c114f7a7.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerbb328c9fbe5806069cb785c4c114f7a7.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/create'
*/
const Controllerbb328c9fbe5806069cb785c4c114f7a7Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerbb328c9fbe5806069cb785c4c114f7a7.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/create'
*/
Controllerbb328c9fbe5806069cb785c4c114f7a7Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerbb328c9fbe5806069cb785c4c114f7a7.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/create'
*/
Controllerbb328c9fbe5806069cb785c4c114f7a7Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerbb328c9fbe5806069cb785c4c114f7a7.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controllerbb328c9fbe5806069cb785c4c114f7a7.form = Controllerbb328c9fbe5806069cb785c4c114f7a7Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/{input}/edit'
*/
const Controller93a809c8a81e4ac67096c4cb4ba62b84 = (args: { input: string | number } | [input: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller93a809c8a81e4ac67096c4cb4ba62b84.url(args, options),
    method: 'get',
})

Controller93a809c8a81e4ac67096c4cb4ba62b84.definition = {
    methods: ["get","head"],
    url: '/inputs/{input}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/{input}/edit'
*/
Controller93a809c8a81e4ac67096c4cb4ba62b84.url = (args: { input: string | number } | [input: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { input: args }
    }

    if (Array.isArray(args)) {
        args = {
            input: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        input: args.input,
    }

    return Controller93a809c8a81e4ac67096c4cb4ba62b84.definition.url
            .replace('{input}', parsedArgs.input.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/{input}/edit'
*/
Controller93a809c8a81e4ac67096c4cb4ba62b84.get = (args: { input: string | number } | [input: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller93a809c8a81e4ac67096c4cb4ba62b84.url(args, options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/{input}/edit'
*/
Controller93a809c8a81e4ac67096c4cb4ba62b84.head = (args: { input: string | number } | [input: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller93a809c8a81e4ac67096c4cb4ba62b84.url(args, options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/{input}/edit'
*/
const Controller93a809c8a81e4ac67096c4cb4ba62b84Form = (args: { input: string | number } | [input: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller93a809c8a81e4ac67096c4cb4ba62b84.url(args, options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/{input}/edit'
*/
Controller93a809c8a81e4ac67096c4cb4ba62b84Form.get = (args: { input: string | number } | [input: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller93a809c8a81e4ac67096c4cb4ba62b84.url(args, options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/inputs/{input}/edit'
*/
Controller93a809c8a81e4ac67096c4cb4ba62b84Form.head = (args: { input: string | number } | [input: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller93a809c8a81e4ac67096c4cb4ba62b84.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controller93a809c8a81e4ac67096c4cb4ba62b84.form = Controller93a809c8a81e4ac67096c4cb4ba62b84Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
const Controllere19ee86e9cf603ce1a59a1ec5d21dec5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})

Controllere19ee86e9cf603ce1a59a1ec5d21dec5.definition = {
    methods: ["get","head"],
    url: '/settings/appearance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url = (options?: RouteQueryOptions) => {
    return Controllere19ee86e9cf603ce1a59a1ec5d21dec5.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
const Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/settings/appearance'
*/
Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controllere19ee86e9cf603ce1a59a1ec5d21dec5.form = Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form

/**
* Multiple routes resolve to \Inertia\Controller::Controller, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `Controller['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const Controller = {
    '/': Controller980bb49ee7ae63891f1d891d2fbcf1c9,
    '/clients': Controller5c268bd1cbc5dd5a03424a10190e8c18,
    '/clients/create': Controller55205497ab781e0c0bcb42db60644e8e,
    '/clients/{client}/edit': Controllerc095ebdaaeefca9ea0ff052b43500226,
    '/orders': Controller46d571d7fe903e8a2eecb1a2ccbb23f8,
    '/orders/create': Controllerfbd9cd50a33bebda657ed84438529dd7,
    '/orders/{order}/edit': Controller998309f13d1ce56f3f663df6f2bdf54c,
    '/inputs': Controller298d02a4992deb0bda3143d379b71802,
    '/inputs/create': Controllerbb328c9fbe5806069cb785c4c114f7a7,
    '/inputs/{input}/edit': Controller93a809c8a81e4ac67096c4cb4ba62b84,
    '/settings/appearance': Controllere19ee86e9cf603ce1a59a1ec5d21dec5,
}

export default Controller