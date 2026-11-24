<?php
/**
 * Funciones auxiliares: sanitización, mensajes flash, validaciones.
 */

declare(strict_types=1);

/**
 * Escapa HTML para prevenir XSS.
 */
function e(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Limpia un string de entrada (trim + eliminar tags).
 */
function limpiar(string $valor): string
{
    return trim(strip_tags($valor));
}

/**
 * Guarda un mensaje flash en sesión.
 */
function setFlash(string $tipo, string $mensaje): void
{
    iniciarSesion();
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/**
 * Devuelve y borra el mensaje flash actual.
 */
function getFlash(): ?array
{
    iniciarSesion();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirige a una URL.
 */
function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Valida que un número sea entero y no negativo.
 */
function esEnteroNoNegativo($valor): bool
{
    return filter_var($valor, FILTER_VALIDATE_INT) !== false
        && (int)$valor >= 0;
}

/**
 * Genera o recupera un token CSRF de la sesión.
 */
function csrfToken(): string
{
    iniciarSesion();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica el token CSRF enviado por POST. Si no coincide, detiene la ejecución.
 */
function verificarCsrf(): void
{
    iniciarSesion();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('Token CSRF inválido. Recargue la página e intente nuevamente.');
    }
}

/**
 * Devuelve el campo oculto HTML con el token CSRF.
 */
function campoCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

/**
 * Formatea un precio en pesos chilenos.
 */
function formatoPrecio(float $valor): string
{
    return '$' . number_format($valor, 0, ',', '.');
}

/**
 * Formatea una fecha y hora.
 */
function formatoFecha(string $fechaHora): string
{
    $ts = strtotime($fechaHora);
    return date('d-m-Y H:i', $ts);
}
