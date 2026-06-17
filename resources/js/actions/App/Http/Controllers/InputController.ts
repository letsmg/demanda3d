import {
    queryParams,
    type RouteQueryOptions,
    type RouteDefinition,
    type RouteFormDefinition,
    applyUrlDefaults,
} from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\InputController::index
 * @see app/Http/Controllers/InputController.php:16
 * @route '/api/inputs'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/api/inputs',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\InputController::index
 * @see app/Http/Controllers/InputController.php:16
 * @route '/api/inputs'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\InputController::index
 * @see app/Http/Controllers/InputController.php:16
 * @route '/api/inputs'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\InputController::index
 * @see app/Http/Controllers/InputController.php:16
 * @route '/api/inputs'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\InputController::index
 * @see app/Http/Controllers/InputController.php:16
 * @route '/api/inputs'
 */
const indexForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\InputController::index
 * @see app/Http/Controllers/InputController.php:16
 * @route '/api/inputs'
 */
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\InputController::index
 * @see app/Http/Controllers/InputController.php:16
 * @route '/api/inputs'
 */
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

index.form = indexForm;

/**
 * @see \App\Http\Controllers\InputController::store
 * @see app/Http/Controllers/InputController.php:31
 * @route '/api/inputs'
 */
export const store = (
    options?: RouteQueryOptions,
): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/api/inputs',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\InputController::store
 * @see app/Http/Controllers/InputController.php:31
 * @route '/api/inputs'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\InputController::store
 * @see app/Http/Controllers/InputController.php:31
 * @route '/api/inputs'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\InputController::store
 * @see app/Http/Controllers/InputController.php:31
 * @route '/api/inputs'
 */
const storeForm = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\InputController::store
 * @see app/Http/Controllers/InputController.php:31
 * @route '/api/inputs'
 */
storeForm.post = (
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
});

store.form = storeForm;

/**
 * @see \App\Http\Controllers\InputController::show
 * @see app/Http/Controllers/InputController.php:24
 * @route '/api/inputs/{input}'
 */
export const show = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});

show.definition = {
    methods: ['get', 'head'],
    url: '/api/inputs/{input}',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\InputController::show
 * @see app/Http/Controllers/InputController.php:24
 * @route '/api/inputs/{input}'
 */
show.url = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { input: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { input: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            input: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        input: typeof args.input === 'object' ? args.input.id : args.input,
    };

    return (
        show.definition.url
            .replace('{input}', parsedArgs.input.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\InputController::show
 * @see app/Http/Controllers/InputController.php:24
 * @route '/api/inputs/{input}'
 */
show.get = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\InputController::show
 * @see app/Http/Controllers/InputController.php:24
 * @route '/api/inputs/{input}'
 */
show.head = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\InputController::show
 * @see app/Http/Controllers/InputController.php:24
 * @route '/api/inputs/{input}'
 */
const showForm = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\InputController::show
 * @see app/Http/Controllers/InputController.php:24
 * @route '/api/inputs/{input}'
 */
showForm.get = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\InputController::show
 * @see app/Http/Controllers/InputController.php:24
 * @route '/api/inputs/{input}'
 */
showForm.head = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'get',
});

show.form = showForm;

/**
 * @see \App\Http\Controllers\InputController::update
 * @see app/Http/Controllers/InputController.php:39
 * @route '/api/inputs/{input}'
 */
export const update = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
});

update.definition = {
    methods: ['put', 'patch'],
    url: '/api/inputs/{input}',
} satisfies RouteDefinition<['put', 'patch']>;

/**
 * @see \App\Http\Controllers\InputController::update
 * @see app/Http/Controllers/InputController.php:39
 * @route '/api/inputs/{input}'
 */
update.url = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { input: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { input: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            input: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        input: typeof args.input === 'object' ? args.input.id : args.input,
    };

    return (
        update.definition.url
            .replace('{input}', parsedArgs.input.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\InputController::update
 * @see app/Http/Controllers/InputController.php:39
 * @route '/api/inputs/{input}'
 */
update.put = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
});

/**
 * @see \App\Http\Controllers\InputController::update
 * @see app/Http/Controllers/InputController.php:39
 * @route '/api/inputs/{input}'
 */
update.patch = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
});

/**
 * @see \App\Http\Controllers\InputController::update
 * @see app/Http/Controllers/InputController.php:39
 * @route '/api/inputs/{input}'
 */
const updateForm = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\InputController::update
 * @see app/Http/Controllers/InputController.php:39
 * @route '/api/inputs/{input}'
 */
updateForm.put = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\InputController::update
 * @see app/Http/Controllers/InputController.php:39
 * @route '/api/inputs/{input}'
 */
updateForm.patch = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

update.form = updateForm;

/**
 * @see \App\Http\Controllers\InputController::destroy
 * @see app/Http/Controllers/InputController.php:47
 * @route '/api/inputs/{input}'
 */
export const destroy = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
});

destroy.definition = {
    methods: ['delete'],
    url: '/api/inputs/{input}',
} satisfies RouteDefinition<['delete']>;

/**
 * @see \App\Http\Controllers\InputController::destroy
 * @see app/Http/Controllers/InputController.php:47
 * @route '/api/inputs/{input}'
 */
destroy.url = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { input: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { input: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            input: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        input: typeof args.input === 'object' ? args.input.id : args.input,
    };

    return (
        destroy.definition.url
            .replace('{input}', parsedArgs.input.toString())
            .replace(/\/+$/, '') + queryParams(options)
    );
};

/**
 * @see \App\Http\Controllers\InputController::destroy
 * @see app/Http/Controllers/InputController.php:47
 * @route '/api/inputs/{input}'
 */
destroy.delete = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
});

/**
 * @see \App\Http\Controllers\InputController::destroy
 * @see app/Http/Controllers/InputController.php:47
 * @route '/api/inputs/{input}'
 */
const destroyForm = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

/**
 * @see \App\Http\Controllers\InputController::destroy
 * @see app/Http/Controllers/InputController.php:47
 * @route '/api/inputs/{input}'
 */
destroyForm.delete = (
    args:
        | { input: number | { id: number } }
        | [input: number | { id: number }]
        | number
        | { id: number },
    options?: RouteQueryOptions,
): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        },
    }),
    method: 'post',
});

destroy.form = destroyForm;

const InputController = { index, store, show, update, destroy };

export default InputController;
