<?php

namespace App\Services;

use App\Enums\ImageGenerationQuality;
use App\Exceptions\InsufficientCreditsException;
use App\Models\ImageCreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ImageGenerationCreditService
{
    public function creditsForQuality(ImageGenerationQuality $quality): int
    {
        return $quality->credits();
    }

    public function canAfford(User $user, ImageGenerationQuality $quality): bool
    {
        return $user->image_credits >= $this->creditsForQuality($quality);
    }

    /**
     * Desconta créditos proporcional à qualidade: low=1, medium=5, high=15.
     */
    public function deduct(User $user, ImageGenerationQuality $quality, ?string $externalJobId = null): int
    {
        $amount = $this->creditsForQuality($quality);

        return DB::transaction(function () use ($user, $quality, $amount, $externalJobId) {
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($locked->image_credits < $amount) {
                throw new InsufficientCreditsException($amount, $locked->image_credits);
            }

            $locked->decrement('image_credits', $amount);

            ImageCreditTransaction::create([
                'user_id' => $locked->id,
                'quality' => $quality->value,
                'credits_used' => $amount,
                'approximate_cost_usd' => $quality->approximateCostUsd(),
                'external_job_id' => $externalJobId,
            ]);

            return $amount;
        });
    }

    /**
     * Infere qualidade pelo custo retornado pela API (ex.: Replicate/OpenAI).
     */
    public function deductByApproximateCost(User $user, float $costUsd, ?string $externalJobId = null): int
    {
        $quality = ImageGenerationQuality::fromCostUsd($costUsd);

        return $this->deduct($user, $quality, $externalJobId);
    }
}
