<?php
/**
 * Conexión a la base de datos mediante PDO (PHP 8).
 * Usar consultas preparadas para todas las operaciones.
 */

declare(strict_types=1);

const DB_HOST    = 'localhost';
const DB_NAME    = 'tecnostock';
const DB_USER    = 'root';
const DB_PASS    = '';   // XAMPP: contraseña vacía por defecto
const DB_CHARSET = 'utf8mb4';

/**
 * Devuelve una instancia PDO única (patrón singleton simple).
 *
 * @return PDO
 * @throws PDOException Si no se puede conectar.
 */
function getConexion(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            // En producción se debería registrar en log, no mostrar al usuario.
            error_log('Error de conexión: ' . $e->getMessage());
            die('No se pudo conectar a la base de datos. Verifique que XAMPP (MySQL) esté activo.');
        }
    }

    return $pdo;
}
