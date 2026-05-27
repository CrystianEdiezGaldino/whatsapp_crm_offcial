<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'OmniChannel ERP') - WhatsApp Service</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'on-primary': '#ffffff', 'primary-fixed': '#dae2fd',
                        'secondary-container': '#5dfd8a', 'surface-tint': '#565e74',
                        'surface-container-lowest': '#ffffff', 'inverse-on-surface': '#eff1f3',
                        'outline': '#76777d', 'on-secondary-fixed': '#002109',
                        'secondary-fixed-dim': '#3de273', 'on-error': '#ffffff',
                        'secondary-fixed': '#66ff8e', 'primary-fixed-dim': '#bec6e0',
                        'surface-bright': '#f7f9fb', 'error-container': '#ffdad6',
                        'surface-container-low': '#f2f4f6', 'on-surface': '#191c1e',
                        'surface-container-highest': '#e0e3e5', 'on-background': '#191c1e',
                        'surface': '#f7f9fb', 'surface-container': '#eceef0',
                        'tertiary-fixed': '#d3e4fe', 'tertiary-container': '#0b1c30',
                        'on-primary-fixed-variant': '#3f465c', 'error': '#ba1a1a',
                        'inverse-primary': '#bec6e0', 'primary-container': '#131b2e',
                        'on-primary-fixed': '#131b2e', 'on-secondary': '#ffffff',
                        'outline-variant': '#c6c6cd', 'tertiary': '#000000',
                        'secondary': '#006d2f', 'primary': '#000000',
                        'inverse-surface': '#2d3133', 'on-error-container': '#93000a',
                        'on-surface-variant': '#45464d', 'on-secondary-container': '#007232',
                        'on-tertiary': '#ffffff', 'surface-container-high': '#e6e8ea',
                        'on-tertiary-fixed': '#0b1c30', 'background': '#f7f9fb',
                        'on-primary-container': '#7c839b', 'surface-dim': '#d8dadc',
                        'surface-variant': '#e0e3e5', 'on-secondary-fixed-variant': '#005322',
                        'tertiary-fixed-dim': '#b7c8e1',
                    },
                    fontFamily: { 'sans': ['Inter', 'sans-serif'] },
                    spacing: { 'sidebar': '260px' },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-background font-sans text-on-background">
    <div class="flex h-screen w-full">
        @if(!($hideSidebar ?? false))
        <!-- Sidebar -->
        <aside class="w-sidebar h-screen sticky top-0 left-0 bg-primary-container flex flex-col py-6 px-4 border-r border-outline-variant shrink-0 z-50">
            <div class="mb-8 px-2">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-10 h-10 bg-secondary-fixed rounded flex items-center justify-center">
                        <span class="material-symbols-outlined text-on-secondary-fixed">hub</span>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-on-primary">OmniChannel ERP</h1>
                        <p class="text-xs text-on-primary-container/70">WhatsApp Service</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 flex flex-col gap-1">
                @php $current = request()->route()?->getName() ?? ''; @endphp
                <a href="{{ route('dashboard') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ $current === 'dashboard' ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="text-sm">Dashboard</span>
                </a>
                <a href="{{ route('conversations.index') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ str_starts_with($current, 'conversations') ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                    <span class="material-symbols-outlined">chat</span>
                    <span class="text-sm">Chats</span>
                </a>
                <a href="{{ route('contacts.index') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ str_starts_with($current, 'contacts') ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                    <span class="material-symbols-outlined">person_book</span>
                    <span class="text-sm">Contatos</span>
                </a>
                <a href="{{ route('macros.index') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ str_starts_with($current, 'macros') ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                    <span class="material-symbols-outlined">bolt</span>
                    <span class="text-sm">Macros</span>
                </a>

                @if(auth()->check() && auth()->user()->isAdmin())
                <div class="border-t border-on-primary-container/10 my-2 pt-2">
                    <a href="{{ route('admin.sectors.index') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ str_starts_with($current, 'admin.sectors') ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                        <span class="material-symbols-outlined">category</span>
                        <span class="text-sm">Setores</span>
                    </a>
                    <a href="{{ route('admin.agents.index') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ str_starts_with($current, 'admin.agents') ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                        <span class="material-symbols-outlined">people</span>
                        <span class="text-sm">Atendentes</span>
                    </a>
                    <a href="{{ route('admin.distribution.index') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ str_starts_with($current, 'admin.distribution') ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                        <span class="material-symbols-outlined">settings</span>
                        <span class="text-sm">Distribuição</span>
                    </a>
                    <a href="{{ route('admin.sla.dashboard') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ str_starts_with($current, 'admin.sla') ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                        <span class="material-symbols-outlined">schedule</span>
                        <span class="text-sm">SLA</span>
                    </a>
                    <a href="{{ route('admin.tags.index') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ str_starts_with($current, 'admin.tags') ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                        <span class="material-symbols-outlined">label</span>
                        <span class="text-sm">Tags</span>
                    </a>
                    <a href="{{ route('admin.complaints.dashboard') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ str_starts_with($current, 'admin.complaints') ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                        <span class="material-symbols-outlined">warning</span>
                        <span class="text-sm">Reclamações</span>
                    </a>
                    <a href="{{ route('admin.transfers.index') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ str_starts_with($current, 'admin.transfers') ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                        <span class="material-symbols-outlined">compare_arrows</span>
                        <span class="text-sm">Transferências</span>
                    </a>
                    <a href="{{ route('admin.whatsapp.token.index') }}" class="flex items-center gap-4 py-2 px-4 rounded-lg transition-colors duration-200 {{ str_starts_with($current, 'admin.whatsapp') ? 'border-l-2 border-secondary-container bg-surface-container-highest/10 text-on-primary font-semibold' : 'text-on-primary-container/70 hover:text-on-primary hover:bg-primary/50' }}">
                        <span class="material-symbols-outlined">vpn_key</span>
                        <span class="text-sm">Tokens WhatsApp</span>
                    </a>
                </div>
                @endif
            </nav>

            <div class="mt-auto flex flex-col gap-1 border-t border-on-primary-container/10 pt-6">
                <div class="flex items-center gap-3 px-4 py-3 mb-2">
                    <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center text-sm font-bold text-on-primary-fixed">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-on-primary text-sm truncate">{{ auth()->user()->name }}</p>
                        <p class="text-on-primary-container/50 text-xs">{{ auth()->user()->role === 'admin' ? 'Admin' : 'Agente' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-4 py-2 px-4 rounded-lg text-on-primary-container/70 hover:text-on-primary transition-colors">
                        <span class="material-symbols-outlined">logout</span>
                        <span class="text-sm">Sair</span>
                    </button>
                </form>
            </div>
        </aside>
        @endif

        <!-- Main -->
        @if($hideSidebar ?? false)
            @yield('content')
        @else
        <main class="flex-1 flex flex-col min-w-0 bg-surface">
            @yield('content')
        </main>
        @endif
    </div>

    <!-- Feedback Containers -->
    <div id="feedback-toast-container" class="fixed bottom-6 right-6 w-96 max-w-full z-40"></div>
    <div id="feedback-modal-container"></div>

    @stack('scripts')
</body>
</html>
