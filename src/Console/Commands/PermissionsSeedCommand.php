<?php

namespace Abdulbaset\Guardify\Console\Commands;

use Illuminate\Console\Command;
use Abdulbaset\Guardify\Models\Permission;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * PermissionsSeedCommand
 *
 * This command seeds the database with permissions from roles configuration.
 * It only adds new permissions and skips existing ones.
 *
 * @package Abdulbaset\Guardify\Console\Commands
 * @author Abdulbaset R. Sayed
 * @link https://github.com/AbdulbasetRS/laravel-guardify
 * @version 1.0.0
 * @license MIT
 */
class PermissionsSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guardify:permissions:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed permissions from roles configuration. Only adds new permissions.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $output = new SymfonyStyle($this->input, $this->output);
        
        try {
            $output->text('🌱 Seeding default permissions...');
            
            // Get all unique permissions from roles
            $permissions = $this->getUniquePermissionsFromRoles();
            
            if (empty($permissions)) {
                $output->warning('No permissions found in roles configuration. Please check your config/guardify.php file.');
                return Command::FAILURE;
            }
            
            $addedCount = 0;
            $existingCount = 0;
            
            foreach ($permissions as $slug => $permissionData) {
                // Check if permission exists
                $permission = Permission::where('slug', $slug)->first();
                
                if ($permission) {
                    $existingCount++;
                    continue;
                }
                
                // Create new permission
                Permission::create([
                    'slug' => $slug,
                    'name' => $permissionData['name'],
                    'description' => $permissionData['description'] ?? ucfirst(str_replace(['-', '_'], ' ', $slug)),
                ]);
                
                $addedCount++;
            }
            
            $output->newLine();
            $output->success("✅ Permissions seeding completed!");
            $output->text("  - Added: {$addedCount} new permissions");
            $output->text("  - Found: {$existingCount} existing permissions");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $output->error('Error seeding permissions: ' . $e->getMessage());
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
