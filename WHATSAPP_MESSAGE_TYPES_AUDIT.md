# WhatsApp Message Types Audit - Meta Documentation Review

**Date**: May 23, 2026  
**Status**: Complete Feature Gap Analysis  
**Sources**:
- [Contact Messages API](https://developers.facebook.com/docs/whatsapp/cloud-api/messages/contacts-messages/)
- [Reaction Messages API](https://developers.facebook.com/docs/whatsapp/cloud-api/messages/reaction-messages/)
- [Authentication Templates Best Practices](https://developers.facebook.com/documentation/business-messaging/whatsapp/templates/authentication-templates/authentication-best-practices/)
- [Media Card Carousel Templates](https://developers.facebook.com/documentation/business-messaging/whatsapp/templates/marketing-templates/media-card-carousel-templates/)

---

## 1. Contact Messages (vCard)

### Current Implementation
❌ **NOT IMPLEMENTED**

### Per Meta Documentation
Contact messages send structured business card information in vCard format via the WhatsApp Cloud API.

#### Contact Data Structure Supported
```json
{
  "addresses": [
    {
      "street": "Street address",
      "city": "City",
      "state": "State",
      "zip": "ZIP code",
      "country": "Country name",
      "country_code": "Country code",
      "type": "HOME|WORK"
    }
  ],
  "birthday": "YYYY-MM-DD",
  "emails": [
    {
      "email": "email@example.com",
      "type": "HOME|WORK"
    }
  ],
  "name": {
    "formatted_name": "John Doe",
    "first_name": "John",
    "last_name": "Doe",
    "middle_name": "Middle",
    "suffix": "Jr",
    "prefix": "Mr"
  },
  "org": {
    "company": "Company Name",
    "department": "Department",
    "title": "Job Title"
  },
  "phones": [
    {
      "phone": "+5511999999999",
      "type": "HOME|WORK|MOBILE|IPHONE|MAIN|OTHER"
    }
  ],
  "urls": [
    {
      "url": "https://example.com",
      "type": "HOME|WORK"
    }
  ]
}
```

#### Use Cases
- Share business contact information
- Send company details with location
- Provide support contact information
- Share team member information

#### Implementation Gap
- No method to send contact messages
- No vCard builder for structured data
- No contact validation

#### Priority
🔴 **HIGH** - Contact sharing is common business need

---

## 2. Reaction Messages (Emoji Reactions)

### Current Implementation
❌ **NOT IMPLEMENTED**

### Per Meta Documentation
Reaction messages are emoji responses to previously received messages using the message WAMID (WhatsApp Message ID).

#### API Specification
```json
{
  "type": "reaction",
  "message_id": "wamid.HBgLMj...",  // WAMID of message to react to
  "emoji": "👍"                      // Single emoji character
}
```

#### Key Requirements
- **message_id**: Must be WAMID format (up to 128 characters)
- **emoji**: Single emoji character (not emoji code)
- **Scope**: Can only react to messages you received from the user
- **Behavior**: Replaces previous reaction on same message

#### Use Cases
- Agent acknowledges message received
- Quick sentiment feedback (👍 👎 ❤️)
- Auto-respond to common message types
- Conversation flow without typing

#### Implementation Gap
- No method to send reaction messages
- No message tracking (no WAMID storage)
- No webhook handling for received message IDs

#### Priority
🟡 **MEDIUM** - Nice-to-have for agent workflow

#### Current Issue
The system stores `wa_message_id` in Message model but doesn't use WAMID format consistently. Need to ensure incoming webhook messages preserve proper WAMID.

---

## 3. Authentication Templates (OTP)

### Current Implementation
⚠️ **PARTIALLY IMPLEMENTED**
- Generic `sendTemplate()` method exists
- No authentication-specific validation
- No OTP code formatting enforcement

### Per Meta Documentation - Critical Requirements

#### Template Format (Fixed by Meta)
Authentication templates use **preset text structure**:
```
"<VERIFICATION_CODE> is your verification code."
```

Optional additions:
- **Security disclaimer**: "For your security, do not share this code"
- **Expiration warning**: "This code expires in <NUM_MINUTES> minutes"

#### Button Options
1. ✅ **One-tap autofill** (RECOMMENDED)
   - Best user experience
   - Native iOS 26+ keyboard suggestion (June 15, 2026)
   - Enabled by default on eligible devices

2. ✅ **Copy code button**
   - User manually copies and pastes code
   - Fallback option

3. ✅ **No button (Zero-tap)**
   - For apps that auto-fill from notifications

#### 2026 Updates (Effective June 15)
- **iOS 26+**: Native OTP autofill from push notifications
- **Keyboard Suggestions**: Enabled by default for all auth templates
- **Impact**: Improved user experience, higher completion rates

#### Mandatory Requirements
```
❌ MUST NOT: Send OTPs as freeform messages
❌ MUST NOT: Use for non-authentication purposes
✅ MUST: Use pre-approved authentication template
✅ MUST: Confirm user's WhatsApp phone before sending
✅ MUST: Keep message focused on OTP only
✅ RECOMMENDED: Code valid for 5-10 minutes
✅ RECOMMENDED: One-time use enforcement
```

#### OTP Best Practices
- **Code Format**: 4-8 character alphanumeric or numeric
- **Validity**: 5-10 minutes recommended
- **Confirmation**: Always confirm user's WhatsApp number matches account
- **Security**: Display disclaimer about not sharing code
- **Message Body**: Keep focused solely on OTP delivery

#### Compliance Impact
```
⚠️ VIOLATION RISK: Sending OTPs without using template
   → Result: Business account suspension
```

#### Implementation Gap
- No authentication template builder
- No OTP code generation/tracking
- No validation that template is pre-approved
- No enforcement of template structure
- No iOS 26+ keyboard suggestion compatibility

#### Priority
🔴 **CRITICAL** - Account suspension risk if misused

---

## 4. Media Card Carousel Templates

### Current Implementation
❌ **NOT IMPLEMENTED**
- No carousel template support
- No multi-card message structure

### Per Meta Documentation

#### Carousel Specification
```json
{
  "type": "carousel",
  "cards": [
    {
      "card_index": 0,
      "header": {
        "type": "IMAGE|VIDEO",
        "media": { "id": "media_id" }
      },
      "body": {
        "text": "Card headline and description (max 160 chars with variables)"
      },
      "footer": {
        "text": "Optional footer text"
      },
      "buttons": [
        {
          "type": "URL|QUICK_REPLY",
          "text": "Button text",
          "url": "https://..." // for URL type
        }
      ]
    }
  ]
}
```

#### Key Constraints
| Feature | Limit |
|---------|-------|
| Cards per carousel | 10 max |
| Buttons per card | 2 max |
| Body text | 160 chars without variables |
| Media type | Same across all cards |
| Button type | Same across all cards |
| Template updates | 1 per day, 10 per month max |

#### Media Requirements
- **Image**: JPG or PNG format
- **Video**: MP4 format
- **Size**: Subject to WhatsApp media limits (16 MB)

#### Use Cases
- Product catalogs with swipeable cards
- Event listings with details and booking
- Promotional campaigns with multiple options
- Service packages comparison
- Gallery of offerings

#### Implementation Gap
- No carousel template builder
- No card validation
- No media format checking
- No update rate limiting
- No template version tracking

#### Priority
🟡 **MEDIUM** - High-value for marketing but requires template management

---

## 📊 Feature Matrix

| Message Type | Status | DB Tracking | Risk Level | Effort |
|---|---|---|---|---|
| Contact Messages | ❌ Not Impl | ❌ No | Low | Medium |
| Reaction Messages | ❌ Not Impl | ⚠️ Partial | Low | Low |
| Auth Templates | ⚠️ Partial | ❌ No | 🔴 Critical | High |
| Carousel Templates | ❌ Not Impl | ❌ No | Medium | High |

---

## 🎯 Implementation Priority

### Phase 1 (CRITICAL - Week 1)
1. **Authentication Templates Enhancement**
   - Add `sendOTP()` method with proper validation
   - Implement OTP code generation and tracking
   - Enforce template structure per Meta requirements
   - Add database migration for OTP tracking
   - **Risk**: Account suspension if not done correctly

### Phase 2 (HIGH - Week 2)
2. **Contact Message Support**
   - Add `sendContact()` method with vCard builder
   - Support all contact fields (phone, email, address, etc)
   - Validation for contact data completeness
   - Database migration for contact log tracking

### Phase 3 (MEDIUM - Week 3)
3. **Reaction Message Support**
   - Add `sendReaction()` method
   - Implement WAMID tracking from webhooks
   - Emoji validation
   - Use case: Agent workflow, customer engagement

### Phase 4 (MEDIUM - Week 4)
4. **Carousel Template Support**
   - Add carousel template builder
   - Media validation (JPG, PNG, MP4)
   - Card structure validation
   - Update rate limiting (1/day, 10/month)
   - Database migration for template versions

---

## 📋 Implementation Checklist

### Contact Messages
```
□ Create ContactBuilder fluent API
□ Support all contact fields (name, phone, email, address, etc)
□ Add sendContact() method to WhatsAppService
□ Implement vCard format generation
□ Add database migration for contact log
□ Create integration tests
□ Document usage with examples
```

### Reaction Messages
```
□ Add sendReaction(messageid, emoji) method
□ Validate emoji format (single character)
□ Update webhook handler to capture WAMID
□ Add database migration for message WAMIDs
□ Implement retry logic for reactions
□ Create integration tests
```

### Authentication Templates (CRITICAL)
```
□ Create OTPTemplateBuilder with enforced structure
□ Implement sendOTP() method
□ Add OTP code generation and validation
□ Create OTP tracking database table
□ Implement OTP expiration (5-10 min)
□ Add security logging (no code in logs)
□ Enforce template pre-approval validation
□ Create account suspension risk warnings in code
□ Document 2026 iOS 26 update implications
□ Create integration tests with rate limiting
```

### Carousel Templates
```
□ Create CarouselBuilder fluent API
□ Support up to 10 cards per carousel
□ Validate media format (JPG, PNG, MP4)
□ Enforce max 2 buttons per card
□ Validate text limits (160 chars + variables)
□ Implement update rate limiting (1/day, 10/month)
□ Create template version tracking
□ Add database migration for carousel templates
□ Create integration tests
□ Document use cases and examples
```

---

## ⚠️ Critical Notes

### Authentication Templates - Account Suspension Risk
```
DO NOT send OTP messages without using the proper authentication template.
Meta will suspend your business account.

✅ CORRECT:
  $otp = WhatsAppService::sendOTP('+5511999999999', $code);

❌ WRONG:
  WhatsAppService::sendText('+5511999999999', 'Your code: 123456');
```

### Message WAMID Tracking
For reactions and status updates, you need to store WAMIDs properly:
```
Current: wa_message_id (may not be WAMID format)
Needed: Ensure WAMID format preservation from webhooks
```

### Rate Limiting for Carousel Templates
```
Template updates are rate-limited by Meta:
- Maximum 1 update per day
- Maximum 10 updates per month
- Violations may prevent template updates
```

---

## 🔍 Next Steps

1. **Immediate**: Review authentication template requirements and add OTP implementation
2. **Week 1**: Add contact message support with vCard builder
3. **Week 2**: Implement reaction messages with WAMID tracking
4. **Week 3**: Add carousel template builder with rate limiting

All features should maintain compliance with Meta's current and 2026 requirements.

---

## 📚 Documentation Links

### Contact Messages
- [Meta Contact Messages](https://developers.facebook.com/docs/whatsapp/cloud-api/messages/contacts-messages/)
- [WhatsApp vCard Format](https://support.whapi.cloud/help-desk/sending/overview-of-other-methods-for-sending/send-contact-vcard)

### Reaction Messages
- [Meta Reaction Messages](https://developers.facebook.com/documentation/business-messaging/whatsapp/messages/reaction-messages/)
- [Reaction Webhook Reference](https://developers.facebook.com/documentation/business-messaging/whatsapp/webhooks/reference/messages/reaction/)

### Authentication Templates
- [Meta Auth Templates](https://developers.facebook.com/documentation/business-messaging/whatsapp/templates/authentication-templates/authentication-templates/)
- [Best Practices Guide](https://developers.facebook.com/documentation/business-messaging/whatsapp/templates/authentication-templates/authentication-best-practices/)
- [WhatsApp OTP Guide](https://business.whatsapp.com/blog/one-time-password-otp-guide)

### Carousel Templates
- [Meta Carousel Templates](https://developers.facebook.com/documentation/business-messaging/whatsapp/templates/marketing-templates/media-card-carousel-templates/)
- [Product Card Carousel Guide](https://docs.360dialog.com/docs/waba-messaging/template-messaging/product-card-carousel-templates)
