import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
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
const orders = {
    confirm: Object.assign(confirm, confirm),
}

export default orders