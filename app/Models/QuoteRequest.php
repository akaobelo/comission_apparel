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
        'sales_rep_id',
        'status',
        'addressed_by',
        'addressed_at',
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
            'addressed_at'         => 'datetime',
        ];
    }

    public function salesRepUser()
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }
}
