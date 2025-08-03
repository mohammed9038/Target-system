<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Region;
use App\Models\Channel;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Salesman;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function test_regions_validation_rules()
    {
        $this->actingAs($this->admin);

        // Test required fields
        $response = $this->post(route('regions.store'), []);
        $response->assertSessionHasErrors(['region_code', 'name', 'status']);

        // Test unique constraint
        Region::factory()->create(['region_code' => 'R001']);
        
        $response = $this->post(route('regions.store'), [
            'region_code' => 'R001',
            'name' => 'Test Region',
            'status' => 'active'
        ]);
        $response->assertSessionHasErrors(['region_code']);

        // Test valid data
        $response = $this->post(route('regions.store'), [
            'region_code' => 'R002',
            'name' => 'Valid Region',
            'status' => 'active'
        ]);
        $response->assertRedirect(route('regions.index'));
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function test_channels_validation_rules()
    {
        $this->actingAs($this->admin);

        // Test required fields
        $response = $this->post(route('channels.store'), []);
        $response->assertSessionHasErrors(['channel_code', 'name', 'status']);

        // Test unique constraint
        Channel::factory()->create(['channel_code' => 'CH001']);
        
        $response = $this->post(route('channels.store'), [
            'channel_code' => 'CH001',
            'name' => 'Test Channel',
            'status' => 'active'
        ]);
        $response->assertSessionHasErrors(['channel_code']);

        // Test valid data
        $response = $this->post(route('channels.store'), [
            'channel_code' => 'CH002',
            'name' => 'Valid Channel',
            'status' => 'active'
        ]);
        $response->assertRedirect(route('channels.index'));
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function test_suppliers_validation_rules()
    {
        $this->actingAs($this->admin);

        // Test required fields
        $response = $this->post(route('suppliers.store'), []);
        $response->assertSessionHasErrors(['supplier_code', 'name', 'status']);

        // Test unique constraint
        Supplier::factory()->create(['supplier_code' => 'SUP001']);
        
        $response = $this->post(route('suppliers.store'), [
            'supplier_code' => 'SUP001',
            'name' => 'Test Supplier',
            'status' => 'active'
        ]);
        $response->assertSessionHasErrors(['supplier_code']);

        // Test valid data
        $response = $this->post(route('suppliers.store'), [
            'supplier_code' => 'SUP002',
            'name' => 'Valid Supplier',
            'status' => 'active'
        ]);
        $response->assertRedirect(route('suppliers.index'));
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function test_categories_validation_rules()
    {
        $this->actingAs($this->admin);

        // Test required fields
        $response = $this->post(route('categories.store'), []);
        $response->assertSessionHasErrors(['category_code', 'name', 'status']);

        // Test unique constraint
        Category::factory()->create(['category_code' => 'CAT001']);
        
        $response = $this->post(route('categories.store'), [
            'category_code' => 'CAT001',
            'name' => 'Test Category',
            'status' => 'active'
        ]);
        $response->assertSessionHasErrors(['category_code']);

        // Test valid data
        $response = $this->post(route('categories.store'), [
            'category_code' => 'CAT002',
            'name' => 'Valid Category',
            'status' => 'active'
        ]);
        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function test_salesmen_validation_rules()
    {
        $this->actingAs($this->admin);

        // Test required fields
        $response = $this->post(route('salesmen.store'), []);
        $response->assertSessionHasErrors(['salesman_code', 'name', 'status']);

        // Test unique constraint
        Salesman::factory()->create(['salesman_code' => 'SALES001']);
        
        $response = $this->post(route('salesmen.store'), [
            'salesman_code' => 'SALES001',
            'name' => 'Test Salesman',
            'status' => 'active'
        ]);
        $response->assertSessionHasErrors(['salesman_code']);

        // Test valid data
        $response = $this->post(route('salesmen.store'), [
            'salesman_code' => 'SALES002',
            'name' => 'Valid Salesman',
            'status' => 'active'
        ]);
        $response->assertRedirect(route('salesmen.index'));
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function test_status_validation()
    {
        $this->actingAs($this->admin);

        // Test invalid status value
        $response = $this->post(route('regions.store'), [
            'region_code' => 'R001',
            'name' => 'Test Region',
            'status' => 'invalid_status'
        ]);
        $response->assertSessionHasErrors(['status']);

        // Test valid status values
        foreach (['active', 'inactive'] as $status) {
            $response = $this->post(route('regions.store'), [
                'region_code' => "R00{$status}",
                'name' => "Test Region {$status}",
                'status' => $status
            ]);
            $response->assertRedirect(route('regions.index'));
            $response->assertSessionHasNoErrors();
        }
    }

    /** @test */
    public function test_code_format_validation()
    {
        $this->actingAs($this->admin);

        // Test code too long (assuming max length is set)
        $response = $this->post(route('regions.store'), [
            'region_code' => str_repeat('A', 256),
            'name' => 'Test Region',
            'status' => 'active'
        ]);
        $response->assertSessionHasErrors(['region_code']);

        // Test name too long
        $response = $this->post(route('regions.store'), [
            'region_code' => 'R001',
            'name' => str_repeat('A', 256),
            'status' => 'active'
        ]);
        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function test_update_validation_excludes_current_record()
    {
        $this->actingAs($this->admin);

        // Create a region
        $region = Region::factory()->create([
            'region_code' => 'R001',
            'name' => 'Original Region',
            'status' => 'active'
        ]);

        // Update with same code should be allowed
        $response = $this->put(route('regions.update', $region), [
            'region_code' => 'R001',
            'name' => 'Updated Region',
            'status' => 'active'
        ]);
        $response->assertRedirect(route('regions.index'));
        $response->assertSessionHasNoErrors();

        // Create another region
        $region2 = Region::factory()->create([
            'region_code' => 'R002',
            'name' => 'Another Region',
            'status' => 'active'
        ]);

        // Update to existing code should fail
        $response = $this->put(route('regions.update', $region2), [
            'region_code' => 'R001',
            'name' => 'Updated Another Region',
            'status' => 'active'
        ]);
        $response->assertSessionHasErrors(['region_code']);
    }
}
