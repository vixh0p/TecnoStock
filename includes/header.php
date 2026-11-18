<?php
/**
 * Cabecera HTML común a todas las páginas.
 * Variables esperadas:
 *   $tituloPagina  — título de la pestaña
 *   $seccionActiva — sección activa del menú
 *   $basePath      — prefijo para rutas relativas ('' para raíz, '../' para subdirectorios)
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$tituloPagina  = $tituloPagina  ?? 'TecnoStock';
$seccionActiva = $seccionActiva ?? '';
$basePath      = $basePath      ?? '../';
$estaLogueado  = isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?> · TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= $basePath ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php if ($estaLogueado): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= $basePath ?>dashboard.php">
            <i class="bi bi-box-seam"></i> TecnoStock
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $seccionActiva === 'dashboard' ? 'active' : '' ?>" href="<?= $basePath ?>dashboard.php">
                        <i class="bi bi-speedometer2"></i> Panel
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $seccionActiva === 'productos' ? 'active' : '' ?>" href="<?= $basePath ?>productos/index.php">
                        <i class="bi bi-boxes"></i> Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $seccionActiva === 'categorias' ? 'active' : '' ?>" href="<?= $basePath ?>categorias/index.php">
                        <i class="bi bi-tags"></i> Categorías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $seccionActiva === 'movimientos' ? 'active' : '' ?>" href="<?= $basePath ?>movimientos/index.php">
                        <i class="bi bi-arrow-left-right"></i> Movimientos
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="navbar-text text-light me-3">
                        <i class="bi bi-person-circle"></i> <?= e(nombreUsuario()) ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm" href="<?= $basePath ?>logout.php">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<div class="container py-4">
    <?php
    $flash = getFlash();
    if ($flash):
    ?>
    <div class="alert alert-<?= $flash['tipo'] === 'error' ? 'danger' : $flash['tipo'] ?> alert-dismissible fade show" role="alert">
        <?= e($flash['mensaje']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
