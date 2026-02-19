<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useApi } from '@/composables/useApi';

interface Seat {
    id: number
    row: string
    number: number
    status: 'available' | 'locked' | 'booked'
}

interface Section {
    id: number
    name: string
    price_in_paisa: number
    formatted_price: string
    layout: { row_count: number; seats_per_row: number; color: string; position: string }
    seats: Seat[]
}

interface Event {
    id: number
    slug: string
    event_date: string
    tour: { name: string; artist: string; description: string; poster_url: string | null }
    venue: { name: string; address: string; city: string; map_url: string | null }
    sections: Section[]
}

const props = defineProps<{ event: Event }>()

const page = usePage()
const isAuthenticated = computed(() => !!page.props.auth?.user)

const selectedSeatIds = ref<number[]>([])
const activeSection = ref<number | null>(null)
const showSeatMap = ref(false)
const { post } = useApi()

// Checkout flow state
type StepStatus = 'pending' | 'loading' | 'success' | 'error'

const showCheckoutModal = ref(false)
const checkoutError = ref<string | null>(null)
const steps = ref<{ name: string; status: StepStatus; message: string }[]>([
    { name: 'availability', status: 'pending', message: 'Checking seat availability' },
    { name: 'locking', status: 'pending', message: 'Reserving seats for you' },
    { name: 'redirect', status: 'pending', message: 'Redirecting to checkout' },
])

const availableSeats = computed((): number =>
    props.event.sections.reduce((sum, s) => sum + s.seats.filter(seat => seat.status === 'available').length, 0)
)

function toggleSeat(seat: Seat): void {
    if (seat.status !== 'available') return
    const idx = selectedSeatIds.value.indexOf(seat.id)
    if (idx > -1) selectedSeatIds.value.splice(idx, 1)
    else selectedSeatIds.value.push(seat.id)
}

function isSeatSelected(seat: Seat): boolean {
    return selectedSeatIds.value.includes(seat.id)
}

function seatClasses(seat: Seat): string {
    const base = 'h-8 w-8 rounded-full text-[10px] font-semibold transition-all duration-200 '
    if (isSeatSelected(seat)) return base + 'bg-rose-500 text-white ring-2 ring-rose-400/50 scale-110'
    if (seat.status === 'available') return base + 'bg-white/[0.06] text-gray-500 hover:bg-white/[0.12] hover:text-white cursor-pointer border border-white/[0.08]'
    if (seat.status === 'locked') return base + 'bg-amber-500/10 text-amber-600/40 border border-amber-500/20 cursor-not-allowed'
    return base + 'bg-white/[0.02] text-gray-700 border border-white/[0.04] cursor-not-allowed'
}

function seatsByRow(seats: Seat[]): Record<string, Seat[]> {
    const grouped: Record<string, Seat[]> = {}
    for (const seat of seats) {
        if (!grouped[seat.row]) grouped[seat.row] = []
        grouped[seat.row].push(seat)
    }
    for (const row of Object.keys(grouped)) {
        grouped[row].sort((a, b) => a.number - b.number)
    }
    return grouped
}

const selectedTotal = computed((): number => {
    let total = 0
    for (const section of props.event.sections) {
        for (const seat of section.seats) {
            if (selectedSeatIds.value.includes(seat.id)) total += section.price_in_paisa
        }
    }
    return total
})

const selectedCount = computed(() => selectedSeatIds.value.length)

function formatPrice(paisa: number): string {
    return '₹' + (paisa / 100).toLocaleString('en-IN', { minimumFractionDigits: 0 })
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString('en-IN', { month: 'long', day: 'numeric', year: 'numeric' })
}

function formatTime(iso: string): string {
    return new Date(iso).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true })
}

function scrollToSeats(): void {
    showSeatMap.value = true
    setTimeout(() => document.getElementById('seat-selection')?.scrollIntoView({ behavior: 'smooth' }), 100)
}

