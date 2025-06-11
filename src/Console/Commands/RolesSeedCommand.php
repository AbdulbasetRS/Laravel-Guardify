<?php

namespace Abdulbaset\Guardify\Console\Commands;

use Illuminate\Console\Command;
use Abdulbaset\Guardify\Models\Role;
use Abdulbaset\Guardify\Models\Permission;

/**
 * RolesSeedCommand
 *
 * This Artisan command seeds the database with default roles and permissions
 * as defined in the roles configuration file. It creates or updates roles
 * and their associated permissions, ensuring the database stays in sync
 * with your configuration.
 *
 * @package Abdulbaset\Guardify\Console\Commands
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-guardify
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed
 * @version 1.0.0
 * @license MIT
 */
class RolesSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guardify:roles:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with default roles and permissions from the configuration file. This is a safe operation that only adds new roles and permissions without removing existing ones.';

    /**
     * Execute the console command.
     * 
     * Reads roles and permissions from the configuration file and creates/updates
     * them in the database. This ensures your database stays in sync with your
     * configuration.
     *
     * @return int Returns 0 on success, 1 on failure
     */
    public function handle()
    {
        $this->info('Seeding default roles and permissions...');
        
        try {
            $roles = config('guardify.roles', []);
            
            if (empty($roles)) {
                $this->warn('No roles found in configuration. Please check your config/guardify.php file.');
                return 1;
            }
            
            foreach ($roles as $roleSlug => $roleData) {
                $this->createRoleWithPermissions($roleSlug, $roleData);
            }
            
            $this->info('✓ Roles and permissions seeded successfully!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error seeding roles and permissions: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Create or update a role with its associated permissions.
     *
     * @param string $roleSlug The slug of the role to create/update
     * @param array $roleData Array containing role information including name and permissions
     * @return void
     */
    protected function createRoleWithPermissions(string $roleSlug, array $roleData)
    {
        // Validate role data
        if (empty($roleData['name'])) {
            $this->warn("Skipping role '{$roleSlug}': Missing role name");
            return;
        }

        // Create or update the role
        $role = Role::updateOrCreate(
            ['slug' => $roleSlug],
            ['name' => $roleData['name']]
        );
        
        $this->line("<info>✓ Role created/updated:</info> {$role->name} ({$role->slug})");
        
        // Process permissions for this role
        $permissionSlugs = $roleData['permissions'] ?? [];
        $permissionIds = [];
        $addedCount = 0;
        
        foreach ($permissionSlugs as $permissionSlug) {
            if (empty($permissionSlug)) {
                continue;
            }
            
            $permission = Permission::firstOrCreate(
                ['slug' => $permissionSlug],
                ['name' => ucfirst(str_replace('-', ' ', $permissionSlug))]
            );
            
            $permissionIds[] = $permission->id;
            $addedCount++;
            $this->line("  ✓ Permission: {$permission->name} ({$permission->slug})");
        }
        
        // Sync all permissions at once for better performance
        $role->permissions()->sync($permissionIds);
        
        if ($addedCount === 0) {
            $this->line("  <comment>No permissions assigned to this role.</comment>");
        }
    }
}
