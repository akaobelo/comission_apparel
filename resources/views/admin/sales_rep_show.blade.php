@extends('layouts.app')
@section('title', 'Sales Partner Profile: ' . $user->name . ' | Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16 pt-32 lg:pt-40" x-data="{
    activeTab: '{{ request('tab', 'overview') }}',
    viewMode: 'batches',
    selectedBatchKey: null,
    copied: false,
    cropperModal: false,
    cropperInstance: null,
    cropSaving: false,
    currentAvatarUrl: '{{ $user->profile_image_url }}',
    tabHistory: [],

    init() {
        const urlParams = new URLSearchParams(window.location.search);
        const urlTab = urlParams.get('tab');
        const urlBatch = urlParams.get('batch');
        const urlView = urlParams.get('view');

        if (urlTab) {
            this.activeTab = urlTab;
        } else {
            const saved = sessionStorage.getItem('sales_rep_tab_{{ $user->id }}');
            if (saved) {
                this.activeTab = saved;
            }
        }
        if (urlBatch) {
            this.selectedBatchKey = urlBatch;
        }
        if (urlView) {
            this.viewMode = urlView;
        }

        this.tabHistory = [this.activeTab];

        // Replace initial state with full parameters
        const u = new URL(window.location);
        u.searchParams.set('tab', this.activeTab);
        if (this.selectedBatchKey) {
            u.searchParams.set('batch', this.selectedBatchKey);
        } else {
            u.searchParams.delete('batch');
        }
        window.history.replaceState({
            tab: this.activeTab,
            batch: this.selectedBatchKey,
            view: this.viewMode
        }, '', u);

        // Listen to browser Back / Forward buttons for step-by-step navigation
        window.addEventListener('popstate', (e) => {
            const p = new URLSearchParams(window.location.search);
            this.activeTab = p.get('tab') || (e.state && e.state.tab) || 'overview';
            this.selectedBatchKey = p.get('batch') || (e.state && e.state.batch) || null;
            this.viewMode = p.get('view') || (e.state && e.state.view) || 'batches';
            sessionStorage.setItem('sales_rep_tab_{{ $user->id }}', this.activeTab);
        });
    },

    setTab(t, push = true) {
        if (this.activeTab === t && !this.selectedBatchKey) return;
        this.activeTab = t;
        this.selectedBatchKey = null;
        if (this.tabHistory[this.tabHistory.length - 1] !== t) {
            this.tabHistory.push(t);
        }
        sessionStorage.setItem('sales_rep_tab_{{ $user->id }}', t);

        const u = new URL(window.location);
        u.searchParams.set('tab', t);
        u.searchParams.delete('batch');

        if (push) {
            window.history.pushState({ tab: t, batch: null, view: this.viewMode }, '', u);
        }
    },

    openBatch(key, push = true) {
        this.selectedBatchKey = key;
        this.viewMode = 'batches';

        const u = new URL(window.location);
        u.searchParams.set('tab', 'orders');
        u.searchParams.set('batch', key);

        if (push) {
            window.history.pushState({ tab: 'orders', batch: key, view: 'batches' }, '', u);
        }
        window.scrollTo({ top: 320, behavior: 'smooth' });
    },

    closeBatch(push = true) {
        if (!this.selectedBatchKey) return;
        this.selectedBatchKey = null;

        const u = new URL(window.location);
        u.searchParams.set('tab', 'orders');
        u.searchParams.delete('batch');

        if (push) {
            window.history.pushState({ tab: 'orders', batch: null, view: this.viewMode }, '', u);
        }
    },

    setViewMode(m, push = true) {
        this.viewMode = m;
        this.selectedBatchKey = null;

        const u = new URL(window.location);
        u.searchParams.set('tab', 'orders');
        u.searchParams.set('view', m);
        u.searchParams.delete('batch');

        if (push) {
            window.history.pushState({ tab: 'orders', batch: null, view: m }, '', u);
        }
    },

    goBackStep() {
        // Step 1: If viewing an open batch detail, step back to order batches list
        if (this.selectedBatchKey) {
            this.closeBatch(true);
            return;
        }

        // Step 2: If in all_orders view mode, step back to batches view
        if (this.activeTab === 'orders' && this.viewMode === 'all_orders') {
            this.setViewMode('batches', true);
            return;
        }

        // Step 3: If on any sub-tab (clients, stores, orders), step back to previous tab or overview
        if (this.activeTab !== 'overview') {
            if (this.tabHistory.length > 1) {
                this.tabHistory.pop(); // remove current tab
                const prev = this.tabHistory[this.tabHistory.length - 1];
                if (prev && prev !== this.activeTab) {
                    this.setTab(prev, true);
                    return;
                }
            }
            this.setTab('overview', true);
            return;
        }

        // Step 4: If on overview, navigate back to Sales Partners list
        window.location.href = '{{ route('admin.dashboard', ['tab' => 'sales_reps']) }}';
    },

    copyLink() {
        navigator.clipboard.writeText('{{ $referralUrl }}');
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
    },
    openCropper(event) {
        const file = event.target.files ? event.target.files[0] : null;
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            this.$refs.cropImage.src = e.target.result;
            this.cropperModal = true;
            this.$nextTick(() => {
                if (this.cropperInstance) {
                    this.cropperInstance.destroy();
                }
                this.cropperInstance = new Cropper(this.$refs.cropImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    dragMode: 'move',
                    background: false,
                });
            });
        };
        reader.readAsDataURL(file);
    },
    closeCropper() {
        this.cropperModal = false;
        if (this.cropperInstance) {
            this.cropperInstance.destroy();
            this.cropperInstance = null;
        }
        if (this.$refs.avatarFileInput) this.$refs.avatarFileInput.value = '';
        if (this.$refs.formAvatarFileInput) this.$refs.formAvatarFileInput.value = '';
    },
    saveCroppedAvatar() {
        if (!this.cropperInstance) return;
        this.cropSaving = true;
        this.cropperInstance.getCroppedCanvas({
            width: 800,
            height: 800,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        }).toBlob((blob) => {
            const formData = new FormData();
            formData.append('profile_image', blob, 'partner-avatar.jpg');
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('admin.sales-rep.avatar', $user) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.cropSaving = false;
                if (data.success) {
                    this.currentAvatarUrl = data.avatar_url;
                    this.closeCropper();
                    window.location.reload();
                } else {
                    alert('Failed to upload cropped image.');
                }
            })
            .catch(err => {
                this.cropSaving = false;
                alert('An error occurred while uploading.');
            });
        }, 'image/jpeg', 0.92);
    }
}">
    <!-- Breadcrumb & Step-by-Step Back Navigation -->
    <div class="flex items-center gap-2 sm:gap-3 mb-6 flex-wrap">
        <button type="button" @click="goBackStep()" class="text-slate-600 hover:text-primary transition-colors flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider cursor-pointer group">
            <svg class="w-4 h-4 text-slate-400 group-hover:text-primary transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span x-text="selectedBatchKey ? 'Back to Order Batches' : (activeTab !== 'overview' ? 'Back to Overview' : 'Back to Sales Partners')">Back to Sales Partners</span>
        </button>
        <span class="text-slate-300">/</span>
        <a href="{{ route('admin.dashboard', ['tab' => 'sales_reps']) }}" class="text-slate-400 hover:text-slate-700 text-xs font-bold uppercase tracking-wider transition-colors" title="Return to Sales Partners List">
            Sales Partners
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold text-xs uppercase tracking-wider" x-text="activeTab === 'overview' ? 'Partner Profile' : (activeTab === 'clients' ? 'Assigned Clients' : (activeTab === 'stores' ? 'Team Stores' : 'Commission Statements'))">Partner Profile</span>
        <template x-if="selectedBatchKey">
            <span class="flex items-center gap-2 text-slate-300">
                <span>/</span>
                <span class="text-primary font-bold text-xs uppercase tracking-wider">Batch Details</span>
            </span>
        </template>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Profile Header Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 lg:p-7 mb-8">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6">
            {{-- Left Block: Large Avatar, Info, Badges --}}
            <div class="flex flex-col md:flex-row md:items-center gap-6 flex-1 min-w-0">
                {{-- 1. Enlarge Profile Picture to Maximum Size within Space --}}
                <div class="relative group cursor-pointer w-28 h-28 sm:w-32 sm:h-32 lg:w-36 lg:h-36 flex-shrink-0 rounded-2xl overflow-hidden border-2 border-slate-200 shadow-md bg-slate-900 transition-all hover:border-primary hover:shadow-lg" @click="$refs.avatarFileInput.click()" title="Click to upload & crop profile photo">
                    <template x-if="currentAvatarUrl">
                        <img :src="currentAvatarUrl" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!currentAvatarUrl">
                        <div class="w-full h-full flex items-center justify-center font-black text-3xl sm:text-4xl text-white">
                            {{ strtoupper(substr($user->first_name ?: 'S', 0, 1) . substr($user->last_name ?: 'P', 0, 1)) }}
                        </div>
                    </template>
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white text-xs font-bold gap-1 p-2 text-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Crop / Edit</span>
                    </div>
                </div>
                <input type="file" x-ref="avatarFileInput" @change="openCropper" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">

                {{-- 2. Partner Name & Contact Details --}}
                <div class="flex-1 min-w-[200px]">
                    <h1 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-slate-900 leading-tight">
                        {{ $user->name }}
                    </h1>
                    <div class="mt-2.5 space-y-1 text-xs text-slate-500 font-medium">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span class="text-slate-600 font-medium">{{ $user->email }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            @if($user->phone)
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $user->phone }}
                                </span>
                                <span class="text-slate-300">·</span>
                            @endif
                            <span class="text-slate-400">Partner Since {{ $user->created_at?->format('M d, Y') ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- 3. Middle Column: Stacked Badges --}}
                <div class="flex flex-col gap-2 justify-center items-start lg:min-w-[210px] border-t md:border-t-0 md:border-l border-slate-100 pt-3 md:pt-0 md:pl-6">
                    <span class="inline-flex px-3 py-1 text-[11px] font-bold uppercase tracking-wider rounded-lg border
                        {{ $user->partner_category === 'established_business' ? 'bg-purple-50 text-purple-800 border-purple-200' : ($user->partner_category === 'authorized_business' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-slate-100 text-slate-700 border-slate-200') }}">
                        {{ $user->partner_category_label }}
                    </span>
                    <span class="inline-flex px-3 py-1 text-[11px] font-bold uppercase tracking-wider rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                        Tier: {{ number_format($user->getEffectiveCommissionRate(), 1) }}% Net Split
                    </span>
                    <span class="inline-flex px-3 py-1 text-[11px] font-bold uppercase tracking-wider rounded-lg border {{ $user->status === 'active' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200' }}">
                        {{ strtoupper($user->status) }}
                    </span>
                </div>
            </div>

            {{-- 4. Right Block: Referral Link Widget --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 lg:min-w-[340px] xl:max-w-[380px] w-full xl:w-auto">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                    Partner Coach Recruitment Link
                </label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $referralUrl }}" class="w-full text-xs bg-white border border-slate-200 rounded px-3 py-2 text-slate-700 font-medium select-all focus:outline-none">
                    <button type="button" @click="copyLink()" class="px-3.5 py-2 bg-slate-900 text-white rounded text-xs font-bold uppercase tracking-wider hover:bg-slate-800 transition-all flex items-center gap-1 whitespace-nowrap shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span x-text="copied ? 'Copied' : 'Copy'">Copy</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Cropper Modal -->
    <div x-show="cropperModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" @click="closeCropper"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                <div>
                    <h3 class="font-black uppercase tracking-tight text-slate-900 text-base">Crop Partner Profile Photo</h3>
                    <p class="text-xs text-slate-500">Drag, zoom and frame the square profile photo.</p>
                </div>
                <button type="button" @click="closeCropper" class="text-slate-400 hover:text-slate-900 transition-colors focus:outline-none text-2xl font-black">&times;</button>
            </div>
            <div class="bg-slate-950 flex-1 w-full h-[55vh] relative overflow-hidden flex items-center justify-center p-4">
                <img x-ref="cropImage" class="block max-w-full max-h-full">
            </div>
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button type="button" @click="cropperInstance && cropperInstance.zoom(0.1)" class="px-3 py-1.5 border border-slate-300 rounded-lg hover:bg-white text-slate-700 text-xs font-bold shadow-xs" title="Zoom In">Zoom +</button>
                    <button type="button" @click="cropperInstance && cropperInstance.zoom(-0.1)" class="px-3 py-1.5 border border-slate-300 rounded-lg hover:bg-white text-slate-700 text-xs font-bold shadow-xs" title="Zoom Out">Zoom -</button>
                    <button type="button" @click="cropperInstance && cropperInstance.rotate(90)" class="px-3 py-1.5 border border-slate-300 rounded-lg hover:bg-white text-slate-700 text-xs font-bold shadow-xs" title="Rotate">Rotate 90°</button>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" @click="closeCropper" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors uppercase">Cancel</button>
                    <button type="button" @click="saveCroppedAvatar" :disabled="cropSaving" class="px-5 py-2 bg-slate-900 text-white text-xs font-black uppercase tracking-wider rounded-lg hover:bg-slate-800 transition-colors shadow-sm flex items-center gap-2">
                        <svg x-show="cropSaving" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="cropSaving ? 'Saving...' : 'Apply Crop & Save'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter Bar -->
    <div class="mb-6 bg-white border border-slate-200 rounded-xl p-3 sm:p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-xs font-black uppercase tracking-wider text-slate-700">Date Filter:</span>
            @if(($dateFilter ?? 'all') !== 'all')
                <span class="inline-flex px-2 py-0.5 text-[10px] font-black rounded-full bg-primary/10 text-primary uppercase">
                    Active Filter
                </span>
            @endif
        </div>
        <div class="flex items-center gap-1.5 flex-wrap">
            @php
                $filters = [
                    'all'             => 'All Time',
                    'today'           => 'Today',
                    'this_week'       => 'This Week',
                    'this_month'      => 'This Month',
                    'this_quarter'    => 'This Quarter',
                    'last_six_months' => 'Last 6 Months',
                    'this_year'       => 'This Year',
                ];
                $activeFilter = $dateFilter ?? 'all';
            @endphp
            @foreach($filters as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['date_filter' => $key]) }}"
                   class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ $activeFilter === $key ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- 6 KPI Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5 font-sans">Qualifying Sales</span>
            <span class="block text-2xl font-bold tracking-tight text-slate-900 font-sans">${{ number_format($totalQualifyingSales, 2) }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block font-sans">Gross order revenue</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5 font-sans">Taxes & Fees</span>
            <span class="block text-2xl font-bold tracking-tight text-slate-900 font-sans">${{ number_format($totalTariffs + $totalFees, 2) }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block font-sans">Deducted fees (Base Mfg: ${{ number_format($totalManufacturingCost, 2) }})</span>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5 font-sans">Net Margin</span>
            <span class="block text-2xl font-bold tracking-tight text-slate-900 font-sans">${{ number_format($totalNetMargin, 2) }}</span>
            <span class="text-[11px] text-slate-400 mt-1 block font-sans">Commissionable pool</span>
        </div>

        <div class="bg-white border border-amber-200/80 rounded-xl p-4 shadow-sm bg-gradient-to-br from-white to-amber-50/30">
            <span class="block text-xs font-semibold uppercase tracking-wider text-amber-700 mb-1.5 font-sans">Pending Payout</span>
            <span class="block text-2xl font-bold tracking-tight text-amber-600 font-sans">${{ number_format($pendingCommission, 2) }}</span>
            <span class="text-[11px] text-amber-600/80 mt-1 block font-sans">In active production</span>
        </div>

        <div class="bg-white border border-blue-200/80 rounded-xl p-4 shadow-sm bg-gradient-to-br from-white to-blue-50/30">
            <span class="block text-xs font-semibold uppercase tracking-wider text-blue-700 mb-1.5 font-sans">Cleared Payout</span>
            <span class="block text-2xl font-bold tracking-tight text-blue-600 font-sans">${{ number_format($clearedCommission, 2) }}</span>
            <span class="text-[11px] text-blue-600/80 mt-1 block font-sans">Ready for deposit</span>
        </div>

        <div class="bg-white border border-emerald-200/80 rounded-xl p-4 shadow-sm bg-gradient-to-br from-white to-emerald-50/30">
            <span class="block text-xs font-semibold uppercase tracking-wider text-emerald-700 mb-1.5 font-sans">Lifetime Paid</span>
            <span class="block text-2xl font-bold tracking-tight text-emerald-600 font-sans">${{ number_format($paidCommission, 2) }}</span>
            <span class="text-[11px] text-emerald-600/80 mt-1 block font-sans">Disbursed via QuickBooks</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 mb-8 flex items-center gap-6 overflow-x-auto">
        <button type="button" @click="setTab('overview')" class="pb-3 text-xs font-black uppercase tracking-wider whitespace-nowrap transition-colors border-b-2" :class="activeTab === 'overview' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800'">
            Partner Settings & Profile
        </button>
        <button type="button" @click="setTab('clients')" class="pb-3 text-xs font-black uppercase tracking-wider whitespace-nowrap transition-colors border-b-2" :class="activeTab === 'clients' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800'">
            Assigned Clients ({{ $clients->count() }})
        </button>
        <button type="button" @click="setTab('stores')" class="pb-3 text-xs font-black uppercase tracking-wider whitespace-nowrap transition-colors border-b-2" :class="activeTab === 'stores' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800'">
            Team Stores & Pipeline ({{ $stores->count() }})
        </button>
        <button type="button" @click="setTab('orders')" class="pb-3 text-xs font-black uppercase tracking-wider whitespace-nowrap transition-colors border-b-2" :class="activeTab === 'orders' ? 'border-primary text-primary' : 'border-transparent text-slate-500 hover:text-slate-800'">
            Commission Statements & Orders ({{ $orders->count() }})
        </button>
    </div>

    <!-- TAB 1: OVERVIEW & PROFILE SETTINGS -->
    <div x-show="activeTab === 'overview'" class="max-w-4xl space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Edit Partner Agreement & Account</h3>
                <p class="text-xs text-slate-500 mt-0.5">Configure commission splits, referral codes, and partner tiers.</p>
            </div>
            <form action="{{ route('admin.sales-rep.update', $user) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Profile Photo / Avatar</label>
                    <div class="flex items-center gap-4">
                        @if($user->profile_image_url)
                            <img src="{{ $user->profile_image_url }}" alt="Profile preview" class="w-16 h-16 rounded-2xl object-cover border-2 border-slate-200 shadow-sm flex-shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                No Photo
                            </div>
                        @endif
                        <div class="flex-1">
                            <input type="file" name="profile_image" x-ref="formAvatarFileInput" @change="openCropper" accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                            <span class="text-[11px] text-slate-400 block mt-1">Upload JPEG, PNG, or WebP. Interactive crop tool allows framing to square.</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">First Name *</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Last Name *</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Email Address (Login Username) *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="e.g. (813) 000-0000" class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Agreement Category Tier *</label>
                        <select name="partner_category" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                            <option value="independent_sales" {{ old('partner_category', $user->partner_category) === 'independent_sales' ? 'selected' : '' }}>Independent Sales Agent</option>
                            <option value="authorized_business" {{ old('partner_category', $user->partner_category) === 'authorized_business' ? 'selected' : '' }}>Authorized Business Partner</option>
                            <option value="established_business" {{ old('partner_category', $user->partner_category) === 'established_business' ? 'selected' : '' }}>Establish Business Partner</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Commission Split Rate (%) *</label>
                        <input type="number" step="0.1" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', $user->commission_rate ?? 50.00) }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm font-bold text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Account Status *</label>
                        <select name="status" required class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="declined" {{ old('status', $user->status) === 'declined' ? 'selected' : '' }}>Deactivated / Declined</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Personal Referral Slug</label>
                        <input type="text" name="referral_slug" value="{{ old('referral_slug', $user->referral_slug) }}" placeholder="e.g. arven" class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                        <span class="text-[11px] text-slate-400 mt-1 block">Used for commissionapparel.com/register?rep=[slug]</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Reset Password (Leave blank to keep current)</label>
                        <input type="password" id="partner_reset_password" name="password" minlength="8" placeholder="Minimum 8 characters" class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                        @error('password')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Confirm Password</label>
                        <input type="password" id="partner_reset_password_confirmation" name="password_confirmation" minlength="8" placeholder="Re-enter new password" oninput="this.setCustomValidity(document.getElementById('partner_reset_password').value && this.value !== document.getElementById('partner_reset_password').value ? 'Passwords do not match.' : '')" class="w-full bg-white border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm text-slate-900 focus:border-primary focus:outline-none shadow-sm">
                        @error('password_confirmation')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-black uppercase tracking-wider transition-all shadow-sm">
                        Save Partner Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 2: ASSIGNED CLIENTS -->
    <div x-show="activeTab === 'clients'" class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Assigned Clients & Organizations ({{ $clients->count() }})</h3>
                    <p class="text-xs text-slate-500 mt-0.5">All clients linked directly to this representative's book of business.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Organization / School</th>
                            <th class="px-6 py-3.5">Primary Contact</th>
                            <th class="px-6 py-3.5">Category</th>
                            <th class="px-6 py-3.5 text-center">Stores</th>
                            <th class="px-6 py-3.5 text-center">Orders</th>
                            <th class="px-6 py-3.5">Onboarded</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($clients as $client)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $client->organization }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $client->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $client->email }} @if($client->phone) · {{ $client->phone }} @endif</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full bg-slate-100 text-slate-700">
                                        {{ $client->sport ?: 'Athletics' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $client->team_stores_count }}</td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $client->parent_orders_count }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500">{{ $client->created_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.coach.edit', ['user' => $client, 'return_to' => route('admin.sales-rep.show', ['user' => $user, 'tab' => 'clients'])]) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors whitespace-nowrap">
                                            Manage Client →
                                        </a>
                                        <form action="{{ route('admin.sales-rep.unassign-coach', ['user' => $user, 'coach' => $client]) }}" method="POST" onsubmit="return confirm('Are you sure you want to unassign {{ addslashes($client->name) }} ({{ addslashes($client->organization) }}) from {{ addslashes($user->name) }}?\n\nAll historical orders and earned commissions will remain permanently accounted for in {{ addslashes($user->name) }}\'s commission statements, but this coach will no longer be assigned to them.')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 hover:text-red-800 rounded text-xs font-bold uppercase tracking-wider border border-red-200 transition-colors whitespace-nowrap flex items-center gap-1 shadow-2xs" title="Unassign Client from this Partner">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/></svg>
                                                <span>Unassign</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                    No clients or organizations linked to this partner yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: TEAM STORES & PIPELINE -->
    <div x-show="activeTab === 'stores'" class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Partner Team Stores ({{ $stores->count() }})</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Team apparel stores and direct bulk batches managed by this partner.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">Store / Event</th>
                            <th class="px-6 py-3.5">Organization / Admin</th>
                            <th class="px-6 py-3.5">Category</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-center">Orders</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($stores as $st)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $st->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">Deadline: {{ $st->order_deadline ? \Carbon\Carbon::parse($st->order_deadline)->format('M d, Y') : 'Rolling' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $st->user?->organization ?? '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ $st->user?->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full bg-slate-100 text-slate-700">
                                        {{ $st->sport ?: ($st->user?->sport ?: 'Athletics') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-full border
                                        {{ $st->status === 'approved' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($st->status === 'pending' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-slate-100 text-slate-700 border-slate-200') }}">
                                        {{ $st->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $st->parent_orders_count }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.store.edit', ['store' => $st, 'return_to' => route('admin.sales-rep.show', ['user' => $user, 'tab' => 'stores'])]) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors">
                                            Manage Store →
                                        </a>
                                        @if($st->slug)
                                            <a href="{{ route('store.show', $st->slug) }}" target="_blank" class="px-2.5 py-1 bg-white hover:bg-slate-50 text-slate-600 rounded text-xs font-bold uppercase tracking-wider border border-slate-200 transition-colors" title="View Public Store">
                                                ↗
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    No team stores launched under this partner yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: COMMISSION STATEMENTS & ORDERS -->
    <div x-show="activeTab === 'orders'" class="space-y-6">
        <!-- STEP 1: GROUPED ORDER BATCHES / STORES (Default Overview) -->
        <div x-show="viewMode === 'batches' && !selectedBatchKey" class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">
                            Order Batches & Store Events ({{ $orderBatches->count() }})
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Orders grouped by Master Store and Direct Order batches. Click any batch to view its itemized breakdown.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-400">View:</span>
                        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 shadow-sm text-xs font-bold">
                            <button type="button" @click="setViewMode('batches')" class="px-3 py-1 rounded-md transition-colors bg-slate-900 text-white font-black uppercase tracking-wider">
                                Grouped Batches ({{ $orderBatches->count() }})
                            </button>
                            <button type="button" @click="setViewMode('all_orders')" class="px-3 py-1 rounded-md transition-colors text-slate-600 hover:text-slate-900 uppercase tracking-wider">
                                All Orders ({{ $orders->count() }})
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3.5 text-center">Sort</th>
                                <th class="px-5 py-3.5">Store Name</th>
                                <th class="px-5 py-3.5">Coach / Organization</th>
                                <th class="px-4 py-3.5 text-center">Orders</th>
                                <th class="px-4 py-3.5 text-secondary font-black">Order Type</th>
                                <th class="px-4 py-3.5">Payment Method</th>
                                <th class="px-4 py-3.5 text-right">Commission</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($orderBatches as $batch)
                                <tr @click="openBatch('{{ $batch->key }}')" class="hover:bg-slate-50 transition-colors cursor-pointer group">
                                    {{-- Sort # --}}
                                    <td class="px-4 py-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 font-mono text-xs text-slate-400">
                                            <span class="text-slate-300 font-bold text-sm leading-none select-none">≡</span>
                                            <span class="w-8 h-7 rounded bg-white border border-slate-300 flex items-center justify-center text-slate-800 font-bold text-xs shadow-2xs">
                                                {{ $loop->iteration }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Store Name --}}
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-slate-900 group-hover:text-primary transition-colors text-sm">
                                            {{ $batch->store_name }}
                                        </div>
                                        @if($batch->slug)
                                            <div class="text-[11px] text-slate-400 font-mono">Slug: {{ $batch->slug }}</div>
                                        @elseif($batch->batch_id)
                                            <div class="text-[10px] text-slate-400 font-mono">Batch: {{ substr($batch->batch_id, 0, 12) }}...</div>
                                        @endif
                                    </td>

                                    {{-- Coach / Organization --}}
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-slate-900 text-sm">{{ $batch->coach_name }}</div>
                                        <div class="text-xs text-slate-500">{{ $batch->organization }}</div>
                                    </td>

                                    {{-- Orders Count --}}
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-bold font-mono rounded-full bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $batch->order_count }} {{ \Illuminate\Support\Str::plural('order', $batch->order_count) }}
                                        </span>
                                    </td>

                                    {{-- Order Type --}}
                                    <td class="px-4 py-4">
                                        @if($batch->is_direct)
                                            <span class="inline-flex items-center px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-md bg-purple-50 text-purple-700 border border-purple-200 shadow-2xs">
                                                FINALIZED DIRECT ORDERS
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 text-[11px] font-black uppercase tracking-wider rounded-md bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs">
                                                FINALIZED MASTER ORDERS
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Payment Method --}}
                                    <td class="px-4 py-4">
                                        @if($batch->payment_method === 'Stripe')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-2xs">
                                                <span>💳</span> Stripe
                                            </span>
                                        @elseif($batch->payment_method === 'Check')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold bg-blue-50 text-blue-800 border border-blue-300 shadow-2xs">
                                                <span>📑</span> Check
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300 shadow-2xs">
                                                <span>💵</span> Cash
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Commission --}}
                                    <td class="px-4 py-4 text-right">
                                        <div class="font-bold text-emerald-600 text-sm">
                                            ${{ number_format($batch->commission, 2) }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            Gross: ${{ number_format($batch->gross, 2) }}
                                        </div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-4 text-right" @click.stop>
                                        <div class="flex items-center justify-end gap-2.5">
                                            @if($batch->slug)
                                                <a href="{{ route('store.show', $batch->slug) }}" target="_blank" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1">
                                                    View Store
                                                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                            @endif
                                            <button type="button" @click="openBatch('{{ $batch->key }}')" class="px-3 py-1 bg-white border border-secondary text-secondary hover:bg-secondary hover:text-white rounded-md text-xs font-bold uppercase tracking-wider transition-colors shadow-2xs cursor-pointer">
                                                Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-500 font-sans">
                                        No order batches recorded for this sales partner yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- STEP 2: ITEMIZED BREAKDOWN FOR SELECTED BATCH --}}
        @foreach($orderBatches as $batch)
            <div x-show="viewMode === 'batches' && (selectedBatchKey === '{{ $batch->key }}' || selectedBatchKey === '{{ $batch->batch_id }}' || selectedBatchKey === 'batch_{{ $batch->batch_id }}')" class="space-y-6" style="display: none;">
                {{-- Back Navigation & Header Bar --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <button type="button" @click="closeBatch()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white rounded-lg text-xs font-black uppercase tracking-wider transition-all shadow-md hover:shadow-lg cursor-pointer w-fit">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        <span>← Back to Order Batches</span>
                    </button>
                    <div class="flex items-center gap-2">
                        @if($batch->is_direct && $batch->batch_id)
                            <a href="{{ route('admin.direct-batch.show', ['batch_id' => $batch->batch_id, 'return_to' => route('admin.sales-rep.show', ['user' => $user, 'tab' => 'orders', 'batch' => $batch->key])]) }}" class="px-3.5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-black uppercase tracking-wider transition-colors shadow-sm inline-flex items-center gap-1.5">
                                Edit Batch Taxes & Fees →
                            </a>
                        @elseif($batch->store_id)
                            <a href="{{ route('admin.store.edit', ['store' => $batch->store_id, 'return_to' => route('admin.sales-rep.show', ['user' => $user, 'tab' => 'orders', 'batch' => $batch->key])]) }}" class="px-3.5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-black uppercase tracking-wider transition-colors shadow-sm inline-flex items-center gap-1.5">
                                Manage Store →
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Batch Summary Card --}}
                <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 shadow-sm">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-5 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h3 class="text-lg font-black text-slate-900">{{ $batch->store_name }}</h3>
                                @if($batch->is_direct)
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded border bg-purple-50 text-purple-700 border-purple-200">
                                        FINALIZED DIRECT ORDERS
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded border bg-blue-50 text-blue-700 border-blue-200">
                                        FINALIZED MASTER ORDERS
                                    </span>
                                @endif
                                <span class="inline-flex px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-full border
                                    {{ $batch->status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($batch->status === 'cleared' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                                    {{ $batch->status }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-1 flex items-center gap-3 flex-wrap">
                                <span>Client: <strong class="text-slate-700">{{ $batch->coach_name }}</strong> ({{ $batch->organization }})</span>
                                @if($batch->batch_id)
                                    <span>· Batch ID: <strong class="font-mono text-slate-700">{{ $batch->batch_id }}</strong></span>
                                @endif
                                <span>· Payment: <strong class="text-slate-700">{{ $batch->payment_method }}</strong></span>
                            </div>
                        </div>
                    </div>

                    {{-- Metrics Strip --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 pt-5 font-sans text-center">
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-sans">Orders</span>
                            <span class="text-base font-bold text-slate-900">{{ $batch->order_count }}</span>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-sans">Gross Sales</span>
                            <span class="text-base font-bold text-slate-900">${{ number_format($batch->gross, 2) }}</span>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-sans">Base Cost</span>
                            <span class="text-base font-bold text-slate-700">${{ number_format($batch->base_cost, 2) }}</span>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-sans">Taxes & Fees</span>
                            <span class="text-base font-bold text-slate-700">${{ number_format($batch->taxes_fees, 2) }}</span>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 font-sans">Net Margin</span>
                            <span class="text-base font-black text-slate-900">${{ number_format($batch->net_margin, 2) }}</span>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-3 border border-emerald-200">
                            <span class="block text-[10px] font-black uppercase tracking-wider text-emerald-800 font-sans">Rep Commission</span>
                            <span class="text-base font-black text-emerald-700">${{ number_format($batch->commission, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Itemized Table for this Batch (Exact table from user's image) --}}
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-black uppercase tracking-tight text-slate-900">
                                Itemized Order Breakdown ({{ $batch->order_count }})
                            </h4>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Individual parent orders and deductions contributing to this batch payout.
                            </p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-5 py-3.5">Order / Batch</th>
                                    <th class="px-5 py-3.5">Client / Store</th>
                                    <th class="px-5 py-3.5 text-right">Gross Sales</th>
                                    <th class="px-5 py-3.5 text-right">Base Cost</th>
                                    <th class="px-5 py-3.5 text-right">Taxes & Fees</th>
                                    <th class="px-5 py-3.5 text-right">Net Margin</th>
                                    <th class="px-5 py-3.5 text-center">Split %</th>
                                    <th class="px-5 py-3.5 text-right">Commission</th>
                                    <th class="px-5 py-3.5 text-center">Status</th>
                                    <th class="px-5 py-3.5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($batch->orders as $ord)
                                    @php
                                        $gross = (float)($ord->subtotal > 0 ? $ord->subtotal : $ord->total_retail_price);
                                        $base = (float)$ord->manufacturing_cost;
                                        $tariffsFees = (float)($ord->tariff_amount + $ord->fee_deduction);
                                        $margin = (float)$ord->net_margin;
                                        $rate = (float)($ord->applied_rep_commission_rate ?? $user->getEffectiveCommissionRate());
                                        $earned = (float)$ord->rep_commission_earned;
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors text-xs font-sans">
                                        <td class="px-5 py-3.5">
                                            <div class="font-bold text-slate-900">#{{ $ord->id }}</div>
                                            <div class="text-[10px] text-slate-400 font-sans">{{ $ord->created_at?->format('M d, Y') ?? '—' }}</div>
                                            @if($ord->batch_id)
                                                <div class="text-[9px] text-slate-400 font-mono truncate max-w-[120px]">Batch: {{ substr($ord->batch_id, 0, 8) }}...</div>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 font-sans">
                                            <div class="font-bold text-slate-900">{{ $ord->teamStore?->name ?? $ord->user?->organization ?? 'Direct Order' }}</div>
                                            <div class="text-[10px] text-slate-500">{{ $ord->user?->name ?? 'Coach' }}</div>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-bold text-slate-900">${{ number_format($gross, 2) }}</td>
                                        <td class="px-5 py-3.5 text-right text-slate-600">${{ number_format($base, 2) }}</td>
                                        <td class="px-5 py-3.5 text-right text-slate-600">${{ number_format($tariffsFees, 2) }}</td>
                                        <td class="px-5 py-3.5 text-right font-bold text-slate-900">${{ number_format($margin, 2) }}</td>
                                        <td class="px-5 py-3.5 text-center font-bold text-slate-700">{{ number_format($rate, 1) }}%</td>
                                        <td class="px-5 py-3.5 text-right font-black text-emerald-600 text-sm">${{ number_format($earned, 2) }}</td>
                                        <td class="px-5 py-3.5 text-center font-sans">
                                            <span class="inline-flex px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-full border
                                                {{ $ord->commission_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($ord->commission_status === 'cleared' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                                                {{ $ord->commission_status ?: 'pending' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-sans whitespace-nowrap">
                                            @if($ord->isDirectOrder() && $ord->batch_id)
                                                <a href="{{ route('admin.direct-batch.show', ['batch_id' => $ord->batch_id, 'return_to' => route('admin.sales-rep.show', ['user' => $user, 'tab' => 'orders', 'batch' => $batch->key])]) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors whitespace-nowrap inline-flex items-center gap-1 shadow-2xs">
                                                    Edit Batch →
                                                </a>
                                            @else
                                                <a href="{{ route('admin.order.edit', ['order' => $ord, 'return_to' => route('admin.sales-rep.show', ['user' => $user, 'tab' => 'orders', 'batch' => $batch->key])]) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors whitespace-nowrap inline-flex items-center gap-1 shadow-2xs">
                                                    Edit Taxes & Fees →
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- OPTIONAL FLAT MASTER LIST OF ALL ORDERS --}}
        <div x-show="viewMode === 'all_orders'" class="space-y-6" style="display: none;">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">
                            Complete Itemized Orders Ledger ({{ $orders->count() }})
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Flat view of every individual parent order across all stores and batches.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-400">View:</span>
                        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5 shadow-sm text-xs font-bold">
                            <button type="button" @click="setViewMode('batches')" class="px-3 py-1 rounded-md transition-colors text-slate-600 hover:text-slate-900 uppercase tracking-wider">
                                Grouped Batches ({{ $orderBatches->count() }})
                            </button>
                            <button type="button" @click="setViewMode('all_orders')" class="px-3 py-1 rounded-md transition-colors bg-slate-900 text-white font-black uppercase tracking-wider">
                                All Orders ({{ $orders->count() }})
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-100/70 text-slate-700 text-[11px] font-black uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3.5 whitespace-nowrap">Order / Batch</th>
                                <th class="px-5 py-3.5 min-w-[160px]">Client / Store</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Gross Sales</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Base Cost</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Taxes & Fees</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Net Margin</th>
                                <th class="px-5 py-3.5 text-center whitespace-nowrap">Split %</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Commission</th>
                                <th class="px-5 py-3.5 text-center whitespace-nowrap">Status</th>
                                <th class="px-5 py-3.5 text-right whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($orders as $ord)
                                @php
                                    $gross = (float)($ord->subtotal > 0 ? $ord->subtotal : $ord->total_retail_price);
                                    $base = (float)$ord->manufacturing_cost;
                                    $tariffsFees = (float)($ord->tariff_amount + $ord->fee_deduction);
                                    $margin = (float)$ord->net_margin;
                                    $rate = (float)($ord->applied_rep_commission_rate ?? $user->getEffectiveCommissionRate());
                                    $earned = (float)$ord->rep_commission_earned;
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors text-xs font-sans">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <div class="font-bold text-slate-900">#{{ $ord->id }}</div>
                                        <div class="text-[10px] text-slate-400 font-sans">{{ $ord->created_at?->format('M d, Y') ?? '—' }}</div>
                                        @if($ord->batch_id)
                                            <div class="text-[9px] text-slate-400 font-mono truncate max-w-[120px]">Batch: {{ substr($ord->batch_id, 0, 8) }}...</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 font-sans min-w-[160px]">
                                        <div class="font-bold text-slate-900">{{ $ord->teamStore?->name ?? $ord->user?->organization ?? 'Direct Order' }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $ord->user?->name ?? 'Coach' }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold text-slate-900 whitespace-nowrap">${{ number_format($gross, 2) }}</td>
                                    <td class="px-5 py-3.5 text-right text-slate-600 whitespace-nowrap">${{ number_format($base, 2) }}</td>
                                    <td class="px-5 py-3.5 text-right text-slate-600 whitespace-nowrap">${{ number_format($tariffsFees, 2) }}</td>
                                    <td class="px-5 py-3.5 text-right font-bold text-slate-900 whitespace-nowrap">${{ number_format($margin, 2) }}</td>
                                    <td class="px-5 py-3.5 text-center font-bold text-slate-700 whitespace-nowrap">{{ number_format($rate, 1) }}%</td>
                                    <td class="px-5 py-3.5 text-right font-black text-emerald-600 text-sm whitespace-nowrap">${{ number_format($earned, 2) }}</td>
                                    <td class="px-5 py-3.5 text-center font-sans whitespace-nowrap">
                                        <span class="inline-flex px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-full border
                                            {{ $ord->commission_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($ord->commission_status === 'cleared' ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                                            {{ $ord->commission_status ?: 'pending' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-sans whitespace-nowrap">
                                        @if($ord->isDirectOrder() && $ord->batch_id)
                                            <a href="{{ route('admin.direct-batch.show', ['batch_id' => $ord->batch_id, 'return_to' => route('admin.sales-rep.show', ['user' => $user, 'tab' => 'orders', 'view' => 'all_orders'])]) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors whitespace-nowrap inline-flex items-center gap-1 shadow-2xs">
                                                Edit Batch →
                                            </a>
                                        @else
                                            <a href="{{ route('admin.order.edit', ['order' => $ord, 'return_to' => route('admin.sales-rep.show', ['user' => $user, 'tab' => 'orders', 'view' => 'all_orders'])]) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-bold uppercase tracking-wider border border-slate-300 transition-colors whitespace-nowrap inline-flex items-center gap-1 shadow-2xs">
                                                Edit Taxes & Fees →
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-5 py-12 text-center text-slate-500 font-sans">
                                        No qualifying orders recorded for this sales partner yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
