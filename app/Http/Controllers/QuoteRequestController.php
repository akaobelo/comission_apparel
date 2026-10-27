<?php

namespace App\Http\Controllers;

use App\Mail\NewLeadNotification;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class QuoteRequestController extends Controller
{
    public function show()
    {
        return view('quote', [
            'sports' => $this->sportOptions(),
        ]);
    }

    public function showLandingPage()
    {
        $collections = \App\Models\LandingCollection::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('landing.free-mockup', compact('collections'));
    }

    public function showThankYouPage()
    {
        return view('landing.thank-you');
    }

    public function store(Request $request)
    {
        $sports = $this->sportOptions();

        $validated = $request->validate([
            'first_name'           => ['required', 'string', 'max:255'],
            'last_name'            => ['required', 'string', 'max:255'],
            'position_title'       => ['required', 'string', 'max:255'],
            'email'                => ['required', 'email', 'max:255', 'confirmed'],
            'phone'                => ['required', 'string', 'max:30'],
            'organization_name'    => ['required', 'string', 'max:255'],
            'apparel_category'     => ['required', 'string', Rule::in($sports)],
            'estimated_quantity'   => ['required', 'integer', 'min:1'],
            'package_type'         => ['required', Rule::in(['base_uniforms', 'full_program_bundle', 'merch_only'])],
            'target_delivery_date' => ['nullable', 'date'],
            'design_vision'        => ['nullable', 'string'],
            'sales_rep'            => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $quote = QuoteRequest::create($validated + [
                'status' => 'new',
                'lead_source' => 'quote_form',
            ]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['form' => 'Quote requests are not ready yet. Please try again later.']);
        }

        \App\Services\EmailNotificationService::notifyInquirySubmitted($quote);

        return redirect()
            ->route('quote.success');
    }

    public function storeLandingLead(Request $request)
    {
        $validated = $request->validate([
            'full_name'         => ['required', 'string', 'max:255'],
            'role'              => ['required', 'string', 'max:255'],
            'organization'      => ['required', 'string', 'max:255'],
            'organization_type' => ['nullable', 'string', 'max:100'],
            'email'             => ['required', 'email', 'max:255'],
            'phone'             => ['required', 'string', 'max:50'],
            'sport'             => ['required', 'string', 'max:100'],
            'quantity'          => ['required', 'string', 'max:50'],
            'utm_source'        => ['nullable', 'string', 'max:255'],
            'utm_medium'        => ['nullable', 'string', 'max:255'],
            'utm_campaign'      => ['nullable', 'string', 'max:255'],
            'utm_term'          => ['nullable', 'string', 'max:255'],
            'utm_content'       => ['nullable', 'string', 'max:255'],
        ]);

        $nameParts = explode(' ', trim($validated['full_name']), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = !empty($nameParts[1]) ? $nameParts[1] : $firstName;

        // Parse quantity range into an integer representation for compatibility
        $rawQty = $validated['quantity'];
        preg_match_all('/\d+/', $rawQty, $matches);
        $parsedQty = !empty($matches[0]) ? (int) end($matches[0]) : 25;

        $lead = QuoteRequest::create([
            'first_name'         => $firstName,
            'last_name'          => $lastName,
            'position_title'     => $validated['role'],
            'email'              => $validated['email'],
            'phone'              => $validated['phone'],
            'organization_name'  => $validated['organization'],
            'organization_type'  => $validated['organization_type'] ?? 'Youth Organization',
            'apparel_category'   => $validated['sport'],
            'estimated_quantity' => $parsedQty,
            'raw_quantity'       => $rawQty,
            'package_type'       => 'free_3d_mockup',
            'lead_source'        => 'landing_page_free_mockup',
            'utm_source'         => $validated['utm_source'] ?? null,
            'utm_medium'         => $validated['utm_medium'] ?? null,
            'utm_campaign'       => $validated['utm_campaign'] ?? null,
            'utm_term'           => $validated['utm_term'] ?? null,
            'utm_content'        => $validated['utm_content'] ?? null,
            'status'             => 'new',
        ]);

        \App\Services\EmailNotificationService::notifyInquirySubmitted($lead);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "You're in, Coach. Our design team is on it.",
                'lead_id' => $lead->id,
                'redirect_url' => route('landing.free-mockup.thank-you'),
            ]);
        }

        return redirect()->route('landing.free-mockup.thank-you');
    }

    private function sportOptions(): array
    {
        return config('sports.categories');
    }
}
