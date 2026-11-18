# TecnoStock — Casos de Prueba

Documento de pruebas que cubre flujos normales, casos de error y casos límite.
Cada caso indica datos de entrada, resultado esperado y resultado obtenido.

---

## 1. Pruebas de inicio de sesión

| ID | Descripción | Datos de entrada | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-01 | Login correcto | admin@tecnostock.cl / admin123 | Accede al panel | ✅ Acceso concedido |
| CP-02 | Clave incorrecta | admin@tecnostock.cl / claveMal | Mensaje "Correo o clave incorrectos" | ✅ Acceso denegado |
| CP-03 | Correo inexistente | noexiste@correo.cl / 123 | Mensaje "Correo o clave incorrectos" | ✅ Acceso denegado |
| CP-04 | Campos vacíos | (vacío) / (vacío) | Validación JS + PHP bloquea envío | ✅ Bloqueado |
| CP-05 | Correo sin formato | "abc" / 123 | Validación JS muestra error de formato | ✅ Bloqueado |

## 2. Pruebas de productos — Crear

| ID | Descripción | Datos de entrada | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-06 | Crear producto válido | CB-TEST-001, Mouse X, Categoría: Accesorios, $8900, stock 10, mín 5 | Producto creado, stock 10 | ✅ Creado correctamente |
| CP-07 | Código duplicado | CB-USB-001 (ya existe) | Mensaje "Ya existe un producto con ese código" | ✅ Rechazado |
| CP-08 | Precio negativo | Código: TEST-002, precio: -100 | Validación JS + PHP bloquea | ✅ Bloqueado |
| CP-09 | Stock negativo | Código: TEST-003, stock_actual: -5 | Validación JS + PHP bloquea | ✅ Bloqueado |
| CP-10 | Nombre muy corto | Código: X-1, nombre: "AB" | Mensaje "El nombre debe tener al menos 3 caracteres" | ✅ Rechazado |
| CP-11 | Sin categoría | Todos los campos válidos menos categoría | Mensaje "Seleccione una categoría" | ✅ Rechazado |

## 3. Pruebas de productos — Editar

| ID | Descripción | Datos de entrada | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-12 | Editar precio válido | Producto CP-06, precio: $9900 | Precio actualizado | ✅ Actualizado |
| CP-13 | Editar stock_actual | Producto CP-06, stock_actual: 20 | Stock actualizado a 20 | ✅ Actualizado |
| CP-14 | Editar categoría | Producto CP-06, categoría: Audio | Categoría actualizada | ✅ Actualizado |
| CP-15 | Código no editable | Intentar cambiar código | Campo código deshabilitado | ✅ No se puede modificar |

## 4. Pruebas de productos — Desactivar

| ID | Descripción | Datos de entrada | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-16 | Desactivar producto con movimientos | Producto CB-USB-001 | Estado → Inactivo (no eliminado) | ✅ Desactivado |
| CP-17 | Reactivar producto | Producto previamente desactivado | Estado → Activo | ✅ Reactivado |
| CP-18 | Confirmación al desactivar | Click en botón desactivar | Confirmación JS "¿Desactivar...?" | ✅ Diálogo de confirmación |

## 5. Pruebas de movimientos — Entradas

| ID | Descripción | Datos de entrada | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-19 | Entrada válida | Entrada, 10 unidades, CB-USB-001 | Stock actual += 10, movimiento registrado | ✅ Stock actualizado y registrado |
| CP-20 | Entrada con observación | Entrada, 5, observación "Reposición" | Movimiento registrado con observación | ✅ Registrado |
| CP-21 | Cantidad cero | Entrada, 0 unidades | Validación JS + PHP: "cantidad mayor a 0" | ✅ Bloqueado |
| CP-22 | Sin seleccionar producto | Entrada, 5, sin producto | "Seleccione un producto" | ✅ Bloqueado |

## 6. Pruebas de movimientos — Salidas

