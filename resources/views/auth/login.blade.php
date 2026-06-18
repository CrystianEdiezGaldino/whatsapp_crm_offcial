<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SisZap</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Figtree', 'sans-serif'] },
                    colors: {
                        'primary': '#1DA85A',
                        'primary-dark': '#15884d',
                        'secondary': '#4353E8',
                        'error': '#D1383E',
                        'gray-50': '#F7F8FB',
                        'gray-100': '#F0F2F7',
                        'gray-200': '#E8EAF0',
                        'gray-400': '#9CA3AF',
                        'gray-600': '#6B7280',
                        'gray-900': '#14171F',
                        'neu-bg': '#E8EBF1',
                        'app-bg': '#E8EBF1',
                    },
                },
            },
        }
    </script>
    @vite(['resources/css/app.css'])
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .login-shell {
            background: #E8EBF1;
            min-height: 100vh;
        }
        .login-brand-panel {
            background: linear-gradient(145deg, #1DA85A 0%, #15884d 55%, #0f6e3a 100%);
            box-shadow: inset -1px 0 0 rgba(255,255,255,0.12);
        }
        @media (max-width: 767px) {
            .login-brand-panel { display: none; }
        }
    </style>
</head>
<body class="login-shell font-sans text-gray-900">
    <div class="min-h-screen flex">
        {{-- Painel marca (desktop) --}}
        <aside class="login-brand-panel hidden md:flex md:w-[42%] lg:w-[45%] flex-col justify-between p-10 lg:p-14 text-white">
            <div>
                <div class="w-12 h-12 rounded-[11px] bg-white/15 backdrop-blur flex items-center justify-center mb-8 ring-1 ring-white/20">
                    <span class="material-symbols-outlined text-[26px]">chat_bubble</span>
                </div>
                <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight mb-3">SisZap</h1>
                <p class="text-white/75 text-base font-semibold max-w-sm leading-relaxed">
                    Plataforma de atendimento WhatsApp do Santa Mônica Clube de Campo.
                </p>
            </div>
            <div class="space-y-3 text-sm font-semibold text-white/70">
                <p class="flex items-center gap-2"><span class="material-symbols-outlined text-lg text-white/90">forum</span> Fila e distribuição em tempo real</p>
                <p class="flex items-center gap-2"><span class="material-symbols-outlined text-lg text-white/90">bolt</span> Macros e fluxos automatizados</p>
                <p class="flex items-center gap-2"><span class="material-symbols-outlined text-lg text-white/90">schedule</span> SLA e métricas de atendimento</p>
            </div>
        </aside>

        {{-- Formulário --}}
        <main class="flex-1 flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-[420px] animate-fadeUp">
                {{-- Brand mobile --}}
                <div class="text-center mb-8 md:hidden">
                    <div class="w-14 h-14 bg-primary rounded-[11px] flex items-center justify-center mx-auto mb-4 shadow-[4px_4px_10px_rgba(29,168,90,0.35)]">
                        <span class="material-symbols-outlined text-white text-3xl">chat_bubble</span>
                    </div>
                    <h1 class="text-2xl font-extrabold tracking-tight">SisZap</h1>
                    <p class="text-gray-400 text-sm font-semibold mt-1">Santa Mônica · WhatsApp</p>
                </div>

                <div class="card-auth">
                    <div class="mb-6">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.1em] text-gray-400 mb-1">Acesso</p>
                        <h2 class="text-[22px] font-extrabold text-gray-900 tracking-tight">Entrar na sua conta</h2>
                        <p class="text-sm text-gray-600 font-medium mt-1">Use seu e-mail corporativo para continuar.</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf

                        @if ($errors->any())
                            <div class="alert-inset-error" role="alert">
                                <span class="material-symbols-outlined text-[18px] shrink-0 mt-0.5">error</span>
                                <span>{{ $errors->first() }}</span>
                            </div>
                        @endif

                        <div class="form-field">
                            <label for="email" class="form-label {{ $errors->has('email') ? 'form-label-error' : '' }}">E-mail</label>
                            <div class="input-inset-wrap {{ $errors->has('email') ? 'is-error' : '' }}">
                                <span class="material-symbols-outlined text-gray-400 text-[18px] shrink-0">mail</span>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    autocomplete="email"
                                    class="input-inset-inner"
                                    placeholder="seu@email.com"
                                >
                            </div>
                            @error('email')
                                <p class="form-error-msg">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-field mb-6">
                            <label for="password" class="form-label {{ $errors->has('password') ? 'form-label-error' : '' }}">Senha</label>
                            <div class="input-inset-wrap {{ $errors->has('password') ? 'is-error' : '' }}">
                                <span class="material-symbols-outlined text-gray-400 text-[18px] shrink-0">lock</span>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    class="input-inset-inner pr-1"
                                    placeholder="Sua senha"
                                >
                                <button type="button" id="togglePassword" class="text-gray-400 hover:text-gray-600 transition-colors p-0.5 shrink-0" aria-label="Mostrar senha">
                                    <span class="material-symbols-outlined text-[20px]" id="togglePasswordIcon">visibility</span>
                                </button>
                            </div>
                            @error('password')
                                <p class="form-error-msg">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn-auth">
                            <span>Entrar</span>
                            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </button>
                    </form>

                    <div class="mt-6 pt-5 border-t border-gray-200/80">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-2 text-center">Contas de teste</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <div class="rounded-xl bg-neu-bg px-3 py-2.5 font-semibold text-gray-600 shadow-[inset_2px_2px_5px_#c4c8d4,inset_-2px_-2px_5px_#ffffff]">
                                <span class="text-gray-400 block text-[10px] uppercase mb-0.5">Admin</span>
                                admin@erp.com
                            </div>
                            <div class="rounded-xl bg-neu-bg px-3 py-2.5 font-semibold text-gray-600 shadow-[inset_2px_2px_5px_#c4c8d4,inset_-2px_-2px_5px_#ffffff]">
                                <span class="text-gray-400 block text-[10px] uppercase mb-0.5">Agente</span>
                                ana@erp.com
                            </div>
                        </div>
                        <p class="form-hint text-center mt-2">Senha padrão: <span class="text-gray-500">password</span></p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (!input || !icon) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.textContent = show ? 'visibility_off' : 'visibility';
        });
    </script>
</body>
</html>
