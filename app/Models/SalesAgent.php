<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesAgent extends Model
{
    protected $fillable = [
        'name',
        'title',
        'state',
        'country',
        'bio',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::deleting(function ($agent) {
            if ($agent->image_path) {
                $pathToRemove = str_replace('/storage/', '', $agent->image_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pathToRemove);
            }
        });
    }
}
