<x-mail::message>
# 🚀 {{ ($quoteRequest->package_type === 'free_3d_mockup' || $quoteRequest->lead_source === 'landing_page_free_mockup') ? 'New Free Mockup Inquiry Received' : 'New Quote Inquiry Received' }}

A new inquiry has just been submitted from the **{{ $quoteRequest->lead_source === 'landing_page_free_mockup' ? 'Free Mockup Landing Page (/free-mockup)' : 'Website Quote Form' }}**.

<x-mail::panel>
**Contact Details:**
- **Name:** {{ $quoteRequest->first_name }} {{ $quoteRequest->last_name }}
- **Role:** {{ $quoteRequest->position_title }}
- **Email:** [{{ $quoteRequest->email }}](mailto:{{ $quoteRequest->email }})
- **Phone:** [{{ $quoteRequest->phone }}](tel:{{ $quoteRequest->phone }})

**Program & Order Details:**
- **Organization / Team:** {{ $quoteRequest->organization_name }}
- **Organization Type:** {{ $quoteRequest->organization_type ?: 'Not specified' }}
- **Sport / Apparel:** {{ $quoteRequest->apparel_category }}
- **Estimated Quantity:** {{ $quoteRequest->raw_quantity ?: $quoteRequest->estimated_quantity }}
- **Package Requested:** {{ ucwords(str_replace('_', ' ', $quoteRequest->package_type)) }}
</x-mail::panel>

@if($quoteRequest->utm_source || $quoteRequest->utm_campaign || $quoteRequest->utm_medium)
<x-mail::panel>
**Marketing & Ad Attribution:**
- **UTM Source:** {{ $quoteRequest->utm_source ?: '—' }}
- **UTM Medium:** {{ $quoteRequest->utm_medium ?: '—' }}
- **UTM Campaign:** {{ $quoteRequest->utm_campaign ?: '—' }}
- **UTM Term:** {{ $quoteRequest->utm_term ?: '—' }}
- **UTM Content:** {{ $quoteRequest->utm_content ?: '—' }}
</x-mail::panel>
@endif

<x-mail::button :url="config('app.url') . '/admin/dashboard'">
View in Admin Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
