<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentOrder extends Model
{
    protected $fillable = [
        'team_store_id',
        'athlete_first_name',
        'athlete_last_name',
        'gender',
        'jersey_name',
        'jersey_number',
        'backpack_name',
        'items_json',
        'special_notes',
        'status',
        'is_edited',
        'edited_by',
        'total_retail_price',
        'user_id',
        'batch_id',
        'is_archived',
    ];

    public function getAthleteNameAttribute()
    {
        return $this->athlete_first_name . ' ' . $this->athlete_last_name;
    }

    protected $casts = [
        'items_json' => 'array',
        'is_edited'  => 'boolean',
        'total_retail_price' => 'decimal:2',
        'is_archived' => 'boolean',
    ];

    public function teamStore()
    {
        return $this->belongsTo(TeamStore::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
