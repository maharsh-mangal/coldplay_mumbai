<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

interface Event {
    id: number
    slug: string
    event_date: string
    tour: { name: string; artist: string; slug: string; poster_url: string | null }
    venue: { name: string; city: string }
    available_seats: number
    total_seats: number
}

defineProps<{ events: Event[] }>()

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString('en-IN', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    })
}

function badge(event: Event): { label: string; color: string } {
    const pct = event.available_seats / event.total_seats
    if (pct === 0) return { label: 'SOLD OUT', color: 'bg-red-500/90' }
    if (pct < 0.15) return { label: 'SELLING FAST', color: 'bg-rose-500/90' }
    if (pct < 0.4) return { label: 'POPULAR', color: 'bg-emerald-500/90' }
    return { label: 'AVAILABLE', color: 'bg-indigo-500/90' }
}
</script>

<template>
    <Head title="Upcoming Concerts" />

    <div class="min-h-screen bg-[#0a0a0f]">
        <!-- Ambient glow -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -top-40 left-1/4 h-96 w-96 rounded-full bg-purple-600/8 blur-[120px]" />
            <div class="absolute -top-20 right-1/4 h-80 w-80 rounded-full bg-rose-600/6 blur-[100px]" />
        </div>

        <div class="relative mx-auto max-w-6xl px-6 py-16">
            <!-- Header -->
            <div class="mb-14">
                <div class="mb-4 flex items-center gap-3">
                    <span class="h-[3px] w-8 rounded-full bg-rose-500" />
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-400">Live Events</span>
                </div>
                <h1 class="text-5xl font-extrabold leading-tight tracking-tight text-white">
                    Upcoming
                    <span class="bg-gradient-to-r from-rose-400 to-purple-400 bg-clip-text text-transparent">Concerts</span>
                </h1>
                <p class="mt-4 max-w-lg text-base text-gray-500">
                    Experience the world's biggest artists live in India. Book your tickets before they sell out.
                </p>
            </div>

            <!-- Empty -->
            <div v-if="events.length === 0" class="rounded-2xl border border-white/5 bg-white/[0.02] p-16 text-center">
                <p class="text-gray-600">No upcoming events at the moment. Check back soon.</p>
            </div>

            <!-- Grid -->
            <div class="grid gap-6 sm:grid-cols-2">
                <Link
                    v-for="event in events"
                    :key="event.id"
                    :href="`/events/${event.slug}`"
                    class="group relative overflow-hidden rounded-2xl border border-white/[0.06] bg-white/[0.02] transition-all duration-300 hover:border-white/[0.12] hover:bg-white/[0.04]"
                >
                    <!-- Concert gradient placeholder -->
                    <div class="relative h-56 overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-900/80 via-rose-900/60 to-indigo-900/80" />
                        <div class="absolute inset-0 opacity-40">
                            <div class="absolute left-1/4 top-0 h-full w-[2px] rotate-[15deg] bg-gradient-to-b from-white/30 to-transparent" />
                            <div class="absolute left-1/2 top-0 h-full w-[1px] rotate-[-8deg] bg-gradient-to-b from-purple-300/25 to-transparent" />
                            <div class="absolute left-3/4 top-0 h-full w-[2px] rotate-[20deg] bg-gradient-to-b from-rose-300/20 to-transparent" />
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-[#0a0a0f] to-transparent" />

                        <!-- Badge -->
                        <div class="absolute left-4 top-4">
                            <span class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white" :class="badge(event).color">
                                {{ badge(event).label }}
                            </span>
                        </div>

                        <!-- Hover shimmer -->
                        <div class="absolute inset-0 translate-x-[-100%] bg-gradient-to-r from-transparent via-white/5 to-transparent transition-transform duration-700 group-hover:translate-x-[100%]" />
                    </div>

                    <!-- Info -->
                    <div class="p-5">
                        <p class="text-sm font-semibold text-rose-400">{{ event.tour.artist }}</p>
                        <h2 class="mt-1 text-lg font-bold text-white group-hover:text-white/90">{{ event.tour.name }}</h2>

                        <div class="mt-3 flex items-center gap-4 text-xs text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="1.5"/><path d="M16 2v4M8 2v4M3 10h18" stroke-width="1.5"/></svg>
                                {{ formatDate(event.event_date) }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" stroke-width="1.5"/><circle cx="12" cy="9" r="2.5" stroke-width="1.5"/></svg>
                                {{ event.venue.city }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <span v-if="event.available_seats > 0" class="text-xs text-gray-500">
                                {{ event.available_seats }} seats left
                            </span>
                            <span v-else class="text-xs text-red-400">Sold Out</span>
                            <span class="flex items-center gap-1 text-xs font-medium text-rose-400 transition group-hover:gap-2">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                View Details
                            </span>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>
