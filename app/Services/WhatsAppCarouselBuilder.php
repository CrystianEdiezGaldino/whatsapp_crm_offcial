<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Carousel Template Builder
 *
 * Create swipeable multi-card templates for product catalogs, promotions, etc.
 * Max 10 cards, 2 buttons per card, same media/button types across all cards
 *
 * Per Meta: Template updates limited to 1 per day, 10 per month
 */
class WhatsAppCarouselBuilder
{
    private string $templateName;
    private array $cards = [];
    private array $errors = [];

    const MAX_CARDS = 10;
    const MAX_BUTTONS_PER_CARD = 2;
    const MAX_BODY_LENGTH = 160;

    /**
     * Create new carousel builder
     */
    public static function create(string $templateName): self
    {
        return new self($templateName);
    }

    public function __construct(string $templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * Add card to carousel
     */
    public function addCard(): CarouselCardBuilder
    {
        if (count($this->cards) >= self::MAX_CARDS) {
            $this->errors[] = "Maximum {self::MAX_CARDS} cards allowed per carousel";
            return new CarouselCardBuilder($this, count($this->cards));
        }

        $cardIndex = count($this->cards);
        $card = new CarouselCardBuilder($this, $cardIndex);
        $this->cards[$cardIndex] = $card;

        return $card;
    }

    /**
     * Register card (internal - called by CarouselCardBuilder)
     */
    public function registerCard(int $index, array $cardData): self
    {
        $this->cards[$index] = $cardData;
        return $this;
    }

    /**
     * Build carousel template
     */
    public function build(): ?array
    {
        if (!$this->isValid()) {
            Log::error('[Carousel] Build failed - validation errors', [
                'template_name' => $this->templateName,
                'errors' => $this->errors,
            ]);
            return null;
        }

        $components = [];

        foreach ($this->cards as $index => $card) {
            if ($card instanceof CarouselCardBuilder) {
                $builtCard = $card->build();
                if ($builtCard) {
                    $components[] = $builtCard;
                }
            } else {
                $components[] = $card;
            }
        }

        Log::info('[Carousel] Template built successfully', [
            'template_name' => $this->templateName,
            'card_count' => count($this->cards),
            'buttons_per_card' => $this->getButtonsPerCard(),
        ]);

        return [
            'name' => $this->templateName,
            'language' => ['code' => 'pt_BR'],
            'components' => $components,
        ];
    }

    /**
     * Validate carousel template
     */
    public function isValid(): bool
    {
        $this->errors = [];

        // Name required
        if (empty($this->templateName)) {
            $this->errors[] = 'Template name is required';
        }

        // At least 2 cards required for carousel
        if (count($this->cards) < 2) {
            $this->errors[] = 'Carousel must have at least 2 cards';
        }

        // Max 10 cards
        if (count($this->cards) > self::MAX_CARDS) {
            $this->errors[] = "Maximum {self::MAX_CARDS} cards allowed";
        }

        // Validate media consistency
        $mediaTypes = $this->getMediaTypes();
        if (count(array_unique($mediaTypes)) > 1) {
            $this->errors[] = 'All cards must have the same media type (image or video)';
        }

        // Validate button consistency
        $buttonTypes = $this->getButtonTypes();
        if (count(array_unique($buttonTypes)) > 1) {
            $this->errors[] = 'All cards must have the same button types';
        }

        return empty($this->errors);
    }

    /**
     * Get media types from all cards
     */
    private function getMediaTypes(): array
    {
        $types = [];

        foreach ($this->cards as $card) {
            if (is_array($card) && isset($card['media_type'])) {
                $types[] = $card['media_type'];
            } elseif ($card instanceof CarouselCardBuilder) {
                $types[] = $card->getMediaType();
            }
        }

        return $types;
    }

    /**
     * Get button types from all cards
     */
    private function getButtonTypes(): array
    {
        $types = [];

        foreach ($this->cards as $card) {
            if (is_array($card) && isset($card['button_types'])) {
                $types = array_merge($types, $card['button_types']);
            } elseif ($card instanceof CarouselCardBuilder) {
                $types = array_merge($types, $card->getButtonTypes());
            }
        }

        return array_unique($types);
    }

    /**
     * Get buttons per card (should be consistent)
     */
    private function getButtonsPerCard(): int
    {
        foreach ($this->cards as $card) {
            if (is_array($card) && isset($card['buttons'])) {
                return count($card['buttons']);
            } elseif ($card instanceof CarouselCardBuilder) {
                return count($card->getButtons());
            }
        }

        return 0;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get card count
     */
    public function getCardCount(): int
    {
        return count($this->cards);
    }

    /**
     * Log for rate limiting awareness
     */
    public function logForRateLimiting(): void
    {
        Log::warning('[Carousel] Template created - note Meta rate limits', [
            'template_name' => $this->templateName,
            'limit' => '1 update/day, 10 updates/month',
            'card_count' => count($this->cards),
        ]);
    }
}

/**
 * Carousel Card Builder - fluent API for building individual cards
 */
class CarouselCardBuilder
{
    private WhatsAppCarouselBuilder $carousel;
    private int $cardIndex;
    private array $card = [];
    private array $buttons = [];
    private ?string $mediaType = null;

    public function __construct(WhatsAppCarouselBuilder $carousel, int $cardIndex)
    {
        $this->carousel = $carousel;
        $this->cardIndex = $cardIndex;
    }

    /**
     * Set image header
     */
    public function image(string $mediaId): self
    {
        $this->card['media_type'] = 'IMAGE';
        $this->card['media'] = [
            'type' => 'IMAGE',
            'image' => ['id' => $mediaId],
        ];
        $this->mediaType = 'IMAGE';
        return $this;
    }

    /**
     * Set video header
     */
    public function video(string $mediaId): self
    {
        $this->card['media_type'] = 'VIDEO';
        $this->card['media'] = [
            'type' => 'VIDEO',
            'video' => ['id' => $mediaId],
        ];
        $this->mediaType = 'VIDEO';
        return $this;
    }

    /**
     * Set card body text (max 160 chars)
     */
    public function body(string $text): self
    {
        if (strlen($text) > WhatsAppCarouselBuilder::MAX_BODY_LENGTH) {
            Log::warning('[Carousel Card] Body text exceeds limit', [
                'length' => strlen($text),
                'max' => WhatsAppCarouselBuilder::MAX_BODY_LENGTH,
            ]);
        }

        $this->card['body'] = ['text' => substr($text, 0, WhatsAppCarouselBuilder::MAX_BODY_LENGTH)];
        return $this;
    }

    /**
     * Set optional card footer
     */
    public function footer(string $text): self
    {
        $this->card['footer'] = ['text' => $text];
        return $this;
    }

    /**
     * Add URL button
     */
    public function urlButton(string $text, string $url): self
    {
        return $this->addButton('URL', $text, $url);
    }

    /**
     * Add quick reply button
     */
    public function quickReplyButton(string $text): self
    {
        return $this->addButton('QUICK_REPLY', $text);
    }

    /**
     * Add button (internal)
     */
    private function addButton(string $type, string $text, ?string $url = null): self
    {
        if (count($this->buttons) >= WhatsAppCarouselBuilder::MAX_BUTTONS_PER_CARD) {
            Log::warning('[Carousel Card] Maximum buttons reached for card', [
                'card_index' => $this->cardIndex,
                'max' => WhatsAppCarouselBuilder::MAX_BUTTONS_PER_CARD,
            ]);
            return $this;
        }

        $button = [
            'type' => $type,
            'text' => $text,
        ];

        if ($url && $type === 'URL') {
            $button['url'] = $url;
        }

        $this->buttons[] = $button;
        return $this;
    }

    /**
     * Build card structure
     */
    public function build(): array
    {
        $card = [
            'type' => 'CAROUSEL_CARD',
            'card_index' => $this->cardIndex,
        ];

        // Add media if set
        if (isset($this->card['media_type'])) {
            $card['media_type'] = $this->card['media_type'];
            if (isset($this->card['media'])) {
                $card['media'] = $this->card['media'];
            }
        }

        // Add body if set
        if (isset($this->card['body'])) {
            $card['body'] = $this->card['body'];
        }

        // Add footer if set
        if (isset($this->card['footer'])) {
            $card['footer'] = $this->card['footer'];
        }

        // Add buttons if any
        if (!empty($this->buttons)) {
            $card['buttons'] = $this->buttons;
            $card['button_types'] = array_column($this->buttons, 'type');
        }

        return $card;
    }

    /**
     * End card and register with carousel
     */
    public function end(): WhatsAppCarouselBuilder
    {
        $this->carousel->registerCard($this->cardIndex, $this->build());
        return $this->carousel;
    }

    /**
     * Get media type
     */
    public function getMediaType(): string
    {
        return $this->mediaType ?? '';
    }

    /**
     * Get button types
     */
    public function getButtonTypes(): array
    {
        return array_column($this->buttons, 'type');
    }

    /**
     * Get buttons
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }
}
