<?php
/**
 * Listado de categorías con cantidad de productos asociados.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requerirLogin();

$pdo = getConexion();

$categorias = $pdo->query(
    'SELECT c.*, COUNT(p.id_producto) AS total_productos
     FROM categoria c
     LEFT JOIN producto p ON c.id_categoria = p.id_categoria
     GROUP BY c.id_categoria
     ORDER BY c.nombre ASC'
)->fetchAll();

$tituloPagina = 'Categorías';
$seccionActiva = 'categorias';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-tags"></i> Categorías</h2>
    <a href="crear.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nueva categoría
    </a>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th class="text-center">Productos</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categorias)): ?>
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    No hay categorías registradas.
                </td>
            </tr>
            <?php else: foreach ($categorias as $c): ?>
            <tr>
                <td><?= $c['id_categoria'] ?></td>
                <td><strong><?= e($c['nombre']) ?></strong></td>
                <td><?= e($c['descripcion'] ?: '—') ?></td>
                <td class="text-center">
                    <span class="badge text-bg-primary"><?= $c['total_productos'] ?></span>
                </td>
                <td class="text-center">
                    <?php if ($c['estado'] == 1): ?>
                    <span class="badge badge-activo">Activa</span>
                    <?php else: ?>
                    <span class="badge badge-inactivo">Inactiva</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <a href="editar.php?id=<?= $c['id_categoria'] ?>" class="btn btn-sm btn-outline-warning"
                       title="Editar"><i class="bi bi-pencil"></i></a>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
