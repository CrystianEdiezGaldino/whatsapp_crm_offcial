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
            <h1 class="text-4xl font-bold text-on-surface mb-4">Chip Component</h1>
            <p class="text-lg text-gray-600">A reusable chip component for tags, filters, and selections with optional delete functionality.</p>
        </div>

        <!-- Preview Section -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Component Preview</h2>
            <x-card title="Chip Types">
                <div class="flex gap-4 items-center flex-wrap">
                    <x-chip type="default">Default Chip</x-chip>
                    <x-chip type="primary">Primary Chip</x-chip>
                    <x-chip type="secondary">Secondary Chip</x-chip>
                    <x-chip type="error">Error Chip</x-chip>
                </div>
            </x-card>

            <x-card title="Deletable Chips" class="mt-6">
                <div class="flex gap-4 items-center flex-wrap">
                    <x-chip type="default" deletable>Deletable Default</x-chip>
                    <x-chip type="primary" deletable>Deletable Primary</x-chip>
                    <x-chip type="secondary" deletable>Deletable Secondary</x-chip>
                    <x-chip type="error" deletable>Deletable Error</x-chip>
                </div>
            </x-card>

            <x-card title="Chip Use Cases" class="mt-6">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-3">Filter Tags:</p>
                        <div class="flex gap-2 flex-wrap">
                            <x-chip type="primary">Product</x-chip>
                            <x-chip type="primary">Support</x-chip>
                            <x-chip type="primary">Billing</x-chip>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-3">Selected Items (Deletable):</p>
                        <div class="flex gap-2 flex-wrap">
                            <x-chip type="secondary" deletable>React</x-chip>
                            <x-chip type="secondary" deletable>Laravel</x-chip>
                            <x-chip type="secondary" deletable>TailwindCSS</x-chip>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-3">Status/Error Indicators:</p>
                        <div class="flex gap-2 flex-wrap">
                            <x-chip type="error">Invalid Input</x-chip>
                            <x-chip type="error">Required Field</x-chip>
                        </div>
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
                        <thead class="border-b border-gray-200 bg-gray-100-low">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Prop</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Default</th>
                                <th class="px-4 py-3 text-left font-semibold text-on-surface">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">type</td>
                                <td class="px-4 py-3 text-gray-600">string</td>
                                <td class="px-4 py-3 text-gray-600">'default'</td>
                                <td class="px-4 py-3 text-gray-600">Type variant: default, primary, secondary, error</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">deletable</td>
                                <td class="px-4 py-3 text-gray-600">boolean</td>
                                <td class="px-4 py-3 text-gray-600">false</td>
                                <td class="px-4 py-3 text-gray-600">Show delete/close button</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">onDelete</td>
                                <td class="px-4 py-3 text-gray-600">string</td>
                                <td class="px-4 py-3 text-gray-600">null</td>
                                <td class="px-4 py-3 text-gray-600">Custom onclick handler for delete button</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">class</td>
                                <td class="px-4 py-3 text-gray-600">string</td>
                                <td class="px-4 py-3 text-gray-600">''</td>
                                <td class="px-4 py-3 text-gray-600">Additional CSS classes</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">slot</td>
                                <td class="px-4 py-3 text-gray-600">HTML</td>
                                <td class="px-4 py-3 text-gray-600">required</td>
                                <td class="px-4 py-3 text-gray-600">Chip content text or HTML</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </section>

        <!-- Usage Examples -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Usage Examples</h2>

            <x-card title="Basic Chip" subtitle="Simple chip component" class="mb-6">
                <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-chip&gt;Laravel&lt;/x-chip&gt;</code></pre>
            </x-card>

            <x-card title="Chip with Type" subtitle="Using different type variants" class="mb-6">
                <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-chip type="primary"&gt;Primary&lt;/x-chip&gt;
&lt;x-chip type="secondary"&gt;Secondary&lt;/x-chip&gt;
&lt;x-chip type="error"&gt;Error&lt;/x-chip&gt;</code></pre>
            </x-card>

            <x-card title="Deletable Chip" subtitle="With close button">
                <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-chip deletable&gt;Removable&lt;/x-chip&gt;</code></pre>
            </x-card>

            <x-card title="Custom Delete Handler" subtitle="Execute custom function on delete" class="mb-6">
                <pre class="bg-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-chip
    type="secondary"
    deletable
    onDelete="removeTag('laravel')"
&gt;
    Laravel
&lt;/x-chip&gt;</code></pre>
            </x-card>
        </section>

        <!-- Real World Example -->
        <section>
            <h2 class="text-2xl font-bold text-on-surface mb-6">Real World Examples</h2>

            <x-card title="Tag Filter">
                <div>
                    <p class="text-sm text-gray-600 mb-4">Filter by category:</p>
                    <div class="flex gap-2 flex-wrap">
                        <x-chip type="primary">All Tickets</x-chip>
                        <x-chip type="secondary">Open (15)</x-chip>
                        <x-chip type="secondary">Closed (8)</x-chip>
                        <x-chip type="secondary">Pending (3)</x-chip>
                    </div>
                </div>
            </x-card>

            <x-card title="Multi-select Field" class="mt-6">
                <div>
                    <p class="text-sm text-gray-600 mb-4">Selected technologies:</p>
                    <div class="flex gap-2 flex-wrap mb-4">
                        <x-chip type="primary" deletable>React</x-chip>
                        <x-chip type="primary" deletable>Laravel</x-chip>
                        <x-chip type="primary" deletable>TailwindCSS</x-chip>
                    </div>
                    <x-button variant="secondary">Add More</x-button>
                </div>
            </x-card>
        </section>

        <!-- Navigation -->
        <div class="mt-12 flex justify-between">
            <x-button href="{{ route('documentation.component', 'avatar') }}" variant="secondary">
                <span class="material-symbols-outlined">arrow_back</span>
                Previous: Avatar
            </x-button>
            <x-button href="{{ route('documentation.component', 'dropdown') }}" variant="secondary">
                Next: Dropdown
                <span class="material-symbols-outlined">arrow_forward</span>
            </x-button>
        </div>
    </div>
</div>
@endsection
