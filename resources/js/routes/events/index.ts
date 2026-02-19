import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
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
* @see \App\Http\Controllers\Web\BookingController::lockSeats
 * @see app/Http/Controllers/Web/BookingController.php:27
 * @route '/events/{slug}/lock-seats'
 */
export const lockSeats = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: lockSeats.url(args, options),
    method: 'post',
})

lockSeats.definition = {
    methods: ["post"],
    url: '/events/{slug}/lock-seats',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\BookingController::lockSeats
 * @see app/Http/Controllers/Web/BookingController.php:27
 * @route '/events/{slug}/lock-seats'
 */
lockSeats.url = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return lockSeats.definition.url
            .replace('{slug}', parsedArgs.slug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BookingController::lockSeats
 * @see app/Http/Controllers/Web/BookingController.php:27
 * @route '/events/{slug}/lock-seats'
 */
lockSeats.post = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: lockSeats.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\BookingController::lockSeats
 * @see app/Http/Controllers/Web/BookingController.php:27
 * @route '/events/{slug}/lock-seats'
 */
    const lockSeatsForm = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: lockSeats.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\BookingController::lockSeats
 * @see app/Http/Controllers/Web/BookingController.php:27
 * @route '/events/{slug}/lock-seats'
 */
        lockSeatsForm.post = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: lockSeats.url(args, options),
            method: 'post',
        })
    
    lockSeats.form = lockSeatsForm
const events = {
    show: Object.assign(show, show),
lockSeats: Object.assign(lockSeats, lockSeats),
}

export default events