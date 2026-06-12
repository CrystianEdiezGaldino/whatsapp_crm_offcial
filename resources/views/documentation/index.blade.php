@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <!-- Header -->
        <div class="mb-12">
            <h1 class="text-4xl font-bold text-on-surface mb-4">Component Library</h1>
            <p class="text-lg text-gray-600">Explore all available components in the WhatsApp ERP System design system.</p>
            <div class="mt-6 flex gap-4">
                <x-button href="#common" variant="primary">
                    Common Components
                </x-button>
                <x-button href="#layout" variant="secondary">
                    Layout Components
                </x-button>
                <x-button href="#feedback" variant="secondary">
                    Feedback Components
                </x-button>
                <x-button href="#bonus" variant="secondary">
                    Bonus Components
                </x-button>
            </div>
        </div>

        <!-- Common Components -->
        <section id="common" class="mb-16">
            <h2 class="text-3xl font-bold text-on-surface mb-8 pb-4 border-b border-gray-200">
                Common Components
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($components as $component)
                    @if($component['category'] === 'Common')
                    <a href="{{ route('documentation.component', $component['path']) }}" class="group">
                        <x-card class="h-full hover:shadow-lg transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-xl font-bold text-on-surface group-hover:text-secondary transition-colors">
                                    {{ $component['name'] }}
                                </h3>
                                <span class="material-symbols-outlined text-secondary group-hover:scale-110 transition-transform">
                                    arrow_forward
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ $component['description'] }}
                            </p>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <span class="inline-block px-3 py-1 rounded-full bg-gray-100 text-on-surface text-xs font-medium">
                                    {{ $component['category'] }}
                                </span>
                            </div>
                        </x-card>
                    </a>
                    @endif
                @endforeach
            </div>
        </section>

        <!-- Layout Components -->
        <section id="layout" class="mb-16">
            <h2 class="text-3xl font-bold text-on-surface mb-8 pb-4 border-b border-gray-200">
                Layout Components
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($components as $component)
                    @if($component['category'] === 'Layout')
                    <a href="{{ route('documentation.component', $component['path']) }}" class="group">
                        <x-card class="h-full hover:shadow-lg transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-xl font-bold text-on-surface group-hover:text-secondary transition-colors">
                                    {{ $component['name'] }}
                                </h3>
                                <span class="material-symbols-outlined text-secondary group-hover:scale-110 transition-transform">
                                    arrow_forward
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ $component['description'] }}
                            </p>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <span class="inline-block px-3 py-1 rounded-full bg-gray-100 text-on-surface text-xs font-medium">
                                    {{ $component['category'] }}
                                </span>
                            </div>
                        </x-card>
                    </a>
                    @endif
                @endforeach
            </div>
        </section>

        <!-- Feedback Components -->
        <section id="feedback" class="mb-16">
            <h2 class="text-3xl font-bold text-on-surface mb-8 pb-4 border-b border-gray-200">
                Feedback Components
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($components as $component)
                    @if($component['category'] === 'Feedback')
                    <a href="{{ route('documentation.component', $component['path']) }}" class="group">
                        <x-card class="h-full hover:shadow-lg transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-xl font-bold text-on-surface group-hover:text-secondary transition-colors">
                                    {{ $component['name'] }}
                                </h3>
                                <span class="material-symbols-outlined text-secondary group-hover:scale-110 transition-transform">
                                    arrow_forward
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ $component['description'] }}
                            </p>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <span class="inline-block px-3 py-1 rounded-full bg-gray-100 text-on-surface text-xs font-medium">
                                    {{ $component['category'] }}
                                </span>
                            </div>
                        </x-card>
                    </a>
                    @endif
                @endforeach
            </div>
        </section>

        <!-- Bonus Components -->
        <section id="bonus" class="mb-16">
            <h2 class="text-3xl font-bold text-on-surface mb-8 pb-4 border-b border-gray-200">
                Bonus Components
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($components as $component)
                    @if($component['category'] === 'Bonus')
                    <a href="{{ route('documentation.component', $component['path']) }}" class="group">
                        <x-card class="h-full hover:shadow-lg transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-xl font-bold text-on-surface group-hover:text-secondary transition-colors">
                                        {{ $component['name'] }}
                                    </h3>
                                    <span class="inline-block px-2 py-1 rounded text-xs font-bold bg-secondary text-on-secondary">
                                        NEW
                                    </span>
                                </div>
                                <span class="material-symbols-outlined text-secondary group-hover:scale-110 transition-transform">
                                    arrow_forward
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ $component['description'] }}
                            </p>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <span class="inline-block px-3 py-1 rounded-full bg-secondary/10 text-secondary text-xs font-medium">
                                    {{ $component['category'] }}
                                </span>
                            </div>
                        </x-card>
                    </a>
                    @endif
                @endforeach
            </div>
        </section>

        <!-- Info Section -->
        <section class="mt-16 bg-gray-100 rounded-xl p-8">
            <h3 class="text-2xl font-bold text-on-surface mb-4">About This Component Library</h3>
            <p class="text-gray-600 mb-4">
                This component library contains 17 reusable Laravel Blade components designed for the WhatsApp ERP System.
                All components follow Material Design 3 principles and are optimized for performance and accessibility.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <div>
                    <h4 class="font-bold text-on-surface mb-2">17 Components</h4>
                    <p class="text-sm text-gray-600">5 common, 6 layout, 5 feedback, and 3 bonus components</p>
                </div>
                <div>
                    <h4 class="font-bold text-on-surface mb-2">Material Design 3</h4>
                    <p class="text-sm text-gray-600">Following modern design system principles</p>
                </div>
                <div>
                    <h4 class="font-bold text-on-surface mb-2">Production Ready</h4>
                    <p class="text-sm text-gray-600">Tested and documented for real-world usage</p>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
