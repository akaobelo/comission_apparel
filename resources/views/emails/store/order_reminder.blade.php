<x-mail::message>
# Friendly Reminder from {{ $store->user->name }} ({{ $store->user->organization }})

We noticed that we haven't received your sizing for the **{{ $store->name }}** yet.

**The ordering deadline is {{ $store->order_deadline->format('l, F j, Y') }}.**

Please click the link below to enter your athlete's information and secure their gear before the deadline passes!

<x-mail::button :url="route('store.show', $store->slug)" color="primary">
Submit Your Sizing Now
</x-mail::button>

If you have already submitted your order, you can safely ignore this message.

Thank you,
The {{ config('app.name') }} Team
</x-mail::message>
