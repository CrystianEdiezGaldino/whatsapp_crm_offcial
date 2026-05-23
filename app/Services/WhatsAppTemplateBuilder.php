<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Template Builder - Ensures compliance with Meta requirements
 *
 * Utility templates: Order confirmations, shipping updates, account alerts
 * Per Meta docs: Must be non-promotional, factual, transactional
 */
class WhatsAppTemplateBuilder
{
    private array $template = [];
    private array $errors = [];

    const TEMPLATE_TYPES = [
        'utility' => 'Transactional (order, shipping, account)',
        'marketing' => 'Promotional content',
        'authentication' => 'One-time codes and verification',
        'account_update' => 'Account status changes',
    ];

    const PROHIBITED_KEYWORDS = [
        'discount', 'offer', 'sale', 'limited time', 'act now',
        'click here', 'buy now', 'order now', 'shop', 'deal',
        'free shipping', 'coupon', 'promo', 'special',
    ];

    public function __construct(
        private string $templateName,
        private string $templateType = 'utility'
    ) {
        $this->validate();
    }

    /**
     * Create a utility template (order confirmation, shipping, account alert)
     */
    public static function utility(string $templateName): self
    {
        return new self($templateName, 'utility');
    }

    /**
     * Create a marketing template
     */
    public static function marketing(string $templateName): self
    {
        return new self($templateName, 'marketing');
    }

    /**
     * Create an authentication template (OTP, verification codes)
     */
    public static function authentication(string $templateName): self
    {
        return new self($templateName, 'authentication');
    }

    /**
     * Add template header (optional)
     * Type: 'TEXT', 'IMAGE', 'VIDEO', 'DOCUMENT'
     */
    public function header(string $type, ?string $text = null, ?string $mediaId = null): self
    {
        $header = ['type' => $type];

        if ($type === 'TEXT' && $text) {
            $header['text'] = $text;
        } elseif (in_array($type, ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
            if (!$mediaId) {
                $this->errors[] = "Header type {$type} requires mediaId";
            } else {
                $header['media'] = ['id' => $mediaId];
            }
        }

        $this->template['header'] = $header;
        return $this;
    }

    /**
     * Add template body with dynamic variables
     * Variables use format: {{1}}, {{2}}, etc.
     */
    public function body(string $text, array $dynamicValues = []): self
    {
        // Validate for prohibited keywords (utility templates only)
        if ($this->templateType === 'utility') {
            $lowerText = strtolower($text);
            foreach (self::PROHIBITED_KEYWORDS as $keyword) {
                if (str_contains($lowerText, $keyword)) {
                    $this->errors[] = "Utility templates cannot contain promotional keyword: '{$keyword}'";
                }
            }
        }

        // Count placeholders vs provided values
        preg_match_all('/\{\{(\d+)\}\}/', $text, $matches);
        $placeholderCount = count(array_unique($matches[1]));

        if ($placeholderCount > 0 && count($dynamicValues) !== $placeholderCount) {
            $this->errors[] = "Body has {$placeholderCount} placeholders but {$dynamicValues} values provided";
        }

        $this->template['body'] = [
            'text' => $text,
        ];

        if (!empty($dynamicValues)) {
            $this->template['body']['parameters'] = array_map(
                fn($value) => ['type' => 'text', 'text' => (string)$value],
                $dynamicValues
            );
        }

        return $this;
    }

    /**
     * Add template footer (optional, secondary text)
     */
    public function footer(string $text): self
    {
        if (strlen($text) > 60) {
            $this->errors[] = 'Footer text should be 60 characters or less';
        }

        $this->template['footer'] = ['text' => $text];
        return $this;
    }

    /**
     * Add quick reply button
     * Max 3 buttons total
     */
    public function quickReplyButton(string $text): self
    {
        return $this->addButton([
            'type' => 'QUICK_REPLY',
            'text' => $text,
        ]);
    }

    /**
     * Add URL button
     * Max 3 buttons total
     */
    public function urlButton(string $text, string $url): self
    {
        return $this->addButton([
            'type' => 'URL',
            'text' => $text,
            'url' => $url,
        ]);
    }

    /**
     * Add phone call button
     * Max 3 buttons total
     */
    public function phoneButton(string $text, string $phoneNumber): self
    {
        return $this->addButton([
            'type' => 'PHONE_NUMBER',
            'text' => $text,
            'phone_number' => $phoneNumber,
        ]);
    }

    /**
     * Add a button
     */
    private function addButton(array $button): self
    {
        if (!isset($this->template['buttons'])) {
            $this->template['buttons'] = [];
        }

        if (count($this->template['buttons']) >= 3) {
            $this->errors[] = 'Maximum 3 buttons allowed per template';
            return $this;
        }

        $this->template['buttons'][] = $button;
        return $this;
    }

    /**
     * Validate template structure
     */
    private function validate(): void
    {
        if (!in_array($this->templateType, array_keys(self::TEMPLATE_TYPES))) {
            $this->errors[] = "Invalid template type: {$this->templateType}";
        }

        if (empty($this->templateName)) {
            $this->errors[] = 'Template name is required';
        }

        if (strlen($this->templateName) > 512) {
            $this->errors[] = 'Template name must be 512 characters or less';
        }
    }

    /**
     * Build and return the template structure
     */
    public function build(): ?array
    {
        if (!$this->isValid()) {
            Log::error('[Template] Build failed - validation errors', [
                'template_name' => $this->templateName,
                'errors' => $this->errors,
            ]);
            return null;
        }

        return [
            'name' => $this->templateName,
            'language' => ['code' => 'pt_BR'],
            'components' => $this->buildComponents(),
        ];
    }

    /**
     * Build component array from template data
     */
    private function buildComponents(): array
    {
        $components = [];

        if (isset($this->template['header'])) {
            $components[] = [
                'type' => 'header',
                'parameters' => [
                    $this->template['header'],
                ],
            ];
        }

        if (isset($this->template['body'])) {
            $components[] = [
                'type' => 'body',
                'parameters' => $this->template['body']['parameters'] ?? [],
            ];
        }

        if (isset($this->template['footer'])) {
            $components[] = [
                'type' => 'footer',
                'parameters' => [$this->template['footer']],
            ];
        }

        if (isset($this->template['buttons']) && !empty($this->template['buttons'])) {
            $components[] = [
                'type' => 'buttons',
                'buttons' => $this->template['buttons'],
            ];
        }

        return $components;
    }

    /**
     * Check if template is valid
     */
    public function isValid(): bool
    {
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
     * Get error messages as string
     */
    public function getErrorsAsString(): string
    {
        return implode('; ', $this->errors);
    }

    /**
     * Log template for audit
     */
    public function log(string $status = 'created'): void
    {
        Log::info('[Template] ' . ucfirst($status), [
            'template_name' => $this->templateName,
            'template_type' => $this->templateType,
            'is_valid' => $this->isValid(),
            'button_count' => count($this->template['buttons'] ?? []),
            'has_header' => isset($this->template['header']),
            'has_footer' => isset($this->template['footer']),
        ]);
    }
}
