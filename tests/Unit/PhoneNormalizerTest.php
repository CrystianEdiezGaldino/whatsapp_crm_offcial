<?php

namespace Tests\Unit;

use App\Support\PhoneNormalizer;
use PHPUnit\Framework\TestCase;

class PhoneNormalizerTest extends TestCase
{
    public function test_variants_br_mobile_with_and_without_nine(): void
    {
        $variants = PhoneNormalizer::variants('5541997796908');

        $this->assertContains('5541997796908', $variants);
        $this->assertContains('554197796908', $variants);
    }

    public function test_for_api_prefers_whatsapp_twelve_digit_format(): void
    {
        $this->assertSame('554197796908', PhoneNormalizer::forApi('5541997796908'));
        $this->assertSame('554197796908', PhoneNormalizer::forApi('554197796908'));
    }
}
