<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignCatalog extends Model
{
    protected $table = 'design_catalog';

    protected $fillable = [
        'name',
        'description',
        'design_collection_id',
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
        'sort_order',
    ];

    protected $casts = [
        'has_name_field' => 'boolean',
        'has_number_field' => 'boolean',
        'wholesale_price' => 'decimal:2',
        'types' => 'array',
        'image_paths' => 'array',
    ];

    public function designCollection()
    {
        return $this->belongsTo(DesignCollection::class);
    }

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
                    'accessory'      => 'Accessories',
                    'arm_sleeve'     => 'Arm Sleeves',
                    'backpack'       => 'Backpacks',
                    'headwear'       => 'Headwear',
                    'hoodie'         => 'Hoodies & Pullovers',
                    'jacket'         => 'Jackets',
                    'leggings'       => 'Leggings/Tights',
                    'pants'          => 'Pants',
                    'polo'           => 'Polos',
                    'shirt_short'    => 'Shirts (short sleeve)',
                    'shirt_long'     => 'Shirts (long sleeve)',
                    'shorts'         => 'Shorts',
                    'socks'          => 'Socks',
                    'uniform_top'    => 'Uniform (top)',
                    'uniform_bottom' => 'Uniform (bottom)',
                    'uniform_set'    => 'Uniform Set (top/bottom)',
                    'warmup_top'     => 'Warm-up (top)',
                    'warmup_bottom'  => 'Warm-up (bottom)',
                    'warmup_set'     => 'Warm-up (top/bottom)',
                    default          => ucfirst(str_replace('_', ' ', $t)),
                };
            }
            return implode(', ', $labels);
        }

        // Fallback to legacy single type
        return match($this->type) {
            'accessory'      => 'Accessories',
            'arm_sleeve'     => 'Arm Sleeves',
            'backpack'       => 'Backpacks',
            'headwear'       => 'Headwear',
            'hoodie'         => 'Hoodies & Pullovers',
            'jacket'         => 'Jackets',
            'leggings'       => 'Leggings/Tights',
            'pants'          => 'Pants',
            'polo'           => 'Polos',
            'shirt_short'    => 'Shirts (short sleeve)',
            'shirt_long'     => 'Shirts (long sleeve)',
            'shorts'         => 'Shorts',
            'socks'          => 'Socks',
            'uniform_top'    => 'Uniform (top)',
            'uniform_bottom' => 'Uniform (bottom)',
            'uniform_set'    => 'Uniform Set (top/bottom)',
            'warmup_top'     => 'Warm-up (top)',
            'warmup_bottom'  => 'Warm-up (bottom)',
            'warmup_set'     => 'Warm-up (top/bottom)',
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
        return [
            'accessory', 'arm_sleeve', 'headwear', 'hoodie', 'jacket', 'leggings', 'pants', 
            'polo', 'shirt_short', 'shirt_long', 'shorts', 'socks', 'uniform_top', 'uniform_bottom', 
            'uniform_set', 'warmup_top', 'warmup_bottom', 'warmup_set'
        ];
    }

    // Standard size chart used across all garments
    public static function sizeChart(): array
    {
        return ['YXXS', 'YXS', 'YS', 'YM', 'YL', 'YXL', 'AXS', 'AS', 'AM', 'AL', 'AXL', 'A2XL', 'A3XL'];
    }
}
