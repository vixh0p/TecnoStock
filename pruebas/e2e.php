<?php
/**
 * Prueba automatizada end-to-end del MVP (criterio 5).
 *
 * Requisitos: Apache y MySQL activos, base `tecnostock` importada.
 * Uso: C:\xampp\php\php.exe pruebas\e2e.php
 *
 * Solo crea y elimina datos de prueba con código E2E-001/E2E-002; no toca el seed.
 */

declare(strict_types=1);

$base = 'http://localhost/TecnoStock';
$jar  = sys_get_temp_dir() . '/tecnostock_e2e_cookies.txt';
@unlink($jar);

$pass = 0;
$fail = 0;

function check(string $name, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "PASS  $name\n";
    } else {
        $fail++;
        echo "FAIL  $name" . ($detail !== '' ? "  [$detail]" : '') . "\n";
    }
}

function req(string $method, string $path, ?array $data = null, bool $follow = false): array
{
    global $base, $jar;
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_TIMEOUT        => 20,
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data ?? []));
    }
    $raw   = curl_exec($ch);
    $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hsize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    return [
        'code'    => $code,
        'headers' => substr((string)$raw, 0, $hsize),
        'body'    => substr((string)$raw, $hsize),
    ];
}

function csrf(string $html): string
{
    return preg_match('/name="csrf_token" value="([^"]+)"/', $html, $m) ? $m[1] : '';
}

function has(string $html, string $needle): bool
{
    return strpos($html, $needle) !== false;
}

function location(array $r): string
{
    return preg_match('/^Location:\s*(.+)$/mi', $r['headers'], $m) ? trim($m[1]) : '';
}

