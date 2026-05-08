@extends('layouts.app')
@section('title', 'Edit Store | Admin')
@section('content')
<div class="max-w-5xl mx-auto px-6 pb-8 pt-32 lg:pt-40">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1 text-sm font-bold uppercase tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold">Store: {{ $store->name }}</span>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-8">
        {{-- Left: Store details --}}
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Store Settings</h2>
                    <p class="text-sm text-slate-500 mt-1">Coach: {{ $store->user->name }} — {{ $store->user->organization }}</p>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.store.update', $store) }}" method="POST" class="space-y-5">
                        @csrf
                        <div class="grid grid-cols-2 gap-5">
                            <div class="col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Store Name</label>
                                <input type="text" name="name" value="{{ old('name', $store->name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Package Type</label>
                                <select name="package_type" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="">Not set</option>
                                    <option value="package_a" {{ old('package_type', $store->package_type) === 'package_a' ? 'selected' : '' }}>Package A — Base Kit</option>
                                    <option value="package_b" {{ old('package_type', $store->package_type) === 'package_b' ? 'selected' : '' }}>Package B — Standard</option>
                                    <option value="package_c" {{ old('package_type', $store->package_type) === 'package_c' ? 'selected' : '' }}>Package C — Full Program</option>
                                    <option value="individual" {{ old('package_type', $store->package_type) === 'individual' ? 'selected' : '' }}>Individual Items</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Store Status</label>
                                <select name="status" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="pending" {{ old('status', $store->status) === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                                    <option value="approved" {{ old('status', $store->status) === 'approved' ? 'selected' : '' }}>Approved / Active</option>
                                    <option value="submitted_to_admin" {{ old('status', $store->status) === 'submitted_to_admin' ? 'selected' : '' }}>Finalized / In Production</option>
                                    <option value="declined" {{ old('status', $store->status) === 'declined' ? 'selected' : '' }}>Declined</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Pricing Status</label>
                                <select name="pricing_approved" required class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    <option value="0" {{ !old('pricing_approved', $store->pricing_approved) ? 'selected' : '' }}>Pending Coach Approval</option>
                                    <option value="1" {{ old('pricing_approved', $store->pricing_approved) ? 'selected' : '' }}>Approved by Coach</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Order Deadline</label>
                                <input type="date" name="order_deadline" value="{{ old('order_deadline', $store->order_deadline?->format('Y-m-d')) }}" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Description</label>
                                <textarea name="description" rows="3" class="w-full bg-white border border-slate-300 rounded-lg px-4 py-3 text-slate-900 focus:border-primary focus:outline-none shadow-sm" placeholder="Optional store description for the coach...">{{ old('description', $store->description) }}</textarea>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="btn btn-primary py-3 px-8 text-sm uppercase tracking-wider">Save Changes</button>
                            <a href="{{ route('admin.stores.export', $store) }}" class="btn btn-outline py-3 px-6 text-sm uppercase tracking-wider">Export CSV</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Set pricing for store items --}}
            @if($store->items->isNotEmpty())
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Line-Item Pricing Checkout</h2>
                </div>
                <div class="p-5">
                    <form action="{{ route('admin.store.pricing.update', $store) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($store->items as $item)
                            <div class="border border-slate-200 rounded-lg p-3 bg-slate-50">
                                <div class="font-bold text-slate-900 mb-2">{{ $item->name }}</div>
                                <div class="flex gap-3">
                                    <div class="flex-1">
                                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Wholesale ($)</label>
                                        <input type="number" step="0.01" name="items[{{ $item->id }}][wholesale_price]" value="{{ old('items.'.$item->id.'.wholesale_price', $item->wholesale_price) }}" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1">Retail ($)</label>
                                        <input type="number" step="0.01" name="items[{{ $item->id }}][retail_price]" value="{{ old('items.'.$item->id.'.retail_price', $item->retail_price) }}" class="w-full bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary py-2 px-6 text-sm uppercase tracking-wider">Save Pricing</button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Package Management --}}
            @php
                $packages = $store->items->filter->isPackage();
            @endphp
            @if($packages->isNotEmpty())
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Package Configuration</h2>
                </div>
                <div class="p-5 space-y-6">
                    @foreach($packages as $package)
                    <div class="border border-slate-200 rounded-lg p-4 bg-slate-50">
                        <div class="font-bold text-slate-900 mb-3">{{ $package->name }}</div>
                        
                        @if($package->components->isNotEmpty())
                            <div class="mb-4 space-y-2">
                                <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500">Included Items</label>
                                @foreach($package->components as $component)
                                    <div class="flex items-center justify-between bg-white border border-slate-200 p-2 rounded">
                                        <span class="text-sm font-medium text-slate-700">{{ $component->name }}</span>
                                        <form action="{{ route('admin.store.package.detach', [$store, $package, $component]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-[10px] font-black uppercase tracking-wider">Remove</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-slate-500 mb-4 italic">No items attached to this package yet.</div>
                        @endif

                        <form action="{{ route('admin.store.package.attach', [$store, $package]) }}" method="POST" class="flex gap-2">
                            @csrf
                            <select name="component_id" required class="flex-1 bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                                <option value="">-- Select an item to add to this package --</option>
                                @foreach($store->items->where('id', '!=', $package->id) as $potentialComponent)
                                    @if(!$package->components->contains($potentialComponent->id) && !$potentialComponent->isPackage())
                                        <option value="{{ $potentialComponent->id }}">{{ $potentialComponent->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary py-2 px-4 text-xs uppercase tracking-wider">Add Item</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Order roster for this store --}}
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Order Roster ({{ $store->parentOrders->count() }} athletes)</h2>
                </div>
                @if($store->parentOrders->isEmpty())
                    <div class="p-8 text-center text-slate-400 text-sm">No orders submitted yet.</div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3 text-left">Athlete</th>
                                <th class="px-5 py-3 text-left">Items</th>
                                <th class="px-5 py-3 text-left">Submitted</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($store->parentOrders as $order)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <div class="font-bold text-slate-900">{{ $order->athlete_name }}</div>
                                    @if($order->is_edited)
                                        <div class="text-[10px] font-bold uppercase tracking-wide text-orange-500">Edited by {{ $order->edited_by }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ is_array($order->items_json) ? count($order->items_json) : 0 }} item(s)</td>
                                <td class="px-5 py-3 text-slate-500 text-xs">{{ $order->created_at->format('M d, Y') }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.order.edit', $order) }}" class="px-3 py-1.5 bg-white border border-secondary text-secondary text-xs font-bold rounded-lg hover:bg-secondary hover:text-white transition-colors">Edit</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Right: Quick stats --}}
        <div class="space-y-5">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-black uppercase tracking-tight text-slate-900 mb-4">Store Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Store Status</span><span class="font-bold text-primary uppercase">{{ str_replace('_', ' ', $store->status) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Pricing Status</span>
                        @if($store->pricing_approved)
                            <span class="font-bold text-green-600 uppercase text-xs inline-block ml-2">Approved</span>
                        @else
                            <span class="font-bold text-orange-500 uppercase text-xs inline-block ml-2">Pending</span>
                        @endif
                    </div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Athletes Ordered</span><span class="font-bold text-slate-900">{{ $store->parentOrders->count() }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Total Items</span><span class="font-bold text-slate-900">{{ $store->parentOrders->sum(fn($o) => count(is_array($o->items_json) ? $o->items_json : [])) }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Deadline</span><span class="font-bold text-slate-900">{{ $store->order_deadline?->format('M d, Y') ?? '—' }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Public URL</span>
                        <a href="{{ route('store.show', $store->slug) }}" target="_blank" class="font-bold text-primary hover:underline text-xs truncate max-w-[150px]">View Store</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
