<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

if (current_user() !== null) {
    redirect('/area.php');
}

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
            $user = auth_service()->authenticate($email, $password);
            if ($user === null) {
                $error = 'E-mail ou senha incorretos.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user'] = $user;
                flash('success', 'Login realizado com sucesso.');
                redirect('/area.php');
            }
        } catch (Throwable $exception) {
            error_log($exception->__toString());
            $error = 'Não foi possível entrar agora. Tente novamente.';
        }
    }
}

$pageTitle = 'Entrar';
require dirname(__DIR__) . '/templates/header.php';
?>
<section class="auth-card" aria-labelledby="login-title">
    <div class="card-heading">
        <span class="eyebrow">Bem-vindo de volta</span>
        <h1 id="login-title">Entre na sua conta</h1>
        <p>Informe os mesmos dados utilizados no cadastro.</p>
    </div>

    <?php if ($error !== null): ?>
        <div class="alert alert-error" role="alert"><?= escape($error) ?></div>
    <?php endif; ?>

    <form method="post" action="/login.php" class="auth-form">
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
            autocomplete="current-password"
            maxlength="72"
            required
        >

        <button type="submit">Entrar</button>
    </form>

    <p class="card-link">Ainda não possui uma conta? <a href="/">Cadastre-se</a>.</p>
</section>
<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
