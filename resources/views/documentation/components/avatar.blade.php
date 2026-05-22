@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background">
    <div class="max-w-4xl mx-auto px-4 py-12">
        <!-- Header -->
        <div class="mb-12">
            <a href="{{ route('documentation.index') }}" class="text-secondary hover:text-secondary/80 flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined">arrow_back</span>
                <span>Back to Components</span>
            </a>
            <h1 class="text-4xl font-bold text-on-surface mb-4">Avatar Component</h1>
            <p class="text-lg text-on-surface-variant">A reusable avatar component for displaying user profiles with optional status indicators.</p>
        </div>

        <!-- Preview Section -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Component Preview</h2>
            <x-card title="Basic Avatars">
                <div class="flex gap-8 items-center flex-wrap">
                    <div class="text-center">
                        <p class="text-sm text-on-surface-variant mb-3">Small</p>
                        <x-avatar size="sm" name="John" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-on-surface-variant mb-3">Medium</p>
                        <x-avatar size="md" name="Jane" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-on-surface-variant mb-3">Large</p>
                        <x-avatar size="lg" name="Bob" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-on-surface-variant mb-3">Extra Large</p>
                        <x-avatar size="xl" name="Alice" />
                    </div>
                </div>
            </x-card>

            <x-card title="Avatar with Status Indicators" class="mt-6">
                <div class="flex gap-8 items-center flex-wrap">
                    <div class="text-center">
                        <p class="text-sm text-on-surface-variant mb-3">Online</p>
                        <x-avatar name="John" status="online" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-on-surface-variant mb-3">Offline</p>
                        <x-avatar name="Jane" status="offline" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-on-surface-variant mb-3">Away</p>
                        <x-avatar name="Bob" status="away" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-on-surface-variant mb-3">Busy</p>
                        <x-avatar name="Alice" status="busy" />
                    </div>
                </div>
            </x-card>
        </section>

        <!-- Props Table -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Props</h2>
            <x-card>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-outline-variant bg-surface-container-low">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Prop</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Default</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">src</td>
                                <td class="px-4 py-3 text-on-surface-variant">string</td>
                                <td class="px-4 py-3 text-on-surface-variant">null</td>
                                <td class="px-4 py-3 text-on-surface-variant">Image URL for the avatar</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">name</td>
                                <td class="px-4 py-3 text-on-surface-variant">string</td>
                                <td class="px-4 py-3 text-on-surface-variant">null</td>
                                <td class="px-4 py-3 text-on-surface-variant">User name (shown on hover, first letter used as fallback)</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">size</td>
                                <td class="px-4 py-3 text-on-surface-variant">string</td>
                                <td class="px-4 py-3 text-on-surface-variant">'md'</td>
                                <td class="px-4 py-3 text-on-surface-variant">Size variant: sm, md, lg, xl</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">status</td>
                                <td class="px-4 py-3 text-on-surface-variant">string</td>
                                <td class="px-4 py-3 text-on-surface-variant">null</td>
                                <td class="px-4 py-3 text-on-surface-variant">Status indicator: online, offline, away, busy</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">class</td>
                                <td class="px-4 py-3 text-on-surface-variant">string</td>
                                <td class="px-4 py-3 text-on-surface-variant">''</td>
                                <td class="px-4 py-3 text-on-surface-variant">Additional CSS classes</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </section>

        <!-- Usage Examples -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Usage Examples</h2>

            <x-card title="Basic Avatar" subtitle="Display user initials" class="mb-6">
                <pre class="bg-surface-container p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-avatar name="John Doe" /&gt;</code></pre>
            </x-card>

            <x-card title="Avatar with Image" subtitle="Display user photo" class="mb-6">
                <pre class="bg-surface-container p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-avatar
    name="John Doe"
    src="https://example.com/avatar.jpg"
/&gt;</code></pre>
            </x-card>

            <x-card title="Avatar with Status" subtitle="Show online status" class="mb-6">
                <pre class="bg-surface-container p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-avatar
    name="John Doe"
    status="online"
/&gt;</code></pre>
            </x-card>

            <x-card title="Avatar Sizes" subtitle="Different size variants">
                <pre class="bg-surface-container p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-avatar name="John" size="sm" /&gt;
&lt;x-avatar name="John" size="md" /&gt;
&lt;x-avatar name="John" size="lg" /&gt;
&lt;x-avatar name="John" size="xl" /&gt;</code></pre>
            </x-card>
        </section>

        <!-- Real World Example -->
        <section>
            <h2 class="text-2xl font-bold text-on-surface mb-6">Real World Example</h2>
            <x-card title="User Profile Card">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <x-avatar name="Sarah Connor" size="lg" status="online" />
                        <div>
                            <h3 class="font-bold text-on-surface">Sarah Connor</h3>
                            <p class="text-sm text-on-surface-variant">Customer Support Agent</p>
                            <p class="text-xs text-green-600">Active now</p>
                        </div>
                    </div>
                    <x-button variant="primary">Message</x-button>
                </div>
            </x-card>
        </section>

        <!-- Navigation -->
        <div class="mt-12 flex justify-between">
            <x-button href="{{ route('documentation.component', 'spinner') }}" variant="secondary">
                <span class="material-symbols-outlined">arrow_back</span>
                Previous: Spinner
            </x-button>
            <x-button href="{{ route('documentation.component', 'chip') }}" variant="secondary">
                Next: Chip
                <span class="material-symbols-outlined">arrow_forward</span>
            </x-button>
        </div>
    </div>
</div>
@endsection
