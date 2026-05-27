# WhatsApp User Blocking - Implementation Guide

Block and unblock users from contacting your WhatsApp Business account.

**Source**: [Meta WhatsApp Cloud API - Block Users](https://developers.facebook.com/docs/whatsapp/cloud-api/block-users/)

---

## Overview

The WhatsApp Blocking API allows you to:

✅ **Block users** - Prevent them from messaging your business
✅ **Unblock users** - Allow them to message again
✅ **Track blocked status** - Know who is blocked
✅ **Bulk operations** - Block/unblock multiple users at once

### When a User is Blocked

```
User's perspective:
❌ Cannot send messages to business
❌ Cannot see when business is online
❌ Cannot call the business

Business's perspective:
❌ Cannot send messages to user
✅ Can unblock anytime
✅ Can track who is blocked
```

### Requirements

- User must have sent a message **in the last 24 hours** to be blocked
- Phone number must be normalized with country code
- Must have valid WhatsApp Business Account credentials

---

## Quick Start

### Block a User

```php
use App\Services\WhatsAppBlockingService;

$blockingService = app(WhatsAppBlockingService::class);

// Block a user
if ($blockingService->blockUser('+5511999999999')) {
    // Success - user is blocked
} else {
    // Failed - possibly no recent message from user
}
```

### Unblock a User

```php
if ($blockingService->unblockUser('+5511999999999')) {
    // Success - user can message again
}
```

### Check Blocked Status

```php
$isBlocked = $blockingService->isBlocked('+5511999999999');
if ($isBlocked) {
    echo "This user is blocked";
}
```

---

## Complete API Reference

### Block User

```php
blockUser(string $phoneNumber): bool

Parameters:
  $phoneNumber: Phone number with country code (e.g., '+5511999999999')

Returns:
  true  - Successfully blocked
  false - Failed (check logs for reason)

Errors:
  551   - No recent message from user (24h requirement)
  400   - Invalid phone number format
  500   - API error
```

### Unblock User

```php
unblockUser(string $phoneNumber): bool

Parameters:
  $phoneNumber: Phone number with country code

Returns:
  true  - Successfully unblocked
  false - Failed
```

### Check Block Status

```php
isBlocked(string $phoneNumber): bool

Returns:
  true  - User is blocked
  false - User is not blocked or doesn't exist
```

### Get Blocked Contacts

```php
static getBlockedContacts(): Collection

Returns:
  Collection of all blocked contacts
  Ordered by blocked_at (newest first)
```

### Block with Reason

```php
blockUserWithReason(string $phoneNumber, string $reason): bool

Parameters:
  $phoneNumber: Phone with country code
  $reason: Reason for blocking (stored in database)

Examples:
  - "Spam"
  - "Offensive behavior"
  - "Fraud attempt"
  - "Manual block"

Returns:
  true  - Blocked
  false - Failed
```

### Bulk Block

```php
blockMultipleUsers(array $phoneNumbers): array

Parameters:
  $phoneNumbers: Array of phone numbers

Returns:
  Array mapping phone => boolean (success/failure)

Example:
  [
    '+5511999999999' => true,
    '+5511888888888' => false,  // failed
    '+5511777777777' => true,
  ]
```

### Bulk Unblock

```php
unblockMultipleUsers(array $phoneNumbers): array

Returns:
  Array mapping phone => boolean (success/failure)
```

---

## Usage Examples

### Example 1: Block Spammer

```php
public function blockSpammer(Contact $contact)
{
    $blockingService = app(WhatsAppBlockingService::class);

    if ($blockingService->blockUserWithReason(
        $contact->phone,
        'Spam/Harassment'
    )) {
        // Log the action for audit
        Log::warning('Contact blocked', [
            'contact_id' => $contact->id,
            'phone' => $contact->phone,
            'reason' => 'Spam/Harassment',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User blocked successfully'
        ]);
    }

    return response()->json([
        'error' => 'Failed to block user - no recent message from them'
    ], 400);
}
```

### Example 2: Temporary Block During Incident

```php
public function blockDuringIncident(Request $request)
{
    $validated = $request->validate([
        'contact_ids' => 'required|array',
        'incident_type' => 'required|in:fraud,abuse,spam',
    ]);

    $blockingService = app(WhatsAppBlockingService::class);
    $contacts = Contact::whereIn('id', $validated['contact_ids'])->get();

    $phoneNumbers = $contacts->pluck('phone')->toArray();
    $results = $blockingService->blockMultipleUsers($phoneNumbers);

    // Log incident for compliance
    Log::critical("Mass block during {$validated['incident_type']}", [
        'contacts' => count($results),
        'results' => $results,
        'timestamp' => now(),
    ]);

    return response()->json($results);
}
```

### Example 3: Auto-block Repeated Abuse

```php
public function checkAndBlockAbusers()
{
    // Find contacts with multiple abuse reports
    $abusers = Contact::whereHas('conversations', function ($q) {
        $q->where('abuse_reports', '>=', 3)
          ->where('last_abuse_at', '>', now()->subDays(7));
    })->get();

    $blockingService = app(WhatsAppBlockingService::class);

    foreach ($abusers as $contact) {
        if ($blockingService->blockUserWithReason(
            $contact->phone,
            'Multiple abuse reports (auto-blocked)'
        )) {
            // Send internal notification
            Log::warning("Auto-blocked abuser: {$contact->phone}");
        }
    }
}
```

### Example 4: Block with Review Period

```php
public function blockForReview(Contact $contact)
{
    $blockingService = app(WhatsAppBlockingService::class);

    // Block temporarily
    if ($blockingService->blockUserWithReason(
        $contact->phone,
        'Under review for compliance'
    )) {
        // Schedule automatic review
        dispatch(new ReviewBlockedContactJob($contact))
            ->delay(now()->addDays(7));  // Review after 7 days

        return true;
    }

    return false;
}
```

---

## Database Schema

```php
Schema::table('contacts', function (Blueprint $table) {
    $table->boolean('is_blocked')->default(false);
    $table->timestamp('blocked_at')->nullable();
    $table->string('block_reason')->nullable();

    $table->index('is_blocked');
    $table->index('blocked_at');
});
```

### Query Examples

```php
// Get all blocked contacts
$blocked = Contact::where('is_blocked', true)->get();

// Get recently blocked
$recentlyBlocked = Contact::where('is_blocked', true)
    ->orderBy('blocked_at', 'desc')
    ->limit(10)
    ->get();

// Get blocked by reason
$spamBlocked = Contact::where('is_blocked', true)
    ->where('block_reason', 'Spam')
    ->get();

// Get active conversations with blocked users
$blocked = Contact::where('is_blocked', true)
    ->with('conversations')
    ->get();

// Unblock users (check manually)
$archived = Contact::where('is_blocked', true)
    ->where('blocked_at', '<', now()->subMonths(3))
    ->get();
```

---

## Best Practices

### ✅ DO

```php
// Check message history before blocking
if (Contact::where('phone', $phone)
    ->whereHas('messages', function ($q) {
        $q->where('direction', 'inbound')
          ->where('created_at', '>', now()->subHours(24));
    })->exists()) {
    $blockingService->blockUser($phone);
}

// Document reason for blocking
$blockingService->blockUserWithReason(
    $phone,
    'Explicit abuse - date 2026-05-23, message ID: 12345'
);

// Log blocking for audit
Log::info('[Blocking] User blocked', [
    'phone' => $phone,
    'reason' => $reason,
    'user' => auth()->user()->email,
    'timestamp' => now(),
]);

// Allow unblocking after review
// Store block reason for appeal process

// Bulk block only after verification
foreach ($suspiciousNumbers as $phone) {
    if ($this->verifyAsSpam($phone)) {
        $blockingService->blockUser($phone);
    }
}
```

### ❌ DON'T

```php
// Don't block users without recent message
$blockingService->blockUser('+5511999999999');
// ❌ Will fail with error 551

// Don't block without logging reason
$blockingService->blockUser($phone);
// ❌ No audit trail

// Don't block in bulk without verification
foreach ($list as $phone) {
    $blockingService->blockUser($phone);  // Could block legitimate users
}

// Don't forget to unblock after resolving issue
// Contact may remain blocked indefinitely

// Don't use blocking as punishment
// Use for safety and compliance only
```

---

## Error Handling

### Common Errors

```php
try {
    $blockingService->blockUser($phone);
} catch (\Exception $e) {
    // Error codes:
    // 400 - Invalid phone format
    // 551 - No message in last 24h
    // 500 - API error
    
    if (strpos($e->getMessage(), '551')) {
        // User hasn't messaged in 24 hours
        // Can't block this user via API
        // Note: User may still be blocked via WhatsApp app
    }
}
```

### Logging Pattern

```php
// Success
Log::info('[WhatsApp Block] User blocked successfully', [
    'phone' => $phone,
    'reason' => $reason,
    'timestamp' => now(),
]);

// Failure
Log::warning('[WhatsApp Block] Cannot block - no recent message', [
    'phone' => $phone,
    'error_code' => 551,
]);

// Audit trail
Log::critical('[WhatsApp Block] Mass block operation', [
    'count' => 50,
    'reason' => 'Spam campaign detected',
    'initiated_by' => auth()->user()->email,
]);
```

---

## Integration with Chat UI

### Controller Method

```php
// In ConversationController or ContactController
public function blockContact(Contact $contact)
{
    $blockingService = app(WhatsAppBlockingService::class);

    if ($blockingService->blockUserWithReason(
        $contact->phone,
        request('reason', 'Manual block')
    )) {
        return response()->json([
            'success' => true,
            'message' => 'Contact blocked',
            'is_blocked' => true,
        ]);
    }

    return response()->json([
        'error' => 'Could not block contact - may not have recent message',
    ], 400);
}

public function unblockContact(Contact $contact)
{
    $blockingService = app(WhatsAppBlockingService::class);

    if ($blockingService->unblockUser($contact->phone)) {
        return response()->json([
            'success' => true,
            'message' => 'Contact unblocked',
            'is_blocked' => false,
        ]);
    }

    return response()->json([
        'error' => 'Could not unblock contact',
    ], 400);
}
```

### Frontend Example (Blade)

```blade
@if($contact->is_blocked)
    <button @click="unblockContact({{ $contact->id }})">
        Unblock Contact
    </button>
    <small class="text-muted">Blocked {{ $contact->blocked_at->diffForHumans() }}</small>
@else
    <button @click="showBlockModal({{ $contact->id }})">
        Block Contact
    </button>
@endif
```

---

## Compliance & Legal

### When to Block

✅ **Legitimate Use Cases**:
- Spam/scam messages
- Abusive or threatening content
- Harassment
- Fraud attempts
- Compliance with law enforcement requests

### When NOT to Block

❌ **Avoid Blocking For**:
- Simple disagreements
- Business disputes
- Unresolved issues (use resolution first)
- Privacy without consent
- Discrimination

### Documentation

Always document:
- **Why**: Clear reason for block
- **When**: Timestamp and details
- **Who**: User who initiated block
- **Appeal**: Process to request unblock

---

## Monitoring & Metrics

### Track Blocking Activity

```php
// Blocked contacts this week
Contact::where('is_blocked', true)
    ->where('blocked_at', '>', now()->subWeek())
    ->count();

// Most common block reasons
Contact::where('is_blocked', true)
    ->groupBy('block_reason')
    ->selectRaw('block_reason, count(*) as count')
    ->get();

// Unblocked users (successful resolutions)
Contact::whereNotNull('blocked_at')
    ->where('is_blocked', false)
    ->count();
```

### Dashboard Widget

```php
// In Dashboard or Health Check
$blockedCount = Contact::where('is_blocked', true)->count();
$blockedThisWeek = Contact::where('is_blocked', true)
    ->where('blocked_at', '>', now()->subWeek())
    ->count();

return [
    'total_blocked' => $blockedCount,
    'blocked_this_week' => $blockedThisWeek,
    'common_reason' => Contact::where('is_blocked', true)
        ->groupBy('block_reason')
        ->selectRaw('block_reason, count(*) as count')
        ->orderByRaw('count DESC')
        ->first()
        ->block_reason ?? 'Unknown',
];
```

---

## References

- [Meta WhatsApp Cloud API - Block Users](https://developers.facebook.com/docs/whatsapp/cloud-api/block-users/)
- [WhatsApp Business API Documentation](https://developers.facebook.com/docs/whatsapp/)
- [Meta for Developers - WhatsApp Support](https://developers.facebook.com/docs/whatsapp/cloud-api/support/)
