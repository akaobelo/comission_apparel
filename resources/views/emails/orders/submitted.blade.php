<x-mail::message>
@if($recipientType === 'sales_rep')
# 🔔 Great News! An Order Was Placed by Your Client

An order has just been submitted by one of your assigned coaches. Here is the summary of the submission:
@else
# 📦 New Order Submitted

A new order submission has arrived and is ready for production processing.
@endif

<x-mail::panel>
**Order Details:**
- **Order / Batch ID:** `{{ $orderData['batch_or_order_id'] ?? 'N/A' }}`
- **Type:** {{ $orderData['type_label'] ?? 'Standard Order' }}
- **Organization / Team:** {{ $orderData['organization'] ?? '—' }}
@if(!empty($orderData['store_name']))
- **Team Store:** {{ $orderData['store_name'] }}
@endif
- **Coach / Client:** {{ $orderData['coach_name'] ?? '—' }}
@if(!empty($orderData['coach_email']))
- **Email:** [{{ $orderData['coach_email'] }}](mailto:{{ $orderData['coach_email'] }})
@endif
@if(!empty($orderData['coach_phone']))
- **Phone:** [{{ $orderData['coach_phone'] }}](tel:{{ $orderData['coach_phone'] }})
@endif
@if(!empty($orderData['order_count']))
- **Athlete Orders:** {{ $orderData['order_count'] }}
@endif
@if(!empty($orderData['total_items']))
- **Total Apparel Items:** {{ $orderData['total_items'] }}
@endif
- **Total Revenue / Qualifying Sales:** **${{ number_format((float)($orderData['total_revenue'] ?? 0), 2) }}**
- **Payment Method / Status:** {{ $orderData['payment_mode'] ?? 'Submitted' }}
@if(!empty($orderData['sales_rep_name']))
- **Assigned Sales Partner:** {{ $orderData['sales_rep_name'] }}
@endif
</x-mail::panel>

<x-mail::button :url="$orderData['action_url'] ?? config('app.url') . '/admin/dashboard'">
@if($recipientType === 'sales_rep')
View in Partner Portal
@else
Review in Admin Dashboard
@endif
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
