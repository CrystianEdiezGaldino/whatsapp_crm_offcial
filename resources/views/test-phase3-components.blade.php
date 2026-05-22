@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <h1 class="text-3xl font-bold text-on-surface mb-8">Phase 3 Components Test</h1>

    <!-- Layout Components Section -->
    <section class="mb-12">
        <h2 class="text-2xl font-bold text-on-surface mb-6">Layout Components</h2>

        <!-- Component 1: Card -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-on-surface mb-4">1. Card Component (x-card)</h3>
            <x-layout.card title="Card Title" subtitle="This is a card subtitle">
                <p class="text-on-surface">This is the card content. Cards are useful for grouping related information and creating visual separation.</p>
            </x-layout.card>
        </div>

        <!-- Component 2: Modal -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-on-surface mb-4">2. Modal Component (x-modal)</h3>
            <button onclick="document.getElementById('testModal').classList.remove('hidden')" class="px-4 py-2 bg-secondary text-white rounded-lg hover:opacity-90">
                Open Modal
            </button>
            <x-layout.modal id="testModal" title="Modal Title">
                <p class="text-on-surface mb-4">This is the modal content. Click the close button or the dark background to dismiss.</p>
                <p class="text-on-surface-variant text-sm">Modals are great for capturing user attention for important tasks or confirmations.</p>
            </x-layout.modal>
        </div>

        <!-- Component 3: Tabs -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-on-surface mb-4">3. Tabs Component (x-tabs)</h3>
            <x-layout.tabs :tabs="[
                ['id' => 'tab1', 'label' => 'Tab 1', 'active' => true],
                ['id' => 'tab2', 'label' => 'Tab 2', 'active' => false],
                ['id' => 'tab3', 'label' => 'Tab 3', 'active' => false],
            ]">
                <div id="tab1" data-tab class="text-on-surface">
                    <p>Content for Tab 1. This is the first tab content.</p>
                </div>
                <div id="tab2" data-tab class="text-on-surface hidden">
                    <p>Content for Tab 2. This is the second tab content.</p>
                </div>
                <div id="tab3" data-tab class="text-on-surface hidden">
                    <p>Content for Tab 3. This is the third tab content.</p>
                </div>
            </x-layout.tabs>
        </div>

        <!-- Component 4: Accordion -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-on-surface mb-4">4. Accordion Component (x-accordion)</h3>
            <x-layout.accordion :items="[
                [
                    'title' => 'First Item',
                    'content' => 'This is the content for the first accordion item. Accordions help organize large amounts of information.',
                    'active' => true
                ],
                [
                    'title' => 'Second Item',
                    'content' => 'This is the content for the second accordion item. Only one item can be expanded at a time.',
                    'active' => false
                ],
                [
                    'title' => 'Third Item',
                    'content' => 'This is the content for the third accordion item. Click the header to expand or collapse.',
                    'active' => false
                ],
            ]" />
        </div>

        <!-- Component 5: Breadcrumb -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-on-surface mb-4">5. Breadcrumb Component (x-breadcrumb)</h3>
            <x-layout.breadcrumb :items="[
                ['label' => 'Home', 'href' => '/'],
                ['label' => 'Components', 'href' => '/components'],
                ['label' => 'Phase 3', 'href' => '#'],
                ['label' => 'Layout'],
            ]" />
        </div>

        <!-- Component 6: Divider -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-on-surface mb-4">6. Divider Component (x-divider)</h3>
            <p class="text-on-surface mb-4">Before divider</p>
            <x-layout.divider text="OR" />
            <p class="text-on-surface mt-4">After divider</p>
        </div>
    </section>

    <!-- Feedback Components Section -->
    <section>
        <h2 class="text-2xl font-bold text-on-surface mb-6">Feedback Components</h2>

        <!-- Component 7: Alert -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-on-surface mb-4">7. Alert Component (x-alert)</h3>
            <div class="space-y-3">
                <x-feedback.alert type="success" title="Success!" dismissible="true">
                    Your operation was completed successfully.
                </x-feedback.alert>
                <x-feedback.alert type="warning" title="Warning" dismissible="true">
                    Please be careful with this action.
                </x-feedback.alert>
                <x-feedback.alert type="error" title="Error" dismissible="true">
                    An error occurred while processing your request.
                </x-feedback.alert>
                <x-feedback.alert type="info" title="Information" dismissible="true">
                    Here is some helpful information for you.
                </x-feedback.alert>
            </div>
        </div>

        <!-- Component 8: Badge -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-on-surface mb-4">8. Badge Component (x-badge)</h3>
            <div class="flex flex-wrap gap-3">
                <x-feedback.badge type="success" size="sm">Success</x-feedback.badge>
                <x-feedback.badge type="warning" size="md">Warning</x-feedback.badge>
                <x-feedback.badge type="error" size="lg">Error</x-feedback.badge>
                <x-feedback.badge type="info" size="md">Info</x-feedback.badge>
                <x-feedback.badge type="secondary" size="md">Secondary</x-feedback.badge>
            </div>
        </div>

        <!-- Component 9: Progress -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-on-surface mb-4">9. Progress Component (x-progress)</h3>
            <div class="space-y-6">
                <x-feedback.progress value="25" type="primary" label="Primary Progress" />
                <x-feedback.progress value="50" type="success" label="Success Progress" />
                <x-feedback.progress value="75" type="warning" label="Warning Progress" />
                <x-feedback.progress value="100" type="error" label="Error Progress" />
            </div>
        </div>

        <!-- Component 10: Spinner -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold text-on-surface mb-4">10. Spinner Component (x-spinner)</h3>
            <div class="flex gap-8 items-start">
                <div>
                    <p class="text-on-surface-variant text-sm mb-2">Small</p>
                    <x-feedback.spinner size="sm" />
                </div>
                <div>
                    <p class="text-on-surface-variant text-sm mb-2">Medium</p>
                    <x-feedback.spinner size="md" text="Loading..." />
                </div>
                <div>
                    <p class="text-on-surface-variant text-sm mb-2">Large</p>
                    <x-feedback.spinner size="lg" color="success" />
                </div>
                <div>
                    <p class="text-on-surface-variant text-sm mb-2">Extra Large</p>
                    <x-feedback.spinner size="xl" color="warning" />
                </div>
            </div>
        </div>
    </section>

    <div class="mt-12 p-4 bg-surface-container rounded-lg">
        <p class="text-on-surface-variant">All 10 Phase 3 components are displayed above. Test interactive features like modals, tabs, accordions, and dismissible alerts.</p>
    </div>
</div>
@endsection
