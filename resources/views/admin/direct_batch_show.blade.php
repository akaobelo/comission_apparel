@extends('layouts.app')
@section('title', 'Review Direct Order Batch | Admin')
@section('content')
<div class="max-w-7xl mx-auto px-6 pb-8 pt-32 lg:pt-40">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.dashboard') }}" class="text-slate-500 hover:text-primary transition-colors flex items-center gap-1 text-sm font-bold uppercase tracking-wide">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Dashboard
        </a>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-bold uppercase tracking-wide">Direct Order Batch: {{ $batchId }}</span>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 font-bold">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-bold">{{ session('error') }}</div>
    @endif

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="lg:w-2/3 space-y-6">
            <!-- Orders List -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">Placed Orders</h3>
                        @if($orders->isNotEmpty())
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">Timeline Tracker:</span>
                                <form action="{{ route('admin.batch.status.update', $batchId) }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <select name="status" class="bg-white border border-slate-300 rounded px-2 py-1 text-[11px] font-bold text-slate-700 focus:border-primary focus:outline-none shadow-sm" onchange="this.form.submit()">
                                        <option value="Submitted to Admin" {{ $firstOrder->status === 'Submitted to Admin' ? 'selected' : '' }}>Submitted to Admin</option>
                                        <option value="Processing" {{ $firstOrder->status === 'Processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="Design Approved" {{ $firstOrder->status === 'Design Approved' ? 'selected' : '' }}>Design Approved</option>
                                        <option value="In Production" {{ $firstOrder->status === 'In Production' ? 'selected' : '' }}>In Production</option>
                                        <option value="Shipped" {{ $firstOrder->status === 'Shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="Delivered" {{ $firstOrder->status === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="Completed" {{ $firstOrder->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </form>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-xs font-bold text-slate-500">{{ $orders->count() }} Orders</div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.batch.export', $batchId) }}" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-700 text-[11px] font-bold uppercase tracking-wide rounded hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                                Roster CSV
                            </a>
                            <a href="{{ route('admin.batch.export-aggregate', $batchId) }}" class="px-3 py-1.5 bg-slate-900 border border-slate-900 text-white text-[11px] font-bold uppercase tracking-wide rounded hover:bg-slate-800 transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                                Aggregate CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <div class="p-6 hover:bg-slate-50 transition-colors group">
                            <div class="flex flex-wrap md:flex-nowrap justify-between gap-4">
                                <div class="w-full md:w-auto">
                                    <div class="font-bold text-slate-900">{{ $order->athlete_first_name }} {{ $order->athlete_last_name }}</div>
                                    <div class="text-xs text-slate-500 mt-1 flex gap-3">
                                        @if($order->gender)<span class="bg-slate-100 px-2 py-0.5 rounded text-slate-600 font-medium">{{ $order->gender }}</span>@endif
                                    </div>
                                    @if($order->is_edited)
                                        <div class="mt-2 text-[10px] font-bold uppercase tracking-wider text-orange-600 bg-orange-50 px-2 py-1 rounded inline-block">
                                            Edited by {{ $order->edited_by }}
                                        </div>
                                    @endif
                                </div>

                                <div class="w-full md:w-auto space-y-2 flex-grow">
                                    @foreach(is_array($order->items_json) ? $order->items_json : [] as $item)
                                        <div class="text-sm flex justify-between bg-white border border-slate-100 p-2 rounded">
                                            <div class="text-slate-700 font-medium truncate pr-4 max-w-[200px]">{{ $item['name'] ?? 'Item' }}</div>
                                            <div class="text-slate-500 text-right whitespace-nowrap">
                                                @if(!empty($item['sizes']))
                                                    @foreach($item['sizes'] as $sizeType => $size)
                                                        <span class="mr-2 border-r border-slate-200 pr-2 last:border-0 last:mr-0 last:pr-0">
                                                            {{ $sizeType === 'default' ? '' : str_replace('_', ' ', $sizeType).':' }} <strong>{{ $size }}</strong>
                                                        </span>
                                                    @endforeach
                                                @elseif(!empty($item['size']))
                                                    <strong>{{ $item['size'] }}</strong>
                                                @endif
                                                <span class="ml-2 bg-slate-100 px-2 py-0.5 rounded font-bold text-slate-700">Qty: {{ $item['qty'] ?? 1 }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($order->special_notes)
                                        <div class="text-xs text-slate-600 bg-yellow-50 p-2 rounded border border-yellow-100 mt-2">
                                            <strong class="text-yellow-800">Note:</strong> {{ $order->special_notes }}
                                        </div>
                                    @endif
                                </div>

                                <div class="w-full md:w-auto flex md:flex-col gap-2 items-end justify-start">
                                    <a href="{{ route('admin.order.edit', $order) }}" class="btn btn-outline py-2 px-4 text-xs">Edit</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-sm">
                            No active orders in this batch.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:w-1/3">
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden sticky top-32">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">Batch Summary</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Coach</span><span class="font-bold text-slate-900 text-right">{{ $firstOrder->user->first_name ?? 'Unknown' }} {{ $firstOrder->user->last_name ?? '' }}<br><span class="text-xs font-normal text-slate-500">{{ $firstOrder->user->organization ?? '' }}</span></span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Submitted</span><span class="font-bold text-slate-900">{{ $firstOrder->created_at->format('M d, Y') }}</span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-500">Total Items</span><span class="font-bold text-slate-900">{{ $financials['total_items_sold'] ?? 0 }}</span></div>
                    
                    <div class="pt-3 mt-3 border-t border-slate-200 space-y-3">
                        <div class="flex justify-between text-sm"><span class="text-slate-500">Total</span><span class="font-bold text-slate-900">${{ number_format($financials['total_sales'] ?? 0, 2) }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-slate-500">Due To TCA</span><span class="font-bold text-secondary">${{ number_format($financials['total_wholesale'] ?? 0, 2) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
