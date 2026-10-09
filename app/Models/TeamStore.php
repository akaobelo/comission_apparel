<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamStore extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'sport',
        'description',
        'slug',
        'cover_image_path',
        'order_deadline',
        'status',
        'package_type',
        'payment_mode',
        'pricing_approved',
        'is_tax_exempt',
        'is_archived',
        'shipping_address',
        'sort_order',
    ];

    public function isOnlinePayment(): bool
    {
        return ($this->payment_mode ?? 'in_house') === 'online';
    }

    public function isCheckPayment(): bool
    {
        return ($this->payment_mode ?? 'in_house') === 'check';
    }

    public function isInHousePayment(): bool
    {
        return ($this->payment_mode ?? 'in_house') === 'in_house';
    }

    public function isTaxExempt(): bool
    {
        return (bool) ($this->is_tax_exempt || $this->user?->is_tax_exempt);
    }

    public function isClosed(): bool
    {
        if ($this->status === 'submitted_to_admin') return true;
        if ($this->status !== 'approved') return true;
        if (!$this->pricing_approved) return true;
        if ($this->order_deadline && $this->order_deadline->isPast()) return true;
        return false;
    }

    protected $casts = [
        'order_deadline' => 'datetime',
        'pricing_approved' => 'boolean',
        'is_tax_exempt' => 'boolean',
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

    public function orders()
    {
        return $this->parentOrders();
    }

    public function rosters()
    {
        return $this->hasMany(StoreRoster::class);
    }
}
