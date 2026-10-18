<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'status',
        'organization',
        'phone',
        'sport',
        'logo_path',
        'sales_rep',
        'sales_rep_id',
        'partner_category',
        'commission_rate',
        'referral_slug',
        'is_tax_exempt',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_tax_exempt' => 'boolean',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSalesRep(): bool
    {
        return in_array($this->role, ['sales_rep', 'partner', 'agent']);
    }

    public function getEffectiveCommissionRate(): float
    {
        return (float) ($this->commission_rate ?? 50.00);
    }

    public function getReferralUrlAttribute(): string
    {
        $slug = $this->referral_slug ?: $this->id;
        return url('/register?rep=' . $slug);
    }

    public function getNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function isCoach(): bool
    {
        return $this->role === 'coach';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function teamStore()
    {
        return $this->hasOne(TeamStore::class);
    }

    // Designs assigned to this coach by admin
    public function designCatalog()
    {
        return $this->belongsToMany(DesignCatalog::class, 'design_catalog_user', 'user_id', 'design_catalog_id');
    }

    public function parentOrders()
    {
        return $this->hasMany(ParentOrder::class);
    }

    // Admins can have multiple stores
    public function teamStores()
    {
        return $this->hasMany(TeamStore::class);
    }

    // Coach assigned to a Sales Rep
    public function assignedSalesRep()
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    // Sales Rep has many assigned Coaches/Clients
    public function assignedClients()
    {
        return $this->hasMany(User::class, 'sales_rep_id');
    }

    // Sales Rep stores
    public function salesRepTeamStores()
    {
        return $this->hasMany(TeamStore::class, 'sales_rep_id');
    }

    // Sales Rep orders
    public function salesRepOrders()
    {
        return $this->hasMany(ParentOrder::class, 'sales_rep_id');
    }
}