function resetCheckoutState(): void {
    steps.value = [
        { name: 'availability', status: 'pending', message: 'Checking seat availability' },
        { name: 'locking', status: 'pending', message: 'Reserving seats for you' },
        { name: 'redirect', status: 'pending', message: 'Redirecting to checkout' },
    ]
    checkoutError.value = null
}

function updateStep(index: number, status: StepStatus): void {
    steps.value[index].status = status
}

async function refreshSeatMap(): Promise<void> {
    router.reload({ only: ['event'] })
}

async function startCheckoutFlow(): Promise<void> {
    if (selectedSeatIds.value.length === 0) return

    resetCheckoutState()
    showCheckoutModal.value = true

    try {
        // Step 1: Check availability
        updateStep(0, 'loading')
        await new Promise(resolve => setTimeout(resolve, 500)) // Small delay for UX

        const availabilityResponse = await post('/seats/available', {
            seats: selectedSeatIds.value
        })

        if (!availabilityResponse.available) {
            updateStep(0, 'error')
            checkoutError.value = 'Some selected seats are no longer available. Please try again.'
            await refreshSeatMap()
            selectedSeatIds.value = []
            return
        }

        updateStep(0, 'success')

        // Step 2: Lock seats
        await new Promise(resolve => setTimeout(resolve, 300))
        updateStep(1, 'loading')

        const lockResponse = await post(`/events/${props.event.slug}/lock-seats`, {
            seat_ids: selectedSeatIds.value
        })

        if (lockResponse.error) {
            updateStep(1, 'error')
            checkoutError.value = lockResponse.error || 'Failed to reserve seats. Please try again.'
            await refreshSeatMap()
            selectedSeatIds.value = []
            return
        }

        updateStep(1, 'success')

        // Step 3: Redirect to checkout
        await new Promise(resolve => setTimeout(resolve, 300))
        updateStep(2, 'loading')

        await new Promise(resolve => setTimeout(resolve, 500))
        updateStep(2, 'success')

        // Redirect (adjust URL as needed)
        await new Promise(resolve => setTimeout(resolve, 300))
        router.visit(lockResponse.redirect || `/checkout/${lockResponse.booking_id}`)

    } catch (error: any) {
        const currentLoadingStep = steps.value.findIndex(s => s.status === 'loading')
        if (currentLoadingStep !== -1) {
            updateStep(currentLoadingStep, 'error')
        }

        checkoutError.value = error.response?.data?.message || 'Something went wrong. Please try again.'
        await refreshSeatMap()
        selectedSeatIds.value = []
    }
}

function closeModal(): void {
    showCheckoutModal.value = false
    resetCheckoutState()
}
</script>

