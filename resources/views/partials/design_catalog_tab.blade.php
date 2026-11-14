            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- ADD NEW COLLECTION -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden xl:col-span-2" x-data="{ expandedAddCollection: false, init() { const k = 'admin_add_new_collection'; this.expandedAddCollection = localStorage.getItem(k) === 'true'; $watch('expandedAddCollection', v => localStorage.setItem(k, v)) } }">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer" @click="expandedAddCollection = !expandedAddCollection">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Add New Collection</h2>
                            <p class="text-xs text-slate-500 mt-1">Create collection entities with cover photos.</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="expandedAddCollection ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="p-5 border-b border-slate-200" x-show="expandedAddCollection" x-cloak>
                        <form action="{{ route('admin.design-collection.create') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="hidden" name="return_to" value="{{ request()->fullUrlWithQuery(['tab' => 'catalog']) }}">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="w-full">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Collection Name</label>
                                    <input type="text" name="name" required placeholder="e.g. Tampa Xpress Track Club Collection" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                </div>
                                <div class="w-full">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                    <input type="number" name="sort_order" placeholder="0" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                                </div>
                                <div class="w-full">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Cover Image</label>
                                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 file:cursor-pointer">
                                </div>
                            </div>
                            <div class="w-full">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Categories (Sports)</label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 bg-slate-50 p-3 rounded-lg border border-slate-200 max-h-32 overflow-y-auto">
                                    @foreach($availableSports as $sport)
                                        <label class="inline-flex items-center text-xs text-slate-700 font-medium cursor-pointer">
                                            <input type="checkbox" name="sports[]" value="{{ $sport }}" class="rounded border-slate-300 text-primary focus:ring-primary mr-2">
                                            {{ $sport }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-secondary hover:bg-[#a11825] text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-colors whitespace-nowrap shadow-sm">
                                    Add Collection
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- UPDATE EXISTING COLLECTIONS -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden xl:col-span-2" x-data="{ 
                    expanded: false,
                    init() {
                        const savedExpanded = localStorage.getItem('collectionsExpanded');
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.has('collection_search') || urlParams.has('collection_page')) {
                            this.expanded = true;
                        } else if (savedExpanded === 'true') {
                            this.expanded = true;
                        }
                        $watch('expanded', value => {
                            localStorage.setItem('collectionsExpanded', value);
                        });
                    }
                }">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4 cursor-pointer" @click="expanded = !expanded">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Update Existing Collections</h2>
                            <p class="text-xs text-slate-500 mt-1">Edit or remove existing collection entities and their cover images.</p>
                        </div>
                        <div class="flex gap-4 items-center">
                            <form id="collections-search-form" method="GET" action="{{ auth()->user()->isSalesRep() && !auth()->user()->isAdmin() ? route('rep.dashboard') : route('admin.dashboard') }}" class="flex gap-2" @click.stop>
                                @foreach(request()->except(['collection_search', 'collection_page', 'ajax_section']) as $k => $v)
                                    @if(is_array($v))
                                        @foreach($v as $arrVal)
                                            <input type="hidden" name="{{ $k }}[]" value="{{ $arrVal }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                    @endif
                                @endforeach
                                <div class="relative">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <input type="text" id="collection-search-input" name="collection_search" value="{{ request('collection_search') }}" placeholder="Search collections..." class="pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm w-full md:w-64">
                                </div>
                                <button type="button" id="collection-clear-btn" class="px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors {{ request()->filled('collection_search') ? 'flex items-center justify-center' : 'hidden' }}">Clear</button>
                                <button type="submit" id="collection-search-btn" class="px-3 py-2 bg-slate-800 text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-700 transition-colors flex items-center gap-1.5">
                                    <span id="collection-search-spinner" class="hidden w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    <span>Search</span>
                                </button>
                            </form>
                            <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div id="collections-ajax-container" class="relative max-h-[900px] overflow-y-auto transition-opacity duration-200" x-show="expanded" x-cloak>
                        @include('admin.partials.existing_collections_list')
                    </div>
                </div>

                @php
                    $collectionDesignsLookup = [];
                    foreach ($allCollections as $colItem) {
                        $collectionDesignsLookup[$colItem->id] = ($colItem->designs ?? collect())->map(function($d) {
                            return [
                                'id' => $d->id,
                                'name' => $d->name,
                                'type_label' => $d->type_label,
                            ];
                        })->values()->all();
                    }
                @endphp
                <script>
                    window.collectionDesignsMap = @json($collectionDesignsLookup);
                </script>

                <!-- ADD NEW PACKAGE -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden xl:col-span-2" x-data="{ expandedAddPackage: {{ $errors->any() ? 'true' : 'false' }}, init() { const k = 'admin_add_new_package'; if (!{{ $errors->any() ? 'true' : 'false' }}) this.expandedAddPackage = localStorage.getItem(k) === 'true'; $watch('expandedAddPackage', v => localStorage.setItem(k, v)) } }">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer" @click="expandedAddPackage = !expandedAddPackage">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Add New Package / Design</h2>
                            <p class="text-xs text-slate-500 mt-1">Create catalog entries for coaches and stores.</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="expandedAddPackage ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="p-5" x-show="expandedAddPackage" x-cloak>
                        <form action="{{ route('admin.design.create') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
                              x-data="{
                                  typesError: false,
                                  selectedCollectionId: '{{ old('design_collection_id', '') }}',
                                  selectedTypes: {{ json_encode(array_values(old('types', []))) }},
                                  get collectionItems() {
                                      return (window.collectionDesignsMap && this.selectedCollectionId && window.collectionDesignsMap[this.selectedCollectionId])
                                          ? window.collectionDesignsMap[this.selectedCollectionId]
                                          : [];
                                  }
                              }"
                              @submit="
                                  const checked = $el.querySelectorAll('input[name=\'types[]\']:checked');
                                  if (checked.length === 0) {
                                      $event.preventDefault();
                                      typesError = true;
                                      const first = $el.querySelector('input[name=\'types[]\']');
                                      if (first) {
                                          first.setCustomValidity('Please select at least one item type.');
                                          first.reportValidity();
                                          first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                      }
                                      return false;
                                  }
                              ">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ request()->fullUrlWithQuery(['tab' => 'catalog']) }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Design Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Springfield Eagles - Home Jersey" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Collection Name (Optional)</label>
                                <div class="relative">
                                    <select name="design_collection_id" x-model="selectedCollectionId" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm appearance-none">
                                        <option value="">Select a Collection...</option>
                                        @foreach($allCollections->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE) as $col)
                                            <option value="{{ $col->id }}" {{ old('design_collection_id') == $col->id ? 'selected' : '' }}>{{ $col->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div x-data="{
                                open: false,
                                search: '{{ old('sport', '') }}',
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
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Categories <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="sport" required x-model="search" @focus="open = true" @click.away="open = false" placeholder="e.g. Football, Basketball" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 pr-10 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm" autocomplete="off">
                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-slate-700 focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                                <div x-show="open && filteredOptions.length > 0" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="opt in filteredOptions" :key="opt">
                                        <div @click="selectOption(opt)" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary cursor-pointer font-medium" x-text="opt"></div>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Package Category <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="category" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm appearance-none">
                                        <option value="">Select Category</option>
                                        <option value="package_a" {{ old('category') === 'package_a' ? 'selected' : '' }}>Package A - Base Kit</option>
                                        <option value="package_b" {{ old('category') === 'package_b' ? 'selected' : '' }}>Package B - Standard</option>
                                        <option value="package_c" {{ old('category') === 'package_c' ? 'selected' : '' }}>Package C - Full Program</option>
                                        <option value="individual" {{ old('category') === 'individual' ? 'selected' : '' }}>Individual Item</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                <span>Item Types (Select all that apply)</span>
                                <span class="text-red-500">*</span>
                                <span x-show="typesError" x-cloak class="text-[11px] font-bold text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded normal-case tracking-normal">
                                    Please select at least one item type.
                                </span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                @foreach($typeOptions as $val => $label)
                                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="types[]" value="{{ $val }}"
                                           x-model="selectedTypes"
                                           @change="typesError = false; const f = $el.form.querySelector('input[name=\'types[]\']'); if (f) f.setCustomValidity('');"
                                           class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                    <span class="text-xs">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>

                            <div x-show="selectedTypes.length > 1" x-cloak class="mt-3 p-3.5 bg-blue-50/70 border border-blue-200 rounded-lg space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-black uppercase tracking-wider text-blue-900">Package / Set Piece Mapping (Link Exact Item per Piece)</span>
                                    <span class="text-[10px] font-semibold text-blue-700">Links by ID (safe if renamed later)</span>
                                </div>
                                <p x-show="!selectedCollectionId" class="text-xs text-amber-700 font-semibold">
                                    Select a "Collection Name" above first to choose existing items from that collection for each checked piece.
                                </p>
                                <div x-show="selectedCollectionId" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                                    @foreach($typeOptions as $val => $label)
                                        <div x-show="selectedTypes.includes('{{ $val }}')" x-cloak class="bg-white p-2.5 rounded border border-blue-100 shadow-2xs">
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-700 mb-1">{{ $label }}</label>
                                            <select name="component_design_ids[{{ $val }}]" :disabled="!selectedTypes.includes('{{ $val }}')" class="w-full bg-slate-50 border border-slate-300 rounded px-2.5 py-1.5 text-xs text-slate-900 focus:border-primary focus:outline-none">
                                                <option value="">-- Auto-detect / Use Package Image --</option>
                                                <template x-for="cItem in collectionItems" :key="cItem.id">
                                                    <option :value="cItem.id" x-text="cItem.name + ' (' + cItem.type_label + ')'"></option>
                                                </template>
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Product Description (Optional)</label>
                            <textarea name="description" rows="3" placeholder="Outline the item(s) included, especially for packages..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">{{ old('description') }}</textarea>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Upload Images</label>
                                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Wholesale Price ($) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" step="0.01" name="wholesale_price" required value="{{ old('wholesale_price') }}" placeholder="e.g. 45.00" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order') }}" placeholder="Auto" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                <input type="checkbox" name="has_name_field" value="1" {{ old('has_name_field') ? 'checked' : '' }} class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                <span class="text-xs font-bold uppercase tracking-wide">Name on Item</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                <input type="checkbox" name="has_number_field" value="1" {{ old('has_number_field') ? 'checked' : '' }} class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                <span class="text-xs font-bold uppercase tracking-wide">Player Number</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-secondary hover:bg-[#a11825] text-white text-sm font-bold uppercase tracking-wider rounded-lg transition-colors">
                            Add Package to Catalog
                        </button>
                        </form>
                    </div>
                </div>

                <!-- ADD NEW DESIGN -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden xl:col-span-2" x-data="{ expandedAddDesign: {{ $errors->any() ? 'true' : 'false' }}, init() { const k = 'admin_add_new_design'; if (!{{ $errors->any() ? 'true' : 'false' }}) this.expandedAddDesign = localStorage.getItem(k) === 'true'; $watch('expandedAddDesign', v => localStorage.setItem(k, v)) } }">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between cursor-pointer" @click="expandedAddDesign = !expandedAddDesign">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Add New Design</h2>
                            <p class="text-xs text-slate-500 mt-1">Create catalog entries for coaches and stores.</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="expandedAddDesign ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="p-5" x-show="expandedAddDesign" x-cloak>
                        <form action="{{ route('admin.design.create') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
                              x-data="{ typesError: false }"
                              @submit="
                                  const checked = $el.querySelectorAll('input[name=\'types[]\']:checked');
                                  if (checked.length === 0) {
                                      $event.preventDefault();
                                      typesError = true;
                                      const first = $el.querySelector('input[name=\'types[]\']');
                                      if (first) {
                                          first.setCustomValidity('Please select at least one item type.');
                                          first.reportValidity();
                                          first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                      }
                                      return false;
                                  }
                              ">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ request()->fullUrlWithQuery(['tab' => 'catalog']) }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Design Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Springfield Eagles - Home Jersey" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Collection Name (Optional)</label>
                                <div class="relative">
                                    <select name="design_collection_id" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm appearance-none">
                                        <option value="">Select a Collection...</option>
                                        @foreach($allCollections->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE) as $col)
                                            <option value="{{ $col->id }}" {{ old('design_collection_id') == $col->id ? 'selected' : '' }}>{{ $col->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div x-data="{
                                open: false,
                                search: '{{ old('sport', '') }}',
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
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Categories <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="sport" required x-model="search" @focus="open = true" @click.away="open = false" placeholder="e.g. Football, Basketball" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 pr-10 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm" autocomplete="off">
                                    <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-slate-700 focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                                <div x-show="open && filteredOptions.length > 0" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="opt in filteredOptions" :key="opt">
                                        <div @click="selectOption(opt)" class="px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary cursor-pointer font-medium" x-text="opt"></div>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Package Category <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="category" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm appearance-none">
                                        <option value="">Select Category</option>
                                        <option value="package_a" {{ old('category') === 'package_a' ? 'selected' : '' }}>Package A - Base Kit</option>
                                        <option value="package_b" {{ old('category') === 'package_b' ? 'selected' : '' }}>Package B - Standard</option>
                                        <option value="package_c" {{ old('category') === 'package_c' ? 'selected' : '' }}>Package C - Full Program</option>
                                        <option value="individual" {{ old('category', 'individual') === 'individual' ? 'selected' : '' }}>Individual Item</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                                <span>Item Types (Select all that apply)</span>
                                <span class="text-red-500">*</span>
                                <span x-show="typesError" x-cloak class="text-[11px] font-bold text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded normal-case tracking-normal">
                                    Please select at least one item type.
                                </span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                @foreach($typeOptions as $val => $label)
                                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="types[]" value="{{ $val }}"
                                           {{ in_array($val, old('types', [])) ? 'checked' : '' }}
                                           @change="typesError = false; const f = $el.form.querySelector('input[name=\'types[]\']'); if (f) f.setCustomValidity('');"
                                           class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                    <span class="text-xs">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Product Description (Optional)</label>
                            <textarea name="description" rows="3" placeholder="Outline the item(s) included, especially for packages..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">{{ old('description') }}</textarea>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Upload Images</label>
                                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-secondary file:text-white hover:file:bg-[#a11825]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                                    Wholesale Price ($) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" step="0.01" name="wholesale_price" required value="{{ old('wholesale_price') }}" placeholder="e.g. 45.00" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Sort Order</label>
                                <input type="number" name="sort_order" value="{{ old('sort_order') }}" placeholder="Auto" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm">
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                <input type="checkbox" name="has_name_field" value="1" {{ old('has_name_field') ? 'checked' : '' }} class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                <span class="text-xs font-bold uppercase tracking-wide">Name on Item</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                <input type="checkbox" name="has_number_field" value="1" {{ old('has_number_field') ? 'checked' : '' }} class="rounded border-slate-300 text-primary focus:ring-primary shadow-sm">
                                <span class="text-xs font-bold uppercase tracking-wide">Player Number</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-secondary hover:bg-[#a11825] text-white text-sm font-bold uppercase tracking-wider rounded-lg transition-colors">
                            Add to Design Catalog
                        </button>
                        </form>
                    </div>
                </div>

                <!-- UPDATE EXISTING CATALOG -->
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden xl:col-span-2" x-data="{ 
                    expandedCatalog: false,
                    search: '',
                    page: 1,
                    perPage: 100,
                    items: {{ json_encode($unassignedDesigns->map(function($d) {
                        return [
                            'id' => $d->id,
                            'name' => $d->name,
                            'collection_name' => $d->designCollection ? $d->designCollection->name : '',
                            'coach_name' => $d->coaches->map(function($c) { return $c->first_name . ' ' . $c->last_name; })->implode(', ')
                        ];
                    })->values()) }},
                    selectedDesigns: [],
                    selectAll: false,
                    bulkCoachId: '',
                    paginatedItemIds: [],
                    get filteredItems() {
                        if (this.search === '') return this.items;
                        return this.items.filter(i => {
                            const name = (i.name || '').toLowerCase();
                            const coll = (i.collection_name || '').toLowerCase();
                            const coach = (i.coach_name || '').toLowerCase();
                            const query = this.search.toLowerCase();
                            return name.includes(query) || coll.includes(query) || coach.includes(query);
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
                        const savedExpanded = localStorage.getItem('catalogExpanded');
                        if (savedExpanded === 'true') {
                            this.expandedCatalog = true;
                        }
                        this.$watch('expandedCatalog', value => {
                            localStorage.setItem('catalogExpanded', value);
                        });

                        const savedPage = localStorage.getItem('catalogActivePage');
                        if (savedPage) {
                            this.page = parseInt(savedPage) || 1;
                        }
                        this.updatePaginated();
                        this.$watch('page', value => {
                            localStorage.setItem('catalogActivePage', value);
                            this.updatePaginated();
                        });
                        this.$watch('search', () => { this.page = 1; this.updatePaginated(); });
                    }
                }">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4 cursor-pointer" @click="expandedCatalog = !expandedCatalog">
                        <div>
                            <h2 class="text-base font-black uppercase tracking-tight text-slate-900">Update Unassigned Catalog Items</h2>
                            <p class="text-xs text-slate-500 mt-1">Edit design details for items not assigned to any collection.</p>
                        </div>
                        <div class="flex gap-4 items-center">
                            <form method="GET" action="{{ auth()->user()->isSalesRep() && !auth()->user()->isAdmin() ? route('rep.dashboard') : route('admin.dashboard') }}" class="flex gap-2" @click.stop>
                                @foreach(request()->except(['unassigned_search', 'unassigned_page']) as $k => $v)
                                    @if(is_array($v))
                                        @foreach($v as $arrVal)
                                            <input type="hidden" name="{{ $k }}[]" value="{{ $arrVal }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                    @endif
                                @endforeach
                                <div class="relative">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <input type="text" name="unassigned_search" value="{{ request('unassigned_search') }}" placeholder="Search unassigned designs..." class="pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none shadow-sm w-full md:w-64">
                                </div>
                                @if(request()->filled('unassigned_search'))
                                    <a href="{{ route(auth()->user()->isSalesRep() && !auth()->user()->isAdmin() ? 'rep.dashboard' : 'admin.dashboard', request()->except(['unassigned_search', 'unassigned_page'])) }}" class="px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors flex items-center justify-center">Clear</a>
                                @endif
                                <button type="submit" class="px-3 py-2 bg-slate-800 text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-700 transition-colors">Search</button>
                            </form>
                            <svg class="w-5 h-5 text-slate-400 transform transition-transform" :class="expandedCatalog ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div x-show="expandedCatalog" x-cloak>
                        @if($unassignedDesigns->isNotEmpty())
                        <form id="bulk-sort-form" action="{{ route('admin.design.bulk-sort') }}" method="POST">
                            @csrf
                        <input type="hidden" name="return_to" value="{{ request()->fullUrlWithQuery(['tab' => 'catalog']) }}">
                        </form>
                        <div class="p-3 bg-white border-b border-slate-200 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Adjust sort orders below and click save</span>
                            <button type="button" onclick="submitUnassignedBulkSort()" class="px-5 py-2 bg-secondary text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm hover:bg-[#a11825] transition-colors">Save Sort Orders</button>
                        </div>
                        <div class="p-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="selectedDesigns = (selectedDesigns.length === filteredItems.length) ? [] : filteredItems.map(i => i.id)" 
                                        class="px-2.5 py-1.5 bg-white border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                                    <span x-text="selectedDesigns.length === filteredItems.length ? 'Deselect All' : 'Select All'"></span>
                                </button>
                                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider" x-show="selectedDesigns.length > 0">
                                    <span x-text="selectedDesigns.length"></span> selected
                                </span>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-2">
                                <form action="{{ route('admin.design.bulk-assign') }}" method="POST" class="flex items-center gap-1.5" @submit="if(selectedDesigns.length === 0) { event.preventDefault(); alert('Please select at least one design to assign.'); }">
                                    @csrf
                        <input type="hidden" name="return_to" value="{{ request()->fullUrlWithQuery(['tab' => 'catalog']) }}">
                                    <template x-for="id in selectedDesigns" :key="id">
                                        <input type="hidden" name="design_ids[]" :value="id">
                                    </template>
                                    <select name="coach_id" required class="text-xs bg-white border border-slate-300 rounded px-2.5 py-1.5 w-44 sm:w-48 focus:border-primary focus:outline-none shadow-sm">
                                        <option value="">Assign selected to...</option>
                                        <template x-for="c in (window.allCoachesList || [])" :key="c.id">
                                            <option :value="c.id" x-text="c.label"></option>
                                        </template>
                                    </select>
                                    <button type="submit" class="text-xs font-bold uppercase px-3 py-1.5 bg-secondary hover:bg-[#a11825] text-white rounded transition-colors shadow-sm whitespace-nowrap">Mass Assign</button>
                                </form>

                                <form action="{{ route('admin.design.bulk-delete') }}" method="POST" class="flex items-center" @submit="if(selectedDesigns.length === 0) { event.preventDefault(); alert('Please select at least one design to delete.'); } else if(!confirm('Are you sure you want to permanently delete ' + selectedDesigns.length + ' selected design(s) from the catalog? This cannot be undone.')) { event.preventDefault(); }">
                                    @csrf
                        <input type="hidden" name="return_to" value="{{ request()->fullUrlWithQuery(['tab' => 'catalog']) }}">
                                    @method('DELETE')
                                    <template x-for="id in selectedDesigns" :key="id">
                                        <input type="hidden" name="design_ids[]" :value="id">
                                    </template>
                                    <button type="submit" class="text-xs font-bold uppercase px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded transition-colors shadow-sm flex items-center gap-1.5 whitespace-nowrap" title="Permanently delete selected designs from catalog">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Mass Delete</span>
                                        <span x-show="selectedDesigns.length > 0" x-text="'(' + selectedDesigns.length + ')'" class="font-extrabold"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div id="update-catalog-sortable-list-unassigned" class="update-catalog-sortable-list max-h-[900px] overflow-y-auto" data-is-collection="false">
                            @foreach($unassignedDesigns as $design)
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
                                    <div id="design-title-{{ $design->id }}" class="text-sm font-bold text-slate-900">{{ $design->name }}</div>
                                <div id="design-subtitle-{{ $design->id }}" class="text-[10px] font-bold uppercase tracking-wider text-primary mt-0.5">
                                    {{ $design->sport ? $design->sport . ' · ' : '' }}{{ $design->type_label }} · {{ $design->category_label }}
                                </div>
                                <div class="mt-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Sort Order</label>
                                    <input type="number" 
                                           name="designs[{{ $design->id }}][sort_order]"
                                           value="{{ $design->sort_order }}" 
                                           form="bulk-sort-form"
                                           class="sort-order-input-catalog-col w-16 bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 focus:border-primary focus:outline-none shadow-sm text-center">
                                </div>
                                <details class="mt-2" x-data="{ editOpen: false }" @toggle="editOpen = $el.open">
                                    <summary class="text-[10px] font-bold uppercase tracking-wider text-slate-500 cursor-pointer hover:text-primary">Edit design</summary>
                                    <template x-if="editOpen">
                                    <form action="{{ route('admin.design.update', $design) }}" method="POST" enctype="multipart/form-data" class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-3" @submit.prevent="window.submitInlineDesignUpdate($event, {{ $design->id }}, items)">
                                        @csrf
                                        <input type="hidden" name="return_to" value="{{ request()->fullUrlWithQuery(['tab' => 'catalog']) }}">
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
                                                        <template x-for="col in (window.allCollectionsList || [])" :key="col.id">
                                                            <option :value="col.id" :selected="String(col.id) === '{{ (string) $design->design_collection_id }}'" x-text="col.name"></option>
                                                        </template>
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
                                        <div class="flex items-center gap-3">
                                            <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider rounded hover:bg-slate-700 transition-colors">
                                                Save Changes
                                            </button>
                                            <span class="inline-save-status text-[10px] font-bold uppercase tracking-wider hidden"></span>
                                        </div>
                                    </form>
                                    </template>
                                </details>
                            </div>
                        </div>
                            <div class="flex flex-col sm:items-end gap-3 shrink-0">
                                <div class="flex items-center gap-3">
                                    <form action="{{ route('admin.design.assign-to-coach-profile', $design) }}" method="POST" class="flex items-center gap-1">
                                        @csrf
                        <input type="hidden" name="return_to" value="{{ request()->fullUrlWithQuery(['tab' => 'catalog']) }}">
                                        <select name="coach_id" required class="text-xs bg-white border border-slate-300 rounded px-2 py-1 w-32 focus:border-primary focus:outline-none">
                                            <option value="">Assign to coach...</option>
                                            <template x-for="c in (window.allCoachesList || [])" :key="c.id">
                                                <option :value="c.id" x-text="c.label"></option>
                                            </template>
                                        </select>
                                        <button type="submit" class="text-[10px] font-bold uppercase px-2 py-1.5 bg-secondary hover:bg-[#a11825] text-white rounded transition-colors" title="Assign Design to Coach">Assign</button>
                                    </form>
                                    <form action="{{ route('admin.design.delete', $design) }}" method="POST" onsubmit="return confirm('Delete this design from the catalog?')">
                                        @csrf
                        <input type="hidden" name="return_to" value="{{ request()->fullUrlWithQuery(['tab' => 'catalog']) }}"> @method('DELETE')
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
                    <div class="mt-4 px-4 py-3 bg-slate-50 border-t border-slate-200">
                        {{ $unassignedDesigns->appends(request()->except('unassigned_page'))->links() }}
                    </div>
                    @else
                    <div class="p-6 text-center text-sm text-slate-400">No designs in catalog yet.</div>
                    @endif
                    </div> <!-- Closing expandedCatalog wrapper -->
                </div>
            </div>
        </div>

<script>
    window.availableSportsList = window.availableSportsList || {!! json_encode(is_array($availableSports) ? array_values($availableSports) : (is_object($availableSports) && method_exists($availableSports, 'values') ? $availableSports->values()->all() : [])) !!};

    function initCollectionsSortable() {
        const updateCollectionsList = document.getElementById('update-collections-sortable-list');
        if (updateCollectionsList && window.Sortable) {
            if (updateCollectionsList._sortable) {
                updateCollectionsList._sortable.destroy();
            }
            updateCollectionsList._sortable = new Sortable(updateCollectionsList, {
                animation: 150,
                handle: '.cursor-move',
                ghostClass: 'bg-slate-50',
                onEnd: function () {
                    const inputs = Array.from(updateCollectionsList.querySelectorAll('.sort-order-input-update-col'));
                    const values = inputs.map(input => parseInt(input.value) || 0).sort((a, b) => a - b);
                    inputs.forEach((input, index) => {
                        input.value = values[index];
                    });
                }
            });
        }

        const updateCatalogLists = document.querySelectorAll('.update-catalog-sortable-list');
        updateCatalogLists.forEach(listEl => {
            if (window.Sortable) {
                if (listEl._sortable) {
                    listEl._sortable.destroy();
                }
                listEl._sortable = new Sortable(listEl, {
                    animation: 150,
                    handle: '.cursor-move',
                    draggable: '.visible-sortable-item',
                    ghostClass: 'bg-slate-50',
                    scroll: true,
                    bubbleScroll: true,
                    onEnd: function (evt) {
                        const inputs = Array.from(listEl.querySelectorAll('.sort-order-input-catalog-col'));
                        let values = inputs.map(input => parseInt(input.value) || 0);
                        const hasDuplicatesOrZeros = new Set(values).size !== values.length || values.some(v => v <= 0);
                        if (hasDuplicatesOrZeros) {
                            inputs.forEach((input, index) => {
                                input.value = index + 1;
                            });
                        } else {
                            values.sort((a, b) => a - b);
                            inputs.forEach((input, index) => {
                                input.value = values[index];
                            });
                        }
                    }
                });
            }
        });
    }

    function loadCollectionsAjax(url, updateHistory = true) {
        const container = document.getElementById('collections-ajax-container');
        const spinner = document.getElementById('collection-search-spinner');
        const clearBtn = document.getElementById('collection-clear-btn');
        const searchInput = document.getElementById('collection-search-input');

        if (!container) return;

        container.classList.add('opacity-40', 'pointer-events-none');
        if (spinner) spinner.classList.remove('hidden');

        const fetchUrl = new URL(url, window.location.origin);
        fetchUrl.searchParams.set('ajax_section', 'collections');

        fetch(fetchUrl.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Ajax-Section': 'collections'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.text();
        })
        .then(html => {
            container.innerHTML = html;

            if (window.Alpine && window.Alpine.initTree) {
                window.Alpine.initTree(container);
            }

            initCollectionsSortable();

            if (updateHistory) {
                const displayUrl = new URL(url, window.location.origin);
                displayUrl.searchParams.delete('ajax_section');
                window.history.pushState(null, '', displayUrl.toString());
            }

            const currentSearch = searchInput ? searchInput.value.trim() : '';
            if (clearBtn) {
                if (currentSearch.length > 0) {
                    clearBtn.classList.remove('hidden');
                    clearBtn.classList.add('flex');
                } else {
                    clearBtn.classList.add('hidden');
                    clearBtn.classList.remove('flex');
                }
            }
        })
        .catch(err => {
            console.error('Error loading collections AJAX:', err);
            window.location.href = url;
        })
        .finally(() => {
            container.classList.remove('opacity-40', 'pointer-events-none');
            if (spinner) spinner.classList.add('hidden');
        });
    }

    function submitBulkSort(collectionId) {
        const form = document.getElementById('bulk-sort-form-' + collectionId);
        const listEl = document.getElementById('update-catalog-sortable-list-' + collectionId);
        if (!form || !listEl) return;

        const rt = form.querySelector('input[name="return_to"]');
        if (rt && window.getAdminCollectionsReturnUrl) {
            rt.value = window.getAdminCollectionsReturnUrl(collectionId);
        }
        
        form.querySelectorAll('.cloned-sort-input').forEach(el => el.remove());
        
        const inputs = listEl.querySelectorAll('.sort-order-input-catalog-col');
        inputs.forEach(input => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = input.name;
            hiddenInput.value = input.value;
            hiddenInput.className = 'cloned-sort-input';
            form.appendChild(hiddenInput);
        });
        
        form.submit();
    }

    function submitUnassignedBulkSort() {
        const form = document.getElementById('bulk-sort-form');
        const listEl = document.getElementById('update-catalog-sortable-list-unassigned');
        if (!form || !listEl) return;
        
        form.querySelectorAll('.cloned-sort-input').forEach(el => el.remove());
        
        const inputs = listEl.querySelectorAll('.sort-order-input-catalog-col');
        inputs.forEach(input => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = input.name;
            hiddenInput.value = input.value;
            hiddenInput.className = 'cloned-sort-input';
            form.appendChild(hiddenInput);
        });
        
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        initCollectionsSortable();

        const collectionsSearchForm = document.getElementById('collections-search-form');
        if (collectionsSearchForm && !collectionsSearchForm._ajaxBound) {
            collectionsSearchForm._ajaxBound = true;
            collectionsSearchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(collectionsSearchForm);
                const params = new URLSearchParams(formData);
                params.delete('collection_page');
                const url = `${collectionsSearchForm.action}?${params.toString()}`;
                loadCollectionsAjax(url);
            });
        }

        const collectionsClearBtn = document.getElementById('collection-clear-btn');
        if (collectionsClearBtn && !collectionsClearBtn._ajaxBound) {
            collectionsClearBtn._ajaxBound = true;
            collectionsClearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const searchInput = document.getElementById('collection-search-input');
                if (searchInput) searchInput.value = '';
                const collectionsSearchForm = document.getElementById('collections-search-form');
                if (collectionsSearchForm) {
                    const formData = new FormData(collectionsSearchForm);
                    formData.delete('collection_search');
                    formData.delete('collection_page');
                    const params = new URLSearchParams(formData);
                    const url = `${collectionsSearchForm.action}?${params.toString()}`;
                    loadCollectionsAjax(url);
                }
            });
        }

        const collectionsContainer = document.getElementById('collections-ajax-container');
        if (collectionsContainer && !collectionsContainer._ajaxBound) {
            collectionsContainer._ajaxBound = true;
            collectionsContainer.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link && (link.closest('.pagination') || link.closest('nav[role="navigation"]'))) {
                    e.preventDefault();
                    loadCollectionsAjax(link.href);
                }
            });
        }
    });
</script>