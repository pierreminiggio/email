<?php

use PierreMiniggio\ConfigProvider\ConfigProvider;
use PierreMiniggio\DatabaseConnection\DatabaseConnection;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

session_start();

$email = $_GET['email'] ?? null;
$token = $_GET['token'] ?? null;

$currentDir = __DIR__ . DIRECTORY_SEPARATOR;

require_once $currentDir . 'functions.php';

if (empty($email) || empty($token)) {
    redirectToformWithErrorMessage('Tu es tombé sur un mauvais lien je pense...');
}

require $currentDir . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$configProvider = new ConfigProvider($currentDir);
$config = $configProvider->get();
$dbConfig = $config['db'];

$fetcher = new DatabaseFetcher(new DatabaseConnection(
    $dbConfig['host'],
    $dbConfig['database'],
    $dbConfig['username'],
    $dbConfig['password'],
    DatabaseConnection::UTF8_MB4
));

$emailsTable = 'email';

$emailAndTokenArgs = ['email' => $email, 'token' => $token];

$fetchedEmailIds = $fetcher->query(
    $fetcher->createQuery(
        $emailsTable
    )->select(
        'id'
    )->where(
        'email = :email AND token = :token'
    ),
    $emailAndTokenArgs
);

if (empty($fetchedEmailIds)) {
    redirectToformWithErrorMessage('Mauvaises informations de vérification');
}

$fetcher->exec(
    $fetcher->createQuery(
        $emailsTable
    )->update(
        'verified_at = NOW()'
    )->where(
        'email = :email AND token = :token'
    ),
    $emailAndTokenArgs
);

redirectToformWithSuccessMessage('L\'email a été confirmé !');
