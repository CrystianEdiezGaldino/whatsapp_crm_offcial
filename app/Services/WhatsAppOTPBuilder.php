<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp OTP Builder - Authentication Template Handler
 *
 * CRITICAL: Must be used for all OTP/verification scenarios
 * Sending OTPs without using this template will result in account suspension
 *
 * Per Meta: Authentication templates require pre-approved templates
 * Format: "<CODE> is your verification code."
 */
class WhatsAppOTPBuilder
{
    private string $code = '';
    private ?int $expiresInMinutes = null;
    private bool $includeSecurityDisclaimer = true;
    private bool $includeExpirationWarning = true;
    private ?string $buttonType = null; // 'ONE_TAP', 'COPY_CODE', null for zero-tap
    private array $errors = [];

    const MIN_OTP_LENGTH = 4;
    const MAX_OTP_LENGTH = 8;
    const DEFAULT_VALIDITY_MINUTES = 10;

    /**
     * Create new OTP builder
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Generate random OTP code
     * Default: 6-digit numeric code
     */
    public function generateCode(int $length = 6, bool $numeric = true): self
    {
        if ($length < self::MIN_OTP_LENGTH || $length > self::MAX_OTP_LENGTH) {
            $this->errors[] = "OTP length must be between " . self::MIN_OTP_LENGTH . " and " . self::MAX_OTP_LENGTH;
            return $this;
        }

        if ($numeric) {
            $this->code = str_pad(random_int(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);
        } else {
            $this->code = Str::random($length);
        }

        Log::info('[OTP] Code generated', ['length' => $length, 'numeric' => $numeric]);

        return $this;
    }

    /**
     * Set OTP code explicitly
     */
    public function code(string $code): self
    {
        if (strlen($code) < self::MIN_OTP_LENGTH || strlen($code) > self::MAX_OTP_LENGTH) {
            $this->errors[] = "OTP length must be between " . self::MIN_OTP_LENGTH . " and " . self::MAX_OTP_LENGTH;
        }

        $this->code = $code;
        return $this;
    }

    /**
     * Set OTP expiration time
     * Default: 10 minutes, recommended: 5-10 minutes
     */
    public function expiresIn(int $minutes): self
    {
        if ($minutes < 1 || $minutes > 60) {
            $this->errors[] = 'OTP expiration must be between 1 and 60 minutes';
            return $this;
        }

        $this->expiresInMinutes = $minutes;
        return $this;
    }

    /**
     * Include security disclaimer
     * "For your security, do not share this code"
     */
    public function withSecurityDisclaimer(bool $include = true): self
    {
        $this->includeSecurityDisclaimer = $include;
        return $this;
    }

    /**
     * Include expiration warning
     * "This code expires in X minutes"
     */
    public function withExpirationWarning(bool $include = true): self
    {
        $this->includeExpirationWarning = $include;
        return $this;
    }

    /**
     * Use one-tap autofill button (RECOMMENDED)
     * Best user experience, native iOS 26+ support (June 15, 2026)
     */
    public function oneTabAutofill(): self
    {
        $this->buttonType = 'ONE_TAP';
        return $this;
    }

    /**
     * Use copy code button
     * User manually copies and pastes code
     */
    public function copyCodeButton(): self
    {
        $this->buttonType = 'COPY_CODE';
        return $this;
    }

    /**
     * Use zero-tap (no button)
     * For apps that auto-fill from notifications
     */
    public function zeroTap(): self
    {
        $this->buttonType = null;
        return $this;
    }

    /**
     * Build the OTP template structure
     * Returns null if validation fails
     */
    public function build(): ?array
    {
        if (!$this->isValid()) {
            Log::error('[OTP] Build failed - validation errors', [
                'errors' => $this->errors,
            ]);
            return null;
        }

        $template = [
            'name' => 'otp_verification',
            'language' => ['code' => 'pt_BR'],
            'components' => $this->buildComponents(),
        ];

        Log::info('[OTP] Template built successfully', [
            'button_type' => $this->buttonType,
            'has_expiration' => $this->includeExpirationWarning,
            'expires_in_minutes' => $this->expiresInMinutes,
        ]);

        return $template;
    }

    /**
     * Build OTP body with preset Meta format
     * Format: "<CODE> is your verification code."
     */
    private function buildComponents(): array
    {
        $bodyText = "{$this->code} is your verification code.";

        // Add security disclaimer if enabled
        if ($this->includeSecurityDisclaimer) {
            $bodyText .= "\n\nFor your security, do not share this code.";
        }

        // Add expiration warning if enabled
        if ($this->includeExpirationWarning && $this->expiresInMinutes) {
            $bodyText .= "\n\nThis code expires in {$this->expiresInMinutes} minutes.";
        }

        $components = [
            [
                'type' => 'body',
                'text' => $bodyText,
            ],
        ];

        // Add button if specified
        if ($this->buttonType === 'ONE_TAP') {
            $components[] = [
                'type' => 'buttons',
                'buttons' => [
                    [
                        'type' => 'ONE_TAP',
                        'one_tap_button_text' => 'Autofill',
                    ],
                ],
            ];
        } elseif ($this->buttonType === 'COPY_CODE') {
            $components[] = [
                'type' => 'buttons',
                'buttons' => [
                    [
                        'type' => 'QUICK_REPLY',
                        'text' => 'Copy Code',
                    ],
                ],
            ];
        }

        return $components;
    }

    /**
     * Validate OTP builder state
     */
    public function isValid(): bool
    {
        // Clear previous errors
        $this->errors = [];

        // Check code is set
        if (empty($this->code)) {
            $this->errors[] = 'OTP code must be set or generated';
        }

        // Check code length
        if (strlen($this->code) < self::MIN_OTP_LENGTH || strlen($this->code) > self::MAX_OTP_LENGTH) {
            $this->errors[] = "OTP code must be " . self::MIN_OTP_LENGTH . "-" . self::MAX_OTP_LENGTH . " characters";
        }

        // Check expiration is set
        if ($this->expiresInMinutes === null) {
            $this->expiresInMinutes = self::DEFAULT_VALIDITY_MINUTES;
        }

        return empty($this->errors);
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the generated OTP code
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get expiration minutes
     */
    public function getExpirationMinutes(): int
    {
        return $this->expiresInMinutes ?? self::DEFAULT_VALIDITY_MINUTES;
    }

    /**
     * Log OTP for security audit (DO NOT LOG THE ACTUAL CODE)
     */
    public function logAudit(string $userId, string $phoneNumber, string $status = 'sent'): void
    {
        Log::info("[OTP] {$status}", [
            'user_id' => $userId,
            'phone' => $phoneNumber,
            'code_length' => strlen($this->code),
            'expires_in_minutes' => $this->getExpirationMinutes(),
            'button_type' => $this->buttonType,
            // NEVER LOG THE ACTUAL CODE
        ]);
    }

    /**
     * Template name (always 'otp_verification' for authentication templates)
     */
    public function getTemplateName(): string
    {
        return 'otp_verification';
    }
}
