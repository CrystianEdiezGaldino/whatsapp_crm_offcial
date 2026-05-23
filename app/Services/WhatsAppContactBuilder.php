<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Contact Message Builder - vCard Format
 *
 * Send structured business card information via WhatsApp
 * Supports: phone, email, address, organization, birthday, URLs
 */
class WhatsAppContactBuilder
{
    private array $contact = [];
    private array $errors = [];

    /**
     * Create new contact builder
     */
    public static function create(string $firstName, string $lastName = ''): self
    {
        $builder = new self();
        $builder->name($firstName, $lastName);
        return $builder;
    }

    /**
     * Set contact name
     */
    public function name(string $firstName, string $lastName = '', ?string $middleName = null): self
    {
        $this->contact['name'] = [
            'formatted_name' => trim("{$firstName} {$lastName}"),
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];

        if ($middleName) {
            $this->contact['name']['middle_name'] = $middleName;
        }

        return $this;
    }

    /**
     * Add name prefix and suffix
     */
    public function nameTitle(?string $prefix = null, ?string $suffix = null): self
    {
        if (!isset($this->contact['name'])) {
            $this->errors[] = 'Set name first using name() method';
            return $this;
        }

        if ($prefix) {
            $this->contact['name']['prefix'] = $prefix;
        }

        if ($suffix) {
            $this->contact['name']['suffix'] = $suffix;
        }

        return $this;
    }

    /**
     * Add phone number
     * Type: HOME, WORK, MOBILE, IPHONE, MAIN, OTHER
     */
    public function phone(string $phoneNumber, string $type = 'MOBILE'): self
    {
        if (!$this->isValidPhone($phoneNumber)) {
            $this->errors[] = "Invalid phone number format: {$phoneNumber}";
            return $this;
        }

        if (!isset($this->contact['phones'])) {
            $this->contact['phones'] = [];
        }

        $this->contact['phones'][] = [
            'phone' => $phoneNumber,
            'type' => $type,
        ];

        return $this;
    }

    /**
     * Add email address
     * Type: HOME, WORK
     */
    public function email(string $email, string $type = 'WORK'): self
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid email format: {$email}";
            return $this;
        }

        if (!isset($this->contact['emails'])) {
            $this->contact['emails'] = [];
        }

        $this->contact['emails'][] = [
            'email' => $email,
            'type' => $type,
        ];

        return $this;
    }

    /**
     * Add organization details
     */
    public function organization(string $company, ?string $department = null, ?string $title = null): self
    {
        $this->contact['org'] = [
            'company' => $company,
        ];

        if ($department) {
            $this->contact['org']['department'] = $department;
        }

        if ($title) {
            $this->contact['org']['title'] = $title;
        }

        return $this;
    }

    /**
     * Add address
     * Type: HOME, WORK
     */
    public function address(
        string $street,
        string $city,
        string $state,
        string $zip,
        string $country,
        string $countryCode,
        string $type = 'WORK'
    ): self {
        if (!isset($this->contact['addresses'])) {
            $this->contact['addresses'] = [];
        }

        $this->contact['addresses'][] = [
            'street' => $street,
            'city' => $city,
            'state' => $state,
            'zip' => $zip,
            'country' => $country,
            'country_code' => $countryCode,
            'type' => $type,
        ];

        return $this;
    }

    /**
     * Add URL
     * Type: HOME, WORK
     */
    public function url(string $url, string $type = 'WORK'): self
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->errors[] = "Invalid URL format: {$url}";
            return $this;
        }

        if (!isset($this->contact['urls'])) {
            $this->contact['urls'] = [];
        }

        $this->contact['urls'][] = [
            'url' => $url,
            'type' => $type,
        ];

        return $this;
    }

    /**
     * Add birthday
     */
    public function birthday(string $date): self
    {
        // Validate YYYY-MM-DD format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->errors[] = "Birthday must be in YYYY-MM-DD format";
            return $this;
        }

        $this->contact['birthday'] = $date;
        return $this;
    }

    /**
     * Build contact structure for WhatsApp API
     */
    public function build(): ?array
    {
        if (!$this->isValid()) {
            Log::error('[Contact] Build failed - validation errors', [
                'errors' => $this->errors,
            ]);
            return null;
        }

        Log::info('[Contact] Contact built successfully', [
            'name' => $this->contact['name']['formatted_name'] ?? 'N/A',
            'phones' => count($this->contact['phones'] ?? []),
            'emails' => count($this->contact['emails'] ?? []),
            'has_org' => isset($this->contact['org']),
            'has_address' => !empty($this->contact['addresses']),
        ]);

        return $this->contact;
    }

    /**
     * Validate contact builder state
     */
    public function isValid(): bool
    {
        $this->errors = [];

        // Name is required
        if (empty($this->contact['name'])) {
            $this->errors[] = 'Contact must have a name';
        }

        // At least one phone or email recommended
        if (empty($this->contact['phones']) && empty($this->contact['emails'])) {
            Log::warning('[Contact] Contact has no phone or email - consider adding at least one');
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
     * Validate phone format (basic)
     */
    private function isValidPhone(string $phone): bool
    {
        // Accept +country code format or digits only
        return preg_match('/^\+?[1-9]\d{1,14}$/', str_replace(['-', ' ', '(', ')'], '', $phone)) === 1;
    }

    /**
     * Get contact formatted name
     */
    public function getFormattedName(): string
    {
        return $this->contact['name']['formatted_name'] ?? '';
    }

    /**
     * Get all phone numbers
     */
    public function getPhones(): array
    {
        return $this->contact['phones'] ?? [];
    }

    /**
     * Get all emails
     */
    public function getEmails(): array
    {
        return $this->contact['emails'] ?? [];
    }
}
