@extends('layouts.app')
@section('title', 'Edit Order | Admin')
@section('content')
<div class="max-w-3xl mx-auto px-6 pb-8 pt-32 lg:pt-40">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.store.edit', $order->teamStore) }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1 text-sm font-bold uppercase tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Store
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold">Editing: {{ $order->athlete_name }}'s Order</span>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Order Editor</h2>
            <p class="text-sm text-slate-500 mt-1">Store: {{ $order->teamStore->name }} · Submitted {{ $order->created_at->format('M d, Y') }}</p>
            @if($order->is_edited)
                <div class="mt-2 inline-flex items-center gap-1 px-2 py-1 bg-orange-100 border border-orange-200 text-orange-700 text-xs font-bold rounded">
                    Previously edited by {{ $order->edited_by }}
                </div>
            @endif
        </div>
        <div class="p-6">
            <form action="{{ route('admin.order.update', $order) }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Athlete Name</label>
                        <input type="text" name="athlete_name" value="{{ old('athlete_name', $order->athlete_name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
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
                                <input type="hidden" name="items[{{ $idx }}][type]" value="{{ $item['type'] ?? '' }}">
                                <div class="flex flex-wrap gap-3">
                                    @if(($item['type'] ?? '') === 'backpack')
                                        <div>
                                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Name on Backpack</label>
                                            <input type="text" name="items[{{ $idx }}][name_on_item]" value="{{ old("items.{$idx}.name_on_item", $item['name_on_item'] ?? '') }}" class="bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm w-36">
                                        </div>
                                    @else
                                        <div>
                                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Size</label>
                                            <select name="items[{{ $idx }}][size]" class="bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                                @foreach($sizeChart as $size)
                                                    <option value="{{ $size }}" {{ old("items.{$idx}.size", $item['size'] ?? '') === $size ? 'selected' : '' }}>{{ $size }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Qty</label>
                                        <input type="number" name="items[{{ $idx }}][qty]" value="{{ old("items.{$idx}.qty", $item['qty'] ?? 1) }}" min="1" max="10" class="bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm w-16">
                                    </div>
                                    @if(!empty($item['number']) || in_array($item['type'] ?? '', ['uniform_top', 'uniform_bottom']))
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Player #</label>
                                        <input type="text" name="items[{{ $idx }}][number]" value="{{ old("items.{$idx}.number", $item['number'] ?? '') }}" placeholder="00" maxlength="3" class="bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm w-16">
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn btn-primary py-3 px-8 text-sm uppercase tracking-wider">Save Changes</button>
                    <a href="{{ route('admin.store.edit', $order->teamStore) }}" class="btn btn-outline py-3 px-6 text-sm uppercase tracking-wider">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
