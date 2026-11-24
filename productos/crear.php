<?php
/**
 * Crear un nuevo producto.
 * Validaciones en JavaScript y en PHP (servidor).
 * Regla de negocio: el código no puede repetirse.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requerirLogin();

$pdo = getConexion();
$errores = [];

// Obtener categorías activas para el select
$categorias = $pdo->query('SELECT id_categoria, nombre FROM categoria WHERE estado = 1 ORDER BY nombre')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo       = limpiar($_POST['codigo'] ?? '');
    $nombre       = limpiar($_POST['nombre'] ?? '');
    $idCategoria  = (int)($_POST['id_categoria'] ?? 0);
    $descripcion  = limpiar($_POST['descripcion'] ?? '');
    $precio       = (float)($_POST['precio'] ?? -1);
    $stockActual  = (int)($_POST['stock_actual'] ?? -1);
    $stockMinimo  = (int)($_POST['stock_minimo'] ?? -1);

    // --- Validaciones en el servidor ---
    if (mb_strlen($codigo) < 3) {
        $errores['codigo'] = 'El código debe tener al menos 3 caracteres';
    }
    if (mb_strlen($nombre) < 3) {
        $errores['nombre'] = 'El nombre debe tener al menos 3 caracteres';
    }
    if ($idCategoria <= 0) {
        $errores['id_categoria'] = 'Seleccione una categoría';
    }
    if ($precio < 0) {
        $errores['precio'] = 'El precio debe ser un número no negativo';
    }
    if ($stockActual < 0) {
        $errores['stock_actual'] = 'El stock actual debe ser un entero no negativo';
    }
    if ($stockMinimo < 0) {
        $errores['stock_minimo'] = 'El stock mínimo debe ser un entero no negativo';
    }

    // Verificar código único
    if (!isset($errores['codigo'])) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM producto WHERE codigo = ?');
        $stmt->execute([$codigo]);
        if ($stmt->fetchColumn() > 0) {
            $errores['codigo'] = 'Ya existe un producto con ese código';
        }
    }

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria)
                 VALUES (?, ?, ?, ?, ?, ?, 1, ?)'
            );
            $stmt->execute([$codigo, $nombre, $descripcion, $precio, $stockActual, $stockMinimo, $idCategoria]);
            setFlash('success', 'Producto "' . $nombre . '" creado correctamente');
            redirigir('index.php');
        } catch (PDOException $ex) {
            $errores['general'] = 'Error al guardar: ' . $ex->getMessage();
        }
    }
}

$tituloPagina = 'Nuevo producto';
$seccionActiva = 'productos';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-plus-circle"></i> Nuevo producto</h2>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (isset($errores['general'])): ?>
        <div class="alert alert-danger"><?= e($errores['general']) ?></div>
        <?php endif; ?>

        <form id="formProducto" method="POST" novalidate>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= isset($errores['codigo']) ? 'is-invalid' : '' ?>" id="codigo" name="codigo" value="<?= e($_POST['codigo'] ?? '') ?>" placeholder="Ej: CB-USB-003" required>
                    <?php if (isset($errores['codigo'])): ?>
                    <div class="invalid-feedback"><?= e($errores['codigo']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-5">
                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre" value="<?= e($_POST['nombre'] ?? '') ?>" required>
                    <?php if (isset($errores['nombre'])): ?>
                    <div class="invalid-feedback"><?= e($errores['nombre']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="id_categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                    <select class="form-select <?= isset($errores['id_categoria']) ? 'is-invalid' : '' ?>"
                            id="id_categoria" name="id_categoria" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>"
                            <?= (($_POST['id_categoria'] ?? '') == $c['id_categoria']) ? 'selected' : '' ?>>
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
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="2"
                              placeholder="Descripción del producto..."><?= e($_POST['descripcion'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label for="precio" class="form-label">Precio ($) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control <?= isset($errores['precio']) ? 'is-invalid' : '' ?>" id="precio" name="precio" value="<?= e($_POST['precio'] ?? '') ?>" placeholder="0.00" required>
                           placeholder="0.00">
                    <?php if (isset($errores['precio'])): ?>
                    <div class="invalid-feedback"><?= e($errores['precio']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="stock_actual" class="form-label">Stock actual <span class="text-danger">*</span></label>
                    <input type="number" min="0" class="form-control <?= isset($errores['stock_actual']) ? 'is-invalid' : '' ?>" id="stock_actual" name="stock_actual" value="<?= e($_POST['stock_actual'] ?? '0') ?>" required>
                    <?php if (isset($errores['stock_actual'])): ?>
                    <div class="invalid-feedback"><?= e($errores['stock_actual']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="stock_minimo" class="form-label">Stock mínimo <span class="text-danger">*</span></label>
                    <input type="number" min="0" class="form-control <?= isset($errores['stock_minimo']) ? 'is-invalid' : '' ?>" id="stock_minimo" name="stock_minimo" value="<?= e($_POST['stock_minimo'] ?? '0') ?>" required>
                    <?php if (isset($errores['stock_minimo'])): ?>
                    <div class="invalid-feedback"><?= e($errores['stock_minimo']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Guardar producto
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
