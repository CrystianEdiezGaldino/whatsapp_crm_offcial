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
                    },
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        .login-card {
            box-shadow: 0 20px 50px -12px rgba(0,0,0,0.08), 0 10px 20px -10px rgba(0,0,0,0.06);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .login-card:hover {
            box-shadow: 0 30px 60px -12px rgba(0,0,0,0.12), 0 18px 36px -18px rgba(0,0,0,0.1);
            transform: translateY(-4px);
        }

        .entrance-hidden {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s ease-out, transform 0.8s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .entrance-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .parallax-zoom {
            animation: slowZoom 40s linear infinite alternate;
        }
        @keyframes slowZoom {
            from { transform: scale(1.0); }
            to { transform: scale(1.1); }
        }

        .typewriter-cursor::after {
            content: '|';
            animation: cursorBlink 1s step-end infinite;
            margin-left: 2px;
            color: #ffffff;
        }
        @keyframes cursorBlink {
            from, to { opacity: 1; }
            50% { opacity: 0; }
        }

        .pulse-glow:hover {
            animation: pulseGlow 2s infinite;
        }
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(29, 168, 90, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(29, 168, 90, 0); }
            100% { box-shadow: 0 0 0 0 rgba(29, 168, 90, 0); }
        }

        button { position: relative; overflow: hidden; }
        .ripple {
            position: absolute;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: rippleAnim 0.6s linear;
            pointer-events: none;
            width: 100px; height: 100px;
            margin-left: -50px; margin-top: -50px;
        }
        @keyframes rippleAnim {
            to { transform: scale(4); opacity: 0; }
        }
    </style>
</head>
<body class="bg-[#F4F6FA] font-sans text-gray-900 antialiased overflow-hidden">
<div class="flex min-h-screen">

    <!-- Left Branding Panel -->
    <section class="hidden lg:flex flex-col justify-between w-1/2 p-16 relative overflow-hidden">
        <!-- Background gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary via-primary-dark to-[#0f6e3a]"></div>
        <!-- Decorative blurs -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-secondary/10 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3"></div>

        <!-- Top: Brand -->
        <div class="relative z-10">
            <div class="flex items-center gap-4 entrance-hidden" id="brand-logo">
                <div class="w-12 h-12 rounded-[14px] bg-white/15 backdrop-blur flex items-center justify-center ring-1 ring-white/20">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <h1 class="text-4xl font-extrabold text-white tracking-tight">SisZap</h1>
            </div>

            <div class="mt-8 entrance-hidden" id="brand-tagline">
                <p class="text-2xl font-bold text-white/90 leading-relaxed max-w-md typewriter-cursor" id="typewriter">
                </p>
            </div>
        </div>

        <!-- Bottom: Features -->
        <div class="relative z-10 space-y-4">
            <div class="flex items-center gap-4 group entrance-hidden" id="prop-1">
                <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center group-hover:bg-white/20 transition-all duration-300 group-hover:scale-110">
                    <span class="material-symbols-outlined text-white/90 text-xl">forum</span>
                </div>
                <div>
                    <p class="text-white font-bold text-sm">Atendimento em tempo real</p>
                    <p class="text-white/60 text-xs font-medium">Fila e distribuição automática de conversas</p>
                </div>
            </div>
            <div class="flex items-center gap-4 group entrance-hidden" id="prop-2">
                <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center group-hover:bg-white/20 transition-all duration-300 group-hover:scale-110">
                    <span class="material-symbols-outlined text-white/90 text-xl">bolt</span>
                </div>
                <div>
                    <p class="text-white font-bold text-sm">Macros e fluxos</p>
                    <p class="text-white/60 text-xs font-medium">Respostas rápidas e automação inteligente</p>
                </div>
            </div>
            <div class="flex items-center gap-4 group entrance-hidden" id="prop-3">
                <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center group-hover:bg-white/20 transition-all duration-300 group-hover:scale-110">
                    <span class="material-symbols-outlined text-white/90 text-xl">schedule</span>
                </div>
                <div>
                    <p class="text-white font-bold text-sm">SLA e métricas</p>
                    <p class="text-white/60 text-xs font-medium">Controle de performance e qualidade</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Right Login Panel -->
    <section class="flex-1 flex flex-col items-center justify-center p-6 sm:p-10 relative">
        <!-- Mobile brand -->
        <div class="text-center mb-8 lg:hidden entrance-hidden" id="mobile-brand">
            <div class="w-14 h-14 bg-primary rounded-[14px] flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary/25">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight">SisZap</h1>
            <p class="text-gray-400 text-sm font-semibold mt-1">Santa Mônica Clube de Campo</p>
        </div>

        <!-- Login Card -->
        <div class="w-full max-w-[440px] entrance-hidden" id="login-container">
            <div class="bg-white login-card p-10 border border-gray-200/60 rounded-3xl">
                <!-- Header -->
                <div class="mb-7">
                    <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Entrar na sua conta</h2>
                    <p class="text-sm text-gray-500 font-medium mt-1.5">Use seu e-mail corporativo para continuar.</p>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('login') }}" novalidate class="space-y-5">
                    @csrf

                    @if ($errors->any())
                    <div class="flex items-start gap-2.5 p-3.5 bg-red-50 border border-red-200/60 rounded-xl text-sm text-error font-medium" role="alert">
                        <span class="material-symbols-outlined text-[18px] shrink-0 mt-0.5">error</span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                    @endif

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1" for="email">E-mail</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[18px] text-gray-400">mail</span>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="seu@email.com.br"
                                class="w-full pl-11 pr-4 py-[14px] bg-[#F7F8FB] border {{ $errors->has('email') ? 'border-error' : 'border-[#E2E5EE]' }} rounded-xl text-sm text-gray-900 font-medium placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all duration-300"
                            >
                        </div>
                        @error('email')
                        <p class="text-error text-xs font-semibold mt-1.5 ml-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1" for="password">Senha</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[18px] text-gray-400">lock</span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full pl-11 pr-12 py-[14px] bg-[#F7F8FB] border {{ $errors->has('password') ? 'border-error' : 'border-[#E2E5EE]' }} rounded-xl text-sm text-gray-900 font-medium placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all duration-300"
                            >
                            <button type="button" id="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary hover:scale-110 transition-all" aria-label="Mostrar senha">
                                <span class="material-symbols-outlined text-[20px]" id="togglePasswordIcon">visibility</span>
                            </button>
                        </div>
                        @error('password')
                        <p class="text-error text-xs font-semibold mt-1.5 ml-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-[14px] rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all pulse-glow text-sm">
                        <span>Entrar</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </form>

                <!-- Test accounts -->
                <div class="mt-7 pt-6 border-t border-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3 text-center">Acesso rápido</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="fillLogin('admin@erp.com')" class="p-3 bg-[#F7F8FB] rounded-xl border border-transparent hover:border-primary/30 transition-all text-left group">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Admin</p>
                            <p class="text-xs font-semibold text-gray-700 group-hover:text-primary transition-colors">admin@erp.com</p>
                        </button>
                        <button type="button" onclick="fillLogin('ana@erp.com')" class="p-3 bg-[#F7F8FB] rounded-xl border border-transparent hover:border-primary/30 transition-all text-left group">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Agente</p>
                            <p class="text-xs font-semibold text-gray-700 group-hover:text-primary transition-colors">ana@erp.com</p>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-400 font-medium text-center mt-2.5">Senha: <span class="text-gray-500 font-semibold">password</span></p>
                </div>
            </div>

            <!-- Footer -->
            <p class="mt-6 text-center text-xs text-gray-400 font-medium">
                Santa Mônica Clube de Campo &middot; SisZap v2.0
            </p>
        </div>
    </section>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');
    if (!input || !icon) return;
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    icon.textContent = show ? 'visibility_off' : 'visibility';
});

