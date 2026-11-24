<?php
/**
 * Editar un producto existente.
 * Permite modificar precio, descripción, categoría y cantidades.
 * No permite cambiar el código (regla: código único e inmutable).
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requerirLogin();

$pdo = getConexion();
$errores = [];

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('error', 'ID de producto no válido');
    redirigir('index.php');
}

// Cargar producto
$stmt = $pdo->prepare('SELECT * FROM producto WHERE id_producto = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    setFlash('error', 'Producto no encontrado');
    redirigir('index.php');
}

// Cargar categorías
$categorias = $pdo->query('SELECT id_categoria, nombre FROM categoria WHERE estado = 1 ORDER BY nombre')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $nombre       = limpiar($_POST['nombre'] ?? '');
    $idCategoria  = (int)($_POST['id_categoria'] ?? 0);
    $descripcion  = limpiar($_POST['descripcion'] ?? '');
    $precio       = filter_var($_POST['precio'] ?? '', FILTER_VALIDATE_FLOAT);
    $stockActual  = filter_var($_POST['stock_actual'] ?? '', FILTER_VALIDATE_INT);
    $stockMinimo  = filter_var($_POST['stock_minimo'] ?? '', FILTER_VALIDATE_INT);

    // --- Validaciones servidor ---
    if (mb_strlen($nombre) < 3) {
        $errores['nombre'] = 'El nombre debe tener al menos 3 caracteres';
    }
    if ($idCategoria <= 0) {
        $errores['id_categoria'] = 'Seleccione una categoría';
    }
    if ($precio === false || $precio < 0) {
        $errores['precio'] = 'El precio debe ser un número no negativo';
    }
    if ($stockActual === false || $stockActual < 0) {
        $errores['stock_actual'] = 'El stock actual debe ser un entero no negativo';
    }
    if ($stockMinimo === false || $stockMinimo < 0) {
        $errores['stock_minimo'] = 'El stock mínimo debe ser un entero no negativo';
    }

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare(
                'UPDATE producto
                 SET nombre = ?, descripcion = ?, precio = ?, stock_actual = ?, stock_minimo = ?, id_categoria = ?
                 WHERE id_producto = ?'
            );
            $stmt->execute([$nombre, $descripcion, $precio, $stockActual, $stockMinimo, $idCategoria, $id]);
            setFlash('success', 'Producto actualizado correctamente');
            redirigir('index.php');
        } catch (PDOException $ex) {
            error_log('Error al actualizar producto: ' . $ex->getMessage());
            $errores['general'] = 'Ocurrió un error al actualizar el producto. Intente nuevamente.';
        }
    }
}

$tituloPagina = 'Editar producto';
$seccionActiva = 'productos';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-pencil-square"></i> Editar producto</h2>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="alert alert-light border">
            <strong>Código:</strong> <?= e($producto['codigo']) ?>
            <span class="text-muted">(el código no se puede modificar)</span>
        </div>

        <?php if (isset($errores['general'])): ?>
        <div class="alert alert-danger"><?= e($errores['general']) ?></div>
        <?php endif; ?>

        <form id="formProducto" method="POST" novalidate>
            <?= campoCsrf() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre" value="<?= e($_POST['nombre'] ?? $producto['nombre']) ?>" required>
                    <?php if (isset($errores['nombre'])): ?>
                    <div class="invalid-feedback"><?= e($errores['nombre']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="id_categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                    <select class="form-select <?= isset($errores['id_categoria']) ? 'is-invalid' : '' ?>"
                            id="id_categoria" name="id_categoria" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>"
                            <?php
                            $valSel = $_POST['id_categoria'] ?? $producto['id_categoria'];
                            echo ($valSel == $c['id_categoria']) ? 'selected' : '';
                            ?>>
                            <?= e($c['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['id_categoria'])): ?>
                    <div class="invalid-feedback"><?= e($errores['id_categoria']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="2"><?= e($_POST['descripcion'] ?? $producto['descripcion']) ?></textarea>
                </div>
                <div class="col-md-4">
                    <label for="precio" class="form-label">Precio ($) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control <?= isset($errores['precio']) ? 'is-invalid' : '' ?>" id="precio" name="precio" value="<?= e($_POST['precio'] ?? $producto['precio']) ?>" placeholder="0.00" required>
                           placeholder="0.00">
                    <?php if (isset($errores['precio'])): ?>
                    <div class="invalid-feedback"><?= e($errores['precio']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="stock_actual" class="form-label">Stock actual <span class="text-danger">*</span></label>
                    <input type="number" min="0" class="form-control <?= isset($errores['stock_actual']) ? 'is-invalid' : '' ?>" id="stock_actual" name="stock_actual" value="<?= e($_POST['stock_actual'] ?? $producto['stock_actual']) ?>" placeholder="0" required>
                           placeholder="0">
                    <?php if (isset($errores['stock_actual'])): ?>
                    <div class="invalid-feedback"><?= e($errores['stock_actual']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="stock_minimo" class="form-label">Stock mínimo <span class="text-danger">*</span></label>
                    <input type="number" min="0" class="form-control <?= isset($errores['stock_minimo']) ? 'is-invalid' : '' ?>" id="stock_minimo" name="stock_minimo" value="<?= e($_POST['stock_minimo'] ?? $producto['stock_minimo']) ?>" placeholder="0" required>
                    <?php if (isset($errores['stock_minimo'])): ?>
                    <div class="invalid-feedback"><?= e($errores['stock_minimo']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Guardar cambios
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
