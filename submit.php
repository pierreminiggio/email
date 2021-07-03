<?php

use NeutronStars\Database\Query;
use PHPMailer\PHPMailer\PHPMailer;
use PierreMiniggio\ConfigProvider\ConfigProvider;
use PierreMiniggio\DatabaseConnection\DatabaseConnection;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

session_start();

require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';

if (! isset($_POST['submit'])) {
    redirectToform();
}

if (empty($_POST['consent'])) {
    redirectToformWithErrorMessage('Veille à accepter les conditions');
}

if (empty($_POST['email'])) {
    redirectToformWithErrorMessage('Email obligatoire');
}

$email = $_POST['email'];

if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
     redirectToformWithErrorMessage('Je crois que t\'as saisi un mauvais email.');
}

$currentDir = __DIR__ . DIRECTORY_SEPARATOR;

$ip = $_SERVER['REMOTE_ADDR'] ?? '';

require $currentDir . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$configProvider = new ConfigProvider($currentDir);
$config = $configProvider->get();
$dbConfig = $config['db'];

$serverPort = $_SERVER['SERVER_PORT'] ?? 80;
$serverUrl = 'https://' . $_SERVER['SERVER_NAME'] . ($serverPort !== 80 ? ':' . $serverPort : '');

$fetcher = new DatabaseFetcher(new DatabaseConnection(
    $dbConfig['host'],
    $dbConfig['database'],
    $dbConfig['username'],
    $dbConfig['password'],
    DatabaseConnection::UTF8_MB4
));

$emailsTable = 'email';

$emailAndIpArgs = [
    'email' => $email,
    'ip' => $ip
];

$queryEmailArgs = [
    $fetcher->createQuery(
        $emailsTable
    )->select(
        'id',
        'token'
    )->where(
        'email = :email AND ip = :ip'
    )->orderBy(
        'created_at',
        Query::ORDER_BY_DESC
    ),
    $emailAndIpArgs
];

$queriedIds = $fetcher->query(...$queryEmailArgs);

if (empty($queriedIds)) {
    $newToken = uniqid('merci');

    $fetcher->exec(
        $fetcher->createQuery(
            $emailsTable
        )->insertInto(
            'email, ip, token',
            ':email, :ip, :token'
        ),
        [
            'email' => $email,
            'ip' => $ip,
            'token' => $newToken
        ]
    );
}

$queriedIds = $fetcher->query(...$queryEmailArgs);

if (empty($queriedIds)) {
    redirectToformWithErrorMessage('Une erreur est survenue en sauvegardant l\'email');
}

$emailEntry = $queriedIds[0];

$emailId = $emailEntry['id'];
$token = $emailEntry['token'];

$mailerConfig = $config['mail'];

$mailer = new PHPMailer();
$mailer->CharSet = 'UTF-8';
$mailer->IsSMTP();
$mailer->Host = $mailerConfig['host'];
$mailer->SMTPAuth = $mailerConfig['smtp_auth'];
$mailer->SMTPSecure = $mailerConfig['smtp_secure'];
$mailer->Username = $mailerConfig['username'];
$mailer->Password = $mailerConfig['password'];
$mailer->Port = $mailerConfig['port'];

$fromEmail = 'pierre@miniggiodev.fr';
$fromName = 'Pierre Miniggio';
$mailer->setFrom($fromEmail, $fromName);
$mailer->addReplyTo($fromEmail, $fromName);

$mailer->addAddress($email);
$mailer->Subject = 'Confirmation d\'inscription aux notifications email';

$mailer->msgHTML(<<<HTML
    <h1>Salut !</h1>
    <p>Pour confirmer le fait que tu veuilles recevoir des notifications email pour mes vidéos,</p>
    <p>je t'invite à cliquer sur le lien suivant :</p>
    <p><a href="{$serverUrl}/confirm.php?email=$email&token={$token}" style="
        display: block;
        text-decoration: none;
        width: fit-content;
        padding: 5px 10px;
        color: #FFFFFF;
        background-color: #F57C00;
        border-radius: 3px;
    ">Confirmer</a></p>
    <br>
    <p>- Pierre</p>
HTML);

if (! $mailer->send()) {
    echo 'Mailer Error: ' . $mailer->ErrorInfo;
    redirectToformWithErrorMessage('Une erreur est survenue pendant l\'envoi de l\'email de confirmation');
}

$fetcher->exec(
    $fetcher->createQuery(
        $emailsTable
    )->update(
        'token_sent_at = NOW()',
    )->where(
        'id = :id'
    ),
    [
        'id' => $emailId,
    ]
);

redirectToformWithSuccessMessage('Un email de confirmation a été envoyée à l\'email ' . $email);
