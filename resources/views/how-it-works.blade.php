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
                        <div class="space-y-6 mt-2">
                            <div>
                                <h4 class="text-base font-bold text-slate-900 mb-2">Is there a fee to get started?</h4>
                                <ul class="list-disc pl-5 mt-1 text-sm text-justify text-slate-600">
                                    <li>Yes, a $300 design deposit is required before we begin work on your project. This deposit is fully credited toward your initial order and will be applied to your balance once your order is placed. The deposit allows our team to dedicate the time, creativity, and attention needed to create a custom design experience tailored to your vision. If you choose not to move forward after the design process is completed, the deposit becomes non-refundable and serves as compensation for the time and expertise invested in your project. We appreciate the opportunity to work with you and are committed to delivering designs that reflect your goals and expectations.</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 mb-2">Is there a fee to use the “Team Store” feature?</h4>
                                <ul class="list-disc pl-5 mt-1 text-sm text-justify text-slate-600">
                                    <li>No, there are no fees associated with using our Team Store feature. We created this service to simplify the ordering process and reduce the administrative burden on coaches, team leaders, and volunteers by eliminating the need to collect, track, and manage individual orders. Our goal is to make apparel ordering as convenient and stress-free as possible for everyone involved.</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 mb-2">Can I set my own prices for my store items?</h4>
                                <ul class="list-disc pl-5 mt-1 text-sm text-justify text-slate-600">
                                    <li>Yes. Once your profile has been created, you have full control over how your items are priced. This flexibility allows you to set prices that align with your goals, whether you\'re fundraising, generating team support, or simply covering costs. We believe you should have the freedom to manage your store in a way that best serves your organization and community.</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 mb-2">Is there a minimum order quantity that must be met?</h4>
                                <ul class="list-disc pl-5 mt-1 text-sm text-justify text-slate-600">
                                    <li>Yes. Each item has a minimum order quantity (MOQ) of 10 units required in order to move into production. This helps us maintain quality standards and ensure efficient production for every order.</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 mb-2">Are payments collected through the site?</h4>
                                <ul class="list-disc pl-5 mt-1 text-sm text-justify text-slate-600">
                                    <li>No. We do not collect payments from your customers through the platform. Instead, you maintain full control over all customer transactions and funds. When you\'re ready to place an order from your Team Store, simply submit your order and we will send you an invoice through Intuit QuickBooks. This process allows you to keep and manage your proceeds immediately, without waiting for payouts or reimbursement. We believe giving you direct control over your funds creates a simpler, more transparent experience for your organization.</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 mb-2">What forms of payment are accepted?</h4>
                                <ul class="list-disc pl-5 mt-1 text-sm text-justify text-slate-600">
                                    <li>We accept payments through Intuit QuickBooks “click and pay” invoices as well as bank wire transfers. These options are designed to make the payment process simple, secure, and convenient for our clients.</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 mb-2">What is the average turnaround time for production and delivery?</h4>
                                <ul class="list-disc pl-5 mt-1 text-sm text-justify text-slate-600">
                                    <li>Most orders are completed within 7–10 business days after payment is received. Once production is finished, you can choose between standard and expedited shipping based on your needs. Standard shipping typically arrives within 10 business days, while expedited shipping is generally delivered within 5 business days. We understand that timelines are important and work diligently to ensure your order is produced and delivered as quickly as possible without compromising quality.</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 mb-2">Can I get a refund on my order?</h4>
                                <ul class="list-disc pl-5 mt-1 text-sm text-justify text-slate-600">
                                    <li>Because all of our products are custom-made, all sales are final and we are unable to offer refunds once an order has been placed. However, your satisfaction is important to us. In the rare event that an item arrives with a manufacturing defect or an error on our part, we will gladly replace the item and cover all associated shipping costs. If you experience an issue with your order, please contact our team and we will work quickly to make it right.</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900 mb-2">How do I get in contact with a member of the team?</h4>
                                <ul class="list-disc pl-5 mt-1 text-sm text-justify text-slate-600">
                                    <li>We’re always happy to help. You can reach our team by emailing Info@TheCommissionApparel.com, and a member of our staff will respond within 24 hours. Once your profile is created, you’ll also be paired with a dedicated team member who will work with you throughout the production process to ensure everything runs smoothly. Whether you have questions about an order, design ideas, or getting started with a Team Store, we’re here to support you and look forward to connecting.</li>
                                </ul>
                            </div>
                        </div>
                    ',
                    'How do I set up my profile?' => '
                        <div class="space-y-4">
                            <p><strong>STEP #1:</strong> Click the “Dashboard Sign-in” option in the top banner which will bring you to the system portal sign-in section</p>
                            <p><strong>STEP #2:</strong> Click the “Apply for Access” link at the bottom of the page to register as a new user</p>
                            <p><strong>STEP #3:</strong> Complete the “User Registration” form in its entirety then click “Create My Account” to submit.</p>
                            <p>Once completed, you will immediately gain access to your admin portal where you will be able to request your team store or create direct orders based on your approved designs.</p>
                        </div>
                    ',
                    'How do I request my team store?' => '
                        <div class="space-y-4">
                            <p><strong>STEP #1:</strong> Log into your admin portal using the user dashboard sign in button</p>
                            <p><strong>STEP #2:</strong> Click the Store Overview” tab atop the page which will bring you to the “Request Your Team Store” form.</p>
                            <p><strong>STEP #3:</strong> Complete the form and click “Submit Store Request”</p>
                            <p class="text-sm italic">Note: All stores must be reviewed and approved by a member of our team, please allow 24 hours for this process.</p>
                        </div>
                    ',
                    'How do I set up my team store?' => '
                        <div class="space-y-4">
                            <p>Once your Team Store has been approved and your custom designs assigned to your profile, you can set up your store by completing the following:</p>
                            <p><strong>STEP #1:</strong> Log into your user portal via the “Dashboard Sign-in”, this will port you to the store overview page in your portal</p>
                            <p><strong>STEP #2:</strong> Scroll down to the bottom of the page where to the “Branding & Artwork” section.</p>
                            <p><strong>STEP #3:</strong> Click “Choose File” in the “Organization Logo” section and select a photo for your page’s profile image.</p>
                            <p><strong>STEP #4:</strong> Click “Choose File” in the Store Cover Image section to select a photo for your page’s cover image.</p>
                            <p><strong>STEP #5:</strong> At the very bottom of the page in the “Store Builder” section, click “Browse Catalog” and select each of the custom designed items you want offered on your store front.</p>
                            <p><strong>STEP #6:</strong> Click the “Add to Store” button to assign each item to your store front store.</p>
                            <ul class="list-disc pl-5 mt-1 text-sm text-justify text-slate-600">
                                <li>Be sure to enter your desired sales price of each item you select</li>
                            </ul>
                            <p><strong>STEP #7:</strong> Once you have selected all stores items to be added from the design catalog, you will need to review all items in the “Pricing Ready for Review” section. Click “Edit Pricing” to amend listing or “I Approve This Pricing” to finalize.</p>
                        </div>
                    ',
                    'How can I edit pricing on the items in my store?' => '
                        <div class="space-y-4">
                            <p>If you would like to update the prices in your store after the initial setup, follow the steps below:</p>
                            <p><strong>STEP #1:</strong> Log into your user portal via the “Dashboard Sign-in”</p>
                            <p><strong>STEP #2:</strong> Scroll down to the “Current Store Items” section</p>
                            <p><strong>STEP #3:</strong> Enter the retail price for each item you’d like revised</p>
                            <p><strong>STEP #4:</strong> Scroll down and click “Save All Changes”</p>
                        </div>
                    ',
                    'How do I set the deadline for orders on my store front?' => '
                        <div class="space-y-4">
                            <p>The “Order Deadline” section is used to control when your storefront is open or closed for purchases. This feature allows you to set an ordering window, helping you organize purchases into separate order batches based on your organization\'s fulfillment schedule.</p>
                            <p>Once the order deadline has passed, your storefront will automatically be marked as Closed, preventing any new purchases. To begin accepting orders again, simply click the Open Store button and set a new order deadline.</p>
                            <p><strong>STEP #1:</strong> Log into your user portal via the “Dashboard Sign-in”</p>
                            <p><strong>STEP #2:</strong> Enter your end date in the “Order Deadline” section atop the page</p>
                            <p><strong>STEP #3:</strong> Click “Set Deadline”</p>
                        </div>
                    ',
                    'How do I share my team store link?' => '
                        <div class="space-y-4">
                            <p>Once you have completed your store setup, sharing your store link with team members, family, friends, and supporters is a simple process.</p>
                            <p><strong>STEP #1:</strong> Log into your user portal via the “Dashboard Sign-in”</p>
                            <p><strong>STEP #2:</strong> Click the “Share link” button atop the page in the Team Store section, this will take you to your team store.</p>
                            <p><strong>STEP #3:</strong> Copy the link from the address bar and share within your team chat and social media platforms as needed.</p>
                        </div>
                    ',
                    'How do I check orders placed on my store?' => '
                        <div class="space-y-4">
                            <p>As the profile administrator, you have access to several areas within the platform where you can monitor your store\'s sales performance and track order progress.</p>
                            <ul class="list-disc pl-5 space-y-6 mt-4">
                                <li>
                                    <strong class="uppercase">STOREFRONT VIEW:</strong>
                                    <ul class="list-[circle] pl-12 mt-2 space-y-2">
                                        <li><strong>STEP #1:</strong> Select your store from the “Team Stores” tab on the Commission Apparel website</li>
                                        <li><strong>STEP #2:</strong> Scroll to the bottom of the page to the “Placed Orders” section and review the orders individually.</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong class="uppercase">SALES TAB</strong>
                                    <ul class="list-[circle] pl-12 mt-2 space-y-2">
                                        <li><strong>STEP #1:</strong> Log into your user portal via the “Dashboard Sign-in”</li>
                                        <li><strong>STEP #2:</strong> Review the snapshot of orders placed during the current order deadline period and financial figures from that same period.</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong class="uppercase">ORDER PROGRESS SECTION</strong>
                                    <ul class="list-[circle] pl-12 mt-2 space-y-2">
                                        <li><strong>STEP #1:</strong> Log into your user portal via the “Dashboard Sign-in”</li>
                                        <li><strong>STEP #2:</strong> Scroll down to the second section “Order Progress” and click each athlete to review their order individually</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong class="uppercase">CSV FORMAT:</strong>
                                    <ul class="list-[circle] pl-12 mt-2 space-y-2">
                                        <li><strong>STEP #1:</strong> Log into your user portal via the “Dashboard Sign-in”</li>
                                        <li><strong>STEP #2:</strong> In the “Team Store” section atop the page, click the “Export CSV” to review all orders placed on your storefront for this deadline period.</li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    ',
                    'How can I edit orders submitted on the store front?' => '
                        <div class="space-y-4">
                            <p>Since parents and customers are not required to create individual accounts prior to placing an order, they are unable to modify an order after it has been submitted. Any requested changes must be communicated to the store administrator, who can update the order as needed. As the admin for your store, amending orders is a very simple process, follow the steps below:</p>
                            <p><strong>STEP #1:</strong> Log into your user portal via the “Dashboard Sign-in”</p>
                            <p><strong>STEP #2:</strong> Scroll down to the “Order Progress” section and find and click the name of the person/athlete requiring the amendment</p>
                            <p><strong>STEP #3:</strong> Click the “View/Edit” button next to their name</p>
                            <p><strong>STEP #4:</strong> Make the necessary amendments to the order</p>
                            <p><strong>STEP #5:</strong> Click “Save Changes”</p>
                            <p class="text-sm italic">Note, if the order is cancelled completely, you have the option to remove it from your order batch by clicking “Delete Order” at the bottom of the page.</p>
                        </div>
                    ',
                    'Can I submit a batch order before my order deadline?' => '
                        <div class="space-y-4">
                            <p>Yes, once you have met the minimum order quantity of 10 units per item within a batch, you may submit that batch at any time to begin production.</p>
                            <p>Before submitting, please carefully review the order to ensure all information is accurate. You can do this by selecting each athlete in the Order Progress section or by clicking the Export CSV button to download the report and review it in Excel.</p>
                            <p>Follow the steps below to submit your order before the deadline date:</p>
                            <p><strong>STEP #1:</strong> Log into your user portal via the “Dashboard Sign-in”</p>
                            <p><strong>STEP #2:</strong> Atop the page in the Team Store section, click the “Export CSV” to review order; since you are submitting ahead of the deadline, we recommend you check with parents that their sizes are final.</p>
                            <p><strong>STEP #3:</strong> Once you review is complete, click the “Approve/Submit” button followed by “Submit Final” and your order will be moved to production.</p>
                            <p><strong>STEP #4:</strong> Your store is automatically closed after submitting your order, you can open your store by clicking the “Re-open” store button atop the page and setting your deadline date.</p>
                        </div>
                    ',
                    'Can I create an order directly without using the store?' => '
                        <div class="space-y-4">
                            <p>Yes. As the account administrator, once your designs have been assigned to your profile, you can manage the ordering process independently of the Team Store if desired. Simply follow the steps below:</p>
                            <p><strong>STEP #1:</strong> Log into your user portal via the “Dashboard Sign-in”</p>
                            <p><strong>STEP #2:</strong> Click the “Create an Order” tab atop your admin portal section.</p>
                            <p><strong>STEP #3:</strong> Determine if you would like to “Order by Person” or “Order by Item”</p>
                            <p><strong>STEP #4:</strong> Check the box of the desired item and enter the quantity you desire next to the size</p>
                            <p><strong>STEP #5:</strong> Click “Add to Draft” to essentially add your items to the cart</p>
                            <p><strong>STEP #6:</strong> Click “Submit Draft To Production” once you have reviewed your order</p>
                        </div>
                    '
                ];
            @endphp

            @foreach($items as $question => $answer)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden transition-all duration-200" :class="{ 'ring-2 ring-primary/20 border-primary/30 shadow-md': activeAccordion === '{{ Str::slug($question) }}' }">
                    <button 
                        @click="activeAccordion = activeAccordion === '{{ Str::slug($question) }}' ? null : '{{ Str::slug($question) }}'"
                        class="w-full flex items-center justify-between p-6 text-left focus:outline-none transition-colors hover:bg-slate-50"
                    >
                        <span class="font-bold text-lg text-slate-900 uppercase">{{ $question }}</span>
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
