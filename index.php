<?php

session_start();

/** @var Array<string, string> $message */
$message = null;

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recevoir des notifications par email</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        #toast-container {
            min-width: 10%;
            top: 240px;
            right: 50%;
            transform: translateX(50%);
        }

        @media only screen and (min-width: 488px) {
            #toast-container {
                top: 180px;
            }
        }

        @media only screen and (min-width: 893px) {
            #toast-container {
                top: 130px;
            }
        }
    </style>
</head>
<body>

    <h1 style="text-align: center;">Notifications Email</h1>
    <p style="text-align: center;">Tu veux être informé de la sortie de mes vidéos par email ?</p>

    <form style="margin-top: 100px;" action="/submit.php" method="POST">
        <div class="row">
            <div class="input-field col s12 m8 offset-m2 l6 offset-l3 xl4 offset-xl4">
                <input placeholder="Placeholder" id="email" name="email" type="email" class="validate">
                <label for="email">Email où envoyer les notifications</label>
            </div>

            <div class="row">
                <label class="col s12 m8 offset-m2 l6 offset-l3 xl4 offset-xl4">
                    <input type="checkbox" class="filled-in" checked="checked" name="consent" />
                    <span>J'accepte que Pierre utilise mon email pour m'envoyer des notifications sur la sortie des nouvelles vidéos ou bien d'autres information en rapport avec la chaîne.</span>
                </label>
                <button style="margin-top: 50px;" class="btn waves-effect waves-light col s12 m8 offset-m2 l6 offset-l3 xl4 offset-xl4" name="submit" type="submit" name="action">
                    S'inscrire <i class="material-icons right">send</i>
                </button>
            </div>
        </div>
    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script>
        <?php if ($message) : ?>
            M.toast({
                html: '<?= str_replace('\'', '\\\'', $message['content']) ?>',
                classes: '<?= $message['type'] === 'error' ? 'red' : 'green' ?>'
            })
        <?php endif ?>
    </script>
</body>
</html>