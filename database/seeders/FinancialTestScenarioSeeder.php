<?php

namespace Database\Seeders;

use App\Models\ParentOrder;
use App\Models\TeamStore;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FinancialTestScenarioSeeder extends Seeder
{
    /**
     * Run the database seeds for staging financial and mathematical testing.
     * Seeds the exact 7 scenarios documented in the Commission Apparel Financial Test Sheet.
     */
    public function run(): void
    {
        $this->command?->info("--- Starting Commission Apparel Financial Test Seeder ---");

        // 1. Resolve or create a dedicated Sales Rep for the test scenarios
        $salesRep = User::where('role', 'sales_rep')->first();
        if (!$salesRep) {
            $salesRep = User::create([
                'first_name'      => 'Test',
                'last_name'       => 'Sales Partner',
                'email'           => 'partner.test@commissionapparel.com',
                'password'        => bcrypt('password123'),
                'role'            => 'sales_rep',
                'status'          => 'active',
                'commission_rate' => 50.00,
                'organization'    => 'Official Sales Partner Network',
            ]);
            $this->command?->info("Created new Sales Rep: {$salesRep->name} ({$salesRep->email})");
        } else {
            $this->command?->info("Using existing Sales Rep: {$salesRep->name} (#{$salesRep->id} - {$salesRep->email})");
        }

        // 2. Resolve or create a Coach/Client user
        $client = User::where('role', 'coach')->first();
        if (!$client) {
            $client = User::create([
                'first_name'   => 'Staging',
                'last_name'    => 'Coach',
                'email'        => 'coach.staging@example.com',
                'password'     => bcrypt('password123'),
                'role'         => 'coach',
                'status'       => 'active',
                'organization' => 'Springfield High Athletics',
                'sales_rep_id' => $salesRep->id,
            ]);
        } else {
            if (!$client->sales_rep_id) {
                $client->sales_rep_id = $salesRep->id;
                $client->save();
            }
        }

        // 3. Resolve or create a Team Store
        $store = TeamStore::where('user_id', $client->id)->first();
        if (!$store) {
            $store = TeamStore::create([
                'user_id'          => $client->id,
                'sales_rep_id'     => $salesRep->id,
                'name'             => 'Springfield Basketball 2026',
                'sport'            => 'Basketball',
                'description'      => 'Official Financial Math Test Storefront',
                'slug'             => 'springfield-basketball-test-' . uniqid(),
                'status'           => 'Active',
                'package_type'     => 'standard',
                'payment_mode'     => 'online',
                'pricing_approved' => true,
            ]);
        }

        // 4. Remove any previous test scenario orders (idempotent clean run)
        $deletedCount = ParentOrder::where('special_notes', 'like', '%[FINANCIAL_TEST_SCENARIO]%')->delete();
        if ($deletedCount > 0) {
            $this->command?->info("Cleaned up {$deletedCount} existing test scenario orders.");
        }

        // 5. Scenario definitions matching the Test Sheet matrix
        $scenarios = [
            [
                'code'        => 'TC-01',
                'title'       => 'Standard Team Store (50/50 split)',
                'gross'       => 1200.00,
                'mfg'         => 600.00,
                'tariff'      => 0.00,
                'fee'         => 0.00,
                'fundraising' => 0.00,
                'rate'        => 50.00,
                'status'      => 'pending',
                'paid_to_rep' => false,
                'athlete'     => ['Marcus', 'Vance'],
                'item_name'   => 'Custom Sublimated Team Uniform Set',
                'notes'       => 'Clean 50/50 split on standard team store order.',
            ],
            [
                'code'        => 'TC-02',
                'title'       => 'Tariff Applied (Import deduction)',
                'gross'       => 2500.00,
                'mfg'         => 1100.00,
                'tariff'      => 150.00,
                'fee'         => 0.00,
                'fundraising' => 0.00,
                'rate'        => 50.00,
                'status'      => 'pending',
                'paid_to_rep' => false,
                'athlete'     => ['Jordan', 'Hayes'],
                'item_name'   => 'Overseas Custom Sublimation Package',
                'notes'       => 'Custom overseas sublimation with $150 tariff deduction.',
            ],
            [
                'code'        => 'TC-03',
                'title'       => 'School Kickback ($5/item fundraising)',
                'gross'       => 3000.00,
                'mfg'         => 1400.00,
                'tariff'      => 0.00,
                'fee'         => 0.00,
                'fundraising' => 300.00,
                'rate'        => 50.00,
                'status'      => 'cleared',
                'paid_to_rep' => false,
                'athlete'     => ['Lucas', 'Reyes'],
                'item_name'   => 'Varsity Fan Wear & Uniform Combo',
                'notes'       => 'School booster club fundraising kickback of $300 deducted.',
            ],
            [
                'code'        => 'TC-04',
                'title'       => 'Senior Rep Tier (60% custom rate)',
                'gross'       => 4800.00,
                'mfg'         => 2200.00,
                'tariff'      => 100.00,
                'fee'         => 100.00,
                'fundraising' => 0.00,
                'rate'        => 60.00,
                'status'      => 'cleared',
                'paid_to_rep' => false,
                'athlete'     => ['Devon', 'Miller'],
                'item_name'   => 'Championship Club Pack (Senior Tier)',
                'notes'       => 'Senior partner commission tier override at 60.0%.',
            ],
            [
                'code'        => 'TC-05',
                'title'       => 'Junior Rep Tier (40% custom rate)',
                'gross'       => 1500.00,
                'mfg'         => 750.00,
                'tariff'      => 0.00,
                'fee'         => 50.00,
                'fundraising' => 0.00,
                'rate'        => 40.00,
                'status'      => 'paid',
                'paid_to_rep' => true,
                'athlete'     => ['Ethan', 'Cruz'],
                'item_name'   => 'Youth League Practice Pack (Junior Tier)',
                'notes'       => 'Junior partner referral tier at 40.0%, fully disbursed.',
            ],
            [
                'code'        => 'TC-06',
                'title'       => 'Zero-Margin Guard (Costs >= Gross)',
                'gross'       => 500.00,
                'mfg'         => 480.00,
                'tariff'      => 20.00,
                'fee'         => 50.00,
                'fundraising' => 0.00,
                'rate'        => 50.00,
                'status'      => 'pending',
                'paid_to_rep' => false,
                'athlete'     => ['Liam', 'Walker'],
                'item_name'   => 'Rushed Prototype Sample Kit',
                'notes'       => 'High sample cost guard test ($550 total costs vs $500 gross). Clamped to $0.00.',
            ],
            [
                'code'        => 'TC-07',
                'title'       => 'High-Volume Club Batch (Tariffs & fees)',
                'gross'       => 8500.00,
                'mfg'         => 3800.00,
                'tariff'      => 350.00,
                'fee'         => 250.00,
                'fundraising' => 0.00,
                'rate'        => 50.00,
                'status'      => 'paid',
                'paid_to_rep' => true,
                'athlete'     => ['Tyler', 'Brooke'],
                'item_name'   => 'Regional Travel Program Elite Batch',
                'notes'       => 'High volume travel club batch, fully cleared and paid out.',
            ],
        ];

        $now = Carbon::now();

        foreach ($scenarios as $index => $sc) {
            $orderDate = $now->copy()->subDays(7 - $index);

            $order = new ParentOrder();
            $order->team_store_id         = $store->id;
            $order->user_id               = $client->id;
            $order->sales_rep_id          = $salesRep->id;
            $order->athlete_first_name    = $sc['athlete'][0];
            $order->athlete_last_name     = $sc['athlete'][1];
            $order->parent_email          = strtolower($sc['athlete'][0]) . '.parent@example.com';
            $order->parent_phone          = '555-01' . str_pad((string)$index, 2, '0', STR_PAD_LEFT);
            $order->status                = 'Submitted';
            $order->payment_status        = 'Paid';
            $order->subtotal              = $sc['gross'];
            $order->total_retail_price    = $sc['gross'];
            $order->total_paid            = $sc['gross'];
            $order->manufacturing_cost    = $sc['mfg'];
            $order->tariff_amount         = $sc['tariff'];
            $order->fee_deduction         = $sc['fee'];
            $order->fundraising_amount    = $sc['fundraising'];
            $order->commission_status     = $sc['status'];
            $order->financial_notes       = "[{$sc['code']}] {$sc['notes']}";
            $order->special_notes         = "[FINANCIAL_TEST_SCENARIO] {$sc['code']}: {$sc['title']}";
            $order->items_json            = [
                [
                    'name'            => $sc['item_name'],
                    'qty'             => 1,
                    'size'            => 'L',
                    'retail_price'    => $sc['gross'],
                    'wholesale_price' => $sc['mfg'],
                ]
            ];
            $order->created_at            = $orderDate;
            $order->paid_at               = $orderDate;

            if ($sc['paid_to_rep']) {
                $order->paid_to_rep_at = $now->copy()->subDays(1);
            }

            // Execute the system calculation engine
            $order->calculateAndApplyCommission($sc['rate']);
            $order->save();

            $this->command?->line(sprintf(
                "  [%s] Gross: $%s | Mfg: $%s | Margin: $%s | Rep Rate: %s%% | Commission: $%s | Status: %s",
                $sc['code'],
                number_format($order->subtotal, 2),
                number_format($order->manufacturing_cost, 2),
                number_format($order->net_margin, 2),
                number_format($order->applied_rep_commission_rate, 1),
                number_format($order->rep_commission_earned, 2),
                strtoupper($order->commission_status)
            ));
        }

        $this->command?->info("--- Financial Test Scenarios successfully seeded! ---");
        $this->command?->info("You can view and audit these immediately in the Sales Partner Deck & Admin Center.");
    }
}
