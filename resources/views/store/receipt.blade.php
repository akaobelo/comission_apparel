@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        {{-- Print controls --}}
        <div class="flex items-center justify-between mb-6 print:hidden">
            <a href="{{ route('store.show', $store->slug) }}" class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-slate-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to {{ $store->name }}
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-xs font-black uppercase tracking-wider rounded-xl hover:bg-slate-800 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Receipt
            </button>
        </div>

        {{-- Success Card --}}
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden p-6 sm:p-10 print:border-none print:shadow-none print:p-0">
            {{-- Header --}}
            <div class="text-center pb-8 border-b border-slate-100">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-200 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-slate-900">Order Confirmed</h1>
                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mt-1">Thank you for your order with The Commission Apparel</p>
                <div class="mt-4 inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest {{ $order->isPaid() ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                    <span>Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                    <span>•</span>
                    <span>{{ $order->isPaid() ? 'PAID ONLINE' : 'COLLECTED IN-HOUSE' }}</span>
                </div>
            </div>

            {{-- Summary Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-100 text-xs">
                <div>
                    <h4 class="font-black uppercase tracking-widest text-slate-400 text-[10px] mb-2">Athlete Details</h4>
                    <p class="font-bold text-slate-900 text-sm">{{ $order->athlete_name }}</p>
                    @if($order->jersey_name || $order->jersey_number)
                        <p class="text-slate-600 mt-1">Jersey: <span class="font-medium text-slate-900">{{ $order->jersey_name ?? '—' }} ({{ $order->jersey_number ?? '—' }})</span></p>
                    @endif
                    @if($order->gender)
                        <p class="text-slate-600">Gender: <span class="font-medium text-slate-900">{{ ucfirst($order->gender) }}</span></p>
                    @endif
                </div>
                <div>
                    <h4 class="font-black uppercase tracking-widest text-slate-400 text-[10px] mb-2">Team Store & Delivery</h4>
                    <p class="font-bold text-slate-900 text-sm">{{ $store->name }}</p>
                    <p class="text-slate-600 mt-1">Coach: <span class="font-medium text-slate-900">{{ $store->user->first_name ?? '' }} {{ $store->user->last_name ?? '' }}</span></p>
                    <p class="text-slate-600">Fulfillment: <span class="font-bold text-secondary">Batch Shipped to Coach</span></p>
                    @if($order->parent_email)
                        <p class="text-slate-600">Email: <span class="font-medium text-slate-900">{{ $order->parent_email }}</span></p>
                    @endif
                </div>
            </div>

            {{-- Items Ordered --}}
            <div class="py-6 border-b border-slate-100">
                <h4 class="font-black uppercase tracking-widest text-slate-400 text-[10px] mb-4">Items Ordered</h4>
                <div class="divide-y divide-slate-100">
                    @foreach($order->items_json ?? [] as $item)
                    <div class="py-3.5 flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-900">{{ $item['name'] ?? 'Custom Apparel Item' }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">Quantity: <span class="font-bold text-slate-800">{{ $item['qty'] ?? 1 }}</span></p>
                            @if(!empty($item['sizes']))
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    Sizes: 
                                    @foreach($item['sizes'] as $type => $sz)
                                        <span class="inline-block bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-bold uppercase text-[10px] mr-1">{{ $type }}: {{ $sz }}</span>
                                    @endforeach
                                </p>
                            @endif
                            @if(!empty($item['components']))
                                <div class="mt-1 space-y-0.5 pl-2 border-l-2 border-slate-200">
                                    @foreach($item['components'] as $comp)
                                        <p class="text-[11px] text-slate-600">
                                            <span class="font-semibold">{{ $comp['name'] ?? 'Item' }}:</span>
                                            @foreach($comp['sizes'] ?? [] as $cType => $cSize)
                                                <span class="text-slate-800 font-bold uppercase text-[10px]">{{ $cType }}: {{ $cSize }}</span>
                                            @endforeach
                                        </p>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-right">
                            @if(isset($item['unit_price']) && $item['unit_price'] > 0)
                                <span class="text-sm font-black text-slate-900">${{ number_format(($item['unit_price'] * ($item['qty'] ?? 1)), 2) }}</span>
                                @if(($item['qty'] ?? 1) > 1)
                                    <span class="block text-[10px] text-slate-400 font-medium">${{ number_format($item['unit_price'], 2) }} each</span>
                                @endif
                            @else
                                <span class="text-xs font-bold text-slate-500">In-House</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Financial Breakdown --}}
            <div class="py-6 border-b border-slate-100 space-y-2 text-xs">
                @if($order->isOnlineOrder())
                    <div class="flex justify-between text-slate-600 font-medium">
                        <span>Items Subtotal</span>
                        <span class="text-slate-900 font-bold">${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600 font-medium">
                        <span>Sales Tax (7.5%)</span>
                        <span class="text-slate-900 font-bold">${{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600 font-medium">
                        <span>Card Processing Fee</span>
                        <span class="text-slate-900 font-bold">${{ number_format($order->fee_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600 font-medium">
                        <span>Shipping & Delivery (Coach Batch)</span>
                        <span class="text-emerald-700 font-bold">FREE</span>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex justify-between items-center text-sm font-black text-slate-900">
                        <span class="uppercase tracking-wider">Total Paid</span>
                        <span class="text-lg text-emerald-700">${{ number_format($order->total_paid, 2) }}</span>
                    </div>
                @else
                    <div class="flex justify-between text-slate-600 font-medium">
                        <span>Total Items</span>
                        <span class="text-slate-900 font-bold">{{ collect($order->items_json ?? [])->sum(fn($i) => $i['qty'] ?? 1) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600 font-medium">
                        <span>Payment Method</span>
                        <span class="text-slate-900 font-bold">In-House (Pay Coach Directly)</span>
                    </div>
                @endif
            </div>

            {{-- Policy Notice --}}
            <div class="mt-6 p-4 bg-slate-50 border border-slate-200 rounded-2xl text-[11px] text-slate-600 leading-relaxed">
                <p class="font-black uppercase tracking-wider text-slate-900 mb-1">Store Policy Notice</p>
                <p>All purchases are custom-manufactured specifically for your athlete and team. <strong>All sales are final — no cancellations, returns, or refunds.</strong> When manufacturing completes, all items will be delivered directly in batch to your coach for distribution.</p>
                @if($order->stripe_payment_intent_id)
                    <p class="mt-2 text-[10px] text-slate-400 font-mono">Transaction Reference: {{ $order->stripe_payment_intent_id }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
