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
        'sort_order',
    ];

    protected $casts = [
        'order_deadline' => 'datetime',
        'pricing_approved' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($store) {
            \DB::table('team_stores')->increment('sort_order');
            $store->sort_order = 1;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StoreItem::class)->orderBy('sort_order', 'asc');
    }

    public function parentOrders()
    {
        return $this->hasMany(ParentOrder::class);
    }

    public function rosters()
    {
        return $this->hasMany(StoreRoster::class);
    }
}
