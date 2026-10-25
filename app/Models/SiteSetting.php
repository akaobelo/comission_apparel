<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('site_landing_settings_array');
            \Illuminate\Support\Facades\Cache::forget('site_landing_settings_keyed');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('site_landing_settings_array');
            \Illuminate\Support\Facades\Cache::forget('site_landing_settings_keyed');
        });
    }

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
