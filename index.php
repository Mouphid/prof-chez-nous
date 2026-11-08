<?php
// Servir directement la page d'accueil à la racine (https://joie-enseignante.com/)
// sans redirection vers /public/index.php
chdir(__DIR__ . '/public');
require __DIR__ . '/public/index.php';
