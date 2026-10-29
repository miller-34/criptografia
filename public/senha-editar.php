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
    exit('Credencial não encontrada.');
}

$error = null;
$formData = [
    'service_name' => $entry['service_name'],
    'website_url' => $entry['website_url'],
    'username' => $entry['username'],
    'notes' => $entry['notes'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($formData) as $field) {
        $formData[$field] = post_string($field);
    }

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $error = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } else {
        try {
            vault_service()->update($entryId, $user['id'], $formData + [
                'password' => post_string('password'),
            ]);
            flash('success', 'Credencial atualizada.');
            redirect('/senha.php?id=' . $entryId);
        } catch (DomainException $exception) {
            $error = $exception->getMessage();
        } catch (Throwable $exception) {
            error_log($exception->__toString());
            $error = 'Não foi possível atualizar a credencial.';
        }
    }
}

$pageTitle = 'Editar senha';
require dirname(__DIR__) . '/templates/header.php';
?>
<section class="auth-card" aria-labelledby="edit-password-title">
    <div class="card-heading">
        <span class="eyebrow">Editar credencial</span>
        <h1 id="edit-password-title"><?= escape($entry['service_name']) ?></h1>
        <p>Deixe a senha em branco se não quiser alterá-la.</p>
    </div>
    <?php if ($error !== null): ?>
        <div class="alert alert-error" role="alert"><?= escape($error) ?></div>
    <?php endif; ?>
    <?php
    $passwordRequired = false;
    $submitLabel = 'Salvar alterações';
    require dirname(__DIR__) . '/templates/vault-form.php';
    ?>
    <p class="card-link"><a href="/senha.php?id=<?= $entryId ?>">← Cancelar</a></p>
</section>
<script src="/assets/vault.js" defer></script>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
