<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentOrder extends Model
{
    protected $fillable = [
        'team_store_id',
        'athlete_name',
        'gender',
        'items_json',
        'special_notes',
        'status',
        'is_edited',
        'edited_by',
        'total_retail_price',
    ];

    protected $casts = [
        'items_json' => 'array',
        'is_edited'  => 'boolean',
        'total_retail_price' => 'decimal:2',
    ];

    public function teamStore()
    {
        return $this->belongsTo(TeamStore::class);
    }
}
