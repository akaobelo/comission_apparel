<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreItem extends Model
{
    protected $fillable = [
        'team_store_id',
        'name',
        'type',
        'image_url',
    ];

    public function teamStore()
    {
        return $this->belongsTo(TeamStore::class);
    }
}
