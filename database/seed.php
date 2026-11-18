<?php
/**
 * Seed: crea el usuario administrador con hash bcrypt correcto.
 * Ejecutar UNA VEZ después de importar tecnostock.sql.
 * Abrir en el navegador: http://localhost/TecnoStock/database/seed.php
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getConexion();

// Verificar si ya existe el usuario admin
$stmt = $pdo->prepare('SELECT id_usuario FROM usuario WHERE correo = ?');
$stmt->execute(['admin@tecnostock.cl']);

if ($stmt->fetch()) {
    // Actualizar la clave
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('UPDATE usuario SET clave = ?, estado = 1 WHERE correo = ?');
    $stmt->execute([$hash, 'admin@tecnostock.cl']);
    echo "<p>Usuario admin actualizado. Clave: <strong>admin123</strong></p>";
} else {
    // Crear el usuario
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO usuario (nombre, correo, clave, estado) VALUES (?, ?, ?, 1)');
    $stmt->execute(['Encargado Inventario', 'admin@tecnostock.cl', $hash]);
    echo "<p>Usuario admin creado con id " . $pdo->lastInsertId() . ". Clave: <strong>admin123</strong></p>";
}

echo '<p><a href="../login.php">Ir a inicio de sesión</a></p>';
