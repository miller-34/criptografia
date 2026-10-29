<?php

declare(strict_types=1);

use App\AuthService;
use App\Database;
use App\SchemaMigration;
use App\VaultCrypto;
use App\VaultService;

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/SchemaMigration.php';
require_once __DIR__ . '/VaultCrypto.php';
require_once __DIR__ . '/VaultService.php';
require_once __DIR__ . '/helpers.php';

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

function auth_service(): AuthService
{
    static $service = null;

    if (!$service instanceof AuthService) {
        $service = new AuthService(database());
    }

    return $service;
}

function database(): \mysqli
{
    static $database = null;

    if (!$database instanceof \mysqli) {
        $database = Database::connect();
        SchemaMigration::run($database);
    }

    return $database;
}

function vault_service(): VaultService
{
    static $service = null;

    if (!$service instanceof VaultService) {
        $encodedKey = getenv('APP_KEY');
        $service = new VaultService(
            database(),
            new VaultCrypto($encodedKey === false ? '' : $encodedKey)
        );
    }

    return $service;
}
