<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SisChat') - Santa Monica</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'on-secondary-fixed-variant': '#354769',
                        'on-tertiary-fixed-variant': '#00522d',
                        'secondary-fixed-dim': '#b5c6f0',
                        'on-primary': '#ffffff',
                        'surface-container-highest': '#e1e3e4',
                        'tertiary-fixed': '#8bf9b2',
                        'on-background': '#191c1d',
                        'secondary-fixed': '#d8e2ff',
                        'on-secondary-fixed': '#061b3c',
                        'on-primary-fixed': '#001159',
                        'surface-dim': '#d9dadb',
                        'on-surface-variant': '#454652',
                        'on-surface': '#191c1d',
                        'background': '#f8f9fa',
                        'primary-fixed': '#dee1ff',
                        'on-primary-fixed-variant': '#283d9d',
                        'error-container': '#ffdad6',
                        'primary-fixed-dim': '#b9c3ff',
                        'surface-container': '#edeeef',
                        'primary': '#001769',
                        'tertiary-fixed-dim': '#6edc98',
                        'tertiary': '#002913',
                        'on-tertiary': '#ffffff',
                        'inverse-primary': '#b9c3ff',
                        'tertiary-container': '#004122',
                        'surface-tint': '#4256b7',
                        'inverse-surface': '#2e3132',
                        'surface-container-lowest': '#ffffff',
                        'surface': '#f8f9fa',
                        'on-secondary': '#ffffff',
                        'secondary': '#4d5e83',
                        'outline-variant': '#c5c5d4',
                        'on-error-container': '#93000a',
                        'on-primary-container': '#879aff',
                        'secondary-container': '#c3d4ff',
                        'on-secondary-container': '#4a5b80',
                        'primary-container': '#142c8e',
                        'on-tertiary-fixed': '#00210f',
                        'on-error': '#ffffff',
                        'surface-variant': '#e1e3e4',
                        'surface-container-high': '#e7e8e9',
                        'surface-bright': '#f8f9fa',
                        'on-tertiary-container': '#46b575',
                        'inverse-on-surface': '#f0f1f2',
                        'surface-container-low': '#f3f4f5',
                        'outline': '#757684',
                        'error': '#ba1a1a'
                    },
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                        'headline': ['Hanken Grotesk', 'sans-serif'],
                    },
                    spacing: { 'sidebar': '260px' },
                },
            },
        }
    </script>
    <style>
        body {
            background-color: #f8f9fa;
            color: #191c1d;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c5c5d4; border-radius: 10px; }
        aside h1 {
            text-shadow: 0px 1px 1px rgba(0, 0, 0, 0.4);
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-background font-sans text-on-background">
    <div class="flex h-screen w-full">
        @if(!($hideSidebar ?? false))
        <!-- Sidebar -->
        <aside class="w-sidebar h-full sticky top-0 left-0 bg-primary flex flex-col py-6 px-4 border-r border-outline-variant shrink-0 z-50">
            <div class="mb-8 flex flex-col items-center text-center">
                <img alt="Santa Monica Logo" class="brightness-0 invert mb-3" src="https://santamonica.rec.br/wp-content/uploads/2023/02/logo-santa-monica.png" />
                <h1 class="text-2xl font-black text-on-primary font-headline tracking-tight">SisChat</h1>
            </div>

            <nav class="flex-1 flex flex-col gap-1">
                @php $current = request()->route()?->getName() ?? ''; @endphp
                <a href="{{ route('dashboard') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ $current === 'dashboard' ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="text-sm">Dashboard</span>
                </a>
                <a href="{{ route('conversations.index') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'conversations') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                    <span class="material-symbols-outlined">chat_bubble</span>
                    <span class="text-sm">Atendimentos</span>
                </a>
                <a href="{{ route('contacts.index') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'contacts') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                    <span class="material-symbols-outlined">person_book</span>
                    <span class="text-sm">Contatos</span>
                </a>
                <a href="{{ route('macros.index') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'macros') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                    <span class="material-symbols-outlined">bolt</span>
                    <span class="text-sm">Macros</span>
                </a>

                @if(auth()->check() && auth()->user()->isAdmin())
                <div class="border-t border-white/10 my-2 pt-2">
                    <a href="{{ route('admin.sectors.index') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'admin.sectors') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                        <span class="material-symbols-outlined">category</span>
                        <span class="text-sm">Setores</span>
                    </a>
                    <a href="{{ route('admin.agents.index') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'admin.agents') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                        <span class="material-symbols-outlined">people</span>
                        <span class="text-sm">Atendentes</span>
                    </a>
                    <a href="{{ route('admin.flows.index') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'admin.flows') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                        <span class="material-symbols-outlined">account_tree</span>
                        <span class="text-sm">Fluxos</span>
                    </a>
                    <a href="{{ route('admin.distribution.index') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'admin.distribution') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                        <span class="material-symbols-outlined">settings</span>
                        <span class="text-sm">Distribuição</span>
                    </a>
                    <a href="{{ route('admin.sla.dashboard') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'admin.sla') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                        <span class="material-symbols-outlined">schedule</span>
                        <span class="text-sm">SLA</span>
                    </a>
                    <a href="{{ route('admin.tags.index') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'admin.tags') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                        <span class="material-symbols-outlined">label</span>
                        <span class="text-sm">Tags</span>
                    </a>
                    <a href="{{ route('admin.complaints.dashboard') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'admin.complaints') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                        <span class="material-symbols-outlined">warning</span>
                        <span class="text-sm">Reclamações</span>
                    </a>
                    <a href="{{ route('admin.transfers.index') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'admin.transfers') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                        <span class="material-symbols-outlined">compare_arrows</span>
                        <span class="text-sm">Transferências</span>
                    </a>
                    <a href="{{ route('admin.whatsapp.token.index') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'admin.whatsapp.token') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                        <span class="material-symbols-outlined">vpn_key</span>
                        <span class="text-sm">Tokens WhatsApp</span>
                    </a>
                    <a href="{{ route('admin.whatsapp.numbers.index') }}" class="flex items-center gap-4 py-3 px-4 rounded transition-colors duration-200 {{ str_starts_with($current, 'admin.whatsapp.numbers') ? 'border-l-4 border-tertiary-fixed bg-white/10 text-on-primary font-semibold' : 'text-secondary-fixed hover:bg-white/5' }}">
                        <span class="material-symbols-outlined">phone</span>
                        <span class="text-sm">Números WhatsApp</span>
                    </a>
                </div>
                @endif
            </nav>

            <div class="mt-auto flex flex-col gap-1 border-t border-white/10 pt-6">
                <div class="flex items-center gap-3 px-4 py-3 mb-2">
                    <div class="w-10 h-10 rounded-full bg-on-primary/20 flex items-center justify-center text-sm font-bold text-on-primary">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-on-primary text-sm truncate">{{ auth()->user()->name }}</p>
                        <p class="text-on-primary/50 text-xs">{{ auth()->user()->role === 'admin' ? 'Admin' : 'Agente' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-4 py-3 px-4 rounded text-secondary-fixed hover:bg-white/5 transition-colors">
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
