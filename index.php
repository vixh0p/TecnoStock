<?php
/**
 * Punto de entrada: redirige al dashboard si hay sesión, si no al login.
 */
require_once __DIR__ . '/includes/auth.php';
iniciarSesion();

if (isset($_SESSION['usuario_id'])) {
    redirigir('dashboard.php');
} else {
    redirigir('login.php');
}
