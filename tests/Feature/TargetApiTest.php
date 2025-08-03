<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\SalesTarget;
use App\Models\Salesman;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\ActiveMonthYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TargetApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->manager = User::factory()->create(['role' => 'manager']);
    }

    public function test_admin_can_view_all_targets()
    {
        $targets = SalesTarget::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/targets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'salesman',
                        'supplier',
                        'category',
                        'period',
                        'target_amount'
                    ]
                ],
                'meta',
                'summary'
            ]);
    }

    public function test_can_create_target_with_valid_data()
    {
        $salesman = Salesman::factory()->create();
        $supplier = Supplier::factory()->create();
        $category = Category::factory()->create(['supplier_id' => $supplier->id]);
        
        ActiveMonthYear::factory()->create([
            'year' => 2024,
            'month' => 1,
            'is_open' => true
        ]);

        $targetData = [
            'salesman_id' => $salesman->id,
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'year' => 2024,
            'month' => 1,
            'target_amount' => 1500.00
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/targets', $targetData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'salesman',
                    'supplier',
                    'category',
                    'target_amount'
                ]
            ]);

        $this->assertDatabaseHas('sales_targets', [
            'salesman_id' => $salesman->id,
            'supplier_id' => $supplier->id,
            'category_id' => $category->id,
            'target_amount' => 1500.00
        ]);
    }

    public function test_cannot_create_target_with_invalid_data()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/targets', [
                'target_amount' => -100 // Invalid amount
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'salesman_id',
                'supplier_id',
                'category_id',
                'year',
                'month'
            ]);
    }

    public function test_can_update_existing_target()
    {
        $target = SalesTarget::factory()->create(['target_amount' => 1000]);

        // Mock open period
        ActiveMonthYear::factory()->create([
            'year' => $target->year,
            'month' => $target->month,
            'is_open' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/targets/{$target->id}", [
                'target_amount' => 1500.00
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data'
            ]);

        $this->assertDatabaseHas('sales_targets', [
            'id' => $target->id,
            'target_amount' => 1500.00
        ]);
    }

    public function test_can_delete_target()
    {
        $target = SalesTarget::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/targets/{$target->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Target deleted successfully']);

        $this->assertDatabaseMissing('sales_targets', ['id' => $target->id]);
    }

    public function test_matrix_endpoint_returns_correct_structure()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/targets/matrix');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'salesmen',
                    'suppliers',
                    'targets',
                    'classifications'
                ],
                'meta' => [
                    'execution_time_ms',
                    'counts'
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_access_targets()
    {
        $response = $this->getJson('/api/v1/targets');

        $response->assertStatus(401);
    }
}
