<?php
/**
 * Registrar entrada o salida de stock.
 *
 * Reglas de negocio implementadas:
 * - La cantidad debe ser un entero mayor a 0.
 * - Una salida no puede dejar el stock bajo cero.
 * - El stock se actualiza automáticamente tras cada movimiento.
 * - Se registra fecha, tipo, cantidad, producto y usuario responsable.
 * - Todo se hace en una transacción para garantizar integridad.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requerirLogin();

$pdo = getConexion();
$errores = [];

// Productos activos para el select
$productos = $pdo->query(
    'SELECT id_producto, codigo, nombre, stock_actual, stock_minimo
     FROM producto WHERE estado = 1 ORDER BY nombre'
)->fetchAll();

// Producto preseleccionado (desde enlace en panel o detalle)
$idProductoPre = (int)($_GET['id_producto'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo       = limpiar($_POST['tipo'] ?? '');
    $cantidad   = (int)($_POST['cantidad'] ?? 0);
    $idProducto = (int)($_POST['id_producto'] ?? 0);
    $observacion = limpiar($_POST['observacion'] ?? '');

    // --- Validaciones servidor ---
    if (!in_array($tipo, ['entrada', 'salida'], true)) {
        $errores['tipo'] = 'Seleccione el tipo de movimiento';
    }
    if ($cantidad <= 0) {
        $errores['cantidad'] = 'La cantidad debe ser un entero mayor a 0';
    }
    if ($idProducto <= 0) {
        $errores['id_producto'] = 'Seleccione un producto';
    }

    // Verificar que el producto existe y está activo
    $producto = null;
    if (!isset($errores['id_producto'])) {
        $stmt = $pdo->prepare('SELECT * FROM producto WHERE id_producto = ? AND estado = 1');
        $stmt->execute([$idProducto]);
        $producto = $stmt->fetch();
        if (!$producto) {
            $errores['id_producto'] = 'El producto no existe o está inactivo';
        }
    }

    // Validar que la salida no deje stock bajo cero
    if (empty($errores) && $tipo === 'salida' && $producto) {
        if ($cantidad > $producto['stock_actual']) {
            $errores['cantidad'] = 'La cantidad de salida (' . $cantidad .
                ') supera el stock actual (' . $producto['stock_actual'] . ')';
        }
    }

    if (empty($errores)) {
        try {
            // Transacción: registrar movimiento + actualizar stock
            $pdo->beginTransaction();

            // 1. Insertar el movimiento
            $stmt = $pdo->prepare(
                'INSERT INTO movimiento (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario)
                 VALUES (NOW(), ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$tipo, $cantidad, $observacion, $idProducto, usuarioId()]);

            // 2. Actualizar el stock del producto
            if ($tipo === 'entrada') {
                $nuevoStock = $producto['stock_actual'] + $cantidad;
            } else {
                $nuevoStock = $producto['stock_actual'] - $cantidad;
            }

            $stmt = $pdo->prepare('UPDATE producto SET stock_actual = ? WHERE id_producto = ?');
            $stmt->execute([$nuevoStock, $idProducto]);

            $pdo->commit();

            // Mensaje con alerta si quedó bajo stock
            $msg = ucfirst($tipo) . ' de ' . $cantidad . ' unidades registrada. Stock actual: ' . $nuevoStock;
            if ($nuevoStock < $producto['stock_minimo']) {
                $msg .= ' — ¡ATENCIÓN: Stock por debajo del mínimo (' . $producto['stock_minimo'] . ')!';
                setFlash('warning', $msg);
            } else {
                setFlash('success', $msg);
            }

            redirigir('index.php');
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errores['general'] = 'Error al registrar el movimiento: ' . $ex->getMessage();
        }
    }
}

$tituloPagina = 'Registrar movimiento';
$seccionActiva = 'movimientos';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-arrow-left-right"></i> Registrar movimiento de stock</h2>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            El stock se actualizará automáticamente según el tipo de movimiento.
            Una salida no puede superar el stock actual.
        </div>

        <?php if (isset($errores['general'])): ?>
        <div class="alert alert-danger"><?= e($errores['general']) ?></div>
        <?php endif; ?>

        <form id="formMovimiento" method="POST" novalidate>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="tipo" class="form-label">Tipo de movimiento <span class="text-danger">*</span></label>
                    <select class="form-select <?= isset($errores['tipo']) ? 'is-invalid' : '' ?>"
                            id="tipo" name="tipo" required>
                        <option value="">Seleccione...</option>
                        <option value="entrada" <?= ($_POST['tipo'] ?? '') === 'entrada' ? 'selected' : '' ?>>
                            <i class="bi bi-arrow-down-circle"></i> Entrada
                        </option>
                        <option value="salida" <?= ($_POST['tipo'] ?? '') === 'salida' ? 'selected' : '' ?>>
                            Salida
                        </option>
                    </select>
                    <?php if (isset($errores['tipo'])): ?>
                    <div class="invalid-feedback"><?= e($errores['tipo']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="id_producto" class="form-label">Producto <span class="text-danger">*</span></label>
                    <select class="form-select <?= isset($errores['id_producto']) ? 'is-invalid' : '' ?>"
                            id="id_producto" name="id_producto" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id_producto'] ?>"
                            data-stock="<?= $p['stock_actual'] ?>"
                            <?= (($_POST['id_producto'] ?? $idProductoPre) == $p['id_producto']) ? 'selected' : '' ?>>
                            <?= e($p['codigo']) ?> — <?= e($p['nombre']) ?>
                            (stock: <?= $p['stock_actual'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['id_producto'])): ?>
                    <div class="invalid-feedback"><?= e($errores['id_producto']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="number" min="1" class="form-control <?= isset($errores['cantidad']) ? 'is-invalid' : '' ?>"
                           id="cantidad" name="cantidad" value="<?= e($_POST['cantidad'] ?? '') ?>"
                           required placeholder="Ej: 10">
                    <?php if (isset($errores['cantidad'])): ?>
                    <div class="invalid-feedback"><?= e($errores['cantidad']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <label for="observacion" class="form-label">Observación</label>
                    <textarea class="form-control" id="observacion" name="observacion" rows="2"
                              placeholder="Motivo del movimiento (opcional)..."><?= e($_POST['observacion'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Mostrar stock actual del producto seleccionado -->
            <div id="infoStock" class="alert alert-light border mt-3 d-none">
                <i class="bi bi-info-circle"></i> Stock actual: <strong id="stockActualValor">—</strong>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Registrar movimiento
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Mostrar stock actual del producto seleccionado
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('id_producto');
    const info = document.getElementById('infoStock');
    const stockVal = document.getElementById('stockActualValor');

    sel.addEventListener('change', function () {
        if (this.selectedOptions[0]) {
            const stock = this.selectedOptions[0].dataset.stock;
            if (stock !== undefined) {
                info.classList.remove('d-none');
                stockVal.textContent = stock;
            }
        } else {
            info.classList.add('d-none');
        }
    });

    // Si hay producto preseleccionado, mostrar su stock
    if (sel.value !== '') {
        sel.dispatchEvent(new Event('change'));
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
