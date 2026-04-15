<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB; // Added for DB queries

class Admin extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $guard = 'admin';

    /**
     * Set the guard name for Spatie permissions
     */
    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | Permission Group Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get unique permission groups for the admin guard.
     */
    public static function getpermissionGroups()
    {
        $permission_groups = DB::table('permissions')
            ->select('group_name as name')
            ->where('guard_name', 'admin') // Ensure we only get admin permissions
            ->groupBy('group_name')
            ->get();
        return $permission_groups;
    }

    /**
     * Get all permissions belonging to a specific group.
     */
    public static function getpermissionsByGroupName($group_name)
    {
        $permissions = DB::table('permissions')
            ->select('name', 'id')
            ->where('group_name', $group_name)
            ->where('guard_name', 'admin') // Ensure we only get admin permissions
            ->get();
        return $permissions;
    }

    /**
     * Check if a specific role has a collection of permissions.
     * Used typically in the UI for "Select All" checkboxes.
     */
    /**
     * Check if a role (or an array of permission names) has all the given permissions.
     */
    public static function roleHasPermissions($role, $permissions)
    {
        foreach ($permissions as $permission) {
            // 1. If $role is an array (used in Livewire forms/state)
            if (is_array($role)) {
                if (!in_array($permission->name, $role)) {
                    return false;
                }
            }
            // 2. If $role is an actual Model instance (used in blade views)
            else {
                if (!$role->hasPermissionTo($permission->name, 'admin')) {
                    return false;
                }
            }
        }
        return true;
    }
}
