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
            <h1 class="text-4xl font-bold text-on-surface mb-4">Dropdown Component</h1>
            <p class="text-lg text-on-surface-variant">A reusable dropdown menu component for actions, navigation, and selections with customizable alignment.</p>
        </div>

        <!-- Preview Section -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Component Preview</h2>
            <x-card title="Basic Dropdowns">
                <div class="flex gap-12 flex-wrap">
                    <div>
                        <p class="text-sm text-on-surface-variant mb-4">Left Aligned</p>
                        <x-dropdown
                            id="demo-left"
                            label="Actions"
                            align="left"
                            :items="[
                                ['label' => 'Edit', 'onclick' => 'alert(\"Edit clicked\")'],
                                ['label' => 'Delete', 'onclick' => 'alert(\"Delete clicked\")'],
                                ['divider' => true],
                                ['label' => 'More', 'onclick' => 'alert(\"More clicked\")'],
                            ]"
                        />
                    </div>
                    <div>
                        <p class="text-sm text-on-surface-variant mb-4">Right Aligned</p>
                        <x-dropdown
                            id="demo-right"
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

            <x-card title="Dropdown with Icons" class="mt-6">
                <x-dropdown
                    id="demo-icons"
                    label="More Options"
                    align="left"
                    :items="[
                        ['label' => 'Download', 'onclick' => 'alert(\"Downloading...\")'],
                        ['label' => 'Share', 'onclick' => 'alert(\"Share clicked\")'],
                        ['label' => 'Export', 'onclick' => 'alert(\"Export clicked\")'],
                        ['divider' => true],
                        ['label' => 'Help', 'href' => '#help'],
                    ]"
                />
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
                                <td class="px-4 py-3 font-mono text-secondary">id</td>
                                <td class="px-4 py-3 text-on-surface-variant">string</td>
                                <td class="px-4 py-3 text-on-surface-variant">required</td>
                                <td class="px-4 py-3 text-on-surface-variant">Unique dropdown identifier</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">label</td>
                                <td class="px-4 py-3 text-on-surface-variant">string</td>
                                <td class="px-4 py-3 text-on-surface-variant">'Menu'</td>
                                <td class="px-4 py-3 text-on-surface-variant">Button label/trigger text</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">items</td>
                                <td class="px-4 py-3 text-on-surface-variant">array</td>
                                <td class="px-4 py-3 text-on-surface-variant">[]</td>
                                <td class="px-4 py-3 text-on-surface-variant">Array of menu items (label, href/onclick, divider)</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-secondary">align</td>
                                <td class="px-4 py-3 text-on-surface-variant">string</td>
                                <td class="px-4 py-3 text-on-surface-variant">'left'</td>
                                <td class="px-4 py-3 text-on-surface-variant">Menu alignment: left, right</td>
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

            <x-card title="Item Object Structure" class="mt-6">
                <div class="bg-surface-container p-4 rounded-lg">
                    <pre class="text-sm overflow-x-auto"><code>// Link item
['label' => 'Edit', 'href' => '/path/to/edit']

// Button item
['label' => 'Delete', 'onclick' => 'deleteItem()']

// Divider
['divider' => true]</code></pre>
                </div>
            </x-card>
        </section>

        <!-- Usage Examples -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-on-surface mb-6">Usage Examples</h2>

            <x-card title="Basic Dropdown" subtitle="Simple menu with actions" class="mb-6">
                <pre class="bg-surface-container p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-dropdown
    id="user-menu"
    label="Account"
    :items="[
        ['label' => 'Profile', 'href' => '/profile'],
        ['label' => 'Settings', 'href' => '/settings'],
        ['divider' => true],
        ['label' => 'Logout', 'onclick' => 'logout()'],
    ]"
