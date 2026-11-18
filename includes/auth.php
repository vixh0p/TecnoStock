<?php
/**
 * Funciones de autenticación y manejo de sesión.
 */

declare(strict_types=1);

/**
 * Inicia la sesión si no está activa.
 */
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Verifica que el usuario esté autenticado. Si no, redirige al login.
 */
function requerirLogin(): void
{
    iniciarSesion();
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

/**
 * Verifica autenticación desde la raíz del proyecto.
 */
function requerirLoginRaiz(): void
{
    iniciarSesion();
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Devuelve el nombre del usuario actual o 'Invitado'.
 */
function nombreUsuario(): string
{
    iniciarSesion();
    return $_SESSION['usuario_nombre'] ?? 'Invitado';
}

/**
 * Devuelve el id del usuario actual o 0.
 */
function usuarioId(): int
{
    iniciarSesion();
    return (int)($_SESSION['usuario_id'] ?? 0);
}
