{{-- Test/Demo page for bonus components: avatar, chip, dropdown --}}
<div class="min-h-screen bg-background p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-bold text-on-surface mb-12">Bonus Components Demo</h1>

        {{-- Avatar Component Demo --}}
        <section class="mb-16">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Avatar Component</h2>
            <x-card title="Avatar Sizes" subtitle="sm, md, lg, xl variants">
                <div class="flex gap-8 items-center flex-wrap">
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Small (sm)</p>
                        <x-avatar size="sm" name="John Doe" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Medium (md)</p>
                        <x-avatar size="md" name="Jane Smith" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Large (lg)</p>
                        <x-avatar size="lg" name="Bob Johnson" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Extra Large (xl)</p>
                        <x-avatar size="xl" name="Alice Brown" />
                    </div>
                </div>
            </x-card>

            <x-card title="Avatar with Status" subtitle="online, offline, away, busy" class="mt-6">
                <div class="flex gap-8 items-center flex-wrap">
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Online</p>
                        <x-avatar name="John" status="online" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Offline</p>
                        <x-avatar name="Jane" status="offline" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Away</p>
                        <x-avatar name="Bob" status="away" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Busy</p>
                        <x-avatar name="Alice" status="busy" />
                    </div>
                </div>
            </x-card>
        </section>

        {{-- Chip Component Demo --}}
        <section class="mb-16">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Chip Component</h2>
            <x-card title="Chip Types" subtitle="default, primary, secondary, error variants">
                <div class="flex gap-4 items-center flex-wrap">
                    <x-chip type="default">Default Chip</x-chip>
                    <x-chip type="primary">Primary Chip</x-chip>
                    <x-chip type="secondary">Secondary Chip</x-chip>
                    <x-chip type="error">Error Chip</x-chip>
                </div>
            </x-card>

            <x-card title="Deletable Chips" subtitle="Chips with close button" class="mt-6">
                <div class="flex gap-4 items-center flex-wrap">
                    <x-chip type="default" deletable>Deletable Default</x-chip>
                    <x-chip type="primary" deletable>Deletable Primary</x-chip>
                    <x-chip type="secondary" deletable>Deletable Secondary</x-chip>
                    <x-chip type="error" deletable>Deletable Error</x-chip>
                </div>
            </x-card>

            <x-card title="Chip Use Cases" subtitle="Practical examples" class="mt-6">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Tags:</p>
                        <div class="flex gap-2 flex-wrap">
                            <x-chip type="primary">Laravel</x-chip>
                            <x-chip type="primary">Blade</x-chip>
                            <x-chip type="primary">Components</x-chip>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-2">Selected Items:</p>
                        <div class="flex gap-2 flex-wrap">
                            <x-chip type="secondary" deletable>Option 1</x-chip>
                            <x-chip type="secondary" deletable>Option 2</x-chip>
                            <x-chip type="secondary" deletable>Option 3</x-chip>
                        </div>
                    </div>
                </div>
            </x-card>
        </section>

        {{-- Dropdown Component Demo --}}
        <section class="mb-16">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Dropdown Component</h2>
            <x-card title="Basic Dropdown" subtitle="Left and right aligned">
                <div class="flex gap-8 flex-wrap">
                    <div>
                        <p class="text-sm text-gray-600 mb-4">Left Aligned (default)</p>
                        <x-dropdown
                            id="dropdown-left"
                            label="Actions"
                            align="left"
                            :items="[
                                ['label' => 'Edit', 'onclick' => 'alert(\"Edit clicked\")'],
                                ['label' => 'Delete', 'onclick' => 'alert(\"Delete clicked\")'],
                                ['divider' => true],
                                ['label' => 'More Options', 'onclick' => 'alert(\"More Options clicked\")'],
                            ]"
                        />
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-4">Right Aligned</p>
                        <x-dropdown
                            id="dropdown-right"
                            label="Settings"
                            align="right"
                            :items="[
                                ['label' => 'Profile', 'href' => '#'],
                                ['label' => 'Preferences', 'href' => '#'],
                                ['divider' => true],
                                ['label' => 'Logout', 'onclick' => 'alert(\"Logout clicked\")'],
                            ]"
                        />
                    </div>
                </div>
            </x-card>

            <x-card title="Dropdown with Links" subtitle="Anchor-based menu items" class="mt-6">
                <x-dropdown
                    id="dropdown-links"
                    label="Navigation"
                    align="left"
                    :items="[
                        ['label' => 'Dashboard', 'href' => '#dashboard'],
                        ['label' => 'Contacts', 'href' => '#contacts'],
                        ['label' => 'Conversations', 'href' => '#conversations'],
                        ['divider' => true],
                        ['label' => 'Help', 'href' => '#help'],
                    ]"
                />
            </x-card>
        </section>

        {{-- Combined Demo --}}
        <section>
            <h2 class="text-2xl font-bold text-on-surface mb-6">Combined Example: User Profile Card</h2>
            <x-card class="max-w-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <x-avatar name="Sarah Connor" size="lg" status="online" />
                        <div>
                            <h3 class="font-bold text-on-surface">Sarah Connor</h3>
                            <p class="text-sm text-gray-600">Active now</p>
                        </div>
                    </div>
                    <x-dropdown
                        id="profile-menu"
                        label=""
                        align="right"
                        :items="[
                            ['label' => 'View Profile', 'onclick' => 'alert(\"View Profile\")'],
                            ['label' => 'Send Message', 'onclick' => 'alert(\"Send Message\")'],
                            ['divider' => true],
                            ['label' => 'Block', 'onclick' => 'alert(\"Block User\")'],
                        ]"
                    >
                        <span class="material-symbols-outlined">more_vert</span>
                    </x-dropdown>
                </div>
                <p class="text-sm text-gray-600 mb-4">Skills:</p>
                <div class="flex gap-2 flex-wrap">
                    <x-chip type="primary">Customer Support</x-chip>
                    <x-chip type="primary">Sales</x-chip>
                    <x-chip type="secondary">Training</x-chip>
                </div>
            </x-card>
        </section>
    </div>
</div>
