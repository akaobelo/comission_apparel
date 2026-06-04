<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SizingChart extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'image_paths',
        'sort_order',
        'is_active',
    ];
    
    protected $casts = [
        'image_paths' => 'array',
    ];
}
