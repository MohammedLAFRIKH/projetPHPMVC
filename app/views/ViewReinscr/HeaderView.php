<?php

namespace App\Views\ViewReinscr;

class HeaderView
{
    public function showHeader($isUserConnected,$title)
    {
        ?>
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title><?php echo $title; ?></title>
            <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css">
            <link href="<?= BASE_URL ?>/public/css/bootstrap-39a54b5522911e4e33afc350df6a93a6.css" rel="stylesheet" type="text/css">
            <link href="<?= BASE_URL ?>/public/css/arabic_keyboard-aa957327e09910f02bb1c501b44c00d3.css" rel="stylesheet" type="text/css">
        </head>
        <body>
        <div class="header">
                <div class="header-content">
                <a href="/apogee_ens/"><img src="<?= BASE_URL ?>/public/images/logo_ens_rabat.png" class="logo"></a>
                    <div class="float-right menu-block js-menu-items hide-in-mobile">
                        <div class="menu-links">
                            <?php if ($isUserConnected): ?>
                                <!-- User is connected, show modify information link -->
                                <a href="/apogee_ens/register/dashboard" class="menu-header">Accueil</a>

                                <a href="/apogee_ens/register/details?enregistrement=page1&modification=true" class="menu-header">Modifier mes informations</a>
                                <a href="/apogee_ens/sessions/remove" class="menu-header show-in-mobile hide-desktop">Quitter</a>
                            <?php else: ?>
                                <!-- User is not connected, show create account and login links -->
                                <a href="/apogee_ens/" class="menu-header">Accueil</a>

                                <a href="/apogee_ens/register/details?enregistrement=new_user" class="menu-header">Créez un compte</a>
                                <a href="/apogee_ens/user/login" class="menu-header">Se connecter</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }
}
