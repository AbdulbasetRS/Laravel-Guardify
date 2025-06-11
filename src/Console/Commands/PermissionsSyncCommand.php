<?php

namespace Abdulbaset\Guardify\Console\Commands;

use Illuminate\Console\Command;
use Abdulbaset\Guardify\Models\Permission;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * PermissionsSyncCommand
 *
 * This command synchronizes permissions by deleting all existing permissions
 * and recreating them from the configuration file.
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
    protected $description = 'Sync permissions by deleting all existing permissions and recreating them from config.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $output = new SymfonyStyle($this->input, $this->output);
        
        try {
            $output->text('🔄 Synchronizing permissions...');
            
            // Get all unique permissions from roles
            $permissions = $this->getUniquePermissionsFromRoles();
            
            if (empty($permissions)) {
                $output->warning('No permissions found in roles configuration. Please check your config/guardify.php file.');
                return Command::FAILURE;
            }
            
            // Delete all existing permissions
            $deletedCount = Permission::query()->delete();
            $output->text("🗑️ Deleted {$deletedCount} existing permissions");
            
            // Create new permissions from config
            $createdCount = 0;
            foreach ($permissions as $slug => $permissionData) {
                Permission::create([
                    'slug' => $slug,
                    'name' => $permissionData['name'],
                    'description' => $permissionData['description'] ?? ucfirst(str_replace(['-', '_'], ' ', $slug)),
                ]);
                $createdCount++;
            }
            
            $output->newLine();
            $output->success("✅ Permissions synchronization completed!");
            $output->text("  - Deleted: {$deletedCount} permissions");
            $output->text("  - Created: {$createdCount} permissions");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->error('Error synchronizing permissions: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get all unique permissions from roles configuration
     *
     * @return array
     */
    protected function getUniquePermissionsFromRoles(): array
    {
        $permissions = [];
        $roles = Config::get('guardify.roles', []);

        foreach ($roles as $role) {
            if (isset($role['permissions']) && is_array($role['permissions'])) {
                foreach ($role['permissions'] as $permission) {
                    // Handle both string and array formats
                    $permissionData = is_string($permission)
                        ? ['name' => $permission]
                        : $permission;

                    // Skip if permission already exists
                    if (isset($permissions[$permissionData['name']])) {
                        continue;
                    }

                    $permissions[$permissionData['name']] = [
                        'name' => $permissionData['name'],
                        'description' => $permissionData['description'] ?? ucfirst(str_replace(['-', '_'], ' ', $permissionData['name'])),
                    ];
                }
            }
        }

        return $permissions;
    }
}
