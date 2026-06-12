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
                        /* SisZap Design System Colors */
                        'primary': '#1DA85A',
                        'primary-dark': '#15884d',
                        'secondary': '#4353E8',
                        'success': '#22C55E',
                        'warning': '#F59E0B',
                        'error': '#D1383E',

                        /* Grayscale */
                        'gray-50': '#F7F8FB',
                        'gray-100': '#F0F2F7',
                        'gray-200': '#E8EAF0',
                        'gray-400': '#9CA3AF',
                        'gray-600': '#6B7280',
                        'gray-700': '#3A4154',
                        'gray-900': '#14171F',

                        /* Background & Surface */
                        'background': '#FFFFFF',
                        'surface': '#F7F8FB',
                        'surface-card': '#FFFFFF',
                        'on-background': '#14171F',
                        'on-surface': '#14171F',

                        /* Legacy Colors (for backward compatibility) */
                        'primary-container': '#1DA85A',
                        'on-primary': '#FFFFFF',
                        'on-primary-container': '#FFFFFF',
                        'on-primary-fixed': '#FFFFFF',
                        'on-primary-fixed-variant': '#FFFFFF',
                        'primary-fixed': '#E8F8EF',
                        'primary-fixed-dim': '#1DA85A',

                        'secondary-container': '#EEF0FE',
                        'on-secondary': '#FFFFFF',
                        'on-secondary-container': '#4353E8',
                        'secondary-fixed': '#EEF0FE',
                        'secondary-fixed-dim': '#4353E8',
                        'on-secondary-fixed': '#4353E8',
                        'on-secondary-fixed-variant': '#4353E8',

                        'tertiary-fixed': '#E8F8EF',
                        'tertiary-fixed-dim': '#22C55E',
                        'on-tertiary-fixed': '#22C55E',
                        'on-tertiary-fixed-variant': '#22C55E',
                        'tertiary': '#22C55E',
                        'on-tertiary': '#FFFFFF',
                        'tertiary-container': '#E8F8EF',
                        'on-tertiary-container': '#22C55E',

                        'error-container': '#F8D2D4',
                        'on-error': '#FFFFFF',
                        'on-error-container': '#D1383E',

                        'surface-dim': '#E8EAF0',
                        'surface-bright': '#FFFFFF',
                        'surface-container-lowest': '#FFFFFF',
                        'surface-container-low': '#F7F8FB',
                        'surface-container': '#F0F2F7',
                        'surface-container-high': '#E8EAF0',
                        'surface-container-highest': '#E8EAF0',
                        'surface-variant': '#E8EAF0',

                        'on-surface-variant': '#6B7280',
                        'outline': '#9CA3AF',
                        'outline-variant': '#E8EAF0',

                        'inverse-surface': '#14171F',
                        'inverse-on-surface': '#F7F8FB',
                        'inverse-primary': '#1DA85A',

                        'surface-tint': '#1DA85A',
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
            background-color: #FFFFFF;
            color: #14171F;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E8EAF0; border-radius: 10px; }
        aside .sidebar-brand {
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        aside .nav-link-active {
            background: linear-gradient(90deg, rgba(29, 168, 90, 0.12) 0%, rgba(255, 255, 255, 0.08) 100%);
            box-shadow: inset 3px 0 0 0 #1DA85A;
        }
        aside .nav-icon-wrap {
            transition: background-color 0.2s, color 0.2s;
        }
        aside a:hover .nav-icon-wrap,
        aside button:hover .nav-icon-wrap {
            background-color: rgba(255, 255, 255, 0.12);
        }
        aside .nav-link-active .nav-icon-wrap {
            background-color: rgba(29, 168, 90, 0.2);
            color: #1DA85A;
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
        <aside class="w-sidebar h-full bg-gray-900 flex flex-col shrink-0 z-50 border-r border-white/10 shadow-[4px_0_24px_rgba(29,168,90,0.1)]">
            {{-- Brand --}}
            <div class="px-4 pt-6 pb-5 border-b border-white/10 flex flex-col items-center text-center">
                <h1 class="sidebar-brand text-3xl font-black text-on-primary font-headline tracking-tight leading-tight">SisZap</h1>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto custom-scrollbar px-3 py-4 flex flex-col gap-1">
                <p class="px-3 mb-1 text-[10px] font-semibold uppercase tracking-widest text-white/40">Operação</p>
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
                <p class="px-3 mt-4 mb-1 text-[10px] font-semibold uppercase tracking-widest text-white/40">Administração</p>
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
