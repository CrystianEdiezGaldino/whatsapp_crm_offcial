# WhatsApp Template Builder - Usage Guide

The `WhatsAppTemplateBuilder` ensures templates comply with Meta's requirements for utility, marketing, and authentication templates.

## Quick Start

### 1. Utility Template (Order Confirmation)

```php
use App\Services\WhatsAppTemplateBuilder;

$template = WhatsAppTemplateBuilder::utility('order_confirmation')
    ->header('TEXT', 'Seu pedido foi confirmado! ✅')
    ->body(
        'Pedido #{{1}}\nData: {{2}}\nTotal: {{3}}',
        ['ORD-12345', '23/05/2026', 'R$ 150,00']
    )
    ->footer('Santa Monica Receptiva')
    ->urlButton('Ver Detalhes', 'https://example.com/orders/12345')
    ->build();

if ($template) {
    app(WhatsAppService::class)->sendTemplate('+5511999999999', 'order_confirmation', 'pt_BR', $template['components']);
} else {
    // Handle validation errors
}
```

### 2. Shipping Update

```php
$template = WhatsAppTemplateBuilder::utility('shipping_update')
    ->header('IMAGE', mediaId: 'image-media-id-from-meta')
    ->body(
        'Seu pacote está a caminho!\nRastreamento: {{1}}\nEntrega: {{2}}',
        ['BR123456789', '25 de maio']
    )
    ->footer('Rastreie em tempo real')
    ->urlButton('Rastrear', 'https://tracking.example.com/BR123456789')
    ->build();
```

### 3. Account Alert (Non-promotional)

```php
$template = WhatsAppTemplateBuilder::utility('account_alert')
    ->body(
        'Alerta de segurança\nNova tentativa de acesso em {{1}}\nSe não foi você, mude sua senha agora.',
        ['São Paulo - 10:45']
    )
    ->urlButton('Mudar Senha', 'https://example.com/change-password')
    ->phoneButton('Ligar', '+551133334444')
    ->build();
```

### 4. Authentication Template (OTP)

```php
$template = WhatsAppTemplateBuilder::authentication('otp_verification')
    ->body(
        'Seu código de verificação é: {{1}}\nValidade: {{2}} minutos',
        ['123456', '10']
    )
    ->build();
```

### 5. Marketing Template (Allowed - Different Type)

```php
$template = WhatsAppTemplateBuilder::marketing('summer_sale')
    ->header('IMAGE', mediaId: 'promo-image-id')
    ->body(
        'Aproveite o desconto de {{1}}% em todos os produtos!\nAcesse agora: {{2}}',
        ['30', 'https://example.com/sale']
    )
    ->urlButton('Comprar Agora', 'https://example.com/sale')
    ->build();
```

## API Reference

### Constructor Methods

```php
WhatsAppTemplateBuilder::utility(string $name)      // Transactional templates
WhatsAppTemplateBuilder::marketing(string $name)    // Promotional
WhatsAppTemplateBuilder::authentication(string $name) // OTP/Verification
```

### Methods

#### `header(string $type, ?string $text = null, ?string $mediaId = null): self`
Add optional header to template.
- `$type`: 'TEXT', 'IMAGE', 'VIDEO', or 'DOCUMENT'
- `$text`: Only for TEXT headers
- `$mediaId`: Required for IMAGE, VIDEO, DOCUMENT

```php
->header('TEXT', 'Order Confirmation')
->header('IMAGE', mediaId: 'image-123-from-meta')
```

#### `body(string $text, array $dynamicValues = []): self`
Add body with required text and optional dynamic variables.
- Variables use `{{1}}`, `{{2}}` format
- `$dynamicValues` must match placeholder count

```php
->body('Hello {{1}}, your order #{{2}} is confirmed', ['John', 'ORD-123'])
```

#### `footer(string $text): self`
Add optional footer (max 60 characters recommended).

```php
->footer('Santa Monica Receptiva')
```

#### `quickReplyButton(string $text): self`
Add quick reply button. Max 3 buttons total.

```php
->quickReplyButton('Yes')
->quickReplyButton('No')
```

#### `urlButton(string $text, string $url): self`
Add URL button that opens link when tapped.

```php
->urlButton('View Order', 'https://example.com/orders/123')
```

#### `phoneButton(string $text, string $phoneNumber): self`
Add phone call button.

```php
->phoneButton('Call Support', '+551133334444')
```

#### `build(): ?array`
Validate and build the template structure. Returns `null` if validation fails.

