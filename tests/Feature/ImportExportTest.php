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
use Maatwebsite\Excel\Facades\Excel;

class ImportExportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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
    public function test_regions_export()
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

        // Test export
        $response = $this->get(route('regions.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('content-disposition');
    }

    /** @test */
    public function test_regions_import()
    {
        $this->actingAs($this->admin);

        Storage::fake('local');

        // Create a test Excel file content
        $csvContent = "region_code,name,status\nR001,North Region,active\nR002,South Region,inactive";
        $file = UploadedFile::fake()->createWithContent('regions.csv', $csvContent);

        // Test import
        $response = $this->post(route('regions.import'), [
            'file' => $file,
            'update_existing' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify data was imported
        $this->assertDatabaseHas('regions', [
            'region_code' => 'R001',
            'name' => 'North Region',
            'status' => 'active'
        ]);

        $this->assertDatabaseHas('regions', [
            'region_code' => 'R002',
            'name' => 'South Region',
            'status' => 'inactive'
        ]);
    }

    /** @test */
    public function test_regions_template_download()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('regions.template'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function test_channels_export()
    {
        $this->actingAs($this->admin);

        Channel::factory()->create([
            'channel_code' => 'CH001',
            'name' => 'Online Channel',
            'status' => 'active'
        ]);

        $response = $this->get(route('channels.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function test_channels_import()
    {
        $this->actingAs($this->admin);

        Storage::fake('local');

        $csvContent = "channel_code,name,status\nCH001,Online Channel,active\nCH002,Retail Channel,inactive";
        $file = UploadedFile::fake()->createWithContent('channels.csv', $csvContent);

        $response = $this->post(route('channels.import'), [
            'file' => $file,
            'update_existing' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('channels', [
            'channel_code' => 'CH001',
            'name' => 'Online Channel',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function test_suppliers_export()
    {
        $this->actingAs($this->admin);

        Supplier::factory()->create([
            'supplier_code' => 'SUP001',
            'name' => 'Test Supplier',
            'status' => 'active'
        ]);

        $response = $this->get(route('suppliers.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function test_suppliers_import()
    {
        $this->actingAs($this->admin);

        Storage::fake('local');

        $csvContent = "supplier_code,name,status\nSUP001,Test Supplier,active\nSUP002,Another Supplier,inactive";
        $file = UploadedFile::fake()->createWithContent('suppliers.csv', $csvContent);

        $response = $this->post(route('suppliers.import'), [
            'file' => $file,
            'update_existing' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('suppliers', [
            'supplier_code' => 'SUP001',
            'name' => 'Test Supplier',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function test_categories_export()
    {
        $this->actingAs($this->admin);

        Category::factory()->create([
            'category_code' => 'CAT001',
            'name' => 'Test Category',
            'status' => 'active'
        ]);

        $response = $this->get(route('categories.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function test_categories_import()
    {
        $this->actingAs($this->admin);

        Storage::fake('local');

        $csvContent = "category_code,name,status\nCAT001,Test Category,active\nCAT002,Another Category,inactive";
        $file = UploadedFile::fake()->createWithContent('categories.csv', $csvContent);

        $response = $this->post(route('categories.import'), [
            'file' => $file,
            'update_existing' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('categories', [
            'category_code' => 'CAT001',
            'name' => 'Test Category',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function test_salesmen_export()
    {
        $this->actingAs($this->admin);

        Salesman::factory()->create([
            'salesman_code' => 'SALES001',
            'name' => 'John Doe',
            'status' => 'active'
        ]);

        $response = $this->get(route('salesmen.export'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function test_salesmen_import()
    {
        $this->actingAs($this->admin);

        Storage::fake('local');

        $csvContent = "salesman_code,name,status\nSALES001,John Doe,active\nSALES002,Jane Smith,inactive";
        $file = UploadedFile::fake()->createWithContent('salesmen.csv', $csvContent);

        $response = $this->post(route('salesmen.import'), [
            'file' => $file,
            'update_existing' => false
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('salesmen', [
            'salesman_code' => 'SALES001',
            'name' => 'John Doe',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function test_import_with_update_existing()
    {
        $this->actingAs($this->admin);

        // Create existing record
        Region::factory()->create([
            'region_code' => 'R001',
            'name' => 'Old Name',
            'status' => 'active'
        ]);

        Storage::fake('local');

        $csvContent = "region_code,name,status\nR001,Updated Name,active";
        $file = UploadedFile::fake()->createWithContent('regions.csv', $csvContent);

        $response = $this->post(route('regions.import'), [
            'file' => $file,
            'update_existing' => true
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify record was updated
        $this->assertDatabaseHas('regions', [
            'region_code' => 'R001',
            'name' => 'Updated Name',
            'status' => 'active'
        ]);

        // Verify only one record exists
        $this->assertEquals(1, Region::where('region_code', 'R001')->count());
    }

    /** @test */
    public function test_import_validation_errors()
    {
        $this->actingAs($this->admin);

        Storage::fake('local');

        // Test invalid file format
        $response = $this->post(route('regions.import'), [
            'file' => UploadedFile::fake()->create('test.txt', 100, 'text/plain'),
            'update_existing' => false
        ]);

        $response->assertStatus(422);

        // Test missing file
        $response = $this->post(route('regions.import'), [
            'update_existing' => false
        ]);

        $response->assertStatus(422);
    }
}