| ID | Descripción | Datos de entrada | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-23 | Salida válida | Salida, 5 unidades, CB-USB-001 | Stock actual -= 5, movimiento registrado | ✅ Stock actualizado y registrado |
| CP-24 | Salida que deja stock bajo mín | Salida que deja stock < stock_minimo | Stock actualizado + alerta de stock bajo | ✅ Alerta mostrada en panel |
| CP-25 | Salida que supera stock disponible | Salida, 1000 unidades (stock < 1000) | "La cantidad supera el stock actual" | ✅ Rechazado |
| CP-26 | Salida que dejaría stock negativo | Salida = stock_actual + 1 | Rechazado por validación PHP | ✅ Rechazado |

## 7. Pruebas de stock bajo (alerta)

| ID | Descripción | Datos de entrada | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-27 | Producto con stock < mínimo | AU-CB-002 (stock 3, mín 6) | Badge rojo en listado + alerta en panel | ✅ Alerta visible |
| CP-28 | Producto con stock = 0 | AC-KB-002 (stock 0, mín 3) | Alerta en panel "stock bajo" | ✅ Alerta visible |
| CP-29 | Producto con stock suficiente | CB-USB-001 (stock 25, mín 5) | Badge verde, sin alerta | ✅ Sin alerta |

## 8. Pruebas de búsqueda

| ID | Descripción | Datos de entrada | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-30 | Buscar por nombre | "cable" | Muestra Cable USB-C 1m, Cable HDMI 2m | ✅ Resultados correctos |
| CP-31 | Buscar por código | "AU-BT" | Muestra Audífonos Bluetooth | ✅ Resultado correcto |
| CP-32 | Buscar por categoría | "Audio" | Muestra productos de categoría Audio | ✅ Resultados correctos |
| CP-33 | Búsqueda sin resultados | "zzzzzz" | Mensaje "No se encontraron productos" | ✅ Mensaje mostrado |
| CP-34 | Filtro por estado inactivos | estado = inactivos | Solo productos inactivos | ✅ Filtro correcto |

## 9. Pruebas de integridad y seguridad

| ID | Descripción | Datos de entrada | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-35 | Inyección SQL en búsqueda | "'; DROP TABLE producto; --" | No afecta la BD (PDO + prepared statements) | ✅ Sin efecto |
| CP-36 | XSS en campo nombre | `<script>alert('xss')</script>` | Texto escapado, no se ejecuta | ✅ Escapado con htmlspecialchars |
| CP-37 | Acceso sin login | URL directa a productos/index.php | Redirige a login | ✅ Redirigido |
| CP-38 | Transacción rollback en error | Simular error en medio de transacción | Stock no se modifica, movimiento no se inserta | ✅ Rollback correcto |

## 10. Pruebas de categorías

| ID | Descripción | Datos de entrada | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-39 | Crear categoría válida | "Pantallas", "Monitores y proyectores" | Categoría creada | ✅ Creada |
| CP-40 | Nombre duplicado | "Audio" (ya existe) | "Ya existe una categoría con ese nombre" | ✅ Rechazado |
| CP-41 | Editar categoría | Cambiar descripción de "Audio" | Actualizada | ✅ Actualizada |
| CP-42 | Contar productos por categoría | Dashboard | Muestra cantidad de productos por categoría | ✅ Conteo correcto |

---

## Resumen de cobertura

| Tipo de prueba | Casos | Aprobados |
|---|---|---|
| Flujos normales | CP-01, CP-06, CP-12-14, CP-19-20, CP-23, CP-30-32, CP-39 | 11/11 |
| Casos de error | CP-02-05, CP-07-11, CP-21-22, CP-25, CP-33, CP-40 | 14/14 |
| Casos límite | CP-24, CP-25-26, CP-28, CP-34 | 6/6 |
| Integridad/seguridad | CP-35-38 | 4/4 |
| **Total** | **42 casos** | **42/42 (100%)** |

## Datos de prueba utilizados

Los datos de prueba se cargan automáticamente al importar `tecnostock.sql`:
- 1 usuario administrador
- 5 categorías
- 10 productos (incluyendo casos con stock bajo)
- 5 movimientos de ejemplo

Productos con stock bajo para validar alertas:
- AU-CB-002 (Audífonos con cable): stock 3, mínimo 6
- AL-HD-002 (Disco Externo 1TB): stock 4, mínimo 3
- AC-KB-002 (Teclado USB): stock 0, mínimo 3
