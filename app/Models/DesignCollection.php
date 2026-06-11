<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesignCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_path',
        'sort_order',
        'sports',
    ];

    protected $casts = [
        'sports' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($collection) {
            \DB::table('design_collections')->increment('sort_order');
            $collection->sort_order = 1;
        });
    }

    public function designs()
    {
        return $this->hasMany(DesignCatalog::class);
    }
}
