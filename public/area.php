<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$user = require_user();
$error = null;
$entries = [];

try {
    $entries = vault_service()->listForUser($user['id']);
} catch (Throwable $exception) {
    error_log($exception->__toString());
    $error = 'Não foi possível abrir seu cofre. Verifique a configuração da chave de criptografia.';
}

$pageTitle = 'Minhas senhas';
$widePage = true;
require dirname(__DIR__) . '/templates/header.php';
?>
<section class="vault-heading">
    <div>
        <span class="eyebrow">Seu cofre pessoal</span>
        <h1>Minhas senhas</h1>
        <p>Credenciais protegidas por criptografia antes de chegarem ao banco.</p>
    </div>
    <a class="button-link" href="/senha-nova.php">+ Guardar senha</a>
</section>

<?php if ($error !== null): ?>
    <div class="alert alert-error" role="alert"><?= escape($error) ?></div>
<?php elseif ($entries === []): ?>
    <section class="empty-state">
        <div class="empty-icon" aria-hidden="true">🔐</div>
        <h2>Seu cofre está vazio</h2>
        <p>Guarde a primeira credencial para encontrá-la com segurança quando precisar.</p>
        <a class="button-link" href="/senha-nova.php">Guardar primeira senha</a>
    </section>
<?php else: ?>
    <div class="vault-grid">
        <?php foreach ($entries as $entry): ?>
            <article class="vault-card">
                <div class="service-icon" aria-hidden="true">
                    <?= escape(strtoupper(substr($entry['service_name'], 0, 1))) ?>
                </div>
                <div class="vault-card-content">
                    <h2><?= escape($entry['service_name']) ?></h2>
                    <p><?= escape($entry['username'] !== '' ? $entry['username'] : 'Sem usuário informado') ?></p>
                    <?php if ($entry['website_url'] !== ''): ?>
                        <span class="website-label"><?= escape((string) parse_url($entry['website_url'], PHP_URL_HOST)) ?></span>
                    <?php endif; ?>
                </div>
                <a class="card-action" href="/senha.php?id=<?= $entry['id'] ?>" aria-label="Abrir <?= escape($entry['service_name']) ?>">Abrir</a>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="logout-panel" method="post" action="/logout.php">
    <span>Conectado como <?= escape($user['email']) ?></span>
    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
    <button class="text-button" type="submit">Sair</button>
</form>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
