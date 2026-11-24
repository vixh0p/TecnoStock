<?php
/**
 * Crear una nueva categoría.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requerirLogin();

$pdo = getConexion();
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $nombre      = limpiar($_POST['nombre'] ?? '');
    $descripcion = limpiar($_POST['descripcion'] ?? '');

    // Validación servidor
    if (mb_strlen($nombre) < 2) {
        $errores['nombre'] = 'El nombre debe tener al menos 2 caracteres';
    }

    // Verificar nombre único
    if (!isset($errores['nombre'])) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM categoria WHERE nombre = ?');
        $stmt->execute([$nombre]);
        if ($stmt->fetchColumn() > 0) {
            $errores['nombre'] = 'Ya existe una categoría con ese nombre';
        }
    }

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO categoria (nombre, descripcion, estado) VALUES (?, ?, 1)');
            $stmt->execute([$nombre, $descripcion]);
            setFlash('success', 'Categoría "' . $nombre . '" creada correctamente');
            redirigir('index.php');
        } catch (PDOException $ex) {
            error_log('Error al guardar categoría: ' . $ex->getMessage());
            $errores['general'] = 'Ocurrió un error al guardar la categoría. Intente nuevamente.';
        }
    }
}

$tituloPagina = 'Nueva categoría';
$seccionActiva = 'categorias';
$basePath = '../';
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-plus-circle"></i> Nueva categoría</h2>
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
                <input type="text" class="form-control <?= isset($errores['nombre']) ? 'is-invalid' : '' ?>"
                       id="nombre" name="nombre" value="<?= e($_POST['nombre'] ?? '') ?>" required
                       placeholder="Ej: Cables y Adaptadores">
                <?php if (isset($errores['nombre'])): ?>
                <div class="invalid-feedback"><?= e($errores['nombre']) ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="2"
                          placeholder="Descripción de la categoría..."><?= e($_POST['descripcion'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Guardar categoría
            </button>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