<template>
    <Head :title="`${event.tour.artist} — ${event.tour.name}`" />

    <div class="min-h-screen bg-[#0a0a0f]">
        <!-- ═══════ HERO ═══════ -->
        <div class="relative overflow-hidden">
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-900/60 via-rose-900/40 to-indigo-950/80" />
                <div class="absolute inset-0 opacity-30">
                    <div class="absolute left-[10%] top-0 h-[120%] w-[3px] rotate-[12deg] bg-gradient-to-b from-white/40 via-purple-300/20 to-transparent" />
                    <div class="absolute left-[30%] top-0 h-[120%] w-[2px] rotate-[-6deg] bg-gradient-to-b from-rose-300/30 via-pink-300/10 to-transparent" />
                    <div class="absolute left-[55%] top-0 h-[120%] w-[4px] rotate-[8deg] bg-gradient-to-b from-purple-200/35 via-indigo-300/15 to-transparent" />
                    <div class="absolute left-[75%] top-0 h-[120%] w-[2px] rotate-[-15deg] bg-gradient-to-b from-white/25 via-rose-200/10 to-transparent" />
                    <div class="absolute left-[90%] top-0 h-[120%] w-[3px] rotate-[18deg] bg-gradient-to-b from-purple-300/30 to-transparent" />
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-48 bg-gradient-to-t from-[#0a0a0f] to-transparent" />
                <div class="absolute right-1/4 top-1/4 h-64 w-64 rounded-full bg-rose-500/10 blur-[80px]" />
            </div>

            <div class="relative mx-auto max-w-6xl px-6 pb-20 pt-12">
                <Link href="/" class="mb-8 inline-flex items-center gap-2 text-sm text-gray-400 transition hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Back to Events
                </Link>

                <div class="mt-6 max-w-xl">
                    <div class="mb-4 flex flex-wrap gap-2">
                        <span v-if="availableSeats < 100" class="rounded-full bg-rose-500/90 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Selling Fast</span>
                        <span class="rounded-full border border-white/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-gray-300">Live Concert</span>
                    </div>

                    <h1 class="text-5xl font-extrabold leading-[1.1] tracking-tight text-white lg:text-6xl">{{ event.tour.artist }}</h1>
                    <p class="mt-2 text-2xl font-bold bg-gradient-to-r from-rose-400 to-purple-400 bg-clip-text text-transparent">{{ event.tour.name }}</p>

                    <div class="mt-8 space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-500/10">
                                <svg class="h-5 w-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.5"/><path d="M16 2v4M8 2v4M3 10h18" stroke-width="1.5"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Date</p>
                                <p class="font-semibold text-white">{{ formatDate(event.event_date) }} · {{ formatTime(event.event_date) }} IST</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-500/10">
                                <svg class="h-5 w-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke-width="1.5"/><circle cx="12" cy="9" r="2.5" stroke-width="1.5"/></svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Venue</p>
                                <p class="font-semibold text-white">{{ event.venue.name }}, {{ event.venue.city }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 flex items-center gap-5">
                        <button
                            class="rounded-xl bg-gradient-to-r from-rose-500 to-purple-600 px-8 py-4 text-sm font-bold text-white shadow-lg shadow-rose-500/20 transition-all hover:shadow-xl hover:shadow-rose-500/30"
                            @click="scrollToSeats"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 002 2h3a2 2 0 002-2V7a2 2 0 00-2-2H5zM5 15a2 2 0 00-2 2v1a2 2 0 002 2h3a2 2 0 002-2v-1a2 2 0 00-2-2H5z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Book Tickets
                            </span>
                        </button>
                        <div v-if="availableSeats > 0" class="flex items-center gap-2 text-sm">
                            <svg class="h-4 w-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="text-gray-400">Only <span class="font-semibold text-white">{{ availableSeats }}</span> tickets left</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════ SEAT SELECTION ═══════ -->
        <div v-if="showSeatMap" id="seat-selection" class="relative mx-auto max-w-6xl px-6 pb-24 pt-8">
            <div class="mb-8">
                <h2 class="text-3xl font-extrabold text-white">Select Your Seats</h2>
                <p class="mt-1 text-sm text-gray-500">{{ event.tour.artist }}: {{ event.tour.name }} · {{ event.venue.name }}</p>
            </div>

            <div class="flex flex-col gap-8 lg:flex-row">
                <!-- Left Sidebar -->
                <div class="w-full shrink-0 space-y-5 lg:w-80">
                    <!-- Sections -->
                    <div class="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-5">
                        <h3 class="mb-3 text-sm font-bold text-white">Sections</h3>
                        <div class="space-y-2">
                            <button
                                v-for="section in event.sections"
                                :key="section.id"
                                class="flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left text-sm transition"
                                :class="activeSection === section.id ? 'border-rose-500/30 bg-rose-500/10 text-white' : 'border-white/[0.06] text-gray-400 hover:border-white/[0.12] hover:text-white'"
                                @click="activeSection = activeSection === section.id ? null : section.id"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: section.layout.color }" />
                                    <span class="font-medium">{{ section.name }}</span>
                                </div>
                                <span class="text-xs text-gray-500">{{ section.formatted_price }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-5">
                        <h3 class="mb-3 text-sm font-bold text-white">Legend</h3>
                        <div class="space-y-2.5">
                            <div class="flex items-center gap-3 text-xs text-gray-400">
                                <span class="h-5 w-5 rounded-full border border-white/[0.08] bg-white/[0.06]" /> Available
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-400">
                                <span class="h-5 w-5 rounded-full bg-rose-500 ring-2 ring-rose-400/50" /> Selected
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-400">
                                <span class="h-5 w-5 rounded-full border border-white/[0.04] bg-white/[0.02]" /> Occupied
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-5">
                        <h3 class="mb-3 text-sm font-bold text-white">Summary</h3>
                        <div v-if="selectedCount === 0" class="text-xs text-gray-600">Click seats on the map to select them.</div>
                        <div v-else class="space-y-2 text-sm">
                            <div class="flex justify-between text-gray-400">
                                <span>{{ selectedCount }} seat(s)</span>
                                <span>{{ formatPrice(selectedTotal) }}</span>
                            </div>
                            <div class="border-t border-white/[0.06] pt-2">
                                <div class="flex justify-between font-bold text-white">
                                    <span>Total</span>
                                    <span class="text-rose-400">{{ formatPrice(selectedTotal) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div v-if="!isAuthenticated" class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 text-center text-sm text-amber-400">
                        <Link href="/login" class="font-semibold underline hover:text-amber-300">Log in</Link> to book tickets
                    </div>
                    <button
                        v-else
                        :disabled="selectedCount === 0"
                        class="w-full rounded-xl bg-gradient-to-r from-rose-500 to-purple-600 py-4 text-sm font-bold text-white shadow-lg shadow-rose-500/20 transition-all hover:shadow-xl hover:shadow-rose-500/30 disabled:opacity-40 disabled:shadow-none"
                        @click="startCheckoutFlow"
                    >
                        Proceed to Checkout
                    </button>
                </div>

                <!-- Seat Map -->
                <div class="flex-1 rounded-2xl border border-white/[0.06] bg-white/[0.02] p-6">
                    <!-- Stage -->
                    <div class="mx-auto mb-10 w-60">
                        <div class="rounded-t-[100%] bg-gradient-to-r from-rose-500/60 via-purple-500/80 to-rose-500/60 px-8 py-3 text-center text-xs font-bold uppercase tracking-[0.25em] text-white/90">Stage</div>
                    </div>

                    <!-- Sections -->
                    <div class="space-y-8">
                        <div
                            v-for="section in event.sections"
                            :key="section.id"
                            :class="{ 'opacity-30': activeSection !== null && activeSection !== section.id }"
                            class="transition-opacity duration-300"
                        >
                            <div class="mb-3 flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: section.layout.color }" />
                                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ section.name }} · {{ section.formatted_price }}</span>
                            </div>

                            <div class="flex flex-col items-center gap-1.5">
                                <div v-for="(seats, row) in seatsByRow(section.seats)" :key="row" class="flex items-center gap-1">
                                    <span class="w-5 text-center text-[10px] font-semibold text-gray-600">{{ row }}</span>
                                    <button
                                        v-for="seat in seats"
                                        :key="seat.id"
                                        :disabled="seat.status !== 'available'"
                                        :class="seatClasses(seat)"
                                        :title="`Row ${seat.row}, Seat ${seat.number}`"
                                        @click="toggleSeat(seat)"
                                    >
                                        {{ seat.number }}
                                    </button>
                                    <span class="w-5 text-center text-[10px] font-semibold text-gray-600">{{ row }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════ CHECKOUT MODAL ═══════ -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showCheckoutModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
                    <div class="mx-4 w-full max-w-md rounded-2xl border border-white/[0.08] bg-[#12121a] p-8 shadow-2xl">
                        <!-- Header -->
                        <div class="mb-8 text-center">
                            <h3 class="text-xl font-bold text-white">Securing Your Tickets</h3>
                            <p class="mt-1 text-sm text-gray-500">Please wait while we process your request</p>
                        </div>

                        <!-- Steps -->
                        <div class="space-y-4">
                            <div
                                v-for="(step, index) in steps"
                                :key="step.name"
                                class="flex items-center gap-4 rounded-xl border p-4 transition-all duration-300"
                                :class="{
                                    'border-white/[0.06] bg-white/[0.02]': step.status === 'pending',
                                    'border-rose-500/30 bg-rose-500/10': step.status === 'loading',
                                    'border-emerald-500/30 bg-emerald-500/10': step.status === 'success',
                                    'border-red-500/30 bg-red-500/10': step.status === 'error',
                                }"
                            >
                                <!-- Icon -->
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                                     :class="{
                                        'bg-white/[0.06]': step.status === 'pending',
                                        'bg-rose-500/20': step.status === 'loading',
                                        'bg-emerald-500/20': step.status === 'success',
                                        'bg-red-500/20': step.status === 'error',
                                    }"
                                >
                                    <!-- Pending -->
                                    <svg v-if="step.status === 'pending'" class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="1.5" />
                                    </svg>

                                    <!-- Loading spinner -->
                                    <svg v-else-if="step.status === 'loading'" class="h-5 w-5 animate-spin text-rose-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>

                                    <!-- Success checkmark -->
                                    <svg v-else-if="step.status === 'success'" class="h-5 w-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>

                                    <!-- Error X -->
                                    <svg v-else class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>

                                <!-- Text -->
                                <div class="flex-1">
                                    <p class="text-sm font-medium"
                                       :class="{
                                            'text-gray-500': step.status === 'pending',
                                            'text-rose-300': step.status === 'loading',
                                            'text-emerald-300': step.status === 'success',
                                            'text-red-300': step.status === 'error',
                                        }"
                                    >
                                        {{ step.message }}
                                    </p>
                                </div>

                                <!-- Step number -->
                                <span class="text-xs font-semibold"
                                      :class="{
                                        'text-gray-600': step.status === 'pending',
                                        'text-rose-400': step.status === 'loading',
                                        'text-emerald-400': step.status === 'success',
                                        'text-red-400': step.status === 'error',
                                    }"
                                >
                                    {{ index + 1 }}/{{ steps.length }}
                                </span>
                            </div>
                        </div>

                        <!-- Error message -->
                        <div v-if="checkoutError" class="mt-6 rounded-xl border border-red-500/20 bg-red-500/5 p-4">
                            <div class="flex items-start gap-3">
                                <svg class="h-5 w-5 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-red-400">{{ checkoutError }}</p>
                                    <p class="mt-1 text-xs text-red-400/70">The seat map has been refreshed with the latest availability.</p>
                                </div>
                            </div>

                            <button
                                class="mt-4 w-full rounded-lg border border-white/[0.08] bg-white/[0.04] py-2.5 text-sm font-medium text-white transition hover:bg-white/[0.08]"
                                @click="closeModal"
                            >
                                Try Again
                            </button>
                        </div>

                        <!-- Info text -->
                        <p v-if="!checkoutError" class="mt-6 text-center text-xs text-gray-600">
                            Do not close this window or refresh the page
                        </p>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Mobile sticky footer -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div
                    v-if="selectedCount > 0 && showSeatMap && !showCheckoutModal"
                    class="fixed inset-x-0 bottom-0 z-50 border-t border-white/[0.06] bg-[#0a0a0f]/95 backdrop-blur-xl lg:hidden"
                >
                    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                        <div>
                            <p class="text-xs text-gray-500">{{ selectedCount }} seat(s)</p>
                            <p class="text-lg font-bold text-white">{{ formatPrice(selectedTotal) }}</p>
                        </div>
                        <button
                            v-if="isAuthenticated"
                            class="rounded-xl bg-gradient-to-r from-rose-500 to-purple-600 px-6 py-3 text-sm font-bold text-white"
                            @click="startCheckoutFlow"
                        >
                            Reserve
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
