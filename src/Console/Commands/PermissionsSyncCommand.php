<?php

namespace Abdulbaset\Guardify\Console\Commands;

use Illuminate\Console\Command;
use Abdulbaset\Guardify\Models\Permission;

/**
 * PermissionsSyncCommand
 *
 * This Artisan command synchronizes permissions between your configuration file
 * and the database. It ensures that all permissions defined in your roles configuration
 * exist in the database, and removes any permissions that are no longer in use.
 *
 * @package Abdulbaset\Guardify\Console\Commands
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-guardify
 * @link https://www.linkedin.com/in/abdulbaset-r-sayed
 * @version 1.0.0
 * @license MIT
 */
class PermissionsSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guardify:permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize permissions with the configuration file. WARNING: This will remove any permissions that are not defined in the configuration file. Use with caution!';

    /**
     * Execute the console command.
     * 
     * This method performs the following actions:
     * 1. Collects all permissions from the roles configuration
     * 2. Creates any new permissions that don't exist in the database
     * 3. Updates existing permissions if their names have changed
     * 4. Removes permissions that are no longer in the configuration
     *
     * @return int Returns 0 on success, 1 on failure
     */
    public function handle()
    {
        $this->info('🔄 Syncing permissions with database...');
        
        try {
            $allPermissions = [];
            $roles = config('guardify.roles', []);
            
            if (empty($roles)) {
                $this->warn('No roles found in configuration. Please check your config/guardify.php file.');
                return 1;
            }
            
            // Collect all unique permissions from all roles
            $this->line('🔍 Collecting permissions from roles configuration...');
            $permissions = $this->getPermissionsFromConfig();
            
            if (empty($permissions)) {
                $this->warn('No valid permissions found in any role.');
                return 1;
            }
            
            // Sync permissions
            $this->line('🔄 Syncing permissions with database...');
            $syncedCount = 0;
            
            foreach ($allPermissions as $slug => $name) {
                $permission = Permission::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $name]
                );
                
                $this->line("  ✓ Permission: {$name} (<comment>{$slug}</comment>)");
                $syncedCount++;
            }
            
            // Remove permissions that no longer exist in config
            $permissionSlugs = array_keys($allPermissions);
            $deletedCount = Permission::whereNotIn('slug', $permissionSlugs)->delete();
            
            $this->newLine();
            $this->info("✅ Successfully synced {$syncedCount} permissions.");
            
            if ($deletedCount > 0) {
                $this->warn("  - Removed {$deletedCount} permissions that no longer exist in configuration.");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error syncing permissions: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
