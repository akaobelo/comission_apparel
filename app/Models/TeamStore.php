<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamStore extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'slug',
        'cover_image_path',
        'order_deadline',
        'status',
        'package_type',
        'pricing_approved',
        'is_archived',
        'shipping_address',
    ];

    protected $casts = [
        'order_deadline' => 'datetime',
        'pricing_approved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StoreItem::class);
    }

    public function parentOrders()
    {
        return $this->hasMany(ParentOrder::class);
    }
}
