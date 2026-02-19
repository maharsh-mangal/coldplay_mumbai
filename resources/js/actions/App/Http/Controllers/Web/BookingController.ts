import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
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
/**
* @see \App\Http\Controllers\Web\BookingController::checkout
 * @see app/Http/Controllers/Web/BookingController.php:51
 * @route '/checkout/{order}'
 */
export const checkout = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: checkout.url(args, options),
    method: 'get',
})

checkout.definition = {
    methods: ["get","head"],
    url: '/checkout/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\BookingController::checkout
 * @see app/Http/Controllers/Web/BookingController.php:51
 * @route '/checkout/{order}'
 */
checkout.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return checkout.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BookingController::checkout
 * @see app/Http/Controllers/Web/BookingController.php:51
 * @route '/checkout/{order}'
 */
checkout.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: checkout.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\BookingController::checkout
 * @see app/Http/Controllers/Web/BookingController.php:51
 * @route '/checkout/{order}'
 */
checkout.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: checkout.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\BookingController::checkout
 * @see app/Http/Controllers/Web/BookingController.php:51
 * @route '/checkout/{order}'
 */
    const checkoutForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: checkout.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\BookingController::checkout
 * @see app/Http/Controllers/Web/BookingController.php:51
 * @route '/checkout/{order}'
 */
        checkoutForm.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: checkout.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\BookingController::checkout
 * @see app/Http/Controllers/Web/BookingController.php:51
 * @route '/checkout/{order}'
 */
        checkoutForm.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: checkout.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    checkout.form = checkoutForm
/**
* @see \App\Http\Controllers\Web\BookingController::confirm
 * @see app/Http/Controllers/Web/BookingController.php:92
 * @route '/orders/{order}/confirm'
 */
export const confirm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(args, options),
    method: 'post',
})

confirm.definition = {
    methods: ["post"],
    url: '/orders/{order}/confirm',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Web\BookingController::confirm
 * @see app/Http/Controllers/Web/BookingController.php:92
 * @route '/orders/{order}/confirm'
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
* @see \App\Http\Controllers\Web\BookingController::confirm
 * @see app/Http/Controllers/Web/BookingController.php:92
 * @route '/orders/{order}/confirm'
 */
confirm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Web\BookingController::confirm
 * @see app/Http/Controllers/Web/BookingController.php:92
 * @route '/orders/{order}/confirm'
 */
    const confirmForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: confirm.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Web\BookingController::confirm
 * @see app/Http/Controllers/Web/BookingController.php:92
 * @route '/orders/{order}/confirm'
 */
        confirmForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: confirm.url(args, options),
            method: 'post',
        })
    
    confirm.form = confirmForm
/**
* @see \App\Http\Controllers\Web\BookingController::confirmation
 * @see app/Http/Controllers/Web/BookingController.php:107
 * @route '/orders/{order}/confirmation'
 */
export const confirmation = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirmation.url(args, options),
    method: 'get',
})

confirmation.definition = {
    methods: ["get","head"],
    url: '/orders/{order}/confirmation',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Web\BookingController::confirmation
 * @see app/Http/Controllers/Web/BookingController.php:107
 * @route '/orders/{order}/confirmation'
 */
confirmation.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return confirmation.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Web\BookingController::confirmation
 * @see app/Http/Controllers/Web/BookingController.php:107
 * @route '/orders/{order}/confirmation'
 */
confirmation.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirmation.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Web\BookingController::confirmation
 * @see app/Http/Controllers/Web/BookingController.php:107
 * @route '/orders/{order}/confirmation'
 */
confirmation.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: confirmation.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Web\BookingController::confirmation
 * @see app/Http/Controllers/Web/BookingController.php:107
 * @route '/orders/{order}/confirmation'
 */
    const confirmationForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: confirmation.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Web\BookingController::confirmation
 * @see app/Http/Controllers/Web/BookingController.php:107
 * @route '/orders/{order}/confirmation'
 */
        confirmationForm.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: confirmation.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Web\BookingController::confirmation
 * @see app/Http/Controllers/Web/BookingController.php:107
 * @route '/orders/{order}/confirmation'
 */
        confirmationForm.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: confirmation.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    confirmation.form = confirmationForm
const BookingController = { lockSeats, checkout, confirm, confirmation }

export default BookingController