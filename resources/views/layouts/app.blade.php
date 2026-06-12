<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SisZap') - Santa Monica</title>
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
        html, body {
            height: 100%;
            overflow: hidden;
        }
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
        aside .sidebar-brand {
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
        }
        aside .nav-link-active {
            background: linear-gradient(90deg, rgba(139, 249, 178, 0.12) 0%, rgba(255, 255, 255, 0.08) 100%);
            box-shadow: inset 3px 0 0 0 #8bf9b2;
        }
        aside .nav-icon-wrap {
            transition: background-color 0.2s, color 0.2s;
        }
        aside a:hover .nav-icon-wrap,
        aside button:hover .nav-icon-wrap {
            background-color: rgba(255, 255, 255, 0.12);
        }
        aside .nav-link-active .nav-icon-wrap {
            background-color: rgba(139, 249, 178, 0.2);
            color: #8bf9b2;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-background font-sans text-on-background h-full overflow-hidden">
    <div class="flex h-full w-full overflow-hidden">
        @if(!($hideSidebar ?? false))
        @php
            $current = request()->route()?->getName() ?? '';
            $navActive = fn (string|array $match) => is_array($match)
                ? collect($match)->contains(fn ($m) => $m === $current || str_starts_with($current, $m . '.'))
                : ($match === $current || str_starts_with($current, $match . '.'));
            $navItemClass = fn (bool $active) => 'group flex items-center gap-3 py-2.5 px-3 rounded-lg transition-all duration-200 '
                . ($active
                    ? 'nav-link-active text-on-primary font-semibold'
                    : 'text-secondary-fixed-dim hover:text-on-primary hover:bg-white/5');
            $mainNav = [
                ['route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard'],
                ['route' => 'conversations.index', 'match' => 'conversations', 'icon' => 'chat_bubble', 'label' => 'Atendimentos'],
                ['route' => 'contacts.index', 'match' => 'contacts', 'icon' => 'person_book', 'label' => 'Contatos'],
                ['route' => 'macros.index', 'match' => 'macros', 'icon' => 'bolt', 'label' => 'Macros'],
            ];
            $adminNav = [
                ['route' => 'admin.sectors.index', 'match' => 'admin.sectors', 'icon' => 'category', 'label' => 'Setores'],
                ['route' => 'admin.agents.index', 'match' => 'admin.agents', 'icon' => 'people', 'label' => 'Atendentes'],
                ['route' => 'admin.flows.index', 'match' => 'admin.flows', 'icon' => 'account_tree', 'label' => 'Fluxos'],
                ['route' => 'admin.distribution.index', 'match' => 'admin.distribution', 'icon' => 'tune', 'label' => 'Distribuição'],
                ['route' => 'admin.sla.dashboard', 'match' => 'admin.sla', 'icon' => 'schedule', 'label' => 'SLA'],
                ['route' => 'admin.tags.index', 'match' => 'admin.tags', 'icon' => 'label', 'label' => 'Tags'],
                ['route' => 'admin.complaints.dashboard', 'match' => 'admin.complaints', 'icon' => 'warning', 'label' => 'Reclamações'],
                ['route' => 'admin.transfers.index', 'match' => 'admin.transfers', 'icon' => 'compare_arrows', 'label' => 'Transferências'],
                ['route' => 'admin.whatsapp.token.index', 'match' => 'admin.whatsapp.token', 'icon' => 'vpn_key', 'label' => 'Tokens WhatsApp'],
                ['route' => 'admin.whatsapp.numbers.index', 'match' => 'admin.whatsapp.numbers', 'icon' => 'phone', 'label' => 'Números WhatsApp'],
            ];
        @endphp
        <aside class="w-sidebar h-full bg-primary-container flex flex-col shrink-0 z-50 border-r border-white/10 shadow-[4px_0_24px_rgba(0,23,105,0.15)]">
            {{-- Brand --}}
            <div class="px-4 pt-6 pb-5 border-b border-white/10 flex flex-col items-center text-center">
                <h1 class="sidebar-brand text-3xl font-black text-on-primary font-headline tracking-tight leading-tight">SisZap</h1>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto custom-scrollbar px-3 py-4 flex flex-col gap-1">
                <p class="px-3 mb-1 text-[10px] font-semibold uppercase tracking-widest text-on-primary-container/60">Operação</p>
                @foreach($mainNav as $item)
                    @php $active = $navActive($item['match']); @endphp
                    <a href="{{ route($item['route']) }}" class="{{ $navItemClass($active) }}">
                        <span class="nav-icon-wrap w-9 h-9 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
                        </span>
                        <span class="text-sm truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach

                @if(auth()->check() && auth()->user()->isAdmin())
                <p class="px-3 mt-4 mb-1 text-[10px] font-semibold uppercase tracking-widest text-on-primary-container/60">Administração</p>
                @foreach($adminNav as $item)
                    @php $active = $navActive($item['match']); @endphp
                    <a href="{{ route($item['route']) }}" class="{{ $navItemClass($active) }}">
                        <span class="nav-icon-wrap w-9 h-9 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
                        </span>
                        <span class="text-sm truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
                @endif
            </nav>

            {{-- User + logout --}}
            <div class="px-3 pb-5 pt-4 border-t border-white/10 bg-primary/40">
                <div class="flex items-center gap-3 px-2 py-3 mb-2 rounded-xl bg-white/5 ring-1 ring-white/10">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-tertiary-fixed/30 to-secondary-fixed/20 flex items-center justify-center text-sm font-bold text-on-primary ring-2 ring-tertiary-fixed/40 shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-on-primary text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <span class="inline-flex mt-0.5 items-center gap-1 text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 rounded {{ auth()->user()->role === 'admin' ? 'bg-tertiary-fixed/20 text-tertiary-fixed' : 'bg-white/10 text-secondary-fixed-dim' }}">
                            {{ auth()->user()->role === 'admin' ? 'Administrador' : 'Agente' }}
                        </span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="group w-full flex items-center gap-3 py-2.5 px-3 rounded-lg text-secondary-fixed-dim hover:text-on-primary hover:bg-white/5 transition-all duration-200">
                        <span class="nav-icon-wrap w-9 h-9 rounded-lg flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[20px]">logout</span>
                        </span>
                        <span class="text-sm">Sair</span>
                    </button>
                </form>
                <div class="mt-4 pt-3 border-t border-white/10 flex justify-center">
                    <img alt="Santa Monica Logo" class="brightness-0 invert opacity-80 w-20 h-auto" src="https://santamonica.rec.br/wp-content/uploads/2023/02/logo-santa-monica.png" />
                </div>
            </div>
        </aside>
        @endif

        <!-- Main -->
        @if($hideSidebar ?? false)
            @yield('content')
        @else
        <main class="flex-1 flex flex-col min-w-0 min-h-0 overflow-y-auto bg-surface custom-scrollbar">
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
