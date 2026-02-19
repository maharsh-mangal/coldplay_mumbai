<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Order {
    id: number;
    status: string;
    total_in_paisa: number;
    subtotal_in_paisa: number;
    tax_in_paisa: number;
    confirmed_at: string | null;
    event: {
        slug: string;
        event_date: string;
        tour: { name: string; artist: string };
        venue: { name: string; city: string };
    };
    items: { seat_label: string; price_in_paisa: number }[];
    payment: { method: string; transaction_id: string; paid_at: string } | null;
}

defineProps<{ order: Order }>();

function formatPrice(paisa: number): string {
    return (
        '₹' +
        (paisa / 100).toLocaleString('en-IN', { minimumFractionDigits: 2 })
    );
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString('en-IN', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatMethod(method: string): string {
    const map: Record<string, string> = {
        upi: 'UPI',
        credit_card: 'Credit Card',
        debit_card: 'Debit Card',
        net_banking: 'Net Banking',
    };
    return map[method] ?? method;
}
</script>

<template>
    <Head title="Booking Confirmed" />

    <div class="min-h-screen bg-[#0a0a0f]">
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div
                class="absolute top-0 left-1/2 h-[500px] w-[600px] -translate-x-1/2 rounded-full bg-emerald-500/5 blur-[150px]"
            />
        </div>

        <div class="relative mx-auto max-w-2xl px-6 py-16">
            <!-- Success -->
            <div class="mb-10 text-center">
                <div
                    class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-500/10 ring-1 ring-emerald-500/20"
                >
                    <svg
                        class="h-10 w-10 text-emerald-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>
                <h1 class="text-4xl font-extrabold text-white">
                    Booking Confirmed!
                </h1>
                <p class="mt-3 text-gray-500">
                    A confirmation email has been sent to your inbox.
                </p>
            </div>

            <!-- Event -->
            <div
                class="mb-6 overflow-hidden rounded-2xl border border-white/[0.06]"
            >
                <div
                    class="relative bg-gradient-to-r from-purple-900/40 via-rose-900/30 to-indigo-900/40 px-6 py-5"
                >
                    <div class="absolute inset-0 opacity-20">
                        <div
                            class="absolute top-0 left-1/4 h-full w-[2px] rotate-[15deg] bg-gradient-to-b from-white/20 to-transparent"
                        />
                        <div
                            class="absolute top-0 left-3/4 h-full w-[1px] rotate-[-10deg] bg-gradient-to-b from-purple-300/15 to-transparent"
                        />
                    </div>
                    <div class="relative">
                        <p class="text-sm font-semibold text-rose-400">
                            {{ order.event.tour.artist }}
                        </p>
                        <p class="mt-1 text-lg font-bold text-white">
                            {{ order.event.tour.name }}
                        </p>
                        <p class="mt-2 text-sm text-gray-400">
                            {{ order.event.venue.name }},
                            {{ order.event.venue.city }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ formatDate(order.event.event_date) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Order -->
            <div
                class="mb-6 rounded-2xl border border-white/[0.06] bg-white/[0.02] p-6"
            >
                <div class="mb-4 flex items-center justify-between">
                    <h3
                        class="text-xs font-bold tracking-[0.15em] text-gray-500 uppercase"
                    >
                        Order Details
                    </h3>
                    <span class="font-mono text-xs text-gray-600"
                        >#{{ order.id }}</span
                    >
                </div>
                <div class="space-y-3">
                    <div
                        v-for="(item, i) in order.items"
                        :key="i"
                        class="flex items-center justify-between text-sm"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-xs text-emerald-400"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M5 13l4 4L19 7"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>
                            <span class="text-gray-300">{{
                                item.seat_label
                            }}</span>
                        </div>
                        <span class="text-gray-500">{{
                            formatPrice(item.price_in_paisa)
                        }}</span>
                    </div>
                </div>
                <div
                    class="mt-5 space-y-2 border-t border-white/[0.06] pt-4 text-sm"
                >
                    <div class="flex justify-between text-gray-500">
                        <span>Subtotal</span
                        ><span>{{ formatPrice(order.subtotal_in_paisa) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>GST (18%)</span
                        ><span>{{ formatPrice(order.tax_in_paisa) }}</span>
                    </div>
                    <div
                        class="flex justify-between pt-2 text-lg font-bold text-white"
                    >
                        <span>Total Paid</span
                        ><span class="text-emerald-400">{{
                            formatPrice(order.total_in_paisa)
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment -->
            <div
                v-if="order.payment"
                class="mb-8 rounded-2xl border border-white/[0.06] bg-white/[0.02] p-6"
            >
                <h3
                    class="mb-4 text-xs font-bold tracking-[0.15em] text-gray-500 uppercase"
                >
                    Payment
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Method</span
                        ><span class="text-gray-300">{{
                            formatMethod(order.payment.method)
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Transaction ID</span
                        ><span class="font-mono text-xs text-gray-400">{{
                            order.payment.transaction_id
                        }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="flex items-center gap-1.5 text-emerald-400"
                            ><span
                                class="h-1.5 w-1.5 rounded-full bg-emerald-400"
                            />Paid</span
                        >
                    </div>
                </div>
            </div>

            <a
                href="/"
                class="block rounded-xl border border-white/[0.06] bg-white/[0.02] py-4 text-center text-sm font-semibold text-gray-400 transition hover:border-white/[0.12] hover:text-white"
            >
                Browse More Events
            </a>
        </div>
    </div>
</template>
