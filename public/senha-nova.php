<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$user = require_user();
$error = null;
$formData = [
    'service_name' => '',
    'website_url' => '',
    'username' => '',
    'notes' => '',
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
            $entryId = vault_service()->create($user['id'], $formData + [
                'password' => post_string('password'),
            ]);
            flash('success', 'Senha guardada com segurança.');
            redirect('/senha.php?id=' . $entryId);
        } catch (DomainException $exception) {
            $error = $exception->getMessage();
        } catch (Throwable $exception) {
            error_log($exception->__toString());
            $error = 'Não foi possível guardar a senha agora.';
        }
    }
}

$pageTitle = 'Guardar senha';
require dirname(__DIR__) . '/templates/header.php';
?>
<section class="auth-card" aria-labelledby="new-password-title">
    <div class="card-heading">
        <span class="eyebrow">Nova credencial</span>
        <h1 id="new-password-title">Guardar senha</h1>
        <p>Os campos sensíveis serão criptografados antes de serem salvos.</p>
    </div>
    <?php if ($error !== null): ?>
        <div class="alert alert-error" role="alert"><?= escape($error) ?></div>
    <?php endif; ?>
    <?php
    $passwordRequired = true;
    $submitLabel = 'Criptografar e guardar';
    require dirname(__DIR__) . '/templates/vault-form.php';
    ?>
    <p class="card-link"><a href="/area.php">← Voltar para o cofre</a></p>
</section>
<script src="/assets/vault.js" defer></script>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
