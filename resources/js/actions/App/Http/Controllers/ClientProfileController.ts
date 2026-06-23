import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
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
* @see \App\Http\Controllers\ClientProfileController::updateProfile
* @see app/Http/Controllers/ClientProfileController.php:33
* @route '/perfil'
*/
export const updateProfile = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateProfile.url(options),
    method: 'put',
})

updateProfile.definition = {
    methods: ["put"],
    url: '/perfil',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\ClientProfileController::updateProfile
* @see app/Http/Controllers/ClientProfileController.php:33
* @route '/perfil'
*/
updateProfile.url = (options?: RouteQueryOptions) => {
    return updateProfile.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientProfileController::updateProfile
* @see app/Http/Controllers/ClientProfileController.php:33
* @route '/perfil'
*/
updateProfile.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateProfile.url(options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\ClientProfileController::updateProfile
* @see app/Http/Controllers/ClientProfileController.php:33
* @route '/perfil'
*/
const updateProfileForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateProfile.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ClientProfileController::updateProfile
* @see app/Http/Controllers/ClientProfileController.php:33
* @route '/perfil'
*/
updateProfileForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateProfile.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateProfile.form = updateProfileForm

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

/**
* @see \App\Http\Controllers\ClientProfileController::updateAddress
* @see app/Http/Controllers/ClientProfileController.php:58
* @route '/perfil/enderecos'
*/
export const updateAddress = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateAddress.url(options),
    method: 'put',
})

updateAddress.definition = {
    methods: ["put"],
    url: '/perfil/enderecos',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\ClientProfileController::updateAddress
* @see app/Http/Controllers/ClientProfileController.php:58
* @route '/perfil/enderecos'
*/
updateAddress.url = (options?: RouteQueryOptions) => {
    return updateAddress.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientProfileController::updateAddress
* @see app/Http/Controllers/ClientProfileController.php:58
* @route '/perfil/enderecos'
*/
updateAddress.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateAddress.url(options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\ClientProfileController::updateAddress
* @see app/Http/Controllers/ClientProfileController.php:58
* @route '/perfil/enderecos'
*/
const updateAddressForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateAddress.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ClientProfileController::updateAddress
* @see app/Http/Controllers/ClientProfileController.php:58
* @route '/perfil/enderecos'
*/
updateAddressForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateAddress.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateAddress.form = updateAddressForm

const ClientProfileController = { profile, updateProfile, addresses, updateAddress }

export default ClientProfileController