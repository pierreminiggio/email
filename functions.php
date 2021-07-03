<?php

function redirectToform(): void
{
    header('Location: /');
    exit;
}

function redirectToformWithMessage(string $messageType, string $message): void
{
    $_SESSION['message'] = ['type' => $messageType, 'content' => $message];
    redirectToform();
}

function redirectToformWithErrorMessage(string $message): void
{
    redirectToformWithMessage('error', $message);
}

function redirectToformWithSuccessMessage(string $message): void
{
    redirectToformWithMessage('success', $message);
}
