import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\OrderController::byClient
 * @see app/Http/Controllers/OrderController.php:55
 * @route '/api/clients/{clientId}/orders'
 */
export const byClient = (
    args:
        | { clientId: string | number }
        | [clientId: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: byClient.url(args, options),
    method: 'get',
});

byClient.definition = {
    methods: ['get', 'head'],
    url: '/api/clients/{clientId}/orders',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\OrderController::byClient
 * @see app/Http/Controllers/OrderController.php:55
 * @route '/api/clients/{clientId}/orders'
 */
byClient.url = (
    args:
        | { clientId: string | number }
        | [clientId: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { clientId: args };
    }

    if (Array.isArray(args)) {
        args = {
            clientId: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        clientId: args.clientId,
    };

    return (
        byClient.definition.url
            .replace('{clientId}', parsedArgs.clientId.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\OrderController::byClient
 * @see app/Http/Controllers/OrderController.php:55
 * @route '/api/clients/{clientId}/orders'
 */
byClient.get = (
    args:
        | { clientId: string | number }
        | [clientId: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: byClient.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\OrderController::byClient
 * @see app/Http/Controllers/OrderController.php:55
 * @route '/api/clients/{clientId}/orders'
 */
byClient.head = (
    args:
        | { clientId: string | number }
        | [clientId: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: byClient.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\OrderController::byClient
 * @see app/Http/Controllers/OrderController.php:55
 * @route '/api/clients/{clientId}/orders'
 */
const byClientForm = (
    args:
        | { clientId: string | number }
        | [clientId: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: byClient.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\OrderController::byClient
 * @see app/Http/Controllers/OrderController.php:55
 * @route '/api/clients/{clientId}/orders'
 */
byClientForm.get = (
    args:
        | { clientId: string | number }
        | [clientId: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: byClient.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\OrderController::byClient
 * @see app/Http/Controllers/OrderController.php:55
 * @route '/api/clients/{clientId}/orders'
 */
byClientForm.head = (
    args:
        | { clientId: string | number }
        | [clientId: string | number]
        | string
        | number,
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: byClient.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

byClient.form = byClientForm;

const orders = {
    byClient: Object.assign(byClient, byClient),
};

export default orders;
