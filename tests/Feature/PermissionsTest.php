<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $guest;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
        ]);
        
        $this->user = User::factory()->create([
            'email' => 'user@test.com',
            'is_admin' => false,
        ]);
    }

    /** @test */
    public function test_admin_can_access_all_pages()
    {
        $this->actingAs($this->admin);

        $routes = [
            'regions.index',
            'channels.index', 
            'suppliers.index',
            'categories.index',
            'salesmen.index',
            'users.index',
            'dashboard'
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function test_regular_user_cannot_access_users_management()
    {
        $this->actingAs($this->user);

        // Should be forbidden
        $response = $this->get(route('users.index'));
        $response->assertStatus(403);

        // Should be able to access other pages
        $routes = [
            'regions.index',
            'channels.index',
            'suppliers.index', 
            'categories.index',
            'salesmen.index',
            'dashboard'
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function test_guest_cannot_access_protected_pages()
    {
        $routes = [
            'regions.index',
            'channels.index',
            'suppliers.index',
            'categories.index', 
            'salesmen.index',
            'users.index',
            'dashboard'
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertRedirect(route('login'));
        }
    }

    /** @test */
    public function test_admin_can_perform_crud_operations()
    {
        $this->actingAs($this->admin);

        // Test create forms access
        $createRoutes = [
            'regions.create',
            'channels.create',
            'suppliers.create',
            'categories.create',
            'salesmen.create'
        ];

        foreach ($createRoutes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function test_regular_user_can_perform_crud_operations()
    {
        $this->actingAs($this->user);

        // Test create forms access
        $createRoutes = [
            'regions.create',
            'channels.create', 
            'suppliers.create',
            'categories.create',
            'salesmen.create'
        ];

        foreach ($createRoutes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function test_admin_can_access_import_export()
    {
        $this->actingAs($this->admin);

        $exportRoutes = [
            'regions.export',
            'channels.export',
            'suppliers.export',
            'categories.export',
            'salesmen.export'
        ];

        foreach ($exportRoutes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }

        $templateRoutes = [
            'regions.template',
            'channels.template',
            'suppliers.template',
            'categories.template',
            'salesmen.template'
        ];

        foreach ($templateRoutes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function test_regular_user_can_access_import_export()
    {
        $this->actingAs($this->user);

        $exportRoutes = [
            'regions.export',
            'channels.export',
            'suppliers.export',
            'categories.export',
            'salesmen.export'
        ];

        foreach ($exportRoutes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function test_user_management_restrictions()
    {
        $this->actingAs($this->user);

        // Regular user should not be able to create users
        $response = $this->get(route('users.create'));
        $response->assertStatus(403);

        // Regular user should not be able to access user management
        $response = $this->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_admin' => false
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function test_authentication_middleware()
    {
        // Test that all protected routes require authentication
        $protectedRoutes = [
            ['GET', 'regions'],
            ['GET', 'channels'],
            ['GET', 'suppliers'],
            ['GET', 'categories'],
            ['GET', 'salesmen'],
            ['GET', 'users'],
            ['GET', 'dashboard'],
            ['GET', 'regions/create'],
            ['POST', 'regions'],
            ['GET', 'regions-export'],
            ['POST', 'regions-import']
        ];

        foreach ($protectedRoutes as [$method, $uri]) {
            $response = $this->call($method, $uri);
            $this->assertContains($response->getStatusCode(), [302, 401, 403]);
        }
    }
}
