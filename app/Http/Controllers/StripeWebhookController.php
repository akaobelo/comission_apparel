<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ParentOrder;
use App\Models\TeamStore;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        $event = null;

        if ($webhookSecret && $sigHeader) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } catch (\UnexpectedValueException $e) {
                Log::warning('Stripe webhook payload invalid: ' . $e->getMessage());
                return response()->json(['error' => 'Invalid payload'], 400);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                Log::warning('Stripe webhook signature verification failed: ' . $e->getMessage());
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        } else {
            // Fallback for local testing or when secret is not yet set
            $data = json_decode($payload, true);
            if (!$data || !isset($data['type'])) {
                return response()->json(['error' => 'Invalid JSON payload'], 400);
            }
            $event = json_decode($payload);
        }

        $eventType = is_object($event) ? ($event->type ?? null) : ($event['type'] ?? null);

        Log::info("Stripe webhook received: {$eventType}");

        switch ($eventType) {
            case 'checkout.session.completed':
                $session = is_object($event) ? $event->data->object : (object) $event['data']['object'];
                $this->handleCheckoutSessionCompleted($session);
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = is_object($event) ? $event->data->object : (object) $event['data']['object'];
                $this->handlePaymentFailed($paymentIntent);
                break;

            default:
                Log::info("Unhandled Stripe event type: {$eventType}");
                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleCheckoutSessionCompleted($session)
    {
        $sessionId = $session->id ?? null;
        $orderId = $session->metadata->order_id ?? null;
        $paymentIntentId = $session->payment_intent ?? null;

        $order = null;
        if ($orderId) {
            $order = ParentOrder::find($orderId);
        }
        if (!$order && $sessionId) {
            $order = ParentOrder::where('stripe_session_id', $sessionId)->first();
        }

        if (!$order) {
            Log::warning("Stripe checkout completed but ParentOrder not found (session: {$sessionId}, order_id: {$orderId})");
            return;
        }

        if ($order->isPaid()) {
            Log::info("Order #{$order->id} is already marked as paid.");
            return;
        }

        $order->update([
            'payment_status'          => 'paid',
            'status'                  => 'Submitted',
            'stripe_payment_intent_id'=> $paymentIntentId ?? $order->stripe_payment_intent_id,
            'paid_at'                 => now(),
        ]);

        // Update roster entry if parent phone/email matches
        if ($order->team_store_id && $order->teamStore) {
            $store = $order->teamStore;
            $parentEmail = trim($order->parent_email);
            $parentPhone = preg_replace('/[^0-9]/', '', (string) $order->parent_phone);

            $store->rosters()->where(function($query) use ($parentEmail, $parentPhone) {
                if ($parentEmail) {
                    $query->where('parent_email', $parentEmail);
                }
                if ($parentPhone) {
                    $query->orWhere('parent_phone', $parentPhone);
                }
            })->update(['has_ordered' => true]);

            // Notify coach
            try {
                if ($store->user) {
                    $fullName = trim($order->athlete_first_name . ' ' . $order->athlete_last_name);
                    $store->user->notify(new \App\Notifications\ParentOrderPlaced($fullName, $store->name));
                }
            } catch (\Exception $e) {
                Log::error("Failed to notify coach of paid order #{$order->id}: " . $e->getMessage());
            }
        }

        Log::info("Order #{$order->id} marked as paid successfully via Stripe webhook.");
    }

    protected function handlePaymentFailed($paymentIntent)
    {
        $paymentIntentId = $paymentIntent->id ?? null;
        if (!$paymentIntentId) return;

        $order = ParentOrder::where('stripe_payment_intent_id', $paymentIntentId)->first();
        if ($order && !$order->isPaid()) {
            $order->update([
                'payment_status' => 'failed',
                'status'         => 'Payment Failed',
            ]);
            Log::info("Order #{$order->id} marked as failed via payment_intent.payment_failed.");
        }
    }
}