```php
$template = $builder->build();
if (!$template) {
    $errors = $builder->getErrors();
}
```

#### `isValid(): bool`
Check if template is valid without building.

```php
if ($builder->isValid()) {
    // Safe to build
}
```

#### `getErrors(): array`
Get array of validation errors.

```php
foreach ($builder->getErrors() as $error) {
    Log::error($error);
}
```

#### `getErrorsAsString(): string`
Get validation errors as single string.

```php
echo $builder->getErrorsAsString();
```

#### `log(string $status = 'created'): void`
Log template creation/usage for auditing.

```php
$builder->log('created');
$builder->log('sent');
```

## Validation Rules

### Utility Templates (Meta Compliance)
✅ **Allowed**:
- Order numbers and dates
- Billing information
- Account details
- Shipping/tracking info
- Transactional links

❌ **NOT Allowed**:
- "Discount", "offer", "sale", "limited time"
- "Click here", "buy now", "order now"
- "Free shipping", "coupon", "promo"
- Any persuasive promotional language

### All Templates
- Template name: ≤ 512 characters
- Footer text: ≤ 60 characters (recommended)
- Body placeholders: `{{1}}`, `{{2}}`, etc (sequential)
- Maximum 3 buttons per template
- Header is optional
- Body is required
- Footer is optional

## Error Handling

```php
$builder = WhatsAppTemplateBuilder::utility('my_template')
    ->body('Hello {{1}} and {{2}}', ['Alice'])  // Missing value
    ->urlButton('Link 1', 'https://...')
    ->urlButton('Link 2', 'https://...')
    ->urlButton('Link 3', 'https://...')
    ->urlButton('Link 4', 'https://...'); // Too many buttons

if (!$builder->isValid()) {
    $errors = $builder->getErrors();
    // errors = [
    //   "Body has 2 placeholders but 1 values provided",
    //   "Maximum 3 buttons allowed per template"
    // ]
}
```

## Database Tracking (Recommended)

Create a migration to track template history:

```php
Schema::create('whatsapp_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->enum('type', ['utility', 'marketing', 'authentication']);
    $table->enum('status', ['pending', 'approved', 'rejected']);
    $table->text('content');
    $table->text('rejection_reason')->nullable();
    $table->integer('send_count')->default(0);
    $table->integer('success_count')->default(0);
    $table->timestamps();
});
```

Then update templates to track:

```php
$template = WhatsAppTemplateBuilder::utility('order_confirmation')
    ->body('Order #{{1}} confirmed', [$orderId])
    ->build();

if ($template) {
    WhatsAppTemplate::updateOrCreate(
        ['name' => 'order_confirmation'],
        [
            'type' => 'utility',
            'content' => json_encode($template),
            'status' => 'approved'
        ]
    );
}
```

## Best Practices

1. **Always validate before sending**
   ```php
   if ($builder->isValid()) {
       // Send template
   }
   ```

2. **Log for audit trail**
   ```php
   $builder->log('created');
   // Later: $builder->log('sent'); $builder->log('failed');
   ```

3. **Use specific order/transaction numbers**
   - Good: "Order #ORD-123456-2026"
   - Bad: "Your order is ready"

4. **Keep messaging factual and transactional**
   - Use utility templates for confirmations
   - Use marketing for actual promotions
   - Don't mix promotional language in utility templates

5. **Test with Meta's template sandbox first**
   - Submit templates for approval
   - Monitor rejection reasons
   - Iterate based on feedback

## Integration with WhatsAppService

```php
use App\Services\WhatsAppService;
use App\Services\WhatsAppTemplateBuilder;

$to = '+5511999999999';

// Build template
$template = WhatsAppTemplateBuilder::utility('order_confirmation')
    ->body('Order #{{1}} confirmed on {{2}}', [$orderId, now()->format('d/m/Y')])
    ->urlButton('View', "https://example.com/orders/{$orderId}")
    ->build();

if ($template) {
    // Send via WhatsAppService
    app(WhatsAppService::class)->sendTemplate(
        $to,
        'order_confirmation',
        'pt_BR',
        $template['components']
    );
}
```

## Meta Documentation References

- [WhatsApp Utility Templates](https://developers.facebook.com/documentation/business-messaging/whatsapp/templates/utility-templates/utility-templates/)
- [WhatsApp Message Templates Overview](https://developers.facebook.com/documentation/business-messaging/whatsapp/templates/overview)
- [Template Components Guide](https://developers.facebook.com/documentation/business-messaging/whatsapp/templates/template-components/)
