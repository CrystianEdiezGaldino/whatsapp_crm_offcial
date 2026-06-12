@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-background">
    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="mb-12">
            <a href="{{ route('documentation.index') }}" class="text-secondary hover:text-secondary/80 flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined">arrow_back</span>
                <span>Back to Components</span>
            </a>
            <h1 class="text-4xl font-bold text-on-surface mb-4">alert.Value.ToUpper()lert Component</h1>
            <p class="text-lg text-gray-600">Alert notifications</p>
        </div>

        <section class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Documentation</h2>
            <x-card>
                <p class="text-gray-600">Detailed documentation for the alert component coming soon.</p>
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <x-button href="{{ route('documentation.index') }}" variant="secondary">
                        Back to All Components
                    </x-button>
                </div>
            </x-card>
        </section>
    </div>
</div>
@endsection