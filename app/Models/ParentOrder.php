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
    ];

    protected $casts = [
        'items_json' => 'array',
    ];

    public function teamStore()
    {
        return $this->belongsTo(TeamStore::class);
    }
}
