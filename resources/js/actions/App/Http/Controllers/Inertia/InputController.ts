import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Inertia\InputController::index
* @see app/Http/Controllers/Inertia/InputController.php:18
* @route '/inputs'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/inputs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Inertia\InputController::index
* @see app/Http/Controllers/Inertia/InputController.php:18
* @route '/inputs'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Inertia\InputController::index
* @see app/Http/Controllers/Inertia/InputController.php:18
* @route '/inputs'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::index
* @see app/Http/Controllers/Inertia/InputController.php:18
* @route '/inputs'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::index
* @see app/Http/Controllers/Inertia/InputController.php:18
* @route '/inputs'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::index
* @see app/Http/Controllers/Inertia/InputController.php:18
* @route '/inputs'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::index
* @see app/Http/Controllers/Inertia/InputController.php:18
* @route '/inputs'
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
* @see \App\Http\Controllers\Inertia\InputController::create
* @see app/Http/Controllers/Inertia/InputController.php:29
* @route '/inputs/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/inputs/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Inertia\InputController::create
* @see app/Http/Controllers/Inertia/InputController.php:29
* @route '/inputs/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Inertia\InputController::create
* @see app/Http/Controllers/Inertia/InputController.php:29
* @route '/inputs/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::create
* @see app/Http/Controllers/Inertia/InputController.php:29
* @route '/inputs/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::create
* @see app/Http/Controllers/Inertia/InputController.php:29
* @route '/inputs/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::create
* @see app/Http/Controllers/Inertia/InputController.php:29
* @route '/inputs/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::create
* @see app/Http/Controllers/Inertia/InputController.php:29
* @route '/inputs/create'
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
* @see \App\Http\Controllers\Inertia\InputController::store
* @see app/Http/Controllers/Inertia/InputController.php:34
* @route '/inputs'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/inputs',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Inertia\InputController::store
* @see app/Http/Controllers/Inertia/InputController.php:34
* @route '/inputs'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Inertia\InputController::store
* @see app/Http/Controllers/Inertia/InputController.php:34
* @route '/inputs'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::store
* @see app/Http/Controllers/Inertia/InputController.php:34
* @route '/inputs'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::store
* @see app/Http/Controllers/Inertia/InputController.php:34
* @route '/inputs'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Inertia\InputController::edit
* @see app/Http/Controllers/Inertia/InputController.php:42
* @route '/inputs/{input}/edit'
*/
export const edit = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/inputs/{input}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Inertia\InputController::edit
* @see app/Http/Controllers/Inertia/InputController.php:42
* @route '/inputs/{input}/edit'
*/
edit.url = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { input: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { input: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            input: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        input: typeof args.input === 'object'
        ? args.input.id
        : args.input,
    }

    return edit.definition.url
            .replace('{input}', parsedArgs.input.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Inertia\InputController::edit
* @see app/Http/Controllers/Inertia/InputController.php:42
* @route '/inputs/{input}/edit'
*/
edit.get = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::edit
* @see app/Http/Controllers/Inertia/InputController.php:42
* @route '/inputs/{input}/edit'
*/
edit.head = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::edit
* @see app/Http/Controllers/Inertia/InputController.php:42
* @route '/inputs/{input}/edit'
*/
const editForm = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::edit
* @see app/Http/Controllers/Inertia/InputController.php:42
* @route '/inputs/{input}/edit'
*/
editForm.get = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::edit
* @see app/Http/Controllers/Inertia/InputController.php:42
* @route '/inputs/{input}/edit'
*/
editForm.head = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\Inertia\InputController::update
* @see app/Http/Controllers/Inertia/InputController.php:49
* @route '/inputs/{input}'
*/
export const update = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/inputs/{input}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Inertia\InputController::update
* @see app/Http/Controllers/Inertia/InputController.php:49
* @route '/inputs/{input}'
*/
update.url = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { input: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { input: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            input: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        input: typeof args.input === 'object'
        ? args.input.id
        : args.input,
    }

    return update.definition.url
            .replace('{input}', parsedArgs.input.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Inertia\InputController::update
* @see app/Http/Controllers/Inertia/InputController.php:49
* @route '/inputs/{input}'
*/
update.put = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::update
* @see app/Http/Controllers/Inertia/InputController.php:49
* @route '/inputs/{input}'
*/
const updateForm = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::update
* @see app/Http/Controllers/Inertia/InputController.php:49
* @route '/inputs/{input}'
*/
updateForm.put = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Inertia\InputController::destroy
* @see app/Http/Controllers/Inertia/InputController.php:57
* @route '/inputs/{input}'
*/
export const destroy = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/inputs/{input}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Inertia\InputController::destroy
* @see app/Http/Controllers/Inertia/InputController.php:57
* @route '/inputs/{input}'
*/
destroy.url = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { input: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { input: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            input: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        input: typeof args.input === 'object'
        ? args.input.id
        : args.input,
    }

    return destroy.definition.url
            .replace('{input}', parsedArgs.input.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Inertia\InputController::destroy
* @see app/Http/Controllers/Inertia/InputController.php:57
* @route '/inputs/{input}'
*/
destroy.delete = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::destroy
* @see app/Http/Controllers/Inertia/InputController.php:57
* @route '/inputs/{input}'
*/
const destroyForm = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Inertia\InputController::destroy
* @see app/Http/Controllers/Inertia/InputController.php:57
* @route '/inputs/{input}'
*/
destroyForm.delete = (args: { input: number | { id: number } } | [input: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const InputController = { index, create, store, edit, update, destroy }

export default InputController