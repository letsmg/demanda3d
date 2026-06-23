import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\ClientProfileController::update
* @see app/Http/Controllers/ClientProfileController.php:56
* @route '/perfil/enderecos'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/perfil/enderecos',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\ClientProfileController::update
* @see app/Http/Controllers/ClientProfileController.php:56
* @route '/perfil/enderecos'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientProfileController::update
* @see app/Http/Controllers/ClientProfileController.php:56
* @route '/perfil/enderecos'
*/
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\ClientProfileController::update
* @see app/Http/Controllers/ClientProfileController.php:56
* @route '/perfil/enderecos'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ClientProfileController::update
* @see app/Http/Controllers/ClientProfileController.php:56
* @route '/perfil/enderecos'
*/
updateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const addresses = {
    update: Object.assign(update, update),
}

export default addresses