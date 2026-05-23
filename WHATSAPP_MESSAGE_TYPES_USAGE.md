# WhatsApp Message Types - Complete Usage Guide

Comprehensive guide for sending Contact, Reaction, OTP, and Carousel messages via WhatsApp API.

---

## 1. Contact Messages (vCard)

Send structured business card information that users can save to their phone contacts.

### Basic Usage

```php
use App\Services\WhatsAppContactBuilder;
use App\Services\WhatsAppService;

$contact = WhatsAppContactBuilder::create('João', 'Silva')
    ->phone('+5511999999999', 'MOBILE')
    ->phone('+551133334444', 'WORK')
    ->email('joao@example.com', 'WORK')
    ->organization('Santa Monica', 'Sales', 'Manager')
    ->build();

if ($contact) {
    app(WhatsAppService::class)->sendContact('+5511988888888', $contact);
} else {
    // Handle validation errors
}
```

### Complete Contact with All Details

```php
$contact = WhatsAppContactBuilder::create('Maria', 'Oliveira', 'Silva')
    ->nameTitle('Dr', 'PhD')
    ->phone('+5511987654321', 'MOBILE')
    ->phone('+551144445555', 'WORK')
    ->email('maria@company.com', 'WORK')
    ->email('maria.oliveira@personal.com', 'HOME')
    ->organization('Tech Company', 'Research & Development', 'Senior Engineer')
    ->address(
        'Avenida Paulista 1000, Apt 501',
        'São Paulo',
        'SP',
        '01311100',
        'Brazil',
        'BR',
        'WORK'
    )
    ->address(
        'Rua Augusta 100',
        'São Paulo',
        'SP',
        '01305100',
        'Brazil',
        'BR',
        'HOME'
    )
    ->url('https://maria-portfolio.com', 'WORK')
    ->birthday('1985-05-15')
    ->build();

if ($contact) {
    app(WhatsAppService::class)->sendContact($recipientPhone, $contact);
}
```

### API Reference

```php
WhatsAppContactBuilder::create(string $firstName, string $lastName = ''): self

->name(string $firstName, string $lastName = '', ?string $middleName = null): self
->nameTitle(?string $prefix = null, ?string $suffix = null): self
->phone(string $phoneNumber, string $type = 'MOBILE'): self    // MOBILE|HOME|WORK|IPHONE|MAIN|OTHER
->email(string $email, string $type = 'WORK'): self             // WORK|HOME
->organization(string $company, ?string $dept = null, ?string $title = null): self
->address(string $street, string $city, string $state, string $zip, string $country, string $cc, string $type = 'WORK'): self
->url(string $url, string $type = 'WORK'): self                 // WORK|HOME
->birthday(string $date): self                                   // YYYY-MM-DD format
->build(): ?array                                                // Returns vCard data or null
->getFormattedName(): string
->getPhones(): array
->getEmails(): array
```

### Use Cases

✅ **Support Team** - Share support contact with phone/email/address
✅ **Sales** - Send agent contact info to qualified leads
✅ **Event Organizers** - Share event contact details
✅ **Network** - Exchange professional contact information
✅ **Compliance** - Provide certified contact information

---

## 2. Reaction Messages (Emoji)

Send emoji reactions to messages you received from the user.

### Basic Usage

```php
use App\Services\WhatsAppService;

$whatsappService = app(WhatsAppService::class);

// React with emoji to a received message
// messageId = WAMID from webhook (stored in Message.wa_message_id)
$whatsappService->sendReaction('wamid.HBgLMj...', '👍');

// Other common reactions
$whatsappService->sendReaction($messageId, '❤️');   // heart
$whatsappService->sendReaction($messageId, '😂');   // laugh
$whatsappService->sendReaction($messageId, '😮');   // surprise
$whatsappService->sendReaction($messageId, '😢');   // sad
$whatsappService->sendReaction($messageId, '🙏');   // pray
```

### Database Schema - Store WAMIDs

```php
// Migration to track message WAMIDs for reactions
Schema::table('messages', function (Blueprint $table) {
    // Ensure wa_message_id is in WAMID format from webhooks
    // Format: wamid.HBgLMjAzNzAxMjM0NTY3ODkwMjM0NTY3ODkwMjM0NTY3=
});
```

### Message Tracking

```php
// When receiving a webhook message, store the WAMID:
$message = Message::create([
    'conversation_id' => $conversation->id,
    'wa_message_id' => $webhookPayload['messages'][0]['id'],  // WAMID format
    'direction' => 'inbound',
    'type' => $type,
    'content' => $content,
    'status' => 'delivered',
]);

// Later, react to it:
app(WhatsAppService::class)->sendReaction(
    $message->wa_message_id,
    '👍'
);
```

### API Reference

```php
sendReaction(string $messageId, string $emoji): ?array

// Parameters:
//   $messageId: WAMID format (from received message)
//   $emoji: Single emoji character

// Returns: API response or null on error
```

### Use Cases

✅ **Agent Workflow** - Quick acknowledgment without typing
✅ **Customer Engagement** - React to feedback (👍 👎 ❤️)
✅ **Conversation Flow** - Non-intrusive feedback mechanism
✅ **Quick Sentiment** - Express emotion without message
✅ **Auto-responses** - System reactions to common triggers

### Important Notes

- Can only react to messages you **received** from the user
- Each message can only have **one reaction** per number
- Sending new reaction replaces previous one
- Emoji must be single character (supported emojis: 👍 ❤️ 😂 😮 😢 🙏)

---

## 3. OTP Authentication Templates (CRITICAL)

Send one-time passwords for user verification. **MUST use authentication template.**

### ⚠️ CRITICAL REQUIREMENT

**DO NOT send OTPs as freeform messages.** This will result in account suspension.

```php
// ✅ CORRECT - Use authentication template
sendOTP('+5511999999999', '123456');

// ❌ WRONG - Account suspension risk
sendText('+5511999999999', 'Your code: 123456');
```

### Basic OTP Usage

```php
use App\Services\WhatsAppService;

$whatsappService = app(WhatsAppService::class);

// Generate and send 6-digit OTP (default)
$otp = WhatsAppOTPBuilder::create()
    ->generateCode(6, numeric: true)  // 6-digit number
    ->expiresIn(10)                   // expires in 10 minutes
    ->oneTabAutofill()                // RECOMMENDED button type
    ->build();

if ($otp) {
    $code = $otp['code'];  // Save this for verification
    app(WhatsAppService::class)->sendOTP(
        '+5511999999999',
        $code,
        10  // expiration minutes
    );
}
```

### OTP with Custom Code

```php
$whatsappService = app(WhatsAppService::class);

// Use your own generated code
$code = generateSecureOTP(6);  // your function

$result = $whatsappService->sendOTP(
    '+5511999999999',
    $code,
    5  // expires in 5 minutes (shorter for security)
);

if ($result) {
    // Store OTP for verification
    OTP::create([
        'user_id' => auth()->id(),
        'phone' => '+5511999999999',
        'code' => $code,
        'attempts' => 0,
        'expires_at' => now()->addMinutes(5),
        'verified_at' => null,
    ]);
} else {
    // Log error, try again later
}
```

### OTP Verification

```php
public function verifyOTP(Request $request)
{
    $validated = $request->validate([
        'code' => 'required|digits:6',
    ]);

    $otp = OTP::where('user_id', auth()->id())
        ->where('phone', auth()->user()->phone)
        ->where('code', $validated['code'])
        ->where('expires_at', '>', now())
        ->where('verified_at', null)
        ->first();

    if (!$otp) {
        return back()->withError('Invalid or expired code');
    }

    // Prevent reuse
    $otp->update([
        'verified_at' => now(),
        'attempts' => DB::raw('attempts + 1'),
    ]);

    // Complete your verification process
    auth()->user()->update(['phone_verified_at' => now()]);

    return redirect('/dashboard')->withSuccess('Phone verified!');
}
```

### Database Schema

```php
Schema::create('otps', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('phone');
    $table->string('code');  // Never store in plaintext in production
    $table->integer('attempts')->default(0);
    $table->integer('max_attempts')->default(3);
    $table->timestamp('expires_at');
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->index(['user_id', 'phone']);
});
```

### API Reference

```php
sendOTP(string $to, string $code, int $expirationMinutes = 10): ?array

WhatsAppOTPBuilder::create()
    ->generateCode(int $length = 6, bool $numeric = true): self
    ->code(string $code): self
    ->expiresIn(int $minutes): self
    ->withSecurityDisclaimer(bool $include = true): self
    ->withExpirationWarning(bool $include = true): self
    ->oneTabAutofill(): self          // RECOMMENDED - iOS 26+ native support
    ->copyCodeButton(): self
    ->zeroTap(): self
    ->build(): ?array
    ->getCode(): string
    ->getExpirationMinutes(): int
    ->logAudit(string $userId, string $phone, string $status): void
```

### Template Format (Meta Fixed)

```
[CODE] is your verification code.

For your security, do not share this code.

This code expires in [MINUTES] minutes.
```

### Best Practices

```php
✅ DO:
  - Confirm user's WhatsApp phone before sending OTP
  - Keep OTP valid for 5-10 minutes
  - Limit verification attempts (3-5 max)
  - Log attempts for security
  - Use one-tap autofill button (best UX)
  - Never send OTP via freeform message

❌ DON'T:
  - Send OTP as text message (account suspension)
  - Store codes in plaintext
  - Make OTP valid for too long (>15 min)
  - Send OTP without checking phone number
  - Use OTP for non-authentication purposes
  - Log the actual OTP code
```

### 2026 iOS Update (June 15)

Starting June 15, 2026:
- iOS 26+ will auto-fill OTPs from push notifications
- Keyboard suggestions enabled by default
- One-tap autofill button becomes native feature
- **Use `oneTabAutofill()` for best compatibility**

---

## 4. Carousel Templates

Send swipeable multi-card templates for products, events, or promotions.

### Basic Carousel (2 Product Cards)

```php
use App\Services\WhatsAppCarouselBuilder;
use App\Services\WhatsAppService;

$carousel = WhatsAppCarouselBuilder::create('product_showcase')
    ->addCard()
        ->image('image-media-id-1')
        ->body('iPhone 15 Pro\n$999.99\n256GB Storage')
        ->urlButton('Buy Now', 'https://example.com/iphone15')
        ->end()
    ->addCard()
        ->image('image-media-id-2')
        ->body('Samsung Galaxy S24\n$899.99\n256GB Storage')
        ->urlButton('Buy Now', 'https://example.com/galaxy')
        ->end()
    ->build();

if ($carousel) {
    app(WhatsAppService::class)->sendTemplate(
        '+5511999999999',
        'product_showcase',
        'pt_BR',
        $carousel['components']
    );
} else {
    // Handle errors
}
```

### Product Catalog (5 Cards with Details)

```php
$carousel = WhatsAppCarouselBuilder::create('summer_collection')
    ->addCard()
        ->image('media-id-1')
        ->body('Summer Shirt\nLightweight Cotton\n$29.99')
        ->urlButton('View', 'https://shop.com/shirt-001')
        ->quickReplyButton('Size?')
        ->end()
    ->addCard()
        ->image('media-id-2')
        ->body('Beach Shorts\nComfort Fit\n$39.99')
        ->urlButton('View', 'https://shop.com/shorts-001')
        ->quickReplyButton('Size?')
        ->end()
    ->addCard()
        ->image('media-id-3')
        ->body('Sunglasses\nUV Protection\n$59.99')
        ->urlButton('View', 'https://shop.com/sunglasses-001')
        ->quickReplyButton('Details')
        ->end()
    ->addCard()
        ->image('media-id-4')
        ->body('Beach Hat\nOne Size Fits All\n$24.99')
        ->urlButton('View', 'https://shop.com/hat-001')
        ->quickReplyButton('Buy')
        ->end()
    ->addCard()
        ->image('media-id-5')
        ->body('Sandals\nWaterproof\n$34.99')
        ->urlButton('View', 'https://shop.com/sandals-001')
        ->quickReplyButton('Colors')
        ->end()
    ->build();
```

### Event Carousel (Multiple Dates/Locations)

```php
$carousel = WhatsAppCarouselBuilder::create('event_dates')
    ->addCard()
        ->image('event-img-sp')
        ->body('São Paulo\n25 de Maio\nAv. Paulista')
        ->urlButton('Book Tickets', 'https://tickets.com/sp')
        ->end()
    ->addCard()
        ->image('event-img-rio')
        ->body('Rio de Janeiro\n28 de Maio\nCopacabana')
        ->urlButton('Book Tickets', 'https://tickets.com/rio')
        ->end()
    ->addCard()
        ->image('event-img-bh')
        ->body('Belo Horizonte\n31 de Maio\nPampulha')
        ->urlButton('Book Tickets', 'https://tickets.com/bh')
        ->end()
    ->build();
```

### API Reference

```php
WhatsAppCarouselBuilder::create(string $templateName): self

->addCard(): CarouselCardBuilder
    ->image(string $mediaId): self
    ->video(string $mediaId): self
    ->body(string $text): self              // max 160 chars
    ->footer(string $text): self            // optional
    ->urlButton(string $text, string $url): self
    ->quickReplyButton(string $text): self
    ->end(): WhatsAppCarouselBuilder       // Return to carousel

->build(): ?array                           // Returns template or null
->getErrors(): array
->getCardCount(): int
->logForRateLimiting(): void
```

### Constraints & Limits

```
Max Cards:        10
Max Buttons:      2 per card
Max Body Length:  160 characters
Media Type:       IMAGE or VIDEO (same across all cards)
Button Types:     Must be same across all cards
Max Updates:      1 per day, 10 per month (Meta rate limit)
Image Formats:    JPG, PNG
Video Formats:    MP4
Media Size:       Up to 16 MB
```

### Important Notes

```
⚠️ Template Update Limits (Meta Enforced):
  - Maximum 1 update per calendar day
  - Maximum 10 updates per calendar month
  - Violations prevent further updates

✅ Best Practices:
  - Use consistent image sizes for better UX
  - Keep body text clear and concise
  - Test on mobile devices
  - Use JPG format for images (smaller file size)
  - Check media IDs are properly uploaded before sending

❌ Common Mistakes:
  - Mixing image and video in same carousel
  - Having different button types per card
  - Body text exceeding 160 characters
  - Too many cards (> 10)
  - Exceeding rate limits (update > 10x/month)
```

### Database Tracking

```php
Schema::create('carousel_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->integer('card_count');
    $table->enum('media_type', ['IMAGE', 'VIDEO']);
    $table->integer('button_count')->default(2);
    $table->enum('button_type', ['URL', 'QUICK_REPLY']);
    $table->text('content');  // JSON structure
    $table->timestamp('last_updated_at');
    $table->integer('update_count_this_month')->default(0);
    $table->timestamps();
});
```

---

## Complete Example - Integration with Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WhatsAppService;
use App\Services\WhatsAppOTPBuilder;
use App\Services\WhatsAppContactBuilder;
use App\Services\WhatsAppCarouselBuilder;

class WhatsAppMessagingController extends Controller
{
    public function sendOTP(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|phone',
        ]);

        $service = app(WhatsAppService::class);

        // Generate OTP
        $otp = WhatsAppOTPBuilder::create()
            ->generateCode(6, true)
            ->expiresIn(10)
            ->oneTabAutofill()
            ->build();

        if (!$otp) {
            return response()->json(['error' => 'Failed to generate OTP'], 400);
        }

        $code = $otp['code'];
        $result = $service->sendOTP($validated['phone'], $code, 10);

        if ($result) {
            // Store for verification
            OTP::create([
                'phone' => $validated['phone'],
                'code' => $code,
                'expires_at' => now()->addMinutes(10),
            ]);

            return response()->json(['success' => 'OTP sent']);
        }

        return response()->json(['error' => $service->getUserFacingError()], 400);
    }

    public function sendContact(Request $request)
    {
        $validated = $request->validate([
            'recipient_phone' => 'required|phone',
            'contact_id' => 'required|exists:contacts,id',
        ]);

        $contact = Contact::find($validated['contact_id']);

        $vcard = WhatsAppContactBuilder::create(
            $contact->first_name,
            $contact->last_name
        )
            ->phone($contact->phone, 'WORK')
            ->email($contact->email, 'WORK')
            ->organization($contact->company, $contact->department, $contact->title)
            ->build();

        if (!$vcard) {
            return response()->json(['error' => 'Invalid contact data'], 400);
        }

        $result = app(WhatsAppService::class)->sendContact(
            $validated['recipient_phone'],
            $vcard
        );

        return response()->json($result);
    }
}
```

---

## Meta Documentation

- [Contact Messages](https://developers.facebook.com/docs/whatsapp/cloud-api/messages/contacts-messages/)
- [Reaction Messages](https://developers.facebook.com/documentation/business-messaging/whatsapp/messages/reaction-messages/)
- [Authentication Templates](https://developers.facebook.com/documentation/business-messaging/whatsapp/templates/authentication-templates/authentication-best-practices/)
- [Carousel Templates](https://developers.facebook.com/documentation/business-messaging/whatsapp/templates/marketing-templates/media-card-carousel-templates/)
