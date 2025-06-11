<?php

namespace Tests\Feature;

use Abdulbaset\Guardify\Models\Permission;
use Abdulbaset\Guardify\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        
        // Create a test user
        $this->user = new (config('auth.providers.users.model'))([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->user->save();
    }

    /** @test */
    public function it_can_create_a_role()
    {
        $role = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Admin',
            'slug' => 'admin',
        ]);
    }

    /** @test */
    public function it_can_assign_role_to_user()
    {
        $role = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        $this->user->giveRole('admin');

        $this->assertTrue($this->user->hasRole('admin'));
        $this->assertCount(1, $this->user->roles);
    }

    /** @test */
    public function it_can_check_user_permission_through_role()
    {
        // Create role and permission
        $role = Role::create(['name' => 'Editor', 'slug' => 'editor']);
        $permission = Permission::create(['name' => 'Edit Posts', 'slug' => 'edit-posts']);
        
        // Attach permission to role
        $role->permissions()->attach($permission);
        
        // Assign role to user
        $this->user->roles()->attach($role);
        
        // Refresh user to load relationships
        $this->user->refresh();
        
        $this->assertTrue($this->user->hasPermission('edit-posts'));
    }

    /** @test */
    public function it_can_use_roles_seed_command()
    {
        $this->artisan('roles:seed')
             ->assertExitCode(0);

        $this->assertDatabaseHas('roles', ['slug' => 'admin']);
        $this->assertDatabaseHas('permissions', ['slug' => 'create']);
    }

    /** @test */
    public function it_can_use_roles_sync_command()
    {
        $this->artisan('roles:sync')
             ->assertExitCode(0);

        $this->assertDatabaseHas('permissions', ['slug' => 'create']);
    }
}
