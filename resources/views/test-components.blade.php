@extends('layouts.app')

@section('title', 'Test Components')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-on-surface mb-8">Component Test Page</h1>

        <!-- Button Component Tests -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-4">Button Component</h2>
            <div class="space-y-4 p-6 bg-gray-100 rounded-lg">
                <div class="flex gap-3 flex-wrap">
                    <x-button variant="primary">Primary Button</x-button>
                    <x-button variant="secondary">Secondary Button</x-button>
                    <x-button variant="danger">Danger Button</x-button>
                    <x-button variant="text">Text Button</x-button>
                </div>
                <div class="flex gap-3 flex-wrap">
                    <x-button variant="primary" disabled>Disabled Primary</x-button>
                    <x-button href="#" variant="secondary">Link Button</x-button>
                </div>
            </div>
        </div>

        <!-- Input Component Tests -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-4">Input Component</h2>
            <div class="space-y-4 p-6 bg-gray-100 rounded-lg">
                <x-input
                    name="test_input"
                    type="text"
                    label="Text Input"
                    placeholder="Enter text..."
                />
                <x-input
                    name="test_email"
                    type="email"
                    label="Email Input"
                    placeholder="your@email.com"
                    required
                />
                <x-input
                    name="test_error"
                    type="text"
                    label="Input with Error"
                    error="This field is invalid"
                    value="Invalid value"
                />
            </div>
        </div>

        <!-- Select Component Tests -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-4">Select Component</h2>
            <div class="space-y-4 p-6 bg-gray-100 rounded-lg">
                <x-select
                    name="category"
                    label="Choose Category"
                    :options="['tech' => 'Technology', 'biz' => 'Business', 'life' => 'Lifestyle']"
                />
                <x-select
                    name="status"
                    label="Status (with error)"
                    :options="['active' => 'Active', 'inactive' => 'Inactive']"
                    error="Please select a valid status"
                    value="invalid"
                />
            </div>
        </div>

        <!-- Textarea Component Tests -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-4">Textarea Component</h2>
            <div class="space-y-4 p-6 bg-gray-100 rounded-lg">
                <x-textarea
                    name="message"
                    label="Message"
                    placeholder="Enter your message..."
                    rows="4"
                />
                <x-textarea
                    name="limited_message"
                    label="Limited Message (max 100 chars)"
                    placeholder="Maximum 100 characters"
                    maxlength="100"
                    rows="3"
                />
                <x-textarea
                    name="error_message"
                    label="Message with Error"
                    error="Message is required"
                    rows="3"
                />
            </div>
        </div>

        <!-- Checkbox Component Tests -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-4">Checkbox Component</h2>
            <div class="space-y-4 p-6 bg-gray-100 rounded-lg">
                <x-checkbox
                    name="terms"
                    value="1"
                    label="I agree with terms and conditions"
                />
                <x-checkbox
                    name="newsletter"
                    value="yes"
                    label="Subscribe to newsletter"
                    checked
                />
                <x-checkbox
                    name="disabled_check"
                    value="1"
                    label="Disabled checkbox"
                    disabled
                />
            </div>
        </div>

        <!-- Form Test -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-4">Complete Form Test</h2>
            <form method="POST" class="p-6 bg-gray-100 rounded-lg space-y-4">
                @csrf

                <x-input
                    name="full_name"
                    label="Full Name"
                    placeholder="John Doe"
                    required
                />

                <x-input
                    name="user_email"
                    type="email"
                    label="Email Address"
                    placeholder="john@example.com"
                    required
                />

                <x-select
                    name="user_category"
                    label="Category"
                    :options="['customer' => 'Customer', 'vendor' => 'Vendor', 'admin' => 'Admin']"
                    required
                />

                <x-textarea
                    name="user_message"
                    label="Message"
                    placeholder="Your message here..."
                    maxlength="500"
                    rows="5"
                    required
                />

                <x-checkbox
                    name="user_terms"
                    value="1"
                    label="I agree with the terms and conditions"
                    required
                />

                <div class="flex gap-3 pt-4">
                    <x-button type="submit" variant="primary">Submit Form</x-button>
                    <x-button type="reset" variant="secondary">Reset Form</x-button>
                    <x-button variant="text">Cancel</x-button>
                </div>
            </form>
        </div>

        <!-- Component Props Reference -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-4">Props Reference</h2>
            <div class="space-y-4 p-6 bg-gray-100 rounded-lg">
                <div>
                    <h3 class="font-bold text-on-surface mb-2">Button Props:</h3>
                    <p class="text-sm text-gray-600">type, variant (primary|secondary|danger|text), disabled, href, class</p>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface mb-2">Input Props:</h3>
                    <p class="text-sm text-gray-600">name, type, label, placeholder, value, error, required, class</p>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface mb-2">Select Props:</h3>
                    <p class="text-sm text-gray-600">name, label, options (array), value, error, required, class</p>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface mb-2">Textarea Props:</h3>
                    <p class="text-sm text-gray-600">name, label, placeholder, value, rows, error, maxlength, required, class</p>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface mb-2">Checkbox Props:</h3>
                    <p class="text-sm text-gray-600">name, type (checkbox|radio), value, label, checked, class</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
