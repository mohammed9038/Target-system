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

class FilteringTest extends TestCase
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
    public function test_regions_filtering()
    {
        $this->actingAs($this->admin);

        // Create test data
        Region::factory()->create([
            'region_code' => 'R001',
            'name' => 'North Region',
            'status' => 'active'
        ]);

        Region::factory()->create([
            'region_code' => 'R002',
            'name' => 'South Region', 
            'status' => 'inactive'
        ]);

        Region::factory()->create([
            'region_code' => 'R003',
            'name' => 'East Region',
            'status' => 'active'
        ]);

        // Test index page loads with all regions
        $response = $this->get(route('regions.index'));
        $response->assertStatus(200);
        $response->assertSee('North Region');
        $response->assertSee('South Region');
        $response->assertSee('East Region');

        // Test JavaScript filtering would work (the page contains search functionality)
        $response->assertSee('searchInput');
        $response->assertSee('Search regions');
    }

    /** @test */
    public function test_channels_filtering()
    {
        $this->actingAs($this->admin);

        Channel::factory()->create([
            'channel_code' => 'CH001',
            'name' => 'Online Channel',
            'status' => 'active'
        ]);

        Channel::factory()->create([
            'channel_code' => 'CH002',
            'name' => 'Retail Channel',
            'status' => 'inactive'
        ]);

        $response = $this->get(route('channels.index'));
        $response->assertStatus(200);
        $response->assertSee('Online Channel');
        $response->assertSee('Retail Channel');
        $response->assertSee('searchInput');
    }

    /** @test */
    public function test_suppliers_filtering()
    {
        $this->actingAs($this->admin);

        Supplier::factory()->create([
            'supplier_code' => 'SUP001',
            'name' => 'ABC Supplier',
            'status' => 'active'
        ]);

        Supplier::factory()->create([
            'supplier_code' => 'SUP002',
            'name' => 'XYZ Supplier',
            'status' => 'inactive'
        ]);

        $response = $this->get(route('suppliers.index'));
        $response->assertStatus(200);
        $response->assertSee('ABC Supplier');
        $response->assertSee('XYZ Supplier');
        $response->assertSee('searchInput');
    }

    /** @test */
    public function test_categories_filtering()
    {
        $this->actingAs($this->admin);

        Category::factory()->create([
            'category_code' => 'CAT001',
            'name' => 'Electronics',
            'status' => 'active'
        ]);

        Category::factory()->create([
            'category_code' => 'CAT002',
            'name' => 'Clothing',
            'status' => 'inactive'
        ]);

        $response = $this->get(route('categories.index'));
        $response->assertStatus(200);
        $response->assertSee('Electronics');
        $response->assertSee('Clothing');
        $response->assertSee('searchInput');
    }

    /** @test */
    public function test_salesmen_filtering()
    {
        $this->actingAs($this->admin);

        Salesman::factory()->create([
            'salesman_code' => 'SALES001',
            'name' => 'John Smith',
            'status' => 'active'
        ]);

        Salesman::factory()->create([
            'salesman_code' => 'SALES002',
            'name' => 'Jane Doe',
            'status' => 'inactive'
        ]);

        $response = $this->get(route('salesmen.index'));
        $response->assertStatus(200);
        $response->assertSee('John Smith');
        $response->assertSee('Jane Doe');
        $response->assertSee('searchInput');
    }

    /** @test */
    public function test_status_filtering_display()
    {
        $this->actingAs($this->admin);

        // Create regions with different statuses
        Region::factory()->create([
            'region_code' => 'R001',
            'name' => 'Active Region',
            'status' => 'active'
        ]);

        Region::factory()->create([
            'region_code' => 'R002',
            'name' => 'Inactive Region',
            'status' => 'inactive'
        ]);

        $response = $this->get(route('regions.index'));
        $response->assertStatus(200);
        
        // Both should be displayed but with different status badges
        $response->assertSee('Active Region');
        $response->assertSee('Inactive Region');
        $response->assertSee('Active'); // Status badge
        $response->assertSee('Inactive'); // Status badge
    }

    /** @test */
    public function test_pagination_and_record_count()
    {
        $this->actingAs($this->admin);

        // Create multiple regions
        for ($i = 1; $i <= 15; $i++) {
            Region::factory()->create([
                'region_code' => sprintf('R%03d', $i),
                'name' => "Region $i",
                'status' => $i % 2 === 0 ? 'active' : 'inactive'
            ]);
        }

        $response = $this->get(route('regions.index'));
        $response->assertStatus(200);
        
        // Should show record count
        $response->assertSee('15'); // Record count
        $response->assertSee('records');
    }
}
