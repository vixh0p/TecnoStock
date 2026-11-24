<?php
/**
 * Editar una categoría existente.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requerirLogin();

$pdo = getConexion();
$errores = [];

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('error', 'ID de categoría no válido');
    redirigir('index.php');
}

// Cargar categoría
$stmt = $pdo->prepare('SELECT * FROM categoria WHERE id_categoria = ?');
$stmt->execute([$id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    setFlash('error', 'Categoría no encontrada');
    redirigir('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $nombre      = limpiar($_POST['nombre'] ?? '');
    $descripcion = limpiar($_POST['descripcion'] ?? '');
    $estado      = isset($_POST['estado']) ? 1 : 0;

    // Validación servidor
    if (mb_strlen($nombre) < 2) {
        $errores['nombre'] = 'El nombre debe tener al menos 2 caracteres';
    }

    // Verificar nombre único (excluyendo el actual)
    if (!isset($errores['nombre'])) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM categoria WHERE nombre = ? AND id_categoria != ?');
        $stmt->execute([$nombre, $id]);
        if ($stmt->fetchColumn() > 0) {
            $errores['nombre'] = 'Ya existe otra categoría con ese nombre';
        }
    }

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare('UPDATE categoria SET nombre = ?, descripcion = ?, estado = ? WHERE id_categoria = ?');
            $stmt->execute([$nombre, $descripcion, $estado, $id]);
            setFlash('success', 'Categoría actualizada correctamente');
            redirigir('index.php');
        } catch (PDOException $ex) {
            error_log('Error al actualizar categoría: ' . $ex->getMessage());
            $errores['general'] = 'Ocurrió un error al actualizar la categoría. Intente nuevamente.';
        }
    }
}

$tituloPagina = 'Editar categoría';
$seccionActiva = 'categorias';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-pencil-square"></i> Editar categoría</h2>
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (isset($errores['general'])): ?>
        <div class="alert alert-danger"><?= e($errores['general']) ?></div>
        <?php endif; ?>

        <form id="formCategoria" method="POST" novalidate>
            <?= campoCsrf() ?>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre" value="<?= e($_POST['nombre'] ?? $categoria['nombre']) ?>" placeholder="Ej: Cables y Adaptadores" required>
                       placeholder="Ej: Cables y Adaptadores">
                <?php if (isset($errores['nombre'])): ?>
                <div class="invalid-feedback"><?= e($errores['nombre']) ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="2"
                          placeholder="Descripción de la categoría..."><?= e($_POST['descripcion'] ?? $categoria['descripcion']) ?></textarea>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="estado" name="estado" <?= (($_POST['estado'] ?? $categoria['estado']) == 1) ? 'checked' : '' ?>>
                <label for="estado" class="form-check-label">Categoría activa</label>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Guardar cambios
            </button>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
