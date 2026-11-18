<?php
/**
 * Cierra la sesión y redirige al login.
 */
require_once __DIR__ . '/includes/auth.php';
iniciarSesion();

$_SESSION = [];
session_destroy();

redirigir('login.php');
