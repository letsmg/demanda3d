import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import profile937a89 from './profile'
import addresses2498c9 from './addresses'
/**
* @see \App\Http\Controllers\ClientProfileController::profile
* @see app/Http/Controllers/ClientProfileController.php:25
* @route '/perfil'
*/
export const profile = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: profile.url(options),
    method: 'get',
})

profile.definition = {
    methods: ["get","head"],
    url: '/perfil',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ClientProfileController::profile
* @see app/Http/Controllers/ClientProfileController.php:25
* @route '/perfil'
*/
profile.url = (options?: RouteQueryOptions) => {
    return profile.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientProfileController::profile
* @see app/Http/Controllers/ClientProfileController.php:25
* @route '/perfil'
*/
profile.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: profile.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ClientProfileController::profile
* @see app/Http/Controllers/ClientProfileController.php:25
* @route '/perfil'
*/
profile.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: profile.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ClientProfileController::profile
* @see app/Http/Controllers/ClientProfileController.php:25
* @route '/perfil'
*/
const profileForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: profile.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ClientProfileController::profile
* @see app/Http/Controllers/ClientProfileController.php:25
* @route '/perfil'
*/
profileForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: profile.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ClientProfileController::profile
* @see app/Http/Controllers/ClientProfileController.php:25
* @route '/perfil'
*/
profileForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: profile.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

profile.form = profileForm

/**
* @see \App\Http\Controllers\ClientProfileController::addresses
* @see app/Http/Controllers/ClientProfileController.php:50
* @route '/perfil/enderecos'
*/
export const addresses = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: addresses.url(options),
    method: 'get',
})

addresses.definition = {
    methods: ["get","head"],
    url: '/perfil/enderecos',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ClientProfileController::addresses
* @see app/Http/Controllers/ClientProfileController.php:50
* @route '/perfil/enderecos'
*/
addresses.url = (options?: RouteQueryOptions) => {
    return addresses.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientProfileController::addresses
* @see app/Http/Controllers/ClientProfileController.php:50
* @route '/perfil/enderecos'
*/
addresses.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: addresses.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ClientProfileController::addresses
* @see app/Http/Controllers/ClientProfileController.php:50
* @route '/perfil/enderecos'
*/
addresses.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: addresses.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ClientProfileController::addresses
* @see app/Http/Controllers/ClientProfileController.php:50
* @route '/perfil/enderecos'
*/
const addressesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: addresses.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ClientProfileController::addresses
* @see app/Http/Controllers/ClientProfileController.php:50
* @route '/perfil/enderecos'
*/
addressesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: addresses.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\ClientProfileController::addresses
* @see app/Http/Controllers/ClientProfileController.php:50
* @route '/perfil/enderecos'
*/
addressesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: addresses.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

addresses.form = addressesForm

const client = {
    profile: Object.assign(profile, profile937a89),
    addresses: Object.assign(addresses, addresses2498c9),
}

export default client