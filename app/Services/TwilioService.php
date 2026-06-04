<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Http\CurlClient;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected $client;
    protected $fromNumber;

    public function __construct()
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $fromNumber = env('TWILIO_FROM');

        if ($sid && $token && $fromNumber) {
            $this->fromNumber = $this->formatPhoneNumber($fromNumber);
            $httpClient = new CurlClient([
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
            $this->client = new Client($sid, $token, null, null, $httpClient);
        }
    }

    /**
     * Send an SMS message
     */
    public function sendSms($to, $message)
    {
        if (!$this->client) {
            Log::warning("Twilio is not configured. Could not send SMS to {$to}");
            return false;
        }

        // Format the phone number to ensure it has + if necessary
        // For PH numbers like 09..., we could prefix +63. For now, we trust the input
        // or we can just send it as is and let Twilio try to parse it.
        $to = $this->formatPhoneNumber($to);

        try {
            $this->client->messages->create(
                $to,
                [
                    'from' => $this->fromNumber,
                    'body' => $message,
                ]
            );
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send SMS to {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format a phone number for Twilio (E.164 format)
     */
    protected function formatPhoneNumber($number)
    {
        $number = preg_replace('/[^0-9+]/', '', $number);
        
        // If it starts with 0 (like PH 0935), assume PH and add +63
        if (substr($number, 0, 1) === '0' && strlen($number) == 11) {
            $number = '+63' . substr($number, 1);
        }
        
        // If it doesn't have a plus, assume US (+1) for 10 digits
        if (substr($number, 0, 1) !== '+' && strlen($number) == 10) {
            $number = '+1' . $number;
        }

        return $number;
    }

    /**
     * Generate the standardized reminder message
     */
    public function generateReminderMessage($store, $daysLeft = null)
    {
        $storeName = $store->name;
        $url = route('store.show', $store->slug);
        
        $timeline = $daysLeft ? "in {$daysLeft} days" : "very soon";

        return "Hi from The Commission Apparel!\n\nThis is a reminder that the team store for {$storeName} closes {$timeline}.\n\nPlease complete your athlete's order here: {$url}\n\nThank you!";
    }
}
