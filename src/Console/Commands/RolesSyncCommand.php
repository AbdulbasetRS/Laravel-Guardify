<?php

namespace Abdulbaset\Guardify\Console\Commands;

use Illuminate\Console\Command;
use Abdulbaset\Guardify\Models\Role;

/**
 * RolesSyncCommand
 *
 * This Artisan command synchronizes roles between your configuration file
 * and the database. It ensures that all roles defined in your roles configuration
 * exist in the database, and removes any roles that are no longer in use.
 *
 * @package Abdulbaset\Guardify\Console\Commands
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-guardify
 * @version 1.0.0
 * @license MIT
 */
class RolesSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guardify:roles:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize roles with the configuration file. WARNING: This will remove any roles that are not defined in the configuration file. Use with caution!';

    /**
     * Execute the console command.
     * 
     * @return int
     */
    public function handle()
    {
        $this->info('🔄 Syncing roles with database...');
        
        try {
            $roles = config('guardify.roles', []);
            
            if (empty($roles)) {
                $this->warn('No roles found in configuration. Please check your config/guardify.php file.');
                return 1;
            }
            
            $roleSlugs = array_keys($roles);
            $syncedCount = 0;
            
            // Create or update roles
            foreach ($roles as $slug => $roleData) {
                Role::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $roleData['name']]
                );
                $this->line("  ✓ Role: {$roleData['name']} (<comment>{$slug}</comment>)");
                $syncedCount++;
            }
            
            // Remove roles not in config
            $deletedCount = Role::whereNotIn('slug', $roleSlugs)->delete();
            
            $this->newLine();
            $this->info("✅ Successfully synced {$syncedCount} roles.");
            if ($deletedCount > 0) {
                $this->info("🗑️  Removed {$deletedCount} roles that are no longer in config.");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error syncing roles: ' . $e->getMessage());
            return 1;
        }
    }
}
