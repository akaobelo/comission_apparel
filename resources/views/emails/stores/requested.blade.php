<x-mail::message>
@if($recipientType === 'sales_rep')
# 🛒 New Team Store Request from Your Client

One of your assigned coaches has created a new team store request on The Commission Apparel.
@else
# 🛒 New Team Store Request Created

A coach has submitted a new team store request awaiting review and activation.
@endif

<x-mail::panel>
**Team Store Details:**
- **Store Name:** {{ $store->name }}
- **Coach / Client:** {{ $store->user?->name ?? 'Coach' }}
@if(!empty($store->user?->email))
- **Email:** [{{ $store->user->email }}](mailto:{{ $store->user->email }})
@endif
- **Organization / Team:** {{ $store->user?->organization ?: 'Not specified' }}
- **Sport / Category:** {{ $store->sport ?: 'General' }}
- **Package Type:** {{ ucwords(str_replace('_', ' ', $store->package_type ?? 'individual')) }}
- **Payment Mode:** {{ ucwords(str_replace('_', ' ', $store->payment_mode ?? 'in_house')) }}
- **Assigned Sales Partner:** {{ $store->salesRep?->name ?: ($store->user?->salesRep?->name ?? 'Direct House (Unassigned)') }}
- **Created Date:** {{ $store->created_at ? $store->created_at->format('M j, Y g:i A') : now()->format('M j, Y g:i A') }}
</x-mail::panel>

<x-mail::button :url="$recipientType === 'sales_rep' ? route('rep.dashboard') : config('app.url') . '/admin/dashboard'">
@if($recipientType === 'sales_rep')
View in Partner Portal
@else
Review Store in Admin Dashboard
@endif
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
