<x-mail::message>
    # Booking Confirmed!

    Your booking for **{{ $order->event->tour->name }}** has been confirmed.

    **Event:** {{ $order->event->tour->artist }} at {{ $order->event->venue->name }}
    **Date:** {{ $order->event->event_date->format('D, d M Y \a\t h:i A') }}
    **Order ID:** #{{ $order->id }}

    <x-mail::table>
        | Seat | Price |
        |:-----|------:|
        @foreach ($order->items as $item)
            | {{ $item->seat->section->name }} — Row {{ $item->seat->row }}, Seat {{ $item->seat->number }} | ₹{{ number_format($item->price_in_paisa / 100, 2) }} |
        @endforeach
        | **Subtotal** | **₹{{ number_format($order->subtotal_in_paisa / 100, 2) }}** |
        | **GST (18%)** | **₹{{ number_format($order->tax_in_paisa / 100, 2) }}** |
        | **Total** | **₹{{ number_format($order->total_in_paisa / 100, 2) }}** |
    </x-mail::table>

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
