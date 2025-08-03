<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Region;
use App\Models\Channel;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Salesman;

class MasterDataTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->admin = User::factory()->create([
            'username' => 'admin_test',
            'role' => 'admin',
        ]);
        
        $this->user = User::factory()->create([
            'username' => 'user_test',
            'role' => 'manager',
        ]);
    }

    /** @test */
    public function test_regions_crud_operations()
    {
        $this->withoutMiddleware();
        $this->actingAs($this->admin);

        // Test Create
        $regionData = [
            'region_code' => 'R001',
            'name' => 'North Region',
            'is_active' => true
        ];

        $response = $this->post(route('regions.store'), $regionData);
        $response->assertRedirect(route('regions.index'));
        $this->assertDatabaseHas('regions', $regionData);

        $region = Region::where('region_code', 'R001')->first();

        // Test Read
        $response = $this->get(route('regions.show', $region));
        $response->assertStatus(200);
        $response->assertSee('North Region');

        // Test Index with filtering
        $response = $this->get(route('regions.index'));
        $response->assertStatus(200);
        $response->assertSee('North Region');

        // Test Update
        $updateData = [
            'name' => 'Updated North Region',
            'is_active' => 1  // Database stores boolean as integer
        ];

        $response = $this->put(route('regions.update', $region), $updateData);
        $response->assertRedirect(route('regions.index'));
        $this->assertDatabaseHas('regions', array_merge(['region_code' => 'R001'], $updateData));

        // Test Delete
        $response = $this->delete(route('regions.destroy', $region));
        $response->assertRedirect(route('regions.index'));
        $this->assertDatabaseMissing('regions', ['id' => $region->id]);
    }

    /** @test */
    public function test_channels_crud_operations()
    {
        $this->actingAs($this->admin);

        // Test Create
        $channelData = [
            'channel_code' => 'CH001',
            'name' => 'Online Channel',
            'status' => 'active'
        ];

        $response = $this->post(route('channels.store'), $channelData);
        $response->assertRedirect(route('channels.index'));
        $this->assertDatabaseHas('channels', $channelData);

        $channel = Channel::where('channel_code', 'CH001')->first();

        // Test Read
        $response = $this->get(route('channels.show', $channel));
        $response->assertStatus(200);
        $response->assertSee('Online Channel');

        // Test Update
        $updateData = [
            'channel_code' => 'CH001',
            'name' => 'Updated Online Channel',
            'status' => 'active'
        ];

        $response = $this->put(route('channels.update', $channel), $updateData);
        $response->assertRedirect(route('channels.index'));
        $this->assertDatabaseHas('channels', $updateData);

        // Test Delete
        $response = $this->delete(route('channels.destroy', $channel));
        $response->assertRedirect(route('channels.index'));
        $this->assertDatabaseMissing('channels', ['id' => $channel->id]);
    }

    /** @test */
    public function test_suppliers_crud_operations()
    {
        $this->actingAs($this->admin);

        // Test Create
        $supplierData = [
            'supplier_code' => 'SUP001',
            'name' => 'Test Supplier',
            'status' => 'active'
        ];

        $response = $this->post(route('suppliers.store'), $supplierData);
        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', $supplierData);

        $supplier = Supplier::where('supplier_code', 'SUP001')->first();

        // Test Read
        $response = $this->get(route('suppliers.show', $supplier));
        $response->assertStatus(200);
        $response->assertSee('Test Supplier');

        // Test Update
        $updateData = [
            'supplier_code' => 'SUP001',
            'name' => 'Updated Test Supplier',
            'status' => 'active'
        ];

        $response = $this->put(route('suppliers.update', $supplier), $updateData);
        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', $updateData);

        // Test Delete
        $response = $this->delete(route('suppliers.destroy', $supplier));
        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    /** @test */
    public function test_categories_crud_operations()
    {
        $this->actingAs($this->admin);

        // Test Create
        $categoryData = [
            'category_code' => 'CAT001',
            'name' => 'Test Category',
            'status' => 'active'
        ];

        $response = $this->post(route('categories.store'), $categoryData);
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', $categoryData);

        $category = Category::where('category_code', 'CAT001')->first();

        // Test Read
        $response = $this->get(route('categories.show', $category));
        $response->assertStatus(200);
        $response->assertSee('Test Category');

        // Test Update
        $updateData = [
            'category_code' => 'CAT001',
            'name' => 'Updated Test Category',
            'status' => 'active'
        ];

        $response = $this->put(route('categories.update', $category), $updateData);
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', $updateData);

        // Test Delete
        $response = $this->delete(route('categories.destroy', $category));
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function test_salesmen_crud_operations()
    {
        $this->actingAs($this->admin);

        // Test Create
        $salesmanData = [
            'salesman_code' => 'SALES001',
            'name' => 'John Doe',
            'status' => 'active'
        ];

        $response = $this->post(route('salesmen.store'), $salesmanData);
        $response->assertRedirect(route('salesmen.index'));
        $this->assertDatabaseHas('salesmen', $salesmanData);

        $salesman = Salesman::where('salesman_code', 'SALES001')->first();

        // Test Read
        $response = $this->get(route('salesmen.show', $salesman));
        $response->assertStatus(200);
        $response->assertSee('John Doe');

        // Test Update
        $updateData = [
            'salesman_code' => 'SALES001',
            'name' => 'Updated John Doe',
            'status' => 'active'
        ];

        $response = $this->put(route('salesmen.update', $salesman), $updateData);
        $response->assertRedirect(route('salesmen.index'));
        $this->assertDatabaseHas('salesmen', $updateData);

        // Test Delete
        $response = $this->delete(route('salesmen.destroy', $salesman));
        $response->assertRedirect(route('salesmen.index'));
        $this->assertDatabaseMissing('salesmen', ['id' => $salesman->id]);
    }

    /** @test */
    public function test_user_permissions()
    {
        // Test admin access
        $this->actingAs($this->admin);
        $response = $this->get(route('users.index'));
        $response->assertStatus(200);

        // Test regular user access (should be denied)
        $this->actingAs($this->user);
        $response = $this->get(route('users.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function test_authentication_required()
    {
        // Test unauthenticated access
        $response = $this->get(route('regions.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('channels.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('suppliers.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('categories.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('salesmen.index'));
        $response->assertRedirect(route('login'));
    }
}
