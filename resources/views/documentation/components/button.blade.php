@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background">
    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="mb-12">
            <a href="{{ route('documentation.index') }}" class="text-secondary hover:text-secondary/80 flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined">arrow_back</span>
                <span>Back to Components</span>
            </a>
            <h1 class="text-4xl font-bold text-on-surface mb-4">Button Component</h1>
            <p class="text-lg text-on-surface-variant">A versatile button component with multiple variants and states.</p>
        </div>

        <section class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Component Preview</h2>
            <x-card title="Button Variants">
                <div class="flex gap-4 flex-wrap items-center">
                    <x-button variant="primary">Primary Button</x-button>
                    <x-button variant="secondary">Secondary Button</x-button>
                    <x-button variant="danger">Danger Button</x-button>
                    <x-button variant="text">Text Button</x-button>
                </div>
            </x-card>

            <x-card title="Disabled State" class="mt-6">
                <div class="flex gap-4 flex-wrap items-center">
                    <x-button variant="primary" disabled>Disabled Primary</x-button>
                    <x-button variant="secondary" disabled>Disabled Secondary</x-button>
                </div>
            </x-card>
        </section>

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
                                <td class="px-4 py-3 font-mono text-secondary">type</td>
                                <td class="px-4 py-3 text-on-surface-variant">string</td>
                                <td class="px-4 py-3 text-on-surface-variant">'button'</td>
                                <td class="px-4 py-3 text-on-surface-variant">Button type: button, submit, reset</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">variant</td>
                                <td class="px-4 py-3 text-on-surface-variant">string</td>
                                <td class="px-4 py-3 text-on-surface-variant">'primary'</td>
                                <td class="px-4 py-3 text-on-surface-variant">Style variant: primary, secondary, danger, text</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">disabled</td>
                                <td class="px-4 py-3 text-on-surface-variant">boolean</td>
                                <td class="px-4 py-3 text-on-surface-variant">false</td>
                                <td class="px-4 py-3 text-on-surface-variant">Disable the button</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">href</td>
                                <td class="px-4 py-3 text-on-surface-variant">string</td>
                                <td class="px-4 py-3 text-on-surface-variant">null</td>
                                <td class="px-4 py-3 text-on-surface-variant">Convert to link element with href</td>
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

        <div class="mt-12 flex justify-between">
            <x-button href="{{ route('documentation.index') }}" variant="secondary">
                Back to All Components
            </x-button>
            <x-button href="{{ route('documentation.component', 'input') }}" variant="secondary">
                Next: Input
                <span class="material-symbols-outlined">arrow_forward</span>
            </x-button>
        </div>
    </div>
</div>
@endsection