$pdo = new PDO('mysql:host=localhost;dbname=tecnostock;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// Limpieza de ejecuciones anteriores (solo fixtures E2E)
$pdo->exec("DELETE m FROM movimiento m JOIN producto p ON p.id_producto = m.id_producto
            WHERE p.codigo IN ('E2E-001', 'E2E-002')");
$pdo->exec("DELETE FROM producto WHERE codigo IN ('E2E-001', 'E2E-002')");

// --- 1. Login ---------------------------------------------------------
$r = req('GET', '/login.php');
check('GET login.php = 200', $r['code'] === 200, 'code ' . $r['code']);
check('login muestra formulario', has($r['body'], 'Iniciar sesi'));
$token = csrf($r['body']);
check('login incluye token CSRF', $token !== '');

$r = req('POST', '/login.php', ['correo' => 'admin@tecnostock.cl', 'clave' => 'claveMala', 'csrf_token' => $token]);
check('login con clave mala rechazado', $r['code'] === 200 && has($r['body'], 'Correo o clave incorrectos'));

$r = req('POST', '/login.php', ['correo' => 'admin@tecnostock.cl', 'clave' => 'admin123', 'csrf_token' => $token]);
check('login correcto redirige', $r['code'] === 302 && has(location($r), 'dashboard.php'), 'code ' . $r['code'] . ' loc ' . location($r));

$r = req('GET', '/dashboard.php');
check('dashboard carga autenticado', $r['code'] === 200 && has($r['body'], 'Panel de Control'));
check('dashboard muestra alerta de stock bajo', has($r['body'], 'Productos con stock bajo'));
check('dashboard muestra navegacion autenticada', has($r['body'], 'Salir'));

// --- 2. CSRF ----------------------------------------------------------
$r = req('POST', '/productos/crear.php', ['codigo' => 'X-1', 'nombre' => 'Sin token']);
check('POST sin CSRF es bloqueado', has($r['body'], 'Token CSRF inv'));

// --- 3. Crear producto ------------------------------------------------
$r = req('GET', '/productos/crear.php');
$token = csrf($r['body']);
check('formulario crear carga', $r['code'] === 200 && $token !== '');

$r = req('POST', '/productos/crear.php', [
    'csrf_token' => $token, 'codigo' => 'E2E-001', 'nombre' => 'Producto E2E',
    'id_categoria' => 1, 'descripcion' => 'Prueba automatizada', 'precio' => '1000',
    'stock_actual' => '10', 'stock_minimo' => '5',
]);
check('crear producto valido redirige', $r['code'] === 302, 'code ' . $r['code']);

$id = (int)$pdo->query("SELECT id_producto FROM producto WHERE codigo = 'E2E-001'")->fetchColumn();
check('producto persistido en BD', $id > 0);

$r = req('GET', '/productos/index.php?q=E2E-001');
check('busqueda por codigo lo encuentra', has($r['body'], 'Producto E2E'));
check('precio formateado en CLP', has($r['body'], '$1.000'));

$r = req('POST', '/productos/crear.php', [
    'csrf_token' => $token, 'codigo' => 'E2E-001', 'nombre' => 'Duplicado',
    'id_categoria' => 1, 'descripcion' => '', 'precio' => '1000',
    'stock_actual' => '1', 'stock_minimo' => '1',
]);
check('codigo duplicado rechazado', has($r['body'], 'Ya existe un producto con ese c'));

$r = req('POST', '/productos/crear.php', [
    'csrf_token' => $token, 'codigo' => 'E2E-002', 'nombre' => 'Precio Negativo',
    'id_categoria' => 1, 'descripcion' => '', 'precio' => '-5',
    'stock_actual' => '1', 'stock_minimo' => '1',
]);
check('precio negativo rechazado', has($r['body'], 'precio debe ser un n'));

// --- 4. Editar --------------------------------------------------------
$r = req('GET', '/productos/editar.php?id=' . $id);
check('formulario editar carga', $r['code'] === 200 && has($r['body'], 'Editar producto'));
$token = csrf($r['body']);

$r = req('POST', '/productos/editar.php?id=' . $id, [
    'csrf_token' => $token, 'nombre' => 'Producto E2E editado', 'id_categoria' => 2,
    'descripcion' => 'Editado', 'precio' => '1500', 'stock_actual' => '10', 'stock_minimo' => '6',
]);
check('editar producto redirige', $r['code'] === 302);

$row = $pdo->query("SELECT nombre, precio, stock_minimo FROM producto WHERE id_producto = $id")->fetch(PDO::FETCH_ASSOC);
check('edicion persistida', $row['nombre'] === 'Producto E2E editado' && (float)$row['precio'] === 1500.0 && (int)$row['stock_minimo'] === 6);
check('codigo no editable', (string)$pdo->query("SELECT codigo FROM producto WHERE id_producto = $id")->fetchColumn() === 'E2E-001');

// --- 5. Movimientos ---------------------------------------------------
$r = req('GET', '/movimientos/registrar.php');
$token = csrf($r['body']);
check('formulario movimiento carga', $r['code'] === 200 && has($r['body'], 'Registrar movimiento de stock'));

$r = req('POST', '/movimientos/registrar.php', [
    'csrf_token' => $token, 'tipo' => 'entrada', 'cantidad' => '5',
    'id_producto' => (string)$id, 'observacion' => 'E2E entrada',
]);
check('entrada valida redirige', $r['code'] === 302);
check('stock sube con entrada', (int)$pdo->query("SELECT stock_actual FROM producto WHERE id_producto = $id")->fetchColumn() === 15);

$r = req('GET', '/movimientos/index.php');
check('flash confirma entrada y stock', has($r['body'], 'Entrada de 5 unidades registrada. Stock actual: 15'));

$r = req('POST', '/movimientos/registrar.php', [
    'csrf_token' => $token, 'tipo' => 'salida', 'cantidad' => '999',
    'id_producto' => (string)$id, 'observacion' => 'E2E salida excedida',
]);
check('salida mayor al stock rechazada', has($r['body'], 'supera el stock actual'));
check('stock intacto tras salida rechazada', (int)$pdo->query("SELECT stock_actual FROM producto WHERE id_producto = $id")->fetchColumn() === 15);

$r = req('POST', '/movimientos/registrar.php', [
    'csrf_token' => $token, 'tipo' => 'salida', 'cantidad' => '0',
    'id_producto' => (string)$id, 'observacion' => 'E2E cantidad cero',
]);
check('cantidad 0 rechazada', has($r['body'], 'entero mayor a 0'));

$r = req('POST', '/movimientos/registrar.php', [
    'csrf_token' => $token, 'tipo' => 'salida', 'cantidad' => '10',
    'id_producto' => (string)$id, 'observacion' => 'E2E salida',
]);
check('salida valida redirige', $r['code'] === 302);
check('stock baja con salida', (int)$pdo->query("SELECT stock_actual FROM producto WHERE id_producto = $id")->fetchColumn() === 5);

$r = req('GET', '/movimientos/index.php');
check('alerta de stock bajo tras salida', has($r['body'], 'ATENCI'));

// --- 6. Desactivar (soft delete) -------------------------------------
$r = req('GET', '/productos/desactivar.php?id=' . $id);
check('GET desactivar redirige sin actuar', $r['code'] === 302 && has(location($r), 'index.php'));
$r = req('GET', '/productos/index.php');
check('GET desactivar informa accion no permitida', has($r['body'], 'no permitida'));
check('GET no cambia estado', (int)$pdo->query("SELECT estado FROM producto WHERE id_producto = $id")->fetchColumn() === 1);

$r = req('POST', '/productos/desactivar.php', ['csrf_token' => $token, 'id' => (string)$id]);
check('POST desactivar redirige', $r['code'] === 302);
check('producto queda inactivo, no borrado', (int)$pdo->query("SELECT estado FROM producto WHERE id_producto = $id")->fetchColumn() === 0);

$r = req('POST', '/movimientos/registrar.php', [
    'csrf_token' => $token, 'tipo' => 'entrada', 'cantidad' => '1',
    'id_producto' => (string)$id, 'observacion' => 'E2E inactivo',
]);
check('producto inactivo no acepta movimientos', has($r['body'], 'no existe o est'));

$r = req('POST', '/productos/activar.php', ['csrf_token' => $token, 'id' => (string)$id]);
check('reactivar redirige', $r['code'] === 302);
check('producto vuelve a activo', (int)$pdo->query("SELECT estado FROM producto WHERE id_producto = $id")->fetchColumn() === 1);

// --- 7. Busquedas -----------------------------------------------------
$r = req('GET', '/productos/index.php?q=Audio');
check('busqueda por categoria', has($r['body'], 'Audio'));
$r = req('GET', '/productos/index.php?q=ZZZZ');
check('busqueda sin resultados informa', has($r['body'], 'No se encontraron productos.'));

// Limpieza final (solo fixtures E2E)
$pdo->exec("DELETE m FROM movimiento m JOIN producto p ON p.id_producto = m.id_producto
            WHERE p.codigo IN ('E2E-001', 'E2E-002')");
$pdo->exec("DELETE FROM producto WHERE codigo IN ('E2E-001', 'E2E-002')");

echo "\nRESULTADO: $pass PASS / $fail FAIL\n";
exit($fail === 0 ? 0 : 1);
