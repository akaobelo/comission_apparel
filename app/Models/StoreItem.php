<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreItem extends Model
{
    protected $fillable = [
        'team_store_id',
        'design_catalog_id',
        'name',
        'type',
        'types',
        'image_url',
        'image_paths',
        'wholesale_price',
        'retail_price',
        'sort_order',
    ];

    protected $casts = [
        'wholesale_price' => 'decimal:2',
        'retail_price'    => 'decimal:2',
        'types'           => 'array',
        'image_paths'     => 'array',
    ];

    public function teamStore()
    {
        return $this->belongsTo(TeamStore::class);
    }

    public function designCatalog()
    {
        return $this->belongsTo(\App\Models\DesignCatalog::class, 'design_catalog_id');
    }

}
