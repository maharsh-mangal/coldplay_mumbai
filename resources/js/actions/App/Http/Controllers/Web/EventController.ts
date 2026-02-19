import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Web\EventController::index
 * @see app/Http/Controllers/Web/EventController.php:19
 * @route '/'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\EventController::index
 * @see app/Http/Controllers/Web/EventController.php:19
 * @route '/'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\EventController::index
 * @see app/Http/Controllers/Web/EventController.php:19
 * @route '/'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\EventController::index
 * @see app/Http/Controllers/Web/EventController.php:19
 * @route '/'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\EventController::index
 * @see app/Http/Controllers/Web/EventController.php:19
 * @route '/'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\EventController::index
 * @see app/Http/Controllers/Web/EventController.php:19
 * @route '/'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\EventController::index
 * @see app/Http/Controllers/Web/EventController.php:19
 * @route '/'
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
* @see \App\Http\Controllers\Web\EventController::show
 * @see app/Http/Controllers/Web/EventController.php:30
 * @route '/events/{slug}'
 */
export const show = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/events/{slug}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\EventController::show
 * @see app/Http/Controllers/Web/EventController.php:30
 * @route '/events/{slug}'
 */
show.url = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { slug: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    slug: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        slug: args.slug,
                }

    return show.definition.url
            .replace('{slug}', parsedArgs.slug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\EventController::show
 * @see app/Http/Controllers/Web/EventController.php:30
 * @route '/events/{slug}'
 */
show.get = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\EventController::show
 * @see app/Http/Controllers/Web/EventController.php:30
 * @route '/events/{slug}'
 */
show.head = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\EventController::show
 * @see app/Http/Controllers/Web/EventController.php:30
 * @route '/events/{slug}'
 */
    const showForm = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\EventController::show
 * @see app/Http/Controllers/Web/EventController.php:30
 * @route '/events/{slug}'
 */
        showForm.get = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\EventController::show
 * @see app/Http/Controllers/Web/EventController.php:30
 * @route '/events/{slug}'
 */
        showForm.head = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Web\EventController::seatsAvailable
 * @see app/Http/Controllers/Web/EventController.php:43
 * @route '/seats/available'
 */
export const seatsAvailable = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: seatsAvailable.url(options),
    method: 'post',
})

seatsAvailable.definition = {
    methods: ["post"],
    url: '/seats/available',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\EventController::seatsAvailable
 * @see app/Http/Controllers/Web/EventController.php:43
 * @route '/seats/available'
 */
seatsAvailable.url = (options?: RouteQueryOptions) => {
    return seatsAvailable.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\EventController::seatsAvailable
 * @see app/Http/Controllers/Web/EventController.php:43
 * @route '/seats/available'
 */
seatsAvailable.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: seatsAvailable.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\EventController::seatsAvailable
 * @see app/Http/Controllers/Web/EventController.php:43
 * @route '/seats/available'
 */
    const seatsAvailableForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: seatsAvailable.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\EventController::seatsAvailable
 * @see app/Http/Controllers/Web/EventController.php:43
 * @route '/seats/available'
 */
        seatsAvailableForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: seatsAvailable.url(options),
            method: 'post',
        })
    
    seatsAvailable.form = seatsAvailableForm
const EventController = { index, show, seatsAvailable }

export default EventController