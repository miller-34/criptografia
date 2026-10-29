<?php

declare(strict_types=1);

$user = current_user();
$flashMessage = take_flash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Cofre pessoal de senhas criptografadas">
    <title><?= escape($pageTitle) ?> | Cofre Seguro</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="<?= $user !== null ? '/area.php' : '/' ?>">Cofre Seguro</a>
        <nav aria-label="Navegação principal">
            <?php if ($user !== null): ?>
                <a href="/area.php">Minhas senhas</a>
                <a href="/senha-nova.php">Nova senha</a>
            <?php else: ?>
                <a href="/">Cadastro</a>
                <a href="/login.php">Entrar</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="page-shell<?= !empty($widePage) ? ' page-shell-wide' : '' ?>">
        <?php if ($flashMessage !== null): ?>
            <div class="alert alert-<?= escape($flashMessage['type']) ?>" role="status">
                <?= escape($flashMessage['message']) ?>
            </div>
        <?php endif; ?>
