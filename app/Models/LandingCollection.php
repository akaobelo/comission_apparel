<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingCollection extends Model
{
    protected $fillable = [
        'tab_name',
        'title',
        'description',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected static function booted()
    {
        static::creating(function ($collection) {
            \DB::table('landing_collections')->increment('sort_order');
            $collection->sort_order = 1;
        });
    }
}
