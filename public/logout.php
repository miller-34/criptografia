<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Método não permitido.');
}

if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Token de segurança inválido.');
}

$_SESSION = [];
session_regenerate_id(true);
flash('success', 'Você saiu da sua conta.');
redirect('/login.php');
