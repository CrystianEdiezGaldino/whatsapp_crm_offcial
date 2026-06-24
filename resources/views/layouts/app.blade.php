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
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
                        'gray-300': '#D7DBE6',
                        'gray-400': '#9CA3AF',
                        'gray-500': '#5A6172',
                        'gray-600': '#6B7280',
                        'gray-700': '#3A4154',
                        'gray-900': '#14171F',

                        /* Design System borders */
                        'border-default': '#E2E5EE',
                        'border-light': '#F2F4F8',

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
                        'app-bg': '#F4F6FA',
                    },
                    fontFamily: {
                        'sans': ['Figtree', 'Inter', 'sans-serif'],
                        'headline': ['Hanken Grotesk', 'sans-serif'],
                        'figtree': ['Figtree', 'sans-serif'],
                    },
                    spacing: { 'sidebar': '236px' },
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
            background-color: #F4F6FA;
            color: #14171F;
            font-family: 'Figtree', Inter, sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        /* SisZap Design System Animations */
        @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(24px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes pop { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @keyframes blink { 0%, 100% { opacity: 0.25; } 50% { opacity: 1; } }
        .animate-fadeUp { animation: fadeUp 0.18s ease; }
        .animate-slideInRight { animation: slideInRight 0.22s ease; }
        .animate-pop { animation: pop 0.15s ease; }
        .animate-blink { animation: blink 1.1s infinite; }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E8EAF0; border-radius: 10px; }

        /* Design scrollbar (wider, matching prototype) */
        .design-scrollbar::-webkit-scrollbar { width: 8px; height: 8px; }
        .design-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .design-scrollbar::-webkit-scrollbar-thumb { background: #D7DBE6; border-radius: 99px; }
        aside.siszap-sidebar .nav-link-active {
            background: #E8F8EF;
            color: #1DA85A;
        }
        aside.siszap-sidebar .nav-link-active .nav-icon-wrap {
            color: #1DA85A;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-app-bg font-sans text-on-background h-full overflow-hidden">
    <div class="flex h-full w-full overflow-hidden">
        @if(!($hideSidebar ?? false))
        @php
            $current = request()->route()?->getName() ?? '';
            $navActive = fn (string|array $match) => is_array($match)
                ? collect($match)->contains(fn ($m) => $m === $current || str_starts_with($current, $m . '.'))
                : ($match === $current || str_starts_with($current, $match . '.'));
            $navItemClass = fn (bool $active) => 'group flex items-center gap-2.5 h-10 px-3 rounded-[10px] transition-all duration-200 text-sm font-semibold '
                . ($active
                    ? 'nav-link-active'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900');
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
        <aside class="siszap-sidebar w-sidebar h-full bg-white flex flex-col shrink-0 z-50 border-r border-gray-200">
            {{-- Brand --}}
            <div class="px-[18px] pt-5 pb-3.5 flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-[11px] bg-primary flex items-center justify-center text-white shrink-0">
                    <svg viewBox="0 0 24 24" width="19" height="19" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg font-extrabold tracking-tight text-gray-900 leading-tight">SisZap</h1>
                    <p class="text-[10.5px] font-semibold text-gray-400 truncate">Santa Mônica Clube de Campo</p>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto design-scrollbar px-3 py-2 flex flex-col gap-0.5">
                <p class="px-3 pt-2.5 pb-1.5 text-[10.5px] font-bold uppercase tracking-[0.09em] text-gray-400">Operação</p>
                @foreach($mainNav as $item)
                    @php $active = $navActive($item['match']); @endphp
                    <a href="{{ route($item['route']) }}" class="{{ $navItemClass($active) }}">
                        <span class="nav-icon-wrap shrink-0">
                            <span class="material-symbols-outlined text-[17px]">{{ $item['icon'] }}</span>
                        </span>
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach

                @if(auth()->check() && auth()->user()->isAdmin())
                <p class="px-3 pt-4 pb-1.5 text-[10.5px] font-bold uppercase tracking-[0.09em] text-gray-400">Administração</p>
                @foreach($adminNav as $item)
                    @php $active = $navActive($item['match']); @endphp
                    <a href="{{ route($item['route']) }}" class="{{ $navItemClass($active) }}">
                        <span class="nav-icon-wrap shrink-0">
                            <span class="material-symbols-outlined text-[17px]">{{ $item['icon'] }}</span>
                        </span>
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
                @endif
            </nav>

            {{-- User + logout --}}
            <div class="p-3 border-t border-gray-100">
                <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-gray-50 mb-2">
                    <div class="relative shrink-0">
                        <div class="w-9 h-9 rounded-full bg-secondary text-white flex items-center justify-center text-sm font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="absolute -right-0.5 -bottom-0.5 w-2.5 h-2.5 rounded-full bg-success border-2 border-gray-50"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13.5px] font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] font-semibold text-gray-400">{{ auth()->user()->role === 'admin' ? 'Administrador' : 'Agente' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Sair" class="p-1 text-gray-400 hover:text-gray-700 transition-colors">
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                        </button>
                    </form>
                </div>
                <div class="pt-2 flex justify-center">
                    <img alt="Santa Monica Logo" class="opacity-70 w-16 h-auto" src="https://santamonica.rec.br/wp-content/uploads/2023/02/logo-santa-monica.png" />
                </div>
            </div>
        </aside>
        @endif

        <!-- Main -->
        @if($hideSidebar ?? false)
            @yield('content')
        @elseif($fullHeight ?? false)
        <main class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden bg-app-bg">
            @yield('content')
        </main>
        @else
        <main class="flex-1 flex flex-col min-w-0 min-h-0 overflow-y-auto bg-app-bg design-scrollbar">
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
