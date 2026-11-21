<?php

namespace App\Services;

use App\Models\ParentOrder;
use App\Models\TeamStore;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripePaymentSyncService
{
    /**
     * Reconcile unpaid/pending orders for a given store against Stripe Checkout Sessions.
     */
    public function syncStorePendingOrders(TeamStore $store, ?StripeClient $stripeClient = null): array
    {
        $orders = $store->parentOrders()
            ->where('is_archived', false)
            ->where(function ($q) {
                $q->whereNull('payment_status')
                  ->orWhere('payment_status', '!=', 'paid');
            })
            ->where(function ($q) {
                $q->whereNotNull('stripe_session_id')
                  ->orWhereNotNull('stripe_payment_intent_id');
            })
            ->get();

        return $this->syncOrders($orders, $stripeClient);
    }

    /**
     * Reconcile unpaid/pending orders in a specific batch against Stripe.
     */
    public function syncBatchPendingOrders(string $batchId, ?StripeClient $stripeClient = null): array
    {
        $orders = ParentOrder::where('batch_id', $batchId)
            ->where(function ($q) {
                $q->whereNull('payment_status')
                  ->orWhere('payment_status', '!=', 'paid');
            })
            ->where(function ($q) {
                $q->whereNotNull('stripe_session_id')
                  ->orWhereNotNull('stripe_payment_intent_id');
            })
            ->get();

        return $this->syncOrders($orders, $stripeClient);
    }

    /**
     * Reconcile a collection of ParentOrder records against Stripe.
     *
     * @param Collection<int, ParentOrder> $orders
     */
    public function syncOrders(Collection $orders, ?StripeClient $stripeClient = null): array
    {
        $result = [
            'checked'           => 0,
            'marked_paid'       => 0,
            'still_unpaid'      => 0,
            'errors'            => 0,
            'updated_order_ids' => [],
        ];

        if ($orders->isEmpty()) {
            return $result;
        }

        if ($stripeClient === null) {
            $stripeSecret = config('services.stripe.secret');
            if (empty($stripeSecret)) {
                return $result;
            }
            $stripeClient = new StripeClient($stripeSecret);
        }

        foreach ($orders as $order) {
            if ($order->isPaid()) {
                continue;
            }

            $result['checked']++;

            try {
                $isPaidOnStripe = false;
                $amountPaid = null;
                $paymentIntentId = $order->stripe_payment_intent_id;
                $paidAt = null;
                $checkoutEmail = null;

                if (!empty($order->stripe_session_id)) {
                    $session = $stripeClient->checkout->sessions->retrieve($order->stripe_session_id);
                    if (($session->payment_status ?? null) === 'paid') {
                        $isPaidOnStripe = true;
                        $paymentIntentId = $session->payment_intent ?? $paymentIntentId;
                        if (isset($session->amount_total) && $session->amount_total > 0) {
                            $amountPaid = round($session->amount_total / 100, 2);
                        }
                        if (!empty($session->created)) {
                            $paidAt = Carbon::createFromTimestamp($session->created);
                        }
                        $checkoutEmail = $session->customer_details->email ?? null;
                    }
                } elseif (!empty($order->stripe_payment_intent_id)) {
                    $intent = $stripeClient->paymentIntents->retrieve($order->stripe_payment_intent_id);
                    if (($intent->status ?? null) === 'succeeded') {
                        $isPaidOnStripe = true;
                        if (isset($intent->amount_received) && $intent->amount_received > 0) {
                            $amountPaid = round($intent->amount_received / 100, 2);
                        }
                        if (!empty($intent->created)) {
                            $paidAt = Carbon::createFromTimestamp($intent->created);
                        }
                    }
                }

                if ($isPaidOnStripe) {
                    $this->markOrderPaid($order, $amountPaid, $paymentIntentId, $paidAt, $checkoutEmail);
                    $result['marked_paid']++;
                    $result['updated_order_ids'][] = $order->id;
                } else {
                    $result['still_unpaid']++;
                }
            } catch (\Throwable $e) {
                $result['errors']++;
                Log::warning("StripePaymentSyncService failed for order #{$order->id}: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Mark a ParentOrder as paid while preserving already-batched statuses.
     */
    public function markOrderPaid(
        ParentOrder $order,
        ?float $amountPaid = null,
        ?string $paymentIntentId = null,
        ?Carbon $paidAt = null,
        ?string $checkoutEmail = null
    ): void {
        $resolvedAmount = ($amountPaid !== null && $amountPaid > 0)
            ? round($amountPaid, 2)
            : round((float) $order->subtotal + (float) $order->tax_amount + (float) $order->fee_amount, 2);

        $newStatus = $order->status;
        if (in_array($order->status, ['Pending Payment', 'Payment Failed', 'Draft', null, ''], true)) {
            $newStatus = !empty($order->batch_id) ? 'Submitted to Admin' : 'Submitted';
        }

        $paidTimestamp = $paidAt ?? now();

        $updateFields = [
            'payment_status'           => 'paid',
            'status'                   => $newStatus,
            'stripe_payment_intent_id' => $paymentIntentId ?: $order->stripe_payment_intent_id,
            'total_paid'               => $resolvedAmount,
            'submitted_at'             => $order->submitted_at ?? $paidTimestamp,
            'paid_at'                  => $order->paid_at ?? $paidTimestamp,
        ];

        if (empty($order->parent_email) && !empty($checkoutEmail)) {
            $updateFields['parent_email'] = $checkoutEmail;
        }

        $order->update($updateFields);

        if ($order->team_store_id && $order->teamStore) {
            $store = $order->teamStore;
            $parentEmail = trim((string) $order->parent_email);
            $parentPhone = preg_replace('/[^0-9]/', '', (string) $order->parent_phone);

            if ($parentEmail || $parentPhone) {
                $store->rosters()->where(function ($query) use ($parentEmail, $parentPhone) {
                    if ($parentEmail) {
                        $query->where('parent_email', $parentEmail);
                    }
                    if ($parentPhone) {
                        $query->orWhere('parent_phone', $parentPhone);
                    }
                })->update(['has_ordered' => true]);
            }
        }

        Log::info("Order #{$order->id} reconciled and marked as paid via StripePaymentSyncService (amount: {$resolvedAmount}).");
    }
}
