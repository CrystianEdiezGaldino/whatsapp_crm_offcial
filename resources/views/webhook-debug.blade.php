@extends('layouts.app')

@section('title', 'Webhook Debug')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Webhook Debug</h1>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <p class="text-yellow-800 text-sm">
            <strong>⚠️ Apenas disponível em desenvolvimento</strong><br>
            Use esta página para testar o webhook sem precisar enviar mensagens reais via WhatsApp.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form -->
        <div class="bg-white rounded-lg border border-outline-variant p-6">
            <h2 class="text-lg font-semibold mb-4">Enviar Webhook de Teste</h2>

            <form id="webhookForm" method="POST" action="{{ route('webhook.debug') }}">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Número do Remetente</label>
                        <input type="text" name="from_phone" id="fromPhone"
                            placeholder="+1 555 646 6644"
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg text-sm focus:ring-1 focus:ring-secondary"
                            value="15556466644">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Tipo de Mensagem</label>
                        <select id="messageType" name="message_type"
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg text-sm focus:ring-1 focus:ring-secondary">
                            <option value="text">Texto</option>
                            <option value="image">Imagem</option>
                            <option value="audio">Áudio</option>
                            <option value="video">Vídeo</option>
                            <option value="document">Documento</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Conteúdo da Mensagem</label>
                        <textarea name="message_content" id="messageContent"
                            placeholder="Olá, tudo bem?"
                            rows="3"
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg text-sm focus:ring-1 focus:ring-secondary">Olá, tudo bem?</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">Nome do Contato</label>
                        <input type="text" name="contact_name" id="contactName"
                            placeholder="João Silva"
                            class="w-full px-3 py-2 border border-outline-variant rounded-lg text-sm focus:ring-1 focus:ring-secondary"
                            value="Test User">
                    </div>

                    <button type="submit"
                        class="w-full bg-secondary text-on-secondary px-4 py-2.5 rounded-lg font-semibold text-sm hover:opacity-90 active:scale-95 transition-all">
                        Enviar Webhook de Teste
                    </button>
                </div>
            </form>
        </div>

        <!-- JSON Preview -->
        <div class="bg-white rounded-lg border border-outline-variant p-6">
            <h2 class="text-lg font-semibold mb-4">Preview do JSON</h2>
            <pre id="jsonPreview" class="bg-slate-50 p-4 rounded text-xs overflow-auto max-h-96 text-gray-700">{}</pre>
        </div>
    </div>

    <!-- Response -->
    <div id="responseSection" class="mt-6 hidden">
        <div class="bg-white rounded-lg border border-outline-variant p-6">
            <h2 class="text-lg font-semibold mb-4">Resposta</h2>
            <pre id="responseContent" class="bg-slate-50 p-4 rounded text-xs overflow-auto max-h-64 text-gray-700"></pre>
        </div>
    </div>

    <!-- Instructions -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4">Como Registrar o Webhook na Meta</h3>
        <ol class="list-decimal list-inside space-y-2 text-blue-900">
            <li>Acesse <a href="https://developers.facebook.com/" target="_blank" class="text-blue-600 hover:underline">developers.facebook.com</a></li>
            <li>Vá para seu app > Webhooks</li>
            <li>Configure a URL: <code class="bg-white px-2 py-1 rounded text-xs">{{ config('app.url') }}/webhook</code></li>
            <li>Verify Token deve ser: <code class="bg-white px-2 py-1 rounded text-xs">{{ config('services.whatsapp.verify_token') }}</code></li>
            <li>Subscribe aos eventos: <code class="bg-white px-2 py-1 rounded text-xs">messages, message_template_status_update</code></li>
        </ol>
    </div>
</div>

<script>
const form = document.getElementById('webhookForm');
const messageType = document.getElementById('messageType');
const messageContent = document.getElementById('messageContent');
const fromPhone = document.getElementById('fromPhone');
const contactName = document.getElementById('contactName');
const jsonPreview = document.getElementById('jsonPreview');

function generateWebhookPayload() {
    const type = messageType.value;
    const messageId = 'wamid.' + Date.now();

    const payload = {
        object: 'whatsapp_business_account',
        entry: [{
            id: Math.random().toString(36).substr(2, 9),
            changes: [{
                value: {
                    messaging_product: 'whatsapp',
                    metadata: {
                        display_phone_number: '{{ config("services.whatsapp.phone_number_id") }}',
                        phone_number_id: '{{ config("services.whatsapp.phone_number_id") }}'
                    },
                    contacts: [{
                        profile: { name: contactName.value },
                        wa_id: fromPhone.value.replace(/\D/g, '')
                    }],
                    messages: [{
                        from: fromPhone.value.replace(/\D/g, ''),
                        id: messageId,
                        timestamp: Math.floor(Date.now() / 1000),
                        type: type,
                        [type]: {
                            ...(type === 'text' && { body: messageContent.value }),
                            ...(type !== 'text' && {
                                id: 'media_' + Date.now(),
                                mime_type: getMimeType(type),
                                ...(type === 'image' && { caption: messageContent.value }),
                                ...(type === 'video' && { caption: messageContent.value }),
                                ...(type === 'document' && { caption: messageContent.value, filename: 'document.pdf' }),
                            })
                        }
                    }]
                }
            }]
        }]
    };

    return payload;
}

function getMimeType(type) {
    const types = {
        image: 'image/jpeg',
        video: 'video/mp4',
        audio: 'audio/ogg',
        document: 'application/pdf'
    };
    return types[type] || 'application/octet-stream';
}

function updatePreview() {
    const payload = generateWebhookPayload();
    jsonPreview.textContent = JSON.stringify(payload, null, 2);
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const payload = generateWebhookPayload();

    try {
        const response = await fetch('{{ route("webhook.debug") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        document.getElementById('responseSection').classList.remove('hidden');
        document.getElementById('responseContent').textContent = JSON.stringify(data, null, 2);

        if (data.success) {
            alert('✓ Webhook enviado com sucesso!\nVerifique em Conversas para a nova conversa.');
        } else {
            alert('✗ Erro: ' + data.error);
        }
    } catch (error) {
        document.getElementById('responseSection').classList.remove('hidden');
        document.getElementById('responseContent').textContent = 'Erro: ' + error.message;
        alert('Erro ao enviar webhook: ' + error.message);
    }
});

messageType.addEventListener('change', updatePreview);
messageContent.addEventListener('input', updatePreview);
fromPhone.addEventListener('input', updatePreview);
contactName.addEventListener('input', updatePreview);

updatePreview();
</script>
@endsection
