<x-mail::message>
@if($recipientType === 'sales_rep')
# 🎉 A New Coach Just Joined Under Your Referral Link!

A coach has successfully registered on The Commission Apparel using your partner link or referral code. They are now linked to your sales portfolio.
@else
# 🚀 New Coach Registration Received

A new coach has just created an account on The Commission Apparel.
@endif

<x-mail::panel>
**Coach Information:**
- **Full Name:** {{ $coach->name }}
- **Email:** [{{ $coach->email }}](mailto:{{ $coach->email }})
- **Phone:** {{ $coach->phone ? '[' . $coach->phone . '](tel:' . $coach->phone . ')' : 'Not provided' }}
- **Organization / Team:** {{ $coach->organization ?: 'Not provided' }}
- **Sport:** {{ $coach->sport ?: 'Not specified' }}
- **Assigned Sales Partner:** {{ $coach->salesRep?->name ?: ($coach->sales_rep ?: 'Direct Website Registration (None)') }}
- **Registration Time:** {{ $coach->created_at ? $coach->created_at->format('M j, Y g:i A') : now()->format('M j, Y g:i A') }}
</x-mail::panel>

<x-mail::button :url="$recipientType === 'sales_rep' ? route('rep.dashboard') : config('app.url') . '/admin/dashboard'">
@if($recipientType === 'sales_rep')
Open Partner Portal
@else
View in Admin Dashboard
@endif
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
