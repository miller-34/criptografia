<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

$user = require_user();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}
if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Token de segurança inválido.');
}

$entryId = filter_var(post_string('id'), FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
if (!is_int($entryId) || !vault_service()->delete($entryId, $user['id'])) {
    http_response_code(404);
    exit('Credencial não encontrada.');
}

flash('success', 'Credencial excluída do cofre.');
redirect('/area.php');
