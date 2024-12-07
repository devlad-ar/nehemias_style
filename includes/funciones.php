<?php

function debuguear($variable) : string {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Escapa / Sanitizar el HTML
function s($html) {
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}


function esUltimo($actual, $proximo) {
    if ($actual === null || $proximo === null) {
        return true; // Si el próximo es null, es el último elemento
    }
    return $actual !== $proximo;
}

// Función que revisa que el usuario este autenticado
function isAuth() : void {
    if(!isset($_SESSION['login'])) {
        header('Location: /');
    }
}

function isAdmin() : void {
    if(!isset($_SESSION['admin'])) {
        header('Location: /');
    }
}