<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRoster extends Model
{
    protected $fillable = [
        'team_store_id',
        'athlete_name',
        'parent_email',
        'parent_phone',
        'has_ordered',
    ];

    protected $casts = [
        'has_ordered' => 'boolean',
    ];

    public function teamStore()
    {
        return $this->belongsTo(TeamStore::class);
    }
}
