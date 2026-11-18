<?php
/**
 * Desactivar un producto (no se elimina físicamente).
 * Regla: un producto con movimientos registrados debe desactivarse, no eliminarse.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requerirLogin();

$pdo = getConexion();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('error', 'ID de producto no válido');
    redirigir('index.php');
}

// Verificar que el producto existe
$stmt = $pdo->prepare('SELECT nombre FROM producto WHERE id_producto = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    setFlash('error', 'Producto no encontrado');
    redirigir('index.php');
}

// Desactivar (cambiar estado a 0)
try {
    $stmt = $pdo->prepare('UPDATE producto SET estado = 0 WHERE id_producto = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Producto "' . $producto['nombre'] . '" desactivado. No se eliminó del sistema.');
} catch (PDOException $ex) {
    setFlash('error', 'Error al desactivar: ' . $ex->getMessage());
}

redirigir('index.php');
