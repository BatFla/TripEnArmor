<?php
session_start();

function isConnectedAsMember(): bool
{
    return isset($_SESSION['id_member'])
;}

function verifyPro()
{
    ob_start(); // Active la mise en mémoire tampon de sortie

    // Vérifie si l'utilisateur est connecté
    if (!isset($_SESSION['id_pro'])) {
        // Si l'utilisateur n'est pas connecté ou si le token ne correspond pas
        header('location: /pro/401'); // TODO: ajouter un lien vers la page de connexion
        exit(); // Termine le script pour s'assurer que rien d'autre ne s'exécute après la redirection
    }
}

function verifyMember()
{
    ob_start(); // Active la mise en mémoire tampon de sortie

    // Vérifie si l'utilisateur est connecté
    if (!isset($_SESSION['id_user'])) {
        // Si l'utilisateur n'est pas connecté
        header('location: /401');
        exit(); // Termine le script pour s'assurer que rien d'autre ne s'exécute après la redirection
    }
}