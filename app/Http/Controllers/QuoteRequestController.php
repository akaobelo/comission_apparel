<?php

namespace App\Http\Controllers;

use App\Models\QuoteRequest;
use Illuminate\Http\Request;
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

        if (! Schema::hasTable('quote_requests')) {
            return back()
                ->withInput()
                ->withErrors(['form' => 'Quote requests are not ready yet. Please run the latest migrations first.']);
        }

        QuoteRequest::create($validated + [
            'status' => 'new',
        ]);

        return redirect()
            ->route('quote.success');
    }

    private function sportOptions(): array
    {
        return config('sports.categories');
    }
}
