<?php
/**
 * Desactivar un producto (no se elimina físicamente).
 * Regla: un producto con movimientos registrados debe desactivarse, no eliminarse.
 * Acepta solo POST con token CSRF (evita CSRF y cambios por enlace GET).
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requerirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Acción no permitida.');
    redirigir('index.php');
}

verificarCsrf();

$pdo = getConexion();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('error', 'ID de producto no válido');
    redirigir('index.php');
}

$stmt = $pdo->prepare('SELECT nombre FROM producto WHERE id_producto = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    setFlash('error', 'Producto no encontrado');
    redirigir('index.php');
}

try {
    $stmt = $pdo->prepare('UPDATE producto SET estado = 0 WHERE id_producto = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Producto "' . $producto['nombre'] . '" desactivado. No se eliminó del sistema.');
} catch (PDOException $ex) {
    error_log('Error al desactivar producto: ' . $ex->getMessage());
    setFlash('error', 'Ocurrió un error al desactivar el producto.');
}

redirigir('index.php');
