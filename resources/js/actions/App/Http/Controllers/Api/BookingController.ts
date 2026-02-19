import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\BookingController::lockSeats
 * @see app/Http/Controllers/Api/BookingController.php:25
 * @route '/api/events/{slug}/lock-seats'
 */
export const lockSeats = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: lockSeats.url(args, options),
    method: 'post',
})

lockSeats.definition = {
    methods: ["post"],
    url: '/api/events/{slug}/lock-seats',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingController::lockSeats
 * @see app/Http/Controllers/Api/BookingController.php:25
 * @route '/api/events/{slug}/lock-seats'
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
* @see \App\Http\Controllers\Api\BookingController::lockSeats
 * @see app/Http/Controllers/Api/BookingController.php:25
 * @route '/api/events/{slug}/lock-seats'
 */
lockSeats.post = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: lockSeats.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingController::lockSeats
 * @see app/Http/Controllers/Api/BookingController.php:25
 * @route '/api/events/{slug}/lock-seats'
 */
    const lockSeatsForm = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: lockSeats.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingController::lockSeats
 * @see app/Http/Controllers/Api/BookingController.php:25
 * @route '/api/events/{slug}/lock-seats'
 */
        lockSeatsForm.post = (args: { slug: string | number } | [slug: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: lockSeats.url(args, options),
            method: 'post',
        })
    
    lockSeats.form = lockSeatsForm
/**
* @see \App\Http\Controllers\Api\BookingController::confirm
 * @see app/Http/Controllers/Api/BookingController.php:49
 * @route '/api/orders/{order}/confirm'
 */
export const confirm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(args, options),
    method: 'post',
})

confirm.definition = {
    methods: ["post"],
    url: '/api/orders/{order}/confirm',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\BookingController::confirm
 * @see app/Http/Controllers/Api/BookingController.php:49
 * @route '/api/orders/{order}/confirm'
 */
confirm.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return confirm.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\BookingController::confirm
 * @see app/Http/Controllers/Api/BookingController.php:49
 * @route '/api/orders/{order}/confirm'
 */
confirm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\BookingController::confirm
 * @see app/Http/Controllers/Api/BookingController.php:49
 * @route '/api/orders/{order}/confirm'
 */
    const confirmForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: confirm.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\BookingController::confirm
 * @see app/Http/Controllers/Api/BookingController.php:49
 * @route '/api/orders/{order}/confirm'
 */
        confirmForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: confirm.url(args, options),
            method: 'post',
        })
    
    confirm.form = confirmForm
const BookingController = { lockSeats, confirm }

export default BookingController