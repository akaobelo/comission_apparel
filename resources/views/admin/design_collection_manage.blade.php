@extends('layouts.app')
@section('title', 'Manage Collection | ' . $collection->name)

@section('content')
<div class="max-w-[1200px] mx-auto px-6 pb-8" style="padding-top: clamp(2rem, 10vw, 7rem);">
    
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-bold flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex flex-col gap-2">
            <div class="flex items-center gap-3 font-bold">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Please correct the following errors:</span>
            </div>
            <ul class="list-disc list-inside ml-8 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white rounded-lg text-xs font-black uppercase tracking-wider transition-all shadow-sm hover:shadow cursor-pointer">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Admin Control Center</span>
        </a>
    </div>

    <div class="mb-6 flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-slate-500">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Dashboard
        </a>
        <span class="text-slate-300">/</span>
        <span>Manage Collection</span>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-8">
        <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center gap-6">
            @if($collection->image_path)
                <img src="{{ $collection->image_path }}" class="w-24 h-24 object-cover rounded-lg border border-slate-200 shadow-sm">
            @else
                <div class="w-24 h-24 bg-slate-200 rounded-lg flex items-center justify-center text-slate-400 font-bold text-xs shadow-sm border border-slate-300 uppercase tracking-wider text-center px-2">No Cover</div>
            @endif
            <div>
                <h1 class="text-3xl font-black uppercase tracking-tight text-slate-900">{{ $collection->name }}</h1>
                <p class="text-sm text-slate-500 font-bold mt-1 uppercase tracking-wider">{{ $collection->designs->count() }} Items in Collection</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Collection Items (Left side - 2 cols) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden" x-data="{ search: '' }">
                <div class="p-5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-black uppercase tracking-tight text-slate-900">Manage Sort Order</h2>
                            <span id="sort-status-indicator" class="text-xs font-bold text-slate-400">Drag items to reorder</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Lower numbers appear first.</p>
                    </div>
                    <div class="relative w-full sm:w-64">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" x-model="search" placeholder="Search collection..." class="pl-9 pr-4 py-2 w-full bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm" autocomplete="off">
                    </div>
                </div>
                
                <div x-show="search.trim() !== ''" x-cloak class="px-5 py-2.5 bg-amber-50 border-b border-amber-200 text-amber-800 text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Search filter is active. Clear search before dragging to reorder items to avoid order conflicts.</span>
                </div>
                
                @php
                    $typeOptions = [
                        'accessory' => 'Accessories',
                        'arm_sleeve' => 'Arm Sleeves',
                        'arm_sleeve_2' => 'Arm Sleeves #2',
                        'arm_sleeve_3' => 'Arm Sleeves #3',
                        'arm_sleeve_4' => 'Arm Sleeves #4',
                        'backpack' => 'Backpacks',
                        'headwear' => 'Headwear',
                        'hoodie' => 'Hoodies & Pullovers',
                        'jacket' => 'Jackets',
                        'leggings' => 'Leggings/Tights',
                        'pants' => 'Pants',
                        'polo' => 'Polos',
                        'shirt_short' => 'Shirts (short sleeve)',
                        'shirt_long' => 'Shirts (long sleeve)',
                        'shorts' => 'Shorts',
                        'socks' => 'Socks',
                        'uniform_top' => 'Uniform (top)',
                        'uniform_bottom' => 'Uniform (bottom)',
                        'uniform_set' => 'Uniform Set (top/bottom)',
                        'uniform_2_top' => 'Uniform #2 (top)',
                        'uniform_2_bottom' => 'Uniform #2 (bottom)',
                        'uniform_set_2' => 'Uniform #2 (Set)',
                        'uniform_3_top' => 'Uniform #3 (top)',
                        'uniform_3_bottom' => 'Uniform #3 (bottom)',
                        'uniform_set_3' => 'Uniform #3 (Set)',
                        'uniform_4_top' => 'Uniform #4 (top)',
                        'uniform_4_bottom' => 'Uniform #4 (bottom)',
                        'uniform_set_4' => 'Uniform #4 (Set)',
                        'warmup_top' => 'Warm-up (top)',
                        'warmup_bottom' => 'Warm-up (bottom)',
                        'warmup_set' => 'Warm-up (top/bottom)',
                        'warmup_2_top' => 'Warm-up #2 (top)',
                        'warmup_2_bottom' => 'Warm-up #2 (bottom)',
                        'warmup_set_2' => 'Warm-up #2 (set)',
                        'uniform_package_gold' => 'Uniform Package (Gold)',
                        'uniform_package_silver' => 'Uniform Package (Silver)',
                        'uniform_package_bronze' => 'Uniform Package (Bronze)',
                        'uniform_package_custom' => 'Uniform Package (Custom)',
                    ];
                @endphp
                <script>
                    window.manageCollectionTypeOptions = {!! json_encode($typeOptions) !!};
                    window.manageCollectionItems = {!! json_encode($collection->designs->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'type_label' => $d->type_label])->values()) !!};
                </script>
                @if($collection->designs->isEmpty())
                    <div class="p-10 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <h3 class="text-base font-black uppercase text-slate-900 mb-1">No Items Yet</h3>
                        <p class="text-sm text-slate-500">Add designs from the global catalog using the panel on the right.</p>
                    </div>
                @else
                    <form action="{{ route('admin.design-collection.bulk-sort', $collection) }}" method="POST">
                        @csrf
                        <div id="sortable-list" class="divide-y divide-slate-100 max-h-[800px] overflow-y-auto">
                            @foreach($collection->designs as $index => $design)
                                <div x-data="{
                                        editingItem: false,
                                        selectedTypes: {{ json_encode(array_values($design->types ?? [])) }},
                                        savedCompIds: {{ json_encode((object) (is_array($design->component_design_ids) ? $design->component_design_ids : [])) }}
                                     }"
                                     x-show="search === '' || '{{ strtolower(addslashes($design->name)) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes($design->sport)) }}'.includes(search.toLowerCase())" 
                                     class="sortable-item p-4 hover:bg-slate-50 transition-colors bg-white">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex items-center gap-4 flex-1">
                                            <div class="cursor-move text-slate-300 hover:text-slate-500 transition-colors px-1" title="Drag to reorder">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                            </div>
                                            <div class="sort-number-display text-slate-400 font-black text-xl opacity-50 w-6 text-center">
                                                {{ $design->sort_order }}
                                            </div>
                                            @php
                                                $imageSrc = null;
                                                if (!empty($design->image_paths)) {
                                                    $imageSrc = $design->image_paths[0];
                                                } elseif ($design->image_url) {
                                                    $imageSrc = $design->image_url;
                                                }
                                            @endphp
                                            @if($imageSrc)
                                                <div class="w-12 h-16 rounded overflow-hidden flex-shrink-0 border border-slate-200">
                                                    <img src="{{ asset($imageSrc) }}" class="w-full h-full object-cover">
                                                </div>
                                            @else
                                                <div class="w-12 h-16 rounded bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0">
                                                    <span class="text-[10px] text-slate-400 font-bold uppercase">No Img</span>
                                                </div>
                                            @endif
                                            
                                            <div>
                                                <div class="text-sm font-bold text-slate-900">{{ $design->name }}</div>
                                                <div class="text-[10px] font-bold uppercase tracking-wider text-primary mt-1">
                                                    {{ $design->sport ? $design->sport . ' · ' : '' }}{{ $design->type_label }}
                                                </div>
                                                <button type="button" @click="editingItem = !editingItem" class="mt-1.5 inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-blue-600 hover:text-blue-800">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span x-text="editingItem ? 'Close Editor' : 'Edit Name / Package Pieces'"></span>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-3 shrink-0">
                                            <div class="flex flex-col">
                                                <label class="text-[10px] font-bold uppercase text-slate-500 mb-1">Sort Order</label>
                                                <input type="hidden" name="designs[{{ $design->id }}][id]" value="{{ $design->id }}">
                                                <input type="number" name="designs[{{ $design->id }}][sort_order]" value="{{ $design->sort_order }}" required class="sort-order-input w-20 bg-white border border-slate-300 rounded px-2 py-1.5 text-sm text-center text-slate-900 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary shadow-sm">
                                            </div>
                                            <div class="h-10 w-px bg-slate-200 mx-1"></div>
                                            <button type="button" onclick="if(confirm('Remove this design from the collection? (It will not be deleted from the system)')) { document.getElementById('remove-design-{{ $design->id }}').submit(); }" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors mt-4" title="Remove from Collection">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Inline Edit & Package Piece Mapping Drawer -->
                                    <template x-if="editingItem">
                                    <div class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                                        <input type="hidden" name="design_collection_id" value="{{ $collection->id }}" form="edit-design-{{ $design->id }}">
                                        <input type="hidden" name="sport" value="{{ $design->sport }}" form="edit-design-{{ $design->id }}">
                                        <input type="hidden" name="category" value="{{ $design->category ?: 'individual' }}" form="edit-design-{{ $design->id }}">
                                        <input type="hidden" name="wholesale_price" value="{{ $design->wholesale_price }}" form="edit-design-{{ $design->id }}">
                                        <input type="hidden" name="sort_order" value="{{ $design->sort_order }}" form="edit-design-{{ $design->id }}">
                                        <input type="hidden" name="description" value="{{ $design->description }}" form="edit-design-{{ $design->id }}">
                                        @if($design->has_name_field)
                                            <input type="hidden" name="has_name_field" value="1" form="edit-design-{{ $design->id }}">
                                        @endif
                                        @if($design->has_number_field)
                                            <input type="hidden" name="has_number_field" value="1" form="edit-design-{{ $design->id }}">
                                        @endif

                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Design / Package Name</label>
                                            <input type="text" name="name" value="{{ $design->name }}" required form="edit-design-{{ $design->id }}" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">Item Types</label>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                @foreach($typeOptions as $val => $label)
                                                    <label class="flex items-center gap-1.5 text-[11px] text-slate-700 cursor-pointer">
                                                        <input
                                                            type="checkbox"
                                                            name="types[]"
                                                            value="{{ $val }}"
                                                            x-model="selectedTypes"
                                                            form="edit-design-{{ $design->id }}"
                                                            class="rounded border-slate-300 text-primary focus:ring-primary"
                                                        >
                                                        <span>{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div x-show="selectedTypes.length > 1" x-cloak class="p-3 bg-blue-50/80 border border-blue-200 rounded-lg space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[10px] font-black uppercase tracking-wider text-blue-900">Package / Set Piece Mapping (Link Exact Item per Piece)</span>
                                                <span class="text-[9px] font-semibold text-blue-700">Links by ID (safe if renamed later)</span>
                                            </div>
                                            <p class="text-[10px] text-slate-600 leading-snug">
                                                Pick which item in <strong>{{ $collection->name }}</strong> corresponds to each checked piece so the Manufacturer Excel Chart pulls the exact garment image and combines quantities automatically:
                                            </p>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                                <template x-for="val in selectedTypes" :key="val">
                                                    <div class="bg-white p-2 rounded border border-blue-100 shadow-2xs">
                                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-700 mb-1" x-text="(window.manageCollectionTypeOptions && window.manageCollectionTypeOptions[val]) ? window.manageCollectionTypeOptions[val] : val"></label>
                                                        <select :name="'component_design_ids[' + val + ']'" form="edit-design-{{ $design->id }}" x-init="$nextTick(() => { if (savedCompIds[val]) $el.value = String(savedCompIds[val]); })" @change="savedCompIds[val] = $event.target.value" class="w-full bg-slate-50 border border-slate-300 rounded px-2 py-1.5 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                                            <option value="">-- Auto-detect / Use Package Image --</option>
                                                            <template x-for="cand in (window.manageCollectionItems || []).filter(i => i.id !== {{ $design->id }})" :key="cand.id">
                                                                <option :value="cand.id" :selected="String(savedCompIds[val] || '') === String(cand.id)" x-text="cand.name + ' (' + (cand.type_label || '') + ')'"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="flex justify-end gap-2 pt-1">
                                            <button type="button" @click="editingItem = false" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-600 text-[10px] font-bold uppercase tracking-wider rounded-lg hover:bg-slate-100">
                                                Cancel
                                            </button>
                                            <button type="submit" form="edit-design-{{ $design->id }}" class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg transition-colors">
                                                Save Item & Piece Links
                                            </button>
                                        </div>
                                    </div>
                                    </template>
                                </div>
                            @endforeach
                        </div>
                        <div class="p-5 border-t border-slate-200 bg-slate-50 flex justify-end">
                            <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-colors shadow-sm">
                                Save Sort Order
                            </button>
                        </div>
                    </form>

                    <!-- Hidden remove & edit forms -->
                    @foreach($collection->designs as $design)
                        <form id="remove-design-{{ $design->id }}" action="{{ route('admin.design-collection.remove-item', ['collection' => $collection->id, 'design' => $design->id]) }}" method="POST" class="hidden">
                            @csrf
                        </form>
                        <form id="edit-design-{{ $design->id }}" action="{{ route('admin.design.update', $design) }}" method="POST" class="hidden">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="return_to" value="{{ route('admin.design-collection.manage', $collection) }}">
                        </form>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Add Items (Right side - 1 col) -->
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden sticky top-24">
                <div class="p-5 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Add Design</h2>
                    <p class="text-xs text-slate-500 mt-1">Assign an existing design to this collection.</p>
                </div>
                <div class="p-5">
                    <form action="{{ route('admin.design-collection.add-item', $collection) }}" method="POST" class="space-y-4">
                        @csrf
                        <div x-data="{
                            open: false,
                            search: '',
                            items: {{ json_encode($availableDesigns->map(function($d) { return ['id' => $d->id, 'name' => $d->name, 'sport' => $d->sport, 'type' => $d->type_label, 'image' => (!empty($d->image_paths) ? asset($d->image_paths[0]) : ($d->image_url ? asset($d->image_url) : ''))]; })->values()) }},
                            selectedId: '',
                            selectedName: '',
                            get filteredItems() {
                                if (this.search === '') return this.items.slice(0, 50); // Show max 50 by default
                                const lowerSearch = this.search.toLowerCase();
                                return this.items.filter(i => 
                                    i.name.toLowerCase().includes(lowerSearch) || 
                                    (i.sport && i.sport.toLowerCase().includes(lowerSearch))
                                ).slice(0, 50);
                            },
                            selectItem(item) {
                                this.selectedId = item.id;
                                this.selectedName = item.name;
                                this.search = '';
                                this.open = false;
                            }
                        }" class="relative">
                            
                            <!-- Search Input -->
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Search Catalog</label>
                            <div class="relative">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" x-model="search" @focus="open = true" @click.away="open = false" placeholder="Type design name or sport..." class="pl-9 pr-4 py-2.5 w-full bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm" autocomplete="off">
                            </div>

                            <!-- Dropdown Results -->
                            <div x-show="open" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-xl max-h-64 overflow-y-auto">
                                <div class="p-1">
                                    <template x-for="item in filteredItems" :key="item.id">
                                        <div @click="selectItem(item)" class="px-3 py-2 hover:bg-slate-50 rounded cursor-pointer transition-colors border-b border-slate-50 last:border-0 flex items-center gap-3">
                                            <div x-show="item.image" class="w-10 h-10 bg-slate-100 rounded border border-slate-200 overflow-hidden flex-shrink-0">
                                                <img :src="item.image" class="w-full h-full object-cover">
                                            </div>
                                            <div x-show="!item.image" class="w-10 h-10 bg-slate-100 rounded border border-slate-200 flex items-center justify-center flex-shrink-0">
                                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest text-center leading-none">No Img</span>
                                            </div>
                                            <div>
                                                <div class="font-bold text-sm text-slate-900" x-text="item.name"></div>
                                                <div class="text-[10px] uppercase font-bold text-slate-500 mt-0.5">
                                                    <span x-show="item.sport" x-text="item.sport + ' · '"></span>
                                                    <span x-text="item.type"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="filteredItems.length === 0" class="px-3 py-4 text-center text-xs text-slate-400">
                                        No designs found matching your search.
                                    </div>
                                </div>
                            </div>

                            <!-- Selected Item -->
                            <div x-show="selectedId !== ''" x-cloak class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="text-[10px] font-bold uppercase text-green-600 mb-1">Selected Design:</div>
                                <div class="flex items-start justify-between gap-2">
                                    <div class="text-sm font-bold text-slate-900" x-text="selectedName"></div>
                                    <button type="button" @click="selectedId = ''; selectedName = ''" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <input type="hidden" name="design_catalog_id" :value="selectedId">

                            <div class="mt-4">
                                <button type="submit" :disabled="selectedId === ''" :class="selectedId === '' ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-secondary hover:bg-[#a11825] shadow-sm'" class="w-full py-2.5 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-all">
                                    Add to Collection
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('sortable-list');
        const statusEl = document.getElementById('sort-status-indicator');
        if (el) {
            new Sortable(el, {
                animation: 150,
                handle: '.cursor-move',
                ghostClass: 'bg-slate-50',
                onEnd: function () {
                    const items = Array.from(el.querySelectorAll('.sortable-item'));
                    const formData = new FormData();
                    const token = document.querySelector('input[name="_token"]')?.value;
                    if (token) formData.append('_token', token);

                    if (statusEl) {
                        statusEl.innerHTML = '<span class="text-amber-600 font-bold animate-pulse">Saving new order...</span>';
                    }
                    
                    items.forEach((item, index) => {
                        const newSort = index + 1;
                        const input = item.querySelector('.sort-order-input');
                        if (input) input.value = newSort;
                        
                        const display = item.querySelector('.sort-number-display');
                        if (display) display.textContent = newSort;
                        
                        const idInput = item.querySelector('input[name$="[id]"]');
                        if (idInput && input) {
                            formData.append(`designs[${index}][id]`, idInput.value);
                            formData.append(`designs[${index}][sort_order]`, newSort);
                        }
                    });

                    // Auto-save via AJAX
                    fetch('{{ route('admin.design-collection.bulk-sort', $collection) }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    }).then(response => {
                        if (response.ok) {
                            if (statusEl) {
                                statusEl.innerHTML = '<span class="text-emerald-600 font-bold flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Order saved!</span>';
                                setTimeout(() => {
                                    statusEl.innerText = 'Drag items to reorder';
                                }, 2000);
                            }
                        } else {
                            if (statusEl) statusEl.innerHTML = '<span class="text-red-500 font-bold">Error saving order</span>';
                        }
                    }).catch(err => {
                        if (statusEl) statusEl.innerHTML = '<span class="text-red-500 font-bold">Error saving order</span>';
                    });
                }
            });
        }
    });
</script>
@endsection
