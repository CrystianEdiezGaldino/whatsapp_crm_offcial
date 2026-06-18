#!/bin/bash
# Deploy notas + etiquetas — rodar no Git Bash LOCAL (não dentro do SSH)
set -e

HOST="cg@192.168.255.5"
REMOTE="/var/www/smcc-whatsapp"
LOCAL="$(cd "$(dirname "$0")" && pwd)"

echo "==> Enviando arquivos para $HOST:$REMOTE"

scp "$LOCAL/app/Http/Controllers/ContactController.php" \
    "$LOCAL/app/Http/Controllers/TagController.php" \
    "$LOCAL/app/Http/Controllers/ConversationController.php" \
    "$LOCAL/app/Http/Controllers/ConversationClaimController.php" \
    "$HOST:$REMOTE/app/Http/Controllers/"

scp "$LOCAL/routes/web.php" \
    "$HOST:$REMOTE/routes/"

scp "$LOCAL/resources/views/components/chat/contact-panel.blade.php" \
    "$HOST:$REMOTE/resources/views/components/chat/"

scp "$LOCAL/resources/views/conversations/index.blade.php" \
    "$HOST:$REMOTE/resources/views/conversations/"

scp "$LOCAL/resources/css/components.css" \
    "$HOST:$REMOTE/resources/css/"

scp "$LOCAL/public/js/helpers/contact-panel.js" \
    "$HOST:$REMOTE/public/js/helpers/"

scp "$LOCAL/tests/test-contact-notes-tags.php" \
    "$LOCAL/tests/test-conversation-dedupe.php" \
    "$HOST:$REMOTE/tests/"

scp "$LOCAL/seed-tags.php" \
    "$HOST:$REMOTE/"

echo "==> Rodando build e cache no servidor"
ssh "$HOST" "cd $REMOTE && \
  php8.3 seed-tags.php && \
  php8.3 artisan view:clear && \
  php8.3 artisan route:clear && \
  npm run build && \
  php8.3 tests/test-contact-notes-tags.php && \
  php8.3 tests/test-conversation-dedupe.php"

echo "==> Deploy concluído!"
