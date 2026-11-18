<?php
/**
 * Listado de todos los movimientos con filtros.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requerirLogin();

$pdo = getConexion();

// Filtros
$tipo = $_GET['tipo'] ?? 'todos';

$sql = "SELECT m.*, p.codigo, p.nombre, u.nombre AS usuario_nombre
        FROM movimiento m
        JOIN producto p ON m.id_producto = p.id_producto
        JOIN usuario u ON m.id_usuario = u.id_usuario";

$params = [];

if ($tipo === 'entrada' || $tipo === 'salida') {
    $sql .= " WHERE m.tipo = ?";
    $params[] = $tipo;
}

$sql .= " ORDER BY m.fecha_hora DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movimientos = $stmt->fetchAll();

$tituloPagina = 'Movimientos';
$seccionActiva = 'movimientos';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-arrow-left-right"></i> Movimientos de inventario</h2>
    <a href="registrar.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Registrar movimiento
    </a>
</div>

<!-- Filtro por tipo -->
<form method="GET" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-auto">
            <label class="form-label">Filtrar por tipo</label>
            <select class="form-select" name="tipo">
                <option value="todos"    <?= $tipo === 'todos' ? 'selected' : '' ?>>Todos</option>
                <option value="entrada"  <?= $tipo === 'entrada' ? 'selected' : '' ?>>Entradas</option>
                <option value="salida"   <?= $tipo === 'salida' ? 'selected' : '' ?>>Salidas</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-dark">
                <i class="bi bi-funnel"></i> Filtrar
            </button>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Fecha y hora</th>
                <th>Tipo</th>
                <th class="text-center">Cantidad</th>
                <th>Producto</th>
                <th>Observación</th>
                <th>Responsable</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($movimientos)): ?>
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No hay movimientos registrados.
                </td>
            </tr>
            <?php else: foreach ($movimientos as $m): ?>
            <tr>
                <td><small><?= formatoFecha($m['fecha_hora']) ?></small></td>
                <td>
                    <?php if ($m['tipo'] === 'entrada'): ?>
                    <span class="badge text-bg-success"><i class="bi bi-arrow-down-circle"></i> Entrada</span>
                    <?php else: ?>
                    <span class="badge text-bg-warning"><i class="bi bi-arrow-up-circle"></i> Salida</span>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?= $m['cantidad'] ?></td>
                <td>
                    <a href="../productos/mostrar.php?id=<?= $m['id_producto'] ?>">
                        <strong><?= e($m['codigo']) ?></strong>
                    </a>
                    <?= e($m['nombre']) ?>
                </td>
                <td><small><?= e($m['observacion'] ?: '—') ?></small></td>
                <td><small><?= e($m['usuario_nombre']) ?></small></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<p class="text-muted text-center">
    Mostrando <?= count($movimientos) ?> movimiento(s) — máximo 100
</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
