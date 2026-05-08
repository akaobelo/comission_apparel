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

    public function components()
    {
        return $this->belongsToMany(StoreItem::class, 'package_store_items', 'package_id', 'component_id');
    }

    public function isPackage()
    {
        if ($this->type === 'package') return true;
        if (is_array($this->types)) {
            foreach ($this->types as $t) {
                if (str_contains($t, 'package')) return true;
            }
        }
        return false;
    }
}
