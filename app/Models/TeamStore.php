<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamStore extends Model
{
    protected $fillable = [
        'user_id',
        'sales_rep_id',
        'name',
        'sport',
        'description',
        'slug',
        'logo_path',
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
        'deposit_amount',
        'deposit_apply_to',
        'deposit_applied',
        'deposit_applied_at',
        'deposit_applied_batch_id',
        'deposit_notes',
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

    public function getEffectiveLogoPathAttribute(): ?string
    {
        return $this->logo_path ?: $this->user?->logo_path;
    }

    public function getLogoUrlAttribute(): ?string
    {
        $path = $this->effective_logo_path;
        if (!$path) {
            return null;
        }
        return \Illuminate\Support\Str::startsWith($path, 'http')
            ? $path
            : \Illuminate\Support\Facades\Storage::url($path);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        $path = $this->cover_image_path;
        if (!$path) {
            return null;
        }
        return \Illuminate\Support\Str::startsWith($path, 'http')
            ? $path
            : \Illuminate\Support\Facades\Storage::url($path);
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
        'deposit_amount' => 'decimal:2',
        'deposit_applied' => 'boolean',
        'deposit_applied_at' => 'datetime',
    ];

    public function canApplyDepositTo(string $orderType = 'store'): bool
    {
        if ($this->deposit_applied || (float) $this->deposit_amount <= 0) {
            return false;
        }
        $scope = $this->deposit_apply_to ?: 'both';
        if ($scope === 'both') {
            return true;
        }
        return $scope === $orderType;
    }

    public function getAvailableDepositFor(string $orderType = 'store'): float
    {
        if ($this->canApplyDepositTo($orderType)) {
            return round((float) $this->deposit_amount, 2);
        }
        if ($this->user && $this->user->canApplyDepositTo($orderType)) {
            return round((float) $this->user->deposit_amount, 2);
        }
        return 0.00;
    }

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

    public function salesRep()
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
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
