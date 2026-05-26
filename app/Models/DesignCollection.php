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

    public function designs()
    {
        return $this->hasMany(DesignCatalog::class);
    }
}
