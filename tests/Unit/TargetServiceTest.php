<?php

namespace Tests\Unit\Services;

use App\Services\TargetService;
use App\Repositories\TargetRepository;
use App\Models\SalesTarget;
use App\Events\TargetUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Mockery;

class TargetServiceTest extends TestCase
{
    use RefreshDatabase;

    private TargetService $targetService;
    private $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockRepository = Mockery::mock(TargetRepository::class);
        $this->targetService = new TargetService($this->mockRepository);
    }

    public function test_create_or_update_target_success()
    {
        Event::fake();
        Cache::shouldReceive('tags->flush')->once();
        
        $targetData = [
            'salesman_id' => 1,
            'supplier_id' => 1,
            'category_id' => 1,
            'year' => 2024,
            'month' => 1,
            'target_amount' => 1000.00
        ];

        $target = new SalesTarget($targetData);
        $target->id = 1;

        $this->mockRepository
            ->shouldReceive('createOrUpdate')
            ->with($targetData)
            ->andReturn($target);

        $result = $this->targetService->createOrUpdateTarget($targetData);

        $this->assertInstanceOf(SalesTarget::class, $result);
        $this->assertEquals(1, $result->id);
        Event::assertDispatched(TargetUpdated::class);
    }

    public function test_get_matrix_data_uses_cache()
    {
        $filters = ['year' => 2024];
        $expectedData = [
            'salesmen' => [],
            'suppliers' => [],
            'targets' => [],
            'classifications' => []
        ];

        Cache::shouldReceive('remember')
            ->once()
            ->with(
                'matrix_data_' . md5(serialize($filters)),
                300,
                Mockery::type('Closure')
            )
            ->andReturn($expectedData);

        $result = $this->targetService->getMatrixData($filters);

        $this->assertEquals($expectedData, $result);
    }

    public function test_validate_target_data_returns_errors_for_invalid_data()
    {
        $invalidData = [
            'salesman_id' => null,
            'supplier_id' => null,
            'target_amount' => -100
        ];

        $errors = $this->targetService->validateTargetData($invalidData);

        $this->assertContains('Salesman is required', $errors);
        $this->assertContains('Supplier is required', $errors);
        $this->assertContains('Target amount must be greater than 0', $errors);
    }

    public function test_validate_target_data_returns_empty_for_valid_data()
    {
        $validData = [
            'salesman_id' => 1,
            'supplier_id' => 1,
            'category_id' => 1,
            'target_amount' => 1000.00
        ];

        $errors = $this->targetService->validateTargetData($validData);

        $this->assertEmpty($errors);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
