<?php

namespace Abdulbaset\Guardify\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Role Model
 *
 * Represents a role in the application's role-based access control system.
 * Roles are used to group permissions and can be assigned to users.
 * Each user can have only one role, but roles can have multiple permissions.
 *
 * @package Abdulbaset\Guardify\Models
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-guardify
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed
 * @version 1.0.0
 * @license MIT
 */
class Role extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Create a new model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('roles.tables.roles');
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',        // The display name of the role (e.g., "Administrator")
        'slug',        // URL-friendly version of the name (e.g., "admin")
        'description', // Description of the role's purpose and permissions
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'pivot'
    ];

    /**
     * The users that belong to the role.
     * Defines a many-to-many relationship with the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            config('roles.user_model'),
            config('roles.tables.role_user'),
            'role_id',
            'user_id'
        );
    }

    /**
     * The permissions that belong to the role.
     * Defines a many-to-many relationship with the Permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            config('roles.tables.permission_role'),
            'role_id',
            'permission_id'
        );
    }

    /**
     * Get all permissions associated with the role.
     * Returns a collection of Permission models.
     *
     * @return \Illuminate\Support\Collection Collection of Permission models
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Check if the role has a specific permission.
     *
     * @param string $permissionSlug The slug of the permission to check
     * @return bool Returns true if the role has the permission, false otherwise
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    /**
     * Assign a permission to the role.
     * If the permission doesn't exist, it will be created.
     *
     * @param string $permissionSlug The slug of the permission to assign
     * @return void
     */
    public function givePermission(string $permissionSlug): void
    {
        $permission = Permission::firstOrCreate(
            ['slug' => $permissionSlug],
            ['name' => ucfirst(str_replace('-', ' ', $permissionSlug))]
        );

        if (!$this->hasPermission($permissionSlug)) {
            $this->permissions()->attach($permission);
        }
    }

    /**
     * Sync the role's permissions with the given array of permission slugs.
     * Any permissions not in the array will be detached from the role.
     *
     * @param array $permissionSlugs Array of permission slugs to sync
     * @return void
     */
    public function syncPermissions(array $permissionSlugs): void
    {
        $permissionIds = [];
        
        foreach ($permissionSlugs as $slug) {
            $permission = Permission::firstOrCreate(
                ['slug' => $slug],
                ['name' => ucfirst(str_replace('-', ' ', $slug))]
            );
            $permissionIds[] = $permission->id;
        }
        
        $this->permissions()->sync($permissionIds);
    }

    /**
     * Remove a specific permission from the role.
     *
     * @param string $permissionSlug The slug of the permission to remove
     * @return bool Returns true if the permission was removed, false if it didn't exist
     */
    public function removePermission(string $permissionSlug): bool
    {
        $permission = Permission::where('slug', $permissionSlug)->first();
        
        if ($permission && $this->hasPermission($permissionSlug)) {
            $this->permissions()->detach($permission->id);
            return true;
        }
        
        return false;
    }

    /**
     * Remove multiple permissions from the role.
     *
     * @param array $permissionSlugs Array of permission slugs to remove
     * @return int Number of permissions that were actually removed
     */
    public function removePermissions(array $permissionSlugs): int
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
        $count = 0;
        
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission->slug)) {
                $this->permissions()->detach($permission->id);
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Remove all permissions from the role.
     *
     * @return int Number of permissions that were removed
     */
    public function removeAllPermissions(): int
    {
        $count = $this->permissions()->count();
        $this->permissions()->detach();
        return $count;
    }
}
