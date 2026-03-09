<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\SettingType;
use App\Traits\BelongsToTenant;
use Illuminate\Support\Str;

class Setting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', // Added
        'key',
        'label',
        'value',
        'type',
        'description',
        'group',
        'is_private',
    ];

    protected $casts = [
        'type' => SettingType::class,
        'is_private' => 'boolean',
    ];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // 1. Global Scope: Filter settings by the active session tenant.
        static::addGlobalScope('tenant', function (Builder $builder) {
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId) {
                $builder->where($builder->qualifyColumn('tenant_id'), $activeTenantId);
            }
        });

        static::creating(function ($setting) {
            // 2. Automatically assign the active tenant ID from session.
            $activeTenantId = session('active_tenant_id');
            if ($activeTenantId && empty($setting->tenant_id)) {
                $setting->tenant_id = $activeTenantId;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    public function getValueAttribute($value)
    {
        if ($this->type instanceof SettingType) {
            return match ($this->type) {
                SettingType::Boolean => (bool) $value,
                SettingType::Integer => (int) $value,
                SettingType::Json => json_decode($value, true),
                default => $value,
            };
        }
        return $value;
    }

    public function setValueAttribute($value)
    {
        if ($this->type instanceof SettingType) {
            $this->attributes['value'] = match ($this->type) {
                SettingType::Boolean => (string) (bool) $value,
                SettingType::Integer => (string) (int) $value,
                SettingType::Json => json_encode($value),
                default => (string) $value,
            };
        } else {
            $this->attributes['value'] = (string) $value;
        }
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->label ?? Str::title(str_replace('_', ' ', $this->key));
    }

    /*
    |--------------------------------------------------------------------------
    | Static Helper Methods (Tenant-Aware)
    |--------------------------------------------------------------------------
    */

    /**
     * Get a setting by its key for the current tenant.
     */
    public static function get(string $key, $default = null)
    {
        // Global scope automatically handles tenant_id via session
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set/Update a setting for the current tenant.
     */
    public static function set(string $key, $value, $type = 'string', string $label = null, string $group = null)
    {
        // firstOrNew will respect the Global Scope (checking only the active tenant's settings)
        $setting = static::firstOrNew(['key' => $key]);

        $setting->type = $type;
        $setting->value = $value;
        if ($label) $setting->label = $label;
        if ($group) $setting->group = $group;

        $setting->save();

        return $setting;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }
}
