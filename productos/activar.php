<?php
/**
 * Reactivar un producto previamente desactivado.
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

$stmt = $pdo->prepare('SELECT nombre FROM producto WHERE id_producto = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    setFlash('error', 'Producto no encontrado');
    redirigir('index.php');
}

try {
    $stmt = $pdo->prepare('UPDATE producto SET estado = 1 WHERE id_producto = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Producto "' . $producto['nombre'] . '" reactivado.');
} catch (PDOException $ex) {
    setFlash('error', 'Error al reactivar: ' . $ex->getMessage());
}

redirigir('index.php');