/&gt;</code></pre>
            </x-card>

            <x-card title="Right-Aligned Dropdown" subtitle="For top-right corner placement" class="mb-6">
                <pre class="bg-surface-container p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-dropdown
    id="admin-menu"
    label="Admin"
    align="right"
    :items="[
        ['label' => 'Dashboard', 'href' => '/admin'],
        ['label' => 'Users', 'href' => '/admin/users'],
        ['label' => 'Settings', 'href' => '/admin/settings'],
    ]"
/&gt;</code></pre>
            </x-card>

            <x-card title="Dropdown with Custom Handlers" subtitle="Using onclick for custom actions">
                <pre class="bg-surface-container p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-dropdown
    id="row-actions"
    label="Actions"
    :items="[
        ['label' => 'Edit', 'onclick' => 'editItem(1)'],
        ['label' => 'Archive', 'onclick' => 'archiveItem(1)'],
        ['divider' => true],
        ['label' => 'Delete', 'onclick' => 'if(confirm(\"Delete?\")) deleteItem(1)'],
    ]"
/&gt;</code></pre>
            </x-card>
        </section>

        <!-- Real World Example -->
        <section>
            <h2 class="text-2xl font-bold text-on-surface mb-6">Real World Examples</h2>

            <x-card title="Table Row Actions">
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-surface-container rounded">
                        <div>
                            <p class="font-semibold text-on-surface">John Doe</p>
                            <p class="text-sm text-on-surface-variant">john@example.com</p>
                        </div>
                        <x-dropdown
                            id="row-menu-1"
                            label=""
                            align="right"
                            :items="[
                                ['label' => 'View', 'onclick' => 'alert(\"View user\")'],
                                ['label' => 'Edit', 'onclick' => 'alert(\"Edit user\")'],
                                ['divider' => true],
                                ['label' => 'Delete', 'onclick' => 'alert(\"Delete user\")'],
                            ]"
                        >
                            <span class="material-symbols-outlined">more_vert</span>
                        </x-dropdown>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-surface-container rounded">
                        <div>
                            <p class="font-semibold text-on-surface">Jane Smith</p>
                            <p class="text-sm text-on-surface-variant">jane@example.com</p>
                        </div>
                        <x-dropdown
                            id="row-menu-2"
                            label=""
                            align="right"
                            :items="[
                                ['label' => 'View', 'onclick' => 'alert(\"View user\")'],
                                ['label' => 'Edit', 'onclick' => 'alert(\"Edit user\")'],
                                ['divider' => true],
                                ['label' => 'Delete', 'onclick' => 'alert(\"Delete user\")'],
                            ]"
                        >
                            <span class="material-symbols-outlined">more_vert</span>
                        </x-dropdown>
                    </div>
                </div>
            </x-card>

            <x-card title="User Profile Menu" class="mt-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <x-avatar name="Sarah C" size="md" status="online" />
                        <div>
                            <p class="font-semibold text-on-surface">Sarah Connor</p>
                            <p class="text-xs text-on-surface-variant">Active now</p>
                        </div>
                    </div>
                    <x-dropdown
                        id="profile-dropdown"
                        label=""
                        align="right"
                        :items="[
                            ['label' => 'View Profile', 'onclick' => 'alert(\"View profile\")'],
                            ['label' => 'Message', 'onclick' => 'alert(\"Open message\")'],
                            ['divider' => true],
                            ['label' => 'Block', 'onclick' => 'alert(\"Block user\")'],
                        ]"
                    >
                        <span class="material-symbols-outlined">more_vert</span>
                    </x-dropdown>
                </div>
            </x-card>
        </section>

        <!-- Navigation -->
        <div class="mt-12 flex justify-between">
            <x-button href="{{ route('documentation.component', 'chip') }}" variant="secondary">
                <span class="material-symbols-outlined">arrow_back</span>
                Previous: Chip
            </x-button>
            <x-button href="{{ route('documentation.index') }}" variant="secondary">
                Back to All Components
            </x-button>
        </div>
    </div>
</div>
@endsection
