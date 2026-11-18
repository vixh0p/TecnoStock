<?php
/**
 * Panel principal: estadísticas generales y alertas de stock bajo.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requerirLoginRaiz();

$pdo = getConexion();

// Total de productos activos
$totalProductos = (int)$pdo->query('SELECT COUNT(*) FROM producto WHERE estado = 1')->fetchColumn();

// Total de categorías activas
$totalCategorias = (int)$pdo->query('SELECT COUNT(*) FROM categoria WHERE estado = 1')->fetchColumn();

// Productos con stock bajo (stock_actual < stock_minimo)
$productosBajoStock = $pdo->query(
    'SELECT codigo, nombre, stock_actual, stock_minimo, id_producto
     FROM producto
     WHERE estado = 1 AND stock_actual < stock_minimo
     ORDER BY (stock_minimo - stock_actual) DESC'
)->fetchAll();

// Movimientos recientes
$movimientosRecientes = $pdo->query(
    'SELECT m.fecha_hora, m.tipo, m.cantidad, p.codigo, p.nombre, u.nombre AS usuario
     FROM movimiento m
     JOIN producto p ON m.id_producto = p.id_producto
     JOIN usuario u ON m.id_usuario = u.id_usuario
     ORDER BY m.fecha_hora DESC
     LIMIT 8'
)->fetchAll();

// Valor total del inventario
$valorInventario = $pdo->query(
    'SELECT COALESCE(SUM(precio * stock_actual), 0) FROM producto WHERE estado = 1'
)->fetchColumn();

$tituloPagina = 'Panel';
$seccionActiva = 'dashboard';
$basePath = '';
include __DIR__ . '/includes/header.php';
?>

<h2 class="mb-4"><i class="bi bi-speedometer2"></i> Panel de Control</h2>

<!-- Tarjetas de estadísticas -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                    <i class="bi bi-boxes text-primary fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0">Productos activos</h6>
                    <h3 class="mb-0"><?= $totalProductos ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                    <i class="bi bi-tags text-success fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0">Categorías activas</h6>
                    <h3 class="mb-0"><?= $totalCategorias ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                    <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0">Alertas stock bajo</h6>
                    <h3 class="mb-0"><?= count($productosBajoStock) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                    <i class="bi bi-cash-coin text-info fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0">Valor inventario</h6>
                    <h3 class="mb-0"><?= formatoPrecio((float)$valorInventario) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerta de stock bajo -->
<?php if (!empty($productosBajoStock)): ?>
<div class="card border-danger mb-4">
    <div class="card-header bg-danger text-white">
        <i class="bi bi-exclamation-triangle-fill"></i> Productos con stock bajo
    </div>
    <div class="card-body">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th class="text-center">Stock actual</th>
                    <th class="text-center">Stock mínimo</th>
                    <th class="text-center">Faltan</th>
                    <th class="text-center">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productosBajoStock as $p): ?>
                <tr class="stock-alerta">
                    <td><strong><?= e($p['codigo']) ?></strong></td>
                    <td><?= e($p['nombre']) ?></td>
                    <td class="text-center"><span class="badge badge-stock-bajo"><?= $p['stock_actual'] ?></span></td>
                    <td class="text-center"><?= $p['stock_minimo'] ?></td>
                    <td class="text-center text-danger fw-bold"><?= $p['stock_minimo'] - $p['stock_actual'] ?></td>
                    <td class="text-center">
                        <a href="movimientos/registrar.php?id_producto=<?= $p['id_producto'] ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Registrar entrada
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle-fill"></i> No hay productos con stock bajo.
</div>
<?php endif; ?>

<!-- Movimientos recientes -->
<div class="card">
    <div class="card-header bg-dark text-white">
        <i class="bi bi-clock-history"></i> Movimientos recientes
    </div>
    <div class="card-body">
        <?php if (empty($movimientosRecientes)): ?>
        <p class="text-muted text-center mb-0">No hay movimientos registrados.</p>
        <?php else: ?>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Tipo</th>
                    <th class="text-center">Cantidad</th>
                    <th>Producto</th>
                    <th>Responsable</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movimientosRecientes as $m): ?>
                <tr>
                    <td><?= formatoFecha($m['fecha_hora']) ?></td>
                    <td>
                        <?php if ($m['tipo'] === 'entrada'): ?>
                        <span class="badge text-bg-success"><i class="bi bi-arrow-down-circle"></i> Entrada</span>
                        <?php else: ?>
                        <span class="badge text-bg-warning"><i class="bi bi-arrow-up-circle"></i> Salida</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= $m['cantidad'] ?></td>
                    <td><strong><?= e($m['codigo']) ?></strong> — <?= e($m['nombre']) ?></td>
                    <td><?= e($m['usuario']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <div class="text-center mt-3">
            <a href="movimientos/index.php" class="btn btn-outline-dark btn-sm">Ver todo</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
