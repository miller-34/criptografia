<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$user = require_user();
$entryId = positive_int_query('id');

try {
    $entry = $entryId > 0 ? vault_service()->findForUser($entryId, $user['id']) : null;
} catch (Throwable $exception) {
    error_log($exception->__toString());
    $entry = null;
}

if ($entry === null) {
    http_response_code(404);
    $pageTitle = 'Senha não encontrada';
    require dirname(__DIR__) . '/templates/header.php';
    ?>
    <section class="auth-card">
        <h1>Senha não encontrada</h1>
        <p class="muted-copy">Esta credencial não existe ou não pertence ao seu cofre.</p>
        <a class="button-link" href="/area.php">Voltar para o cofre</a>
    </section>
    <?php
    require dirname(__DIR__) . '/templates/footer.php';
    exit;
}

$pageTitle = $entry['service_name'];
require dirname(__DIR__) . '/templates/header.php';
?>
<section class="auth-card credential-detail" aria-labelledby="credential-title">
    <div class="detail-heading">
        <div class="service-icon service-icon-large" aria-hidden="true"><?= escape(strtoupper(substr($entry['service_name'], 0, 1))) ?></div>
        <div>
            <span class="eyebrow">Credencial protegida</span>
            <h1 id="credential-title"><?= escape($entry['service_name']) ?></h1>
        </div>
    </div>

    <dl class="credential-list">
        <div>
            <dt>Usuário ou e-mail</dt>
            <dd>
                <span id="credential-username"><?= escape($entry['username'] !== '' ? $entry['username'] : '—') ?></span>
                <?php if ($entry['username'] !== ''): ?><button class="copy-button" type="button" data-copy="credential-username">Copiar</button><?php endif; ?>
            </dd>
        </div>
        <div>
            <dt>Senha</dt>
            <dd class="password-detail">
                <input id="credential-password" type="password" readonly value="<?= escape($entry['password']) ?>" aria-label="Senha guardada">
                <button class="copy-button" type="button" data-toggle-password="credential-password">Mostrar</button>
                <button class="copy-button" type="button" data-copy-input="credential-password">Copiar</button>
            </dd>
        </div>
        <?php if ($entry['website_url'] !== ''): ?>
            <div>
                <dt>Site</dt>
                <dd><a href="<?= escape($entry['website_url']) ?>" target="_blank" rel="noopener noreferrer"><?= escape($entry['website_url']) ?></a></dd>
            </div>
        <?php endif; ?>
        <?php if ($entry['notes'] !== ''): ?>
            <div>
                <dt>Observações</dt>
                <dd class="notes-content"><?= nl2br(escape($entry['notes'])) ?></dd>
            </div>
        <?php endif; ?>
    </dl>

    <div class="detail-actions">
        <a class="button-link button-secondary-link" href="/senha-editar.php?id=<?= $entry['id'] ?>">Editar</a>
        <form method="post" action="/senha-excluir.php" data-confirm-delete>
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= $entry['id'] ?>">
            <button class="button-danger" type="submit">Excluir</button>
        </form>
    </div>
    <p class="card-link"><a href="/area.php">← Voltar para o cofre</a></p>
</section>
<script src="/assets/vault.js" defer></script>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
