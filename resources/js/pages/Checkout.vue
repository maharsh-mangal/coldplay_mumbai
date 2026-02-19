<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface OrderItem {
    id: number;
    seat_label: string;
    price_in_paisa: number;
}

interface Order {
    id: number;
    subtotal_in_paisa: number;
    tax_in_paisa: number;
    total_in_paisa: number;
    expires_at: string;
    event: {
        slug: string;
        event_date: string;
        tour: { name: string; artist: string };
        venue: { name: string; city: string };
    };
    items: OrderItem[];
}

interface Address {
    id: number;
    label: string;
    full_address: string;
    is_default: boolean;
}

interface PaymentMethodOption {
    value: string;
    label: string;
}

const props = defineProps<{
    order: Order;
    addresses: Address[];
    paymentMethods: PaymentMethodOption[];
}>();

const selectedAddress = ref<number | null>(
    props.addresses.find((a) => a.is_default)?.id ??
        props.addresses[0]?.id ??
        null,
);
const selectedPayment = ref(props.paymentMethods[0]?.value ?? '');
const isSubmitting = ref(false);

const remainingSeconds = ref(0);
let timer: ReturnType<typeof setInterval>;

onMounted(() => {
    updateRemaining();
    timer = setInterval(updateRemaining, 1000);
});
onUnmounted(() => clearInterval(timer));

function updateRemaining(): void {
    const diff = Math.floor(
        (new Date(props.order.expires_at).getTime() - Date.now()) / 1000,
    );
    remainingSeconds.value = Math.max(0, diff);
    if (remainingSeconds.value === 0) clearInterval(timer);
}

const countdown = computed((): string => {
    const m = Math.floor(remainingSeconds.value / 60);
    const s = remainingSeconds.value % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
});

const isExpired = computed(() => remainingSeconds.value === 0);
const isUrgent = computed(
    () => remainingSeconds.value < 120 && remainingSeconds.value > 0,
);

function formatPrice(paisa: number): string {
    return (
        '₹' +
        (paisa / 100).toLocaleString('en-IN', { minimumFractionDigits: 2 })
    );
}

function confirmBooking(): void {
    if (isSubmitting.value || isExpired.value) return;
    isSubmitting.value = true;
    router.post(
        `/orders/${props.order.id}/confirm`,
        {
            address_id: selectedAddress.value,
            payment_method: selectedPayment.value,
        },
        {
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Checkout" />

    <div class="min-h-screen bg-[#0a0a0f]">
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div
                class="absolute -top-40 right-1/4 h-96 w-96 rounded-full bg-purple-600/5 blur-[120px]"
            />
        </div>

        <div class="relative mx-auto max-w-3xl px-6 py-12">
            <a
                :href="`/events/${order.event.slug}`"
                class="mb-8 inline-flex items-center gap-2 text-sm text-gray-500 transition hover:text-white"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M15 19l-7-7 7-7"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                Back to event
            </a>

            <h1 class="mb-2 text-3xl font-extrabold text-white">Checkout</h1>
            <p class="mb-8 text-sm text-gray-500">
                {{ order.event.tour.artist }} · {{ order.event.venue.name }},
                {{ order.event.venue.city }}
            </p>

            <!-- Timer -->
            <div
                class="mb-8 flex items-center justify-center gap-3 rounded-2xl border p-4 text-sm"
                :class="{
                    'border-red-500/20 bg-red-500/5 text-red-400': isExpired,
                    'border-amber-500/20 bg-amber-500/5 text-amber-400':
                        isUrgent && !isExpired,
                    'border-white/[0.06] bg-white/[0.02] text-gray-400':
                        !isExpired && !isUrgent,
                }"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <circle cx="12" cy="12" r="10" stroke-width="1.5" />
                    <path
                        d="M12 6v6l4 2"
                        stroke-width="1.5"
                        stroke-linecap="round"
                    />
                </svg>
                <span v-if="isExpired"
                    >Your seat hold has expired.
                    <a
                        :href="`/events/${order.event.slug}`"
                        class="font-semibold underline"
                        >Go back</a
                    ></span
                >
                <span v-else
                    >Seats held for
                    <span class="font-mono font-bold text-white">{{
                        countdown
                    }}</span></span
                >
            </div>

            <div class="space-y-6">
                <!-- Seats -->
                <div
                    class="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-6"
                >
                    <h3
                        class="mb-4 text-xs font-bold tracking-[0.15em] text-gray-500 uppercase"
                    >
                        Your Seats
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center justify-between text-sm"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/[0.04] text-xs text-gray-500"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 002 2h3a2 2 0 002-2V7a2 2 0 00-2-2H5zM5 15a2 2 0 00-2 2v1a2 2 0 002 2h3a2 2 0 002-2v-1a2 2 0 00-2-2H5z"
                                            stroke-width="1.5"
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
                            ><span>{{
                                formatPrice(order.subtotal_in_paisa)
                            }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>GST (18%)</span
                            ><span>{{ formatPrice(order.tax_in_paisa) }}</span>
                        </div>
                        <div
                            class="flex justify-between pt-2 text-lg font-bold text-white"
                        >
                            <span>Total</span
                            ><span>{{
                                formatPrice(order.total_in_paisa)
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div
                    v-if="addresses.length > 0"
                    class="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-6"
                >
                    <h3
                        class="mb-4 text-xs font-bold tracking-[0.15em] text-gray-500 uppercase"
                    >
                        Delivery Address
                    </h3>
                    <div class="space-y-2">
                        <label
                            v-for="addr in addresses"
                            :key="addr.id"
                            class="flex cursor-pointer items-start gap-4 rounded-xl border p-4 transition"
                            :class="
                                selectedAddress === addr.id
                                    ? 'border-rose-500/30 bg-rose-500/5'
                                    : 'border-white/[0.06] hover:border-white/[0.12]'
                            "
                        >
                            <input
                                v-model="selectedAddress"
                                type="radio"
                                :value="addr.id"
                                class="mt-0.5 accent-rose-500"
                            />
                            <div>
                                <p class="text-sm font-semibold text-white">
                                    {{ addr.label }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    {{ addr.full_address }}
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Payment -->
                <div
                    class="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-6"
                >
                    <h3
                        class="mb-4 text-xs font-bold tracking-[0.15em] text-gray-500 uppercase"
                    >
                        Payment Method
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            v-for="method in paymentMethods"
                            :key="method.value"
                            class="cursor-pointer rounded-xl border p-4 text-center text-sm font-medium transition"
                            :class="
                                selectedPayment === method.value
                                    ? 'border-rose-500/30 bg-rose-500/5 text-white'
                                    : 'border-white/[0.06] text-gray-500 hover:border-white/[0.12] hover:text-gray-300'
                            "
                        >
                            <input
                                v-model="selectedPayment"
                                type="radio"
                                :value="method.value"
                                class="sr-only"
                            />
                            {{ method.label }}
                        </label>
                    </div>
                </div>

                <button
                    :disabled="isSubmitting || isExpired || !selectedPayment"
                    class="w-full rounded-xl bg-gradient-to-r from-rose-500 to-purple-600 py-4 text-base font-bold text-white shadow-lg shadow-rose-500/20 transition-all hover:shadow-xl hover:shadow-rose-500/30 disabled:opacity-40 disabled:shadow-none"
                    @click="confirmBooking"
                >
                    {{
                        isSubmitting
                            ? 'Processing Payment...'
                            : `Pay ${formatPrice(order.total_in_paisa)}`
                    }}
                </button>
            </div>
        </div>
    </div>
</template>
