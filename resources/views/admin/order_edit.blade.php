@extends('layouts.app')
@section('title', 'Edit Order | Admin')
@section('content')
<div class="max-w-3xl mx-auto px-6 pb-8 pt-32 lg:pt-40">
    <div class="flex items-center gap-4 mb-8">
        @if($order->teamStore)
            <a href="{{ route('admin.store.edit', $order->teamStore) }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1 text-sm font-bold uppercase tracking-wide">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Store
            </a>
        @else
            <a href="{{ route('admin.direct-batch.show', $order->batch_id) }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1 text-sm font-bold uppercase tracking-wide">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Batch
            </a>
        @endif
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold">Editing: {{ $order->athlete_first_name }} {{ $order->athlete_last_name }}'s Order</span>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Order Editor</h2>
            @if($order->teamStore)
                <p class="text-sm text-slate-500 mt-1">Store: {{ $order->teamStore->name }} · Submitted {{ $order->created_at->format('M d, Y') }}</p>
            @else
                <p class="text-sm text-slate-500 mt-1">Direct Order Batch: {{ $order->batch_id }} · Submitted {{ $order->created_at->format('M d, Y') }}</p>
            @endif
            @if($order->is_edited)
                <div class="mt-2 inline-flex items-center gap-1 px-2 py-1 bg-orange-100 border border-orange-200 text-orange-700 text-xs font-bold rounded">
                    Previously edited by {{ $order->edited_by }}
                </div>
            @endif
        </div>
        <div class="p-6">
            <form action="{{ route('admin.order.update', $order) }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-2 md:grid-cols-3 gap-5 border-b border-slate-200 pb-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">First Name</label>
                        <input type="text" name="athlete_first_name" value="{{ old('athlete_first_name', $order->athlete_first_name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Last Name</label>
                        <input type="text" name="athlete_last_name" value="{{ old('athlete_last_name', $order->athlete_last_name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Gender</label>
                        <input type="text" name="gender" value="{{ old('gender', $order->gender) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Jersey Name</label>
                        <input type="text" name="jersey_name" value="{{ old('jersey_name', $order->jersey_name) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Jersey Number</label>
                        <input type="text" name="jersey_number" value="{{ old('jersey_number', $order->jersey_number) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Backpack Name</label>
                        <input type="text" name="backpack_name" value="{{ old('backpack_name', $order->backpack_name) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Special Notes</label>
                        <input type="text" name="special_notes" value="{{ old('special_notes', $order->special_notes) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-700 mb-4 pb-2 border-b border-slate-200">Order Items</h3>
                    <div class="space-y-4">
                        @foreach(is_array($order->items_json) ? $order->items_json : [] as $idx => $item)
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                            <div class="flex items-start justify-between gap-4 flex-wrap">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $item['name'] ?? 'Unknown Item' }}</div>
                                    <div class="text-xs font-bold uppercase tracking-wider text-primary mt-0.5">{{ str_replace('_', ' ', $item['type'] ?? '') }}</div>
                                </div>
                                <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $item['id'] ?? $idx }}">
                                <input type="hidden" name="items[{{ $idx }}][name]" value="{{ $item['name'] ?? '' }}">
                                @if(isset($item['types']) && is_array($item['types']))
                                    @foreach($item['types'] as $tIdx => $type)
                                        <input type="hidden" name="items[{{ $idx }}][types][{{ $tIdx }}]" value="{{ $type }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="items[{{ $idx }}][type]" value="{{ $item['type'] ?? '' }}">
                                @endif
                                <div class="flex flex-wrap gap-3">
                                    @php
                                        $sizes = $item['sizes'] ?? [];
                                        if (empty($sizes) && isset($item['size'])) {
                                            $sizes = ['default' => $item['size']];
                                        }
                                    @endphp
                                    @foreach($sizes as $sizeType => $sizeVal)
                                        <div>
                                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">{{ $sizeType === 'default' ? 'Size' : str_replace('_', ' ', $sizeType) }}</label>
                                            <select name="items[{{ $idx }}][sizes][{{ $sizeType }}]" class="bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                                @foreach($sizeChart as $size)
                                                    <option value="{{ $size }}" {{ $sizeVal === $size ? 'selected' : '' }}>{{ $size }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Qty</label>
                                        <input type="number" name="items[{{ $idx }}][qty]" value="{{ old("items.{$idx}.qty", $item['qty'] ?? 1) }}" min="1" max="10" class="bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm w-16">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn btn-primary py-3 px-8 text-sm uppercase tracking-wider">Save Changes</button>
                    @if($order->teamStore)
                        <a href="{{ route('admin.store.edit', $order->teamStore) }}" class="btn btn-outline py-3 px-6 text-sm uppercase tracking-wider">Cancel</a>
                    @else
                        <a href="{{ route('admin.direct-batch.show', $order->batch_id) }}" class="btn btn-outline py-3 px-6 text-sm uppercase tracking-wider">Cancel</a>
                    @endif
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-200 flex justify-end">
                <form action="{{ route('admin.order.delete', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to completely delete this order? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-2.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 hover:text-red-700 font-bold uppercase tracking-wider rounded-lg text-xs transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
