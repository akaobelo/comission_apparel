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
        'component_design_ids',
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
        'component_design_ids' => 'array',
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
        $nameLower = strtolower((string) $this->name);
        if (str_contains($nameLower, 'package') || str_contains($nameLower, 'pkg') || str_contains($nameLower, 'bundle')) {
            return true;
        }
        if (is_array($this->types)) {
            if (count($this->types) > 2) {
                return true;
            }
            foreach ($this->types as $t) {
                if (str_contains($t, 'package')) return true;
            }
        }
        return false;
    }

    /**
     * Detect whether an item name clearly restricts it to a single gender cut.
     * Returns 'Mens / Boys', 'Womens / Girls', or null (Unisex / both allowed).
     */
    public static function detectGenderRestrictionFromName(?string $name): ?string
    {
        $clean = trim((string) $name);
        if ($clean === '') {
            return null;
        }

        $isFemale = (bool) preg_match('/\b(female|females|women|womens|women\'s|woman|girl|girls|girl\'s|ladies|lady|racerback)\b/i', $clean);
        $isMale = (bool) preg_match('/\b(male|males|men|mens|men\'s|man|boy|boys|boy\'s)\b/i', $clean);

        if ($isFemale && !$isMale) {
            return 'Womens / Girls';
        }
        if ($isMale && !$isFemale) {
            return 'Mens / Boys';
        }

        return null;
    }

    /**
     * Return 'Mens / Boys', 'Womens / Girls', or null if this store item allows both cuts.
     */
    public function detectedGenderRestriction(): ?string
    {
        return self::detectGenderRestrictionFromName($this->name);
    }

    /**
     * Normalize any gender string into 'Female', 'Male', or 'Unisex'.
     */
    public static function normalizeGenderBucket(?string $gender): string
    {
        $g = strtolower(trim((string) $gender));
        if (str_contains($g, 'fem') || str_contains($g, 'wom') || str_contains($g, 'girl') || str_contains($g, 'lady') || str_contains($g, 'ladies')) {
            return 'Female';
        }
        if (str_contains($g, 'male') || str_contains($g, 'men') || str_contains($g, 'boy')) {
            return 'Male';
        }
        return 'Unisex';
    }

    public function getWholesalePriceAttribute($value)
    {
        if (($value === null || (float) $value <= 0) && $this->design_catalog_id) {
            return $this->designCatalog?->wholesale_price ?? $value;
        }
        return $value;
    }

    public function getRetailPriceAttribute($value)
    {
        if ($value === null || (float) $value <= 0) {
            $wholesale = $this->wholesale_price;
            if ($wholesale !== null && (float) $wholesale > 0) {
                return $wholesale;
            }
            return $this->designCatalog?->wholesale_price ?? $value;
        }
        return $value;
    }
}
