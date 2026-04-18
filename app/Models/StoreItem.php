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
        'image_url',
        'wholesale_price',
        'retail_price',
    ];

    protected $casts = [
        'wholesale_price' => 'decimal:2',
        'retail_price'    => 'decimal:2',
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
