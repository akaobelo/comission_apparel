@extends('layouts.app')
@section('title', 'Edit Order | Coach Portal')
@section('content')
<div class="max-w-3xl mx-auto px-6 pb-8 pt-32 lg:pt-40">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('coach.dashboard', ['tab' => 'overview']) }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1 text-sm font-bold uppercase tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold">Editing: {{ $order->athlete_first_name }} {{ $order->athlete_last_name }}'s Order</span>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold">{{ session('success') }}</div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Edit Parent Order</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $store ? 'Store: ' . $store->name : 'Direct Order' }} · Submitted {{ $order->created_at->format('M d, Y') }}</p>
            <p class="text-xs text-orange-600 font-bold mt-2">⚠ You are editing this order on behalf of the parent. Changes are logged.</p>
        </div>
        <div class="p-6">
            <form action="{{ route('coach.order.update', $order) }}" method="POST" class="space-y-6">
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

                <div x-data="orderItemsEditor()">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200">
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-700">Order Items</h3>
                        <div class="relative flex items-center">
                            <select x-model="newItemId" class="bg-white border border-slate-300 rounded px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-slate-600 focus:border-primary focus:outline-none shadow-sm appearance-none pr-8">
                                <option value="">+ Add Store Item</option>
                                <template x-for="item in availableItems" :key="item.id">
                                    <option :value="item.id" x-text="item.name"></option>
                                </template>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-12 flex items-center px-2 text-slate-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <button type="button" @click="addItem()" class="ml-2 px-3 py-1.5 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded shadow-sm hover:bg-[#a11825] transition-colors">Add</button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl relative">
                                <button type="button" @click="removeItem(idx)" class="absolute top-3 right-3 w-6 h-6 flex items-center justify-center rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors" title="Remove item">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                
                                <div class="flex items-start justify-between gap-4 flex-wrap pr-8">
                                    <div>
                                        <div class="font-bold text-slate-900" x-text="item.name || 'Unknown Item'"></div>
                                        <div class="text-xs font-bold uppercase tracking-wider text-primary mt-0.5" x-text="(item.type || '').replace(/_/g, ' ')"></div>
                                    </div>
                                    
                                    <input type="hidden" :name="`items[${idx}][id]`" :value="item.id">
                                    <input type="hidden" :name="`items[${idx}][name]`" :value="item.name">
                                    
                                    <template x-if="item.types && Array.isArray(item.types)">
                                        <template x-for="(t, tIdx) in item.types" :key="tIdx">
                                            <input type="hidden" :name="`items[${idx}][types][${tIdx}]`" :value="t">
                                        </template>
                                    </template>
                                    <template x-if="!item.types || !Array.isArray(item.types)">
                                        <input type="hidden" :name="`items[${idx}][type]`" :value="item.type || ''">
                                    </template>
                                    
                                    <div class="flex flex-wrap gap-3">
                                        <template x-for="(sizeVal, sizeType) in item.sizes" :key="sizeType">
                                            <div>
                                                <label class="block text-[10px] font-black uppercase text-slate-500 mb-1" x-text="sizeType === 'default' ? 'Size' : sizeType.replace(/_/g, ' ')"></label>
                                                <select :name="`items[${idx}][sizes][${sizeType}]`" x-model="item.sizes[sizeType]" class="bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm min-w-[80px]">
                                                    @foreach($sizeChart as $size)
                                                        <option value="{{ $size }}">{{ $size }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </template>
                                        
                                        <div>
                                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Qty</label>
                                            <input type="number" :name="`items[${idx}][qty]`" x-model="item.qty" min="1" max="50" class="bg-white border border-slate-300 rounded px-3 py-2 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm w-16">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="items.length === 0" class="p-6 text-center text-slate-500 font-bold text-sm uppercase tracking-wider bg-slate-50 border border-slate-200 border-dashed rounded-xl">
                            No items in order
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn btn-primary py-3 px-8 text-sm uppercase tracking-wider">Save Changes</button>
                    <a href="{{ route('coach.dashboard', ['tab' => 'overview']) }}" class="btn btn-outline py-3 px-6 text-sm uppercase tracking-wider">Cancel</a>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-200 flex justify-end">
                <form action="{{ route('coach.order.delete', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to completely delete this order? This cannot be undone.')">
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

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('orderItemsEditor', () => ({
        items: @json(is_array($order->items_json) ? $order->items_json : []),
        availableItems: @json($availableItems),
        newItemId: '',
        
        init() {
            // Ensure all existing items have a proper sizes object
            this.items.forEach(item => {
                if (!item.sizes && item.size) {
                    item.sizes = { 'default': item.size };
                } else if (!item.sizes) {
                    item.sizes = {};
                }
            });
        },
        
        addItem() {
            if (!this.newItemId) return;
            const storeItem = this.availableItems.find(i => i.id == this.newItemId);
            if (!storeItem) return;
            
            const sizes = {};
            if (storeItem.sizedTypes && storeItem.sizedTypes.length > 0) {
                storeItem.sizedTypes.forEach(t => {
                    sizes[t] = 'AS'; // default size
                });
            } else {
                sizes['default'] = 'AS'; // fallback
            }
            
            this.items.push({
                id: storeItem.id,
                name: storeItem.name,
                type: storeItem.types[0] || 'Unknown',
                types: storeItem.types,
                qty: 1,
                sizes: sizes
            });
            
            this.newItemId = '';
        },
        
        removeItem(idx) {
            this.items.splice(idx, 1);
        }
    }));
});
</script>
@endsection
