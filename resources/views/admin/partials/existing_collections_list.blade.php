@php
if (!isset($typeOptions)) {
    $typeOptions = [
        'accessory' => 'Accessories',
        'arm_sleeve' => 'Arm Sleeves',
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
        'uniform_set_2' => 'Uniform Set #2 (top/bottom)',
        'warmup_top' => 'Warm-up (top)',
        'warmup_bottom' => 'Warm-up (bottom)',
        'warmup_set' => 'Warm-up (top/bottom)',
    ];
}
@endphp

@if($designCollections->isEmpty())
    <div class="p-8 text-center text-slate-500 font-bold uppercase tracking-widest text-sm">
        @if(request()->filled('collection_search'))
            No collections found matching "{{ request('collection_search') }}".
        @else
            No collections created yet.
        @endif
    </div>
@else
    <form id="bulk-collections-sort-form" action="{{ route('admin.design-collection.bulk-update') }}" method="POST">
        @csrf
    </form>
    <div class="p-3 bg-white border-b border-slate-200 flex justify-between items-center">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Adjust sort orders below and click save</span>
        <button type="submit" form="bulk-collections-sort-form" class="px-5 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm hover:bg-[#a11825] transition-colors">Save Sort Orders</button>
    </div>

    <div id="update-collections-sortable-list">
    @foreach($designCollections as $collection)
    <div x-data="{ showModal: false, showItems: false }" class="border-b border-slate-100 last:border-0 bg-white">
        <div class="flex items-center justify-between px-4 py-3 hover:bg-slate-50">
            <div class="flex items-center gap-4 flex-1">
                <div class="cursor-move text-slate-300 hover:text-slate-500 transition-colors px-1" title="Drag to reorder">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                </div>
                @if($collection->image_path)
                    <div class="w-12 h-12 rounded-lg bg-slate-200 overflow-hidden flex-shrink-0">
                        <img src="{{ $collection->image_path }}" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center flex-shrink-0">
                        <span class="text-[10px] text-slate-400 font-bold">NONE</span>
                    </div>
                @endif
                <div class="flex-1 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pr-4">
                    <div class="text-sm font-bold text-slate-900">{{ $collection->name }}</div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Sort Order:</span>
                        <input type="number" 
                               name="collections[{{ $collection->id }}][sort_order]"
                               value="{{ $collection->sort_order }}" 
                               form="bulk-collections-sort-form"
                               class="sort-order-input-update-col w-16 bg-white border border-slate-300 rounded-lg px-2 py-1 text-xs text-slate-900 focus:border-primary focus:outline-none shadow-sm text-center">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <button @click="showItems = !showItems" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 hover:text-secondary flex items-center gap-1 transition-colors mr-2">
                    <svg class="w-3 h-3 transform transition-transform" :class="showItems ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    View Items
                </button>
                <a href="{{ route('admin.design-collection.manage', $collection) }}" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 hover:text-secondary flex items-center gap-1 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Manage Items
                </a>
                <button @click="showModal = true" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 hover:text-primary flex items-center gap-1 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit
                </button>
            </div>

            <div x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center" x-cloak>
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showModal = false" x-transition.opacity></div>
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                        <h3 class="font-black uppercase tracking-tight text-slate-900">Edit Collection</h3>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-5">
                        <form action="{{ route('admin.design-collection.update', $collection) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Collection Name</label>
                                <input type="text" name="name" value="{{ $collection->name }}" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" value="{{ $collection->sort_order }}" required class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Update Cover Image</label>
                                <input type="file" name="image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Categories (Sports)</label>
                                <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-lg border border-slate-200 max-h-32 overflow-y-auto">
                                    @foreach($availableSports as $sport)
                                        <label class="inline-flex items-center text-xs text-slate-700 font-medium cursor-pointer">
                                            <input type="checkbox" name="sports[]" value="{{ $sport }}" 
                                                   @if(is_array($collection->sports) && in_array($sport, $collection->sports)) checked @endif
                                                   class="rounded border-slate-300 text-primary focus:ring-primary mr-2">
                                            {{ $sport }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="w-full py-2.5 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-700 transition-colors">Save Changes</button>
                            </div>
                        </form>
                        <form action="{{ route('admin.design-collection.delete', $collection) }}" method="POST" class="mt-4 pt-4 border-t border-slate-100" onsubmit="return confirm('Delete this collection? Designs in this collection will NOT be deleted, but will lose their collection grouping.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2.5 bg-white border border-red-200 text-red-600 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-red-50 transition-colors">Delete Collection</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @php
            $collectionItems = $collection->designs;
        @endphp
        <div class="border-t border-slate-100 bg-white" x-show="showItems" x-cloak x-data="{
            search: '',
            page: 1,
            perPage: 100,
            items: {{ json_encode($collectionItems->map(function($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'collection_name' => $d->designCollection ? $d->designCollection->name : '',
                    'coach_name' => $d->coaches->map(function($c) { return $c->first_name . ' ' . $c->last_name; })->implode(', ')
                ];
            })->values()) }},
            selectedDesigns: [],
            paginatedItemIds: [],
            get filteredItems() {
                if (this.search === '') return this.items;
                return this.items.filter(i => {
                    const name = (i.name || '').toLowerCase();
                    const coach = (i.coach_name || '').toLowerCase();
                    const query = this.search.toLowerCase();
                    return name.includes(query) || coach.includes(query);
                });
            },
            get totalPages() {
                return Math.max(1, Math.ceil(this.filteredItems.length / this.perPage));
            },
            updatePaginated() {
                const start = (this.page - 1) * this.perPage;
                const end = start + this.perPage;
                this.paginatedItemIds = this.filteredItems.slice(start, end).map(i => i.id);
            },
            init() {
                this.updatePaginated();
                this.$watch('search', () => { this.page = 1; this.updatePaginated(); });
                this.$watch('page', () => { this.updatePaginated(); });
            }
        }">
            <div x-show="showItems" x-cloak>
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-sm font-bold text-slate-700">Collection Items</div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <div class="relative flex-1 sm:flex-none">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="search" placeholder="Search designs..." class="pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm w-full md:w-64">
                        </div>
                        <button type="button" x-show="search !== ''" @click="search = ''" x-cloak class="px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors">Clear</button>
                    </div>
                </div>
                @if($collectionItems->isNotEmpty())
                <form id="bulk-sort-form-{{ $collection->id }}" action="{{ route('admin.design.bulk-sort') }}" method="POST">
                    @csrf
                </form>
                <div class="p-3 bg-white border-b border-slate-200 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Adjust sort orders below and click save</span>
                    <button type="button" onclick="submitBulkSort('{{ $collection->id }}')" class="px-5 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm hover:bg-[#a11825] transition-colors">Save Sort Orders</button>
                </div>
                <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="selectedDesigns = (selectedDesigns.length === filteredItems.length) ? [] : filteredItems.map(i => i.id)" 
                                class="px-2.5 py-1.5 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors">
                            <span x-text="selectedDesigns.length === filteredItems.length ? 'Deselect All' : 'Select All'"></span>
                        </button>
                        <span class="text-xs text-slate-500 font-bold uppercase tracking-wider" x-show="selectedDesigns.length > 0">
                            <span x-text="selectedDesigns.length"></span> selected
                        </span>
                    </div>
                    
                    <form action="{{ route('admin.design.bulk-assign') }}" method="POST" class="flex items-center gap-1.5" @submit="if(selectedDesigns.length === 0) { event.preventDefault(); alert('Please select at least one design to assign.'); }">
                        @csrf
                        <template x-for="id in selectedDesigns" :key="id">
                            <input type="hidden" name="design_ids[]" :value="id">
                        </template>
                        <select name="coach_id" required class="text-xs bg-white border border-slate-300 rounded px-2.5 py-1.5 w-48 focus:border-primary focus:outline-none shadow-sm">
                            <option value="">Assign selected to...</option>
                            @foreach($allCoaches as $c)
                                <option value="{{ $c->id }}">{{ $c->teamStore?->name ?? $c->organization ?? 'No Org' }} ({{ $c->first_name }} {{ $c->last_name }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="text-xs font-bold uppercase px-3 py-1.5 bg-secondary hover:bg-[#a11825] text-white rounded transition-colors">Mass Assign</button>
                    </form>
                </div>
                <div id="update-catalog-sortable-list-{{ isset($collection) ? $collection->id : 'unassigned' }}" class="update-catalog-sortable-list max-h-[900px] overflow-y-auto" data-is-collection="{{ isset($collection) ? 'true' : 'false' }}">
                    @foreach($collectionItems as $design)
                    <div x-show="paginatedItemIds.includes({{ $design->id }})" :class="paginatedItemIds.includes({{ $design->id }}) ? 'visible-sortable-item' : 'hidden-sortable-item'" x-cloak class="flex flex-col sm:flex-row sm:items-start justify-between px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0 gap-4 bg-white">
                        <div class="flex items-start gap-3 flex-1 pr-4">
                            <div class="cursor-move text-slate-300 hover:text-slate-500 transition-colors px-1 mt-1" title="Drag to reorder">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                            </div>
                            <input type="checkbox" value="{{ $design->id }}" x-model="selectedDesigns" class="mt-1.5 rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
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
                            <div class="flex-1">
                                <div class="text-sm font-bold text-slate-900">{{ $design->name }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-primary mt-0.5">
                                    {{ $design->sport ? $design->sport . ' · ' : '' }}{{ $design->type_label }} · {{ $design->category_label }}
                                </div>
                                <div class="mt-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Sort Order</label>
                                    <input type="number" 
                                           name="designs[{{ $design->id }}][sort_order]"
                                           value="{{ $design->sort_order }}" 
                                           form="bulk-sort-form-{{ $collection->id }}"
                                           class="sort-order-input-catalog-col w-16 bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:border-primary focus:outline-none shadow-sm text-center">
                                </div>
                                <details class="mt-2">
                                    <summary class="text-[10px] font-bold uppercase tracking-wider text-slate-500 cursor-pointer hover:text-primary">Edit design</summary>
                                    <form action="{{ route('admin.design.update', $design) }}" method="POST" enctype="multipart/form-data" class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-3">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Name</label>
                                                <input type="text" name="name" value="{{ $design->name }}" required class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Collection Name</label>
                                                <div class="relative">
                                                    <select name="design_collection_id" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none appearance-none">
                                                        <option value="">No Collection</option>
                                                        @foreach($allCollections as $col)
                                                            <option value="{{ $col->id }}" {{ $design->design_collection_id == $col->id ? 'selected' : '' }}>{{ $col->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <div x-data="{
                                                open: false,
                                                search: '{{ addslashes($design->sport) }}',
                                                options: window.availableSportsList,
                                                get filteredOptions() {
                                                    if (this.search === '') return this.options;
                                                    return this.options.filter(i => i.toLowerCase().includes(this.search.toLowerCase()));
                                                },
                                                selectOption(val) {
                                                    this.search = val;
                                                    this.open = false;
                                                }
                                            }" class="relative">
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Sport</label>
                                                <div class="relative">
                                                    <input type="text" name="sport" x-model="search" @focus="open = true" @click.away="open = false" placeholder="e.g. Football" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 pr-8 text-xs text-slate-900 focus:border-primary focus:outline-none" autocomplete="off">
                                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center px-2 text-slate-500 hover:text-slate-700 focus:outline-none">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                    </button>
                                                </div>
                                                <div x-show="open && filteredOptions.length > 0" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded shadow-lg max-h-40 overflow-y-auto">
                                                    <template x-for="opt in filteredOptions" :key="opt">
                                                        <div @click="selectOption(opt)" class="px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50 hover:text-primary cursor-pointer font-medium" x-text="opt"></div>
                                                    </template>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Category</label>
                                                <input list="edit_category_options_{{ $design->id }}" name="category" value="{{ $design->category }}" required class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                                <datalist id="edit_category_options_{{ $design->id }}">
                                                    <option value="package_a">Package A — Base</option>
                                                    <option value="package_b">Package B — Standard</option>
                                                    <option value="package_c">Package C — Full</option>
                                                    <option value="individual">Individual Item</option>
                                                </datalist>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Item Types</label>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                                @foreach($typeOptions as $val => $label)
                                                    <label class="flex items-center gap-1.5 text-[10px] text-slate-700">
                                                        <input
                                                            type="checkbox"
                                                            name="types[]"
                                                            value="{{ $val }}"
                                                            class="rounded border-slate-300 text-primary focus:ring-primary"
                                                            {{ in_array($val, $design->types ?? []) ? 'checked' : '' }}
                                                        >
                                                        <span>{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Product Description (Optional)</label>
                                            <textarea name="description" rows="3" placeholder="Outline the item(s) included, especially for packages..." class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">{{ $design->description }}</textarea>
                                        </div>
                                        @php
                                            $currentImages = is_array($design->image_paths) ? $design->image_paths : [];
                                            if (empty($currentImages) && !empty($design->image_url)) {
                                                $currentImages[] = $design->image_url;
                                            }
                                        @endphp
                                        @if(!empty($currentImages))
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">Current Images (Use arrows to reorder. Click DEL to remove)</label>
                                                <div x-data="{
                                                    images: {{ json_encode($currentImages) }},
                                                    removed: [],
                                                    moveLeft(idx) {
                                                        if (idx > 0) {
                                                            let temp = this.images[idx];
                                                            this.images[idx] = this.images[idx - 1];
                                                            this.images[idx - 1] = temp;
                                                        }
                                                    },
                                                    moveRight(idx) {
                                                        if (idx < this.images.length - 1) {
                                                            let temp = this.images[idx];
                                                            this.images[idx] = this.images[idx + 1];
                                                            this.images[idx + 1] = temp;
                                                        }
                                                    },
                                                    removeImage(idx) {
                                                        this.removed.push(this.images[idx]);
                                                        this.images.splice(idx, 1);
                                                    }
                                                }" class="flex flex-wrap gap-2">
                                                    <template x-for="(imgPath, idx) in images" :key="imgPath">
                                                        <div class="relative group block w-16 h-16 bg-white rounded-md border border-slate-200 shadow-sm">
                                                            <input type="hidden" name="existing_images[]" :value="imgPath">
                                                            <img :src="imgPath" class="w-full h-full object-cover rounded-md">

                                                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center rounded-md">
                                                                <div class="flex gap-1 mb-1">
                                                                    <button type="button" @click.prevent="moveLeft(idx)" x-show="idx > 0" class="p-1 bg-white hover:bg-slate-200 text-slate-900 rounded-sm" title="Move Left">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                                                                    </button>
                                                                    <button type="button" @click.prevent="moveRight(idx)" x-show="idx < images.length - 1" class="p-1 bg-white hover:bg-slate-200 text-slate-900 rounded-sm" title="Move Right">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                                                    </button>
                                                                </div>
                                                                <button type="button" @click.prevent="removeImage(idx)" class="px-2 py-0.5 bg-red-500 hover:bg-red-600 text-white text-[9px] font-bold rounded-sm" title="Remove">
                                                                    DEL
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template x-for="rm in removed">
                                                        <input type="hidden" name="remove_images[]" :value="rm">
                                                    </template>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Wholesale Price ($)</label>
                                                <input type="number" step="0.01" min="0" name="wholesale_price" value="{{ $design->wholesale_price }}" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                                <input type="number" name="sort_order" value="{{ $design->sort_order }}" class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Add More Images</label>
                                                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1.5 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-3">
                                            <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                <input type="checkbox" name="has_name_field" value="1" class="rounded border-slate-300 text-primary focus:ring-primary" {{ $design->has_name_field ? 'checked' : '' }}>
                                                Name on Item
                                            </label>
                                            <label class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                                <input type="checkbox" name="has_number_field" value="1" class="rounded border-slate-300 text-primary focus:ring-primary" {{ $design->has_number_field ? 'checked' : '' }}>
                                                Player Number
                                            </label>
                                        </div>
                                        <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider rounded hover:bg-slate-700 transition-colors">
                                            Save Changes
                                        </button>
                                    </form>
                                </details>
                            </div>
                        </div>
                        <div class="flex flex-col sm:items-end gap-3 shrink-0">
                            <div class="flex items-center gap-3">
                                <form action="{{ route('admin.design.assign-to-coach-profile', $design) }}" method="POST" class="flex items-center gap-1">
                                    @csrf
                                    <select name="coach_id" required class="text-xs bg-white border border-slate-300 rounded px-2 py-1 w-32 focus:border-primary focus:outline-none">
                                        <option value="">Assign to coach...</option>
                                        @foreach($allCoaches as $c)
                                            <option value="{{ $c->id }}">{{ $c->teamStore?->name ?? $c->organization ?? 'No Org' }} ({{ $c->first_name }} {{ $c->last_name }})</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="text-[10px] font-bold uppercase px-2 py-1.5 bg-secondary hover:bg-[#a11825] text-white rounded transition-colors" title="Assign Design to Coach">Assign</button>
                                </form>
                                <form action="{{ route('admin.design.delete', $design) }}" method="POST" onsubmit="return confirm('Delete this design from the catalog?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-400 hover:text-red-600 transition-colors p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    <div x-show="filteredItems.length === 0" x-cloak class="p-6 text-center text-sm text-slate-400">
                        No designs found matching "<span x-text="search"></span>".
                    </div>
                </div>
                <div class="p-5 border-t border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4" x-show="totalPages > 1" x-cloak>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider text-center md:text-left">
                        Showing <span x-text="((page - 1) * perPage) + 1"></span> to <span x-text="Math.min(page * perPage, filteredItems.length)"></span> of <span x-text="filteredItems.length"></span> results
                    </div>
                    <div class="flex items-center justify-center md:justify-end gap-1">
                        <button @click="if (page > 1) page--" :disabled="page === 1" :class="page === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-200 text-slate-700'" class="px-3 py-1.5 rounded bg-white border border-slate-300 text-xs font-bold uppercase transition-colors">Prev</button>
                        <span class="text-xs font-bold px-2 text-slate-600"><span x-text="page"></span> / <span x-text="totalPages"></span></span>
                        <button @click="if (page < totalPages) page++" :disabled="page === totalPages" :class="page === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-200 text-slate-700'" class="px-3 py-1.5 rounded bg-white border border-slate-300 text-xs font-bold uppercase transition-colors">Next</button>
                    </div>
                </div>
                @else
                <div class="p-6 text-center text-sm text-slate-400">No designs in catalog yet.</div>
                @endif
            </div> 
        </div>
    </div>
    @endforeach
    </div>
    <div class="mt-4 px-4 py-3 bg-slate-50 border-t border-slate-200">
        {{ $designCollections->appends(request()->except('collection_page'))->links() }}
    </div>
@endif