// Fill login shortcut
function fillLogin(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password';
    document.getElementById('email').focus();
}

// Ripple effect
document.querySelectorAll('button[type="submit"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        ripple.className = 'ripple';
        ripple.style.left = (e.clientX - btn.getBoundingClientRect().left) + 'px';
        ripple.style.top = (e.clientY - btn.getBoundingClientRect().top) + 'px';
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
    });
});

// Entrance animations
document.addEventListener('DOMContentLoaded', () => {
    const els = [
        { id: 'brand-logo', delay: 100 },
        { id: 'brand-tagline', delay: 300 },
        { id: 'prop-1', delay: 600 },
        { id: 'prop-2', delay: 750 },
        { id: 'prop-3', delay: 900 },
        { id: 'mobile-brand', delay: 100 },
        { id: 'login-container', delay: 200 },
    ];
    els.forEach(({ id, delay }) => {
        const el = document.getElementById(id);
        if (el) setTimeout(() => el.classList.add('entrance-visible'), delay);
    });

    // Typewriter effect
    const phrases = [
        'Atendimento WhatsApp inteligente.',
        'Conecte sua equipe aos clientes.',
        'Gerencie conversas com eficiência.',
    ];
    const el = document.getElementById('typewriter');
    if (!el) return;
    let phraseIdx = 0, charIdx = 0, isDeleting = false;

    function type() {
        const current = phrases[phraseIdx];
        if (!isDeleting) {
            el.textContent = current.substring(0, charIdx + 1);
            charIdx++;
            if (charIdx === current.length) {
                setTimeout(() => { isDeleting = true; type(); }, 2500);
                return;
            }
            setTimeout(type, 50);
        } else {
            el.textContent = current.substring(0, charIdx - 1);
            charIdx--;
            if (charIdx === 0) {
                isDeleting = false;
                phraseIdx = (phraseIdx + 1) % phrases.length;
                setTimeout(type, 400);
                return;
            }
            setTimeout(type, 30);
        }
    }
    setTimeout(type, 1000);
});
</script>
</body>
</html>
