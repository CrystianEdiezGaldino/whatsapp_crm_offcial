@extends('layouts.app')

@section('title', 'Adicionar Número WhatsApp')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-on-surface mb-6">Adicionar Número WhatsApp</h1>

    <div class="bg-white rounded-lg border border-outline-variant p-6">
        <form action="{{ route('admin.whatsapp.numbers.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="text-sm font-semibold text-on-surface block mb-2">Número de Telefone *</label>
                <input type="text" name="phone_number" value="{{ old('phone_number') }}" required placeholder="554188746624" class="w-full border border-outline-variant rounded-lg px-4 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary @error('phone_number') border-error @enderror">
                @error('phone_number')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-on-surface block mb-2">Nome para Exibição *</label>
                <input type="text" name="display_name" value="{{ old('display_name') }}" required placeholder="Suporte Principal" class="w-full border border-outline-variant rounded-lg px-4 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary @error('display_name') border-error @enderror">
                @error('display_name')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-sm font-semibold text-on-surface block mb-2">Access Token do WhatsApp *</label>
                <textarea name="access_token" required class="w-full border border-outline-variant rounded-lg px-4 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary font-mono text-xs @error('access_token') border-error @enderror" rows="4" placeholder="Cola o access token da API do WhatsApp"></textarea>
                @error('access_token')
                <p class="text-error text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">ID da Conta Comercial</label>
                    <input type="text" name="business_account_id" value="{{ old('business_account_id') }}" placeholder="ID da conta" class="w-full border border-outline-variant rounded-lg px-4 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary">
                </div>

                <div>
                    <label class="text-sm font-semibold text-on-surface block mb-2">ID do Número de Telefone</label>
                    <input type="text" name="phone_number_id" value="{{ old('phone_number_id') }}" placeholder="ID do número" class="w-full border border-outline-variant rounded-lg px-4 py-2 text-sm focus:ring-1 focus:ring-secondary focus:border-secondary">
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-xs text-blue-900">
                    <span class="font-semibold">💡 Dica:</span> Você pode encontrar essas informações na <a href="https://developers.facebook.com/docs/whatsapp" target="_blank" class="underline font-semibold">documentação da API do WhatsApp</a>.
                </p>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="bg-primary text-on-primary px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">check</span> Adicionar Número
                </button>
                <a href="{{ route('admin.whatsapp.numbers.index') }}" class="bg-surface-container-low text-on-surface px-6 py-2 rounded-lg font-semibold hover:bg-surface-container transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">close</span> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
