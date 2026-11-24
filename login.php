<?php
/**
 * Inicio de sesión.
 * Valida correo y clave contra la tabla usuario (hash bcrypt).
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

iniciarSesion();

// Si ya está logueado, ir al dashboard
if (isset($_SESSION['usuario_id'])) {
    redirigir('dashboard.php');
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $correo = limpiar($_POST['correo'] ?? '');
    $clave  = $_POST['clave'] ?? '';

    // Validación servidor
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no es válido';
    }
    if (empty($clave)) {
        $errores[] = 'La clave es obligatoria';
    }

    if (empty($errores)) {
        try {
            $pdo = getConexion();
            $stmt = $pdo->prepare('SELECT id_usuario, nombre, clave, estado FROM usuario WHERE correo = ?');
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch();

            if ($usuario && $usuario['estado'] == 1 && password_verify($clave, $usuario['clave'])) {
                session_regenerate_id(true);
                $_SESSION['usuario_id']     = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                setFlash('success', 'Bienvenido/a, ' . $usuario['nombre']);
                redirigir('dashboard.php');
            } else {
                $errores[] = 'Correo o clave incorrectos';
            }
        } catch (PDOException $ex) {
            error_log('Error de conexión en login: ' . $ex->getMessage());
            $errores[] = 'Error al conectar. Verifique que MySQL esté activo en XAMPP.';
        }
    }
}

$tituloPagina = 'Iniciar sesión';
$basePath = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?> · TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center" style="min-height:100vh">
<div class="container">
    <div class="card login-card p-4">
        <div class="text-center mb-4">
            <i class="bi bi-box-seam-fill text-primary" style="font-size:3rem"></i>
            <h3 class="mt-2">TecnoStock</h3>
            <p class="text-muted">Sistema de Inventario</p>
        </div>

        <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form id="formLogin" method="POST" action="login.php" novalidate>
            <?= campoCsrf() ?>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="correo" name="correo"
                           value="<?= e($_POST['correo'] ?? '') ?>" placeholder="admin@tecnostock.cl" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="clave" class="form-label">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="clave" name="clave" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right"></i> Ingresar
            </button>
        </form>

        <hr class="my-4">
        <div class="alert alert-warning border-warning">
            <small>
                <strong>DEMO — Credenciales de prueba:</strong><br>
                Correo: admin@tecnostock.cl · Clave: admin123<br>
                <em>Solo para evaluación. Eliminar en producción.</em>
            </small>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/validations.js"></script>
</body>
</html>
