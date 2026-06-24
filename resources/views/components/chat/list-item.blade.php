@props([
    'href',
    'contact',
    'conversation',
    'active' => false,
    'pending' => false,
    'resolved' => false,
])

@php
    $tags = $conversation->tags;
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'chat-list-item',
        'chat-list-item--active' => $active,
        'chat-list-item--resolved' => $resolved,
    ]) }}
    data-conversation-id="{{ $conversation->id }}"
    data-chat-name="{{ strtolower($contact->name) }}"
    data-chat-phone="{{ $contact->phone }}"
>
    <div class="chat-list-item__row">
        <x-common.contact-avatar :initials="$contact->initials" :variant="$pending ? 'pending' : 'default'" />
        <div class="chat-list-item__body">
            <div class="chat-list-item__top">
                <h3 class="chat-list-item__name">{{ $contact->name }}</h3>
                <time class="chat-list-item__time">{{ $conversation->last_message_at?->locale('pt_BR')->diffForHumans(short: true) ?? '???' }}</time>
            </div>
            <div class="chat-list-item__preview-row">
                <p class="chat-list-item__preview">{{ $conversation->lastMessage?->content ?? 'Sem mensagens' }}</p>
                @if($pending)
                    <span class="chat-list-item__unread" title="Aguardando">!</span>
                @endif
            </div>
            <div class="chat-list-item__tags">
                @foreach($tags as $tag)
                    <span
                        class="chat-list-chip chat-list-chip--tag"
                        style="--tag-color: {{ $tag->color }}"
                    >{{ $tag->name }}</span>
                @endforeach
                @if($pending)
                    <span class="chat-list-chip chat-list-chip--warning">Aguardando</span>
                @elseif($resolved)
                    <span class="chat-list-chip chat-list-chip--muted">Encerrado</span>
                @endif
            </div>
        </div>
    </div>
</a>

