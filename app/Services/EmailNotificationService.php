<?php

namespace App\Services;

use App\Mail\CoachRegisteredNotification;
use App\Mail\NewLeadNotification;
use App\Mail\OrderSubmittedNotification;
use App\Mail\StoreRequestedNotification;
use App\Models\ParentOrder;
use App\Models\QuoteRequest;
use App\Models\TeamStore;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    /**
     * Get list of admin notification recipient emails.
     *
     * @return array<string>
     */
    public static function getAdminRecipients(): array
    {
        $recipients = config('mail.admin_recipients', [
            'Luke@speedcapital.com',
        ]);

        if (is_string($recipients)) {
            $recipients = explode(',', $recipients);
        }

        return array_values(array_filter(array_map('trim', (array) $recipients), function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }));
    }

    /**
     * Notify Admins and assigned Sales Rep of a Coach Master Order Batch submission.
     */
    public static function notifyMasterOrderSubmitted(TeamStore $store, string $batchId): void
    {
        try {
            $orders = ParentOrder::where('batch_id', $batchId)->get();
            $coach = $store->user;
            $salesRep = $store->salesRep ?: ($coach?->salesRep);

            $orderCount = $orders->count();
            $totalItems = 0;
            foreach ($orders as $ord) {
                $items = is_array($ord->items_json) ? $ord->items_json : [];
                foreach ($items as $item) {
                    $totalItems += (int) ($item['qty'] ?? ($item['quantity'] ?? 1));
                }
            }
            if ($totalItems === 0) {
                $totalItems = $orderCount;
            }

            $totalRevenue = $orders->sum(function ($order) {
                return (float) ($order->subtotal > 0 ? $order->subtotal : $order->getCalculatedSubtotal());
            });

            $paymentMode = 'Paid Online';
            if ($store->isCheckPayment()) {
                $paymentMode = 'Pay by Check (Direct to Admin)';
            } elseif ($store->isInHousePayment()) {
                $paymentMode = 'In-House Collection';
            }

            $orderData = [
                'type_label'        => 'Finalized Master Order',
                'batch_or_order_id' => substr($batchId, 0, 8),
                'organization'      => $coach?->organization ?: $store->name,
                'store_name'        => $store->name,
                'coach_name'        => $coach?->name ?: 'Coach',
                'coach_email'       => $coach?->email,
                'coach_phone'       => $coach?->phone,
                'order_count'       => $orderCount,
                'total_items'       => $totalItems,
                'total_revenue'     => $totalRevenue,
                'payment_mode'      => $paymentMode,
                'sales_rep_name'    => $salesRep?->name,
                'action_url'        => config('app.url') . '/admin/dashboard',
            ];

            // 1. Send to Super Admins (Luke & Info)
            $admins = self::getAdminRecipients();
            if (!empty($admins)) {
                Mail::to($admins)->send(new OrderSubmittedNotification($orderData, 'admin'));
            }

            // 2. Send to Assigned Sales Rep if any
            if ($salesRep && !empty($salesRep->email) && filter_var($salesRep->email, FILTER_VALIDATE_EMAIL)) {
                $repData = $orderData;
                $repData['action_url'] = route('rep.dashboard');
                Mail::to($salesRep->email)->send(new OrderSubmittedNotification($repData, 'sales_rep'));
            }
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService::notifyMasterOrderSubmitted failed: ' . $e->getMessage(), [
                'batch_id' => $batchId,
                'store_id' => $store->id ?? null,
                'trace'    => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Notify Admins and assigned Sales Rep of a Direct Order Batch submission.
     */
    public static function notifyDirectOrderSubmitted($orders, ?User $coach = null, ?string $batchId = null): void
    {
        try {
            $ordersCollection = $orders instanceof Collection ? $orders : collect($orders);
            if ($ordersCollection->isEmpty()) {
                return;
            }

            $firstOrder = $ordersCollection->first();
            $coach = $coach ?: $firstOrder?->user;
            $salesRep = $firstOrder?->salesRep ?: ($coach?->salesRep);

            $orderCount = $ordersCollection->count();
            $totalItems = 0;
            foreach ($ordersCollection as $ord) {
                $items = is_array($ord->items_json) ? $ord->items_json : [];
                foreach ($items as $item) {
                    $totalItems += (int) ($item['qty'] ?? ($item['quantity'] ?? 1));
                }
            }
            if ($totalItems === 0) {
                $totalItems = $orderCount;
            }
            $totalRevenue = $ordersCollection->sum(function ($order) {
                return (float) ($order->total_paid > 0 ? $order->total_paid : $order->getCalculatedSubtotal());
            });

            $orderData = [
                'type_label'        => 'Finalized Direct Order',
                'batch_or_order_id' => $batchId ? substr($batchId, 0, 8) : ($firstOrder->batch_id ? substr($firstOrder->batch_id, 0, 8) : "#{$firstOrder->id}"),
                'organization'      => $coach?->organization ?: 'Direct Order',
                'store_name'        => $firstOrder?->teamStore?->name,
                'coach_name'        => $coach?->name ?: 'Coach',
                'coach_email'       => $coach?->email,
                'coach_phone'       => $coach?->phone,
                'order_count'       => $orderCount,
                'total_items'       => $totalItems,
                'total_revenue'     => $totalRevenue,
                'payment_mode'      => 'Paid Online (Direct Order)',
                'sales_rep_name'    => $salesRep?->name,
                'action_url'        => config('app.url') . '/admin/dashboard',
            ];

            // 1. Send to Super Admins
            $admins = self::getAdminRecipients();
            if (!empty($admins)) {
                Mail::to($admins)->send(new OrderSubmittedNotification($orderData, 'admin'));
            }

            // 2. Send to Assigned Sales Rep if any
            if ($salesRep && !empty($salesRep->email) && filter_var($salesRep->email, FILTER_VALIDATE_EMAIL)) {
                $repData = $orderData;
                $repData['action_url'] = route('rep.dashboard');
                Mail::to($salesRep->email)->send(new OrderSubmittedNotification($repData, 'sales_rep'));
            }
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService::notifyDirectOrderSubmitted failed: ' . $e->getMessage(), [
                'batch_id' => $batchId,
                'trace'    => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Notify Admins and assigned Sales Rep of a single customer/parent store order.
     * Note: Admin notifications for individual orders are suppressed by default to prevent
     * email inbox clogging during the season. Finalized Masters and Finalized Directs are sent instead.
     */
    public static function notifyParentOrderSubmitted(ParentOrder $order): void
    {
        try {
            $notifyAdmin = (bool) config('mail.notify_admin_on_individual_orders', false);
            $notifyRep = (bool) config('mail.notify_rep_on_individual_orders', false);

            if (!$notifyAdmin && !$notifyRep) {
                return;
            }

            $store = $order->teamStore;
            $coach = $store?->user ?: $order->user;
            $salesRep = $order->salesRep ?: ($store?->salesRep ?: $coach?->salesRep);

            $revenue = (float) ($order->total_paid > 0 ? $order->total_paid : $order->getCalculatedSubtotal());
            $items = is_array($order->items_json) ? $order->items_json : [];
            $itemsCount = 0;
            foreach ($items as $item) {
                $itemsCount += (int) ($item['qty'] ?? ($item['quantity'] ?? 1));
            }
            if ($itemsCount === 0) {
                $itemsCount = 1;
            }

            $paymentMode = $order->isPaid() 
                ? 'Paid Online (Stripe)' 
                : ($store?->isCheckPayment() ? 'Pay by Check' : 'In-House Collection');

            $athleteOrParent = trim(($order->parent_name ?: $order->athlete_name) . ($coach ? " (Coach: {$coach->name})" : ''));

            $orderData = [
                'type_label'        => 'Store Customer Order',
                'batch_or_order_id' => (string) $order->id,
                'organization'      => $coach?->organization ?: ($store?->name ?? 'Team Store'),
                'store_name'        => $store?->name,
                'coach_name'        => $athleteOrParent ?: 'Customer',
                'coach_email'       => $order->parent_email ?: $coach?->email,
                'coach_phone'       => $order->parent_phone ?: $coach?->phone,
                'order_count'       => 1,
                'total_items'       => $itemsCount,
                'total_revenue'     => $revenue,
                'payment_mode'      => $paymentMode,
                'sales_rep_name'    => $salesRep?->name,
                'action_url'        => config('app.url') . '/admin/dashboard',
            ];

            // 1. Send to Super Admins (only if explicitly enabled)
            if ($notifyAdmin) {
                $admins = self::getAdminRecipients();
                if (!empty($admins)) {
                    Mail::to($admins)->send(new OrderSubmittedNotification($orderData, 'admin'));
                }
            }

            // 2. Send to Assigned Sales Rep if any (only if explicitly enabled)
            if ($notifyRep && $salesRep && !empty($salesRep->email) && filter_var($salesRep->email, FILTER_VALIDATE_EMAIL)) {
                $repData = $orderData;
                $repData['action_url'] = route('rep.dashboard');
                Mail::to($salesRep->email)->send(new OrderSubmittedNotification($repData, 'sales_rep'));
            }
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService::notifyParentOrderSubmitted failed: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'trace'    => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Notify Admins of a new Quote or Free Mockup Inquiry.
     */
    public static function notifyInquirySubmitted(QuoteRequest $quote): void
    {
        try {
            $admins = self::getAdminRecipients();
            $recipients = $admins;

            // Always ensure Info@TheCommissionApparel.com is included
            $fallback = 'Info@TheCommissionApparel.com';
            $hasFallback = false;
            foreach ($recipients as $email) {
                if (strcasecmp($email, $fallback) === 0) {
                    $hasFallback = true;
                    break;
                }
            }
            if (!$hasFallback) {
                $recipients[] = $fallback;
            }

            if (!empty($recipients)) {
                Mail::to($recipients)->send(new NewLeadNotification($quote));
            }
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService::notifyInquirySubmitted failed: ' . $e->getMessage(), [
                'quote_id' => $quote->id ?? null,
                'trace'    => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Notify Admins and assigned Sales Rep of a new Coach registration.
     */
    public static function notifyCoachRegistered(User $coach): void
    {
        try {
            $salesRep = $coach->salesRep ?: ($coach->sales_rep_id ? User::find($coach->sales_rep_id) : null);

            // 1. Send to Super Admins
            $admins = self::getAdminRecipients();
            if (!empty($admins)) {
                Mail::to($admins)->send(new CoachRegisteredNotification($coach, 'admin'));
            }

            // 2. Send to Assigned Sales Rep if registered under their link or assigned to them
            if ($salesRep && !empty($salesRep->email) && filter_var($salesRep->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($salesRep->email)->send(new CoachRegisteredNotification($coach, 'sales_rep'));
            }
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService::notifyCoachRegistered failed: ' . $e->getMessage(), [
                'coach_id' => $coach->id ?? null,
                'trace'    => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Notify Admins and assigned Sales Rep of a new Team Store creation request.
     */
    public static function notifyStoreRequested(TeamStore $store): void
    {
        try {
            $coach = $store->user;
            $salesRep = $store->salesRep ?: ($coach?->salesRep);

            // 1. Send to Super Admins
            $admins = self::getAdminRecipients();
            if (!empty($admins)) {
                Mail::to($admins)->send(new StoreRequestedNotification($store, 'admin'));
            }

            // 2. Send to Assigned Sales Rep if assigned
            if ($salesRep && !empty($salesRep->email) && filter_var($salesRep->email, FILTER_VALIDATE_EMAIL)) {
                Mail::to($salesRep->email)->send(new StoreRequestedNotification($store, 'sales_rep'));
            }
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService::notifyStoreRequested failed: ' . $e->getMessage(), [
                'store_id' => $store->id ?? null,
                'trace'    => $e->getTraceAsString(),
            ]);
        }
    }
}
