<?php
/**
 * Ver detalle de un producto + historial de movimientos.
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

// Cargar producto con categoría
$stmt = $pdo->prepare(
    'SELECT p.*, c.nombre AS categoria_nombre
     FROM producto p
     JOIN categoria c ON p.id_categoria = c.id_categoria
     WHERE p.id_producto = ?'
);
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    setFlash('error', 'Producto no encontrado');
    redirigir('index.php');
}

// Cargar movimientos del producto
$stmt = $pdo->prepare(
    'SELECT m.*, u.nombre AS usuario_nombre
     FROM movimiento m
     JOIN usuario u ON m.id_usuario = u.id_usuario
     WHERE m.id_producto = ?
     ORDER BY m.fecha_hora DESC'
);
$stmt->execute([$id]);
$movimientos = $stmt->fetchAll();

// Calcular totales
$entradas = 0;
$salidas  = 0;
foreach ($movimientos as $m) {
    if ($m['tipo'] === 'entrada') $entradas += $m['cantidad'];
    else $salidas += $m['cantidad'];
}

$tituloPagina = 'Detalle: ' . $producto['nombre'];
$seccionActiva = 'productos';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-eye"></i> Detalle del producto</h2>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row g-4">
    <!-- Información del producto -->
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-box"></i> Información
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width:35%">Código</th>
                        <td><strong><?= e($producto['codigo']) ?></strong></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Nombre</th>
                        <td><?= e($producto['nombre']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Categoría</th>
                        <td><span class="badge text-bg-light"><?= e($producto['categoria_nombre']) ?></span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Descripción</th>
                        <td><?= e($producto['descripcion'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Precio</th>
                        <td><?= formatoPrecio((float)$producto['precio']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Stock actual</th>
                        <td>
                            <?php if ($producto['stock_actual'] < $producto['stock_minimo']): ?>
                            <span class="badge badge-stock-bajo"><?= $producto['stock_actual'] ?></span>
                            <span class="badge text-bg-warning">Stock bajo</span>
                            <?php else: ?>
                            <?= $producto['stock_actual'] ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Stock mínimo</th>
                        <td><?= $producto['stock_minimo'] ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Estado</th>
                        <td>
                            <?php if ($producto['estado'] == 1): ?>
                            <span class="badge badge-activo">Activo</span>
                            <?php else: ?>
                            <span class="badge badge-inactivo">Inactivo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <div class="d-flex gap-2 mt-3">
                    <a href="editar.php?id=<?= $id ?>" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <a href="../movimientos/registrar.php?id_producto=<?= $id ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left-right"></i> Registrar movimiento
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos del producto -->
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-clock-history"></i> Historial de movimientos
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="text-center p-2 border rounded">
                            <small class="text-muted">Entradas</small>
                            <h4 class="text-success mb-0"><?= $entradas ?></h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2 border rounded">
                            <small class="text-muted">Salidas</small>
                            <h4 class="text-warning mb-0"><?= $salidas ?></h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2 border rounded">
                            <small class="text-muted">Total movs.</small>
                            <h4 class="mb-0"><?= count($movimientos) ?></h4>
                        </div>
                    </div>
                </div>

                <?php if (empty($movimientos)): ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Este producto no tiene movimientos registrados.
                </p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Fecha y hora</th>
                                <th>Tipo</th>
                                <th class="text-center">Cantidad</th>
                                <th>Observación</th>
                                <th>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $m): ?>
                            <tr>
                                <td><small><?= formatoFecha($m['fecha_hora']) ?></small></td>
                                <td>
                                    <?php if ($m['tipo'] === 'entrada'): ?>
                                    <span class="badge text-bg-success">Entrada</span>
                                    <?php else: ?>
                                    <span class="badge text-bg-warning">Salida</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= $m['cantidad'] ?></td>
                                <td><small><?= e($m['observacion'] ?: '—') ?></small></td>
                                <td><small><?= e($m['usuario_nombre']) ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
