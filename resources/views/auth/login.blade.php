<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OmniChannel ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'sans-serif'] },
                    colors: {
                        'primary-container': '#131b2e', 'on-primary': '#ffffff',
                        'secondary': '#006d2f', 'on-secondary': '#ffffff',
                        'secondary-container': '#5dfd8a', 'on-secondary-container': '#007232',
                        'surface': '#f7f9fb', 'on-surface': '#191c1e',
                        'outline-variant': '#c6c6cd', 'surface-container-low': '#f2f4f6',
                        'on-surface-variant': '#45464d', 'error': '#ba1a1a',
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-primary min-h-screen flex items-center justify-center font-sans">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-secondary-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl font-bold text-on-secondary-container">W</span>
            </div>
            <h1 class="text-2xl font-bold text-on-primary">OmniChannel ERP</h1>
            <p class="text-on-primary/60 text-sm mt-1">WhatsApp Service Platform</p>
        </div>

        <div class="bg-surface rounded-xl shadow-lg p-8">
            <h2 class="text-lg font-bold text-on-surface mb-6">Entrar na sua conta</h2>

            <form method="POST" action="{{ route('login') }}">
                @csrf
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-error text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-secondary-container focus:border-secondary outline-none transition-all"
                        placeholder="seu@email.com">
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wider">Senha</label>
                    <input type="password" name="password" required
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-secondary-container focus:border-secondary outline-none transition-all"
                        placeholder="Sua senha">
                </div>

                <button type="submit"
                    class="w-full bg-secondary text-on-secondary py-2.5 rounded-lg font-semibold text-sm hover:opacity-90 active:scale-95 transition-all">
                    Entrar
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-600 text-center">
                    Admin: admin@erp.com / password<br>
                    Agente: ana@erp.com / password
                </p>
            </div>
        </div>
    </div>
</body>
</html>
