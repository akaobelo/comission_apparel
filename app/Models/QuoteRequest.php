<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'position_title',
        'email',
        'phone',
        'organization_name',
        'apparel_category',
        'estimated_quantity',
        'package_type',
        'target_delivery_date',
        'design_vision',
        'sales_rep',
        'status',
        'organization_type',
        'lead_source',
        'raw_quantity',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
    ];

    protected function casts(): array
    {
        return [
            'target_delivery_date' => 'date',
        ];
    }
}
