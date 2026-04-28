<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignCatalog extends Model
{
    protected $table = 'design_catalog';

    protected $fillable = [
        'name',
        'sport',
        'type',
        'types',
        'category',
        'image_url',
        'image_paths',
        'has_name_field',
        'has_number_field',
        'notes',
        'wholesale_price',
    ];

    protected $casts = [
        'has_name_field' => 'boolean',
        'has_number_field' => 'boolean',
        'wholesale_price' => 'decimal:2',
        'types' => 'array',
        'image_paths' => 'array',
    ];

    // Coaches this design is assigned to
    public function coaches()
    {
        return $this->belongsToMany(User::class, 'design_catalog_user', 'design_catalog_id', 'user_id');
    }

    // Human-readable type label
    public function getTypeLabelAttribute(): string
    {
        if (!empty($this->types) && is_array($this->types)) {
            $labels = [];
            foreach ($this->types as $t) {
                $labels[] = match($t) {
                    'uniform_top'    => 'Uniform Top',
                    'uniform_bottom' => 'Uniform Bottom',
                    'warmup_top'     => 'Warm-up Top',
                    'warmup_bottom'  => 'Warm-up Bottom',
                    'backpack'       => 'Backpack',
                    'arm_sleeve'     => 'Arm Sleeve',
                    'accessory'      => 'Accessory',
                    default          => ucfirst(str_replace('_', ' ', $t)),
                };
            }
            return implode(', ', $labels);
        }

        // Fallback to legacy single type
        return match($this->type) {
            'uniform_top'    => 'Uniform Top',
            'uniform_bottom' => 'Uniform Bottom',
            'warmup_top'     => 'Warm-up Top',
            'warmup_bottom'  => 'Warm-up Bottom',
            'backpack'       => 'Backpack',
            'arm_sleeve'     => 'Arm Sleeve',
            'accessory'      => 'Accessory',
            default          => ucfirst(str_replace('_', ' ', $this->type ?? '')),
        };
    }

    // Category label for display
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'package_a'  => 'Package A — Base Kit',
            'package_b'  => 'Package B — Standard',
            'package_c'  => 'Package C — Full Program',
            'individual' => 'Individual Item',
            default      => ucfirst($this->category),
        };
    }

    // All types that use the standard size chart
    public static function sizedTypes(): array
    {
        return ['uniform_top', 'uniform_bottom', 'warmup_top', 'warmup_bottom', 'arm_sleeve', 'accessory'];
    }

    // Standard size chart used across all garments
    public static function sizeChart(): array
    {
        return ['YXXS', 'YXS', 'YS', 'YM', 'YL', 'YXL', 'AXS', 'AS', 'AM', 'AL', 'AXL', 'A2XL', 'A3XL'];
    }
}
