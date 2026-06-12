<?php

namespace Tests\Unit;

use App\Enums\ImageGenerationQuality;
use App\Exceptions\InsufficientCreditsException;
use App\Models\User;
use App\Services\ImageGenerationCreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageGenerationCreditServiceTest extends TestCase
{
    use RefreshDatabase;

    private ImageGenerationCreditService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ImageGenerationCreditService();
    }

    public function test_quality_credits_mapping(): void
    {
        $this->assertSame(1, ImageGenerationQuality::Low->credits());
        $this->assertSame(5, ImageGenerationQuality::Medium->credits());
        $this->assertSame(15, ImageGenerationQuality::High->credits());
    }

    public function test_deducts_proportional_credits_per_quality(): void
    {
        $user = User::factory()->create(['image_credits' => 100]);

        $this->assertSame(1, $this->service->deduct($user, ImageGenerationQuality::Low, 'job-low'));
        $this->assertSame(99, $user->fresh()->image_credits);

        $this->assertSame(5, $this->service->deduct($user->fresh(), ImageGenerationQuality::Medium, 'job-med'));
        $this->assertSame(94, $user->fresh()->image_credits);

        $this->assertSame(15, $this->service->deduct($user->fresh(), ImageGenerationQuality::High, 'job-high'));
        $this->assertSame(79, $user->fresh()->image_credits);
    }

    public function test_deduct_by_api_cost_usd(): void
    {
        $user = User::factory()->create(['image_credits' => 50]);

        $this->service->deductByApproximateCost($user, 0.01);
        $this->assertSame(49, $user->fresh()->image_credits);

        $this->service->deductByApproximateCost($user->fresh(), 0.05);
        $this->assertSame(44, $user->fresh()->image_credits);

        $this->service->deductByApproximateCost($user->fresh(), 0.13);
        $this->assertSame(29, $user->fresh()->image_credits);
    }

    public function test_throws_when_insufficient_credits(): void
    {
        $user = User::factory()->create(['image_credits' => 3]);

        $this->expectException(InsufficientCreditsException::class);
        $this->service->deduct($user, ImageGenerationQuality::High);
    }
}
