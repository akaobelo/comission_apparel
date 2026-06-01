@extends('layouts.app')

@section('title', 'How It Works | The Commission Apparel')

@section('content')
<div class="pt-32 pb-24 min-h-screen bg-slate-50">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight text-slate-900 mb-4">How It Works</h1>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">Everything you need to know about setting up your profile, team store, and managing orders with The Commission Apparel.</p>
        </div>

        <div class="space-y-4" x-data="{ activeAccordion: null }">
            
            @php
                $items = [
                    'FAQs' => '
                        <div class="space-y-4 mt-2">
                            <div>
                                <h4 class="font-semibold text-slate-800">Is there a fee to get started?</h4>
                                <p class="mt-1 text-sm text-justify">Yes, a $300 design deposit is required before we begin work on your project. This deposit is fully credited toward your initial order and will be applied to your balance once your order is placed. The deposit allows our team to dedicate the time, creativity, and attention needed to create a custom design experience tailored to your vision. If you choose not to move forward after the design process is completed, the deposit becomes non-refundable and serves as compensation for the time and expertise invested in your project. We appreciate the opportunity to work with you and are committed to delivering designs that reflect your goals and expectations.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800">Is there a fee to use the “Team Store” feature?</h4>
                                <p class="mt-1 text-sm text-justify">No, there are no fees associated with using our Team Store feature. We created this service to simplify the ordering process and reduce the administrative burden on coaches, team leaders, and volunteers by eliminating the need to collect, track, and manage individual orders. Our goal is to make apparel ordering as convenient and stress-free as possible for everyone involved.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800">Can I set my own prices for my store items?</h4>
                                <p class="mt-1 text-sm text-justify">Yes. Once your profile has been created, you have full control over how your items are priced. This flexibility allows you to set prices that align with your goals, whether you\'re fundraising, generating team support, or simply covering costs. We believe you should have the freedom to manage your store in a way that best serves your organization and community.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800">Is there a minimum order quantity that must be met?</h4>
                                <p class="mt-1 text-sm text-justify">Yes. Each item has a minimum order quantity (MOQ) of 10 units required in order to move into production. This helps us maintain quality standards and ensure efficient production for every order.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800">Are payments collected through the site?</h4>
                                <p class="mt-1 text-sm text-justify">No. We do not collect payments from your customers through the platform. Instead, you maintain full control over all customer transactions and funds. When you\'re ready to place an order from your Team Store, simply submit your order and we will send you an invoice through Intuit QuickBooks. This process allows you to keep and manage your proceeds immediately, without waiting for payouts or reimbursement. We believe giving you direct control over your funds creates a simpler, more transparent experience for your organization.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800">What forms of payment are accepted?</h4>
                                <p class="mt-1 text-sm text-justify">We accept payments through Intuit QuickBooks “click and pay” invoices as well as bank wire transfers. These options are designed to make the payment process simple, secure, and convenient for our clients.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800">What is the average turnaround time for production and delivery?</h4>
                                <p class="mt-1 text-sm text-justify">Most orders are completed within 7–10 business days after payment is received. Once production is finished, you can choose between standard and expedited shipping based on your needs. Standard shipping typically arrives within 10 business days, while expedited shipping is generally delivered within 5 business days. We understand that timelines are important and work diligently to ensure your order is produced and delivered as quickly as possible without compromising quality.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800">Can I get a refund on my order?</h4>
                                <p class="mt-1 text-sm text-justify">Because all of our products are custom-made, all sales are final and we are unable to offer refunds once an order has been placed. However, your satisfaction is important to us. In the rare event that an item arrives with a manufacturing defect or an error on our part, we will gladly replace the item and cover all associated shipping costs. If you experience an issue with your order, please contact our team and we will work quickly to make it right.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-slate-800">How do I get in contact with a member of the team?</h4>
                                <p class="mt-1 text-sm text-justify">We’re always happy to help. You can reach our team by emailing Info@TheCommissionApparel.com, and a member of our staff will respond within 24 hours. Once your profile is created, you’ll also be paired with a dedicated team member who will work with you throughout the production process to ensure everything runs smoothly. Whether you have questions about an order, design ideas, or getting started with a Team Store, we’re here to support you and look forward to connecting.</p>
                            </div>
                        </div>
                    ',
                    'How do I set up my profile?' => 'Setting up your profile is simple. Navigate to your dashboard, click on Profile Settings, and fill in your team details, logo, and preferred contact information.',
                    'How do I set up my team store?' => 'From your dashboard, click "Create Store". You can select designs from our catalog, set a closing date, and customize the store appearance for your players and parents.',
                    'How do I set prices on my store?' => 'When adding items to your team store, you will see the base price. You can enter a markup percentage or a flat markup amount. The difference between the retail price and base price becomes your fundraising profit.',
                    'How do clients order on my store?' => 'Once your store is live, you can share the link with your team. Parents and players can browse the products, select sizes, and pay securely using their credit card directly on the store page.',
                    'How do I place a direct order?' => 'If you are ordering in bulk and not using a team store, go to the Direct Orders tab in your dashboard. Select your designs, input the roster sizes and numbers, and submit the roster for approval.',
                    'How do I submit my orders from my store?' => 'Once your team store deadline is reached, the store will close automatically. You can then review all orders in the coach portal and click "Submit Master Order" to send it to production.',
                    'How do I the check status of my orders?' => 'In your coach portal, navigate to the Orders tab. You will see all your submitted batches along with their current status (e.g., In Production, Shipped) and estimated delivery dates.'
                ];
            @endphp

            @foreach($items as $question => $answer)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden transition-all duration-200" :class="{ 'ring-2 ring-primary/20 border-primary/30 shadow-md': activeAccordion === '{{ Str::slug($question) }}' }">
                    <button 
                        @click="activeAccordion = activeAccordion === '{{ Str::slug($question) }}' ? null : '{{ Str::slug($question) }}'"
                        class="w-full flex items-center justify-between p-6 text-left focus:outline-none transition-colors hover:bg-slate-50"
                    >
                        <span class="font-bold text-lg text-slate-900">{{ $question }}</span>
                        <svg class="w-6 h-6 text-slate-400 transform transition-transform duration-300" :class="{ 'rotate-180 text-primary': activeAccordion === '{{ Str::slug($question) }}' }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <div 
                        x-show="activeAccordion === '{{ Str::slug($question) }}'" 
                        class="px-6 pb-6 text-slate-600 leading-relaxed"
                        style="display: none;"
                    >
                        <div class="pt-2 border-t border-slate-100 text-justify">
                            {!! $answer !!}
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
        
        <div class="mt-16 mb-24 text-center">
            <p class="text-slate-600 mb-6">Still have questions? We're here to help.</p>
            <a href="/quote" class="inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-base font-bold rounded-lg text-white bg-primary hover:bg-primary/90 shadow-md hover:shadow-lg transition-all">
                Contact Support
            </a>
        </div>
    </div>
</div>
@endsection
