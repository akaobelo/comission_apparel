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
                    'FAQs' => 'Find general answers to frequently asked questions about our process, materials, and shipping.',
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
                        <div class="pt-2 border-t border-slate-100">
                            {{ $answer }}
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
