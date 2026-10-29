<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = post_string('email');
    $password = post_string('password');

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(400);
        $error = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } else {
        try {
            auth_service()->register($email, $password);
            flash('success', 'Cadastro realizado. Agora você já pode entrar.');
            redirect('/login.php');
        } catch (DomainException $exception) {
            $error = $exception->getMessage();
        } catch (Throwable $exception) {
            error_log($exception->__toString());
            $error = 'Não foi possível concluir o cadastro agora. Tente novamente.';
        }
    }
}

$pageTitle = 'Criar conta';
require dirname(__DIR__) . '/templates/header.php';
?>
<section class="auth-card" aria-labelledby="register-title">
    <div class="card-heading">
        <span class="eyebrow">Comece por aqui</span>
        <h1 id="register-title">Crie sua conta</h1>
        <p>Use um e-mail válido e uma senha com pelo menos 8 caracteres.</p>
    </div>

    <?php if ($error !== null): ?>
        <div class="alert alert-error" role="alert"><?= escape($error) ?></div>
    <?php endif; ?>

    <form method="post" action="/" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">

        <label for="email">E-mail</label>
        <input
            id="email"
            name="email"
            type="email"
            autocomplete="email"
            maxlength="254"
            value="<?= escape($email) ?>"
            required
            autofocus
        >

        <label for="password">Senha</label>
        <input
            id="password"
            name="password"
            type="password"
            autocomplete="new-password"
            minlength="8"
            maxlength="72"
            required
        >

        <button type="submit">Criar conta</button>
    </form>

    <p class="card-link">Já possui uma conta? <a href="/login.php">Entre aqui</a>.</p>
</section>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
