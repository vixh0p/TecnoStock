<?php
/**
 * Listado de productos con búsqueda por nombre, código o categoría.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requerirLogin();

$pdo = getConexion();

// Parámetros de búsqueda y filtro
$q       = limpiar($_GET['q'] ?? '');
$estado  = $_GET['estado'] ?? 'todos';

// Construir consulta con consultas preparadas
$sql = "SELECT p.*, c.nombre AS categoria_nombre
        FROM producto p
        JOIN categoria c ON p.id_categoria = c.id_categoria
        WHERE 1=1";

$params = [];

if ($q !== '') {
    $sql .= " AND (p.nombre LIKE ? OR p.codigo LIKE ? OR c.nombre LIKE ?)";
    $termino = '%' . $q . '%';
    $params[] = $termino;
    $params[] = $termino;
    $params[] = $termino;
}

if ($estado === 'activos') {
    $sql .= " AND p.estado = 1";
} elseif ($estado === 'inactivos') {
    $sql .= " AND p.estado = 0";
}

$sql .= " ORDER BY p.nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$tituloPagina = 'Productos';
$seccionActiva = 'productos';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-boxes"></i> Productos</h2>
    <a href="crear.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo producto
    </a>
</div>

<!-- Barra de búsqueda -->
<form method="GET" class="card mb-4 p-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label">Buscar</label>
            <input type="text" class="form-control" name="q" value="<?= e($q) ?>"
                   placeholder="Nombre, código o categoría...">
        </div>
        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select class="form-select" name="estado">
                <option value="todos"     <?= $estado === 'todos' ? 'selected' : '' ?>>Todos</option>
                <option value="activos"   <?= $estado === 'activos' ? 'selected' : '' ?>>Activos</option>
                <option value="inactivos" <?= $estado === 'inactivos' ? 'selected' : '' ?>>Inactivos</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-dark w-100">
                <i class="bi bi-search"></i> Buscar
            </button>
            <a href="index.php" class="btn btn-outline-secondary w-100 mt-1">
                <i class="bi bi-x-circle"></i> Limpiar
            </a>
        </div>
    </div>
</form>

<!-- Tabla de productos -->
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th class="text-end">Precio</th>
                <th class="text-center">Stock actual</th>
                <th class="text-center">Stock mín.</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No se encontraron productos.
                </td>
            </tr>
            <?php else: foreach ($productos as $p): ?>
            <tr>
                <td><strong><?= e($p['codigo']) ?></strong></td>
                <td><?= e($p['nombre']) ?></td>
                <td><span class="badge text-bg-light"><?= e($p['categoria_nombre']) ?></span></td>
                <td class="text-end"><?= formatoPrecio((float)$p['precio']) ?></td>
                <td class="text-center">
                    <?php if ($p['stock_actual'] < $p['stock_minimo'] && $p['estado'] == 1): ?>
                    <span class="badge badge-stock-bajo" title="Stock bajo el mínimo">
                        <?= $p['stock_actual'] ?>
                    </span>
                    <?php else: ?>
                    <?= $p['stock_actual'] ?>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?= $p['stock_minimo'] ?></td>
                <td class="text-center">
                    <?php if ($p['estado'] == 1): ?>
                    <span class="badge badge-activo">Activo</span>
                    <?php else: ?>
                    <span class="badge badge-inactivo">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <a href="mostrar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-outline-info"
                       title="Ver detalle"><i class="bi bi-eye"></i></a>
                    <a href="editar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-outline-warning"
                       title="Editar"><i class="bi bi-pencil"></i></a>
                    <?php if ($p['estado'] == 1): ?>
                    <a href="desactivar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-outline-danger confirmar-accion"
                       data-mensaje="¿Desactivar el producto <?= e($p['nombre']) ?>? No se eliminará físicamente."
                       title="Desactivar"><i class="bi bi-toggle-on"></i></a>
                    <?php else: ?>
                    <a href="activar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-outline-success confirmar-accion"
                       data-mensaje="¿Reactivar el producto <?= e($p['nombre']) ?>?"
                       title="Activar"><i class="bi bi-toggle-off"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<p class="text-muted text-center">
    Mostrando <?= count($productos) ?> producto(s)
</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
