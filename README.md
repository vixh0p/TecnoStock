# TecnoStock — Sistema de Inventario

Sistema web de gestión de inventario para el emprendimiento TecnoStock.
Permite administrar productos, categorías y movimientos de stock (entradas/salidas)
con alertas de stock bajo. Desarrollado con PHP 8 + PDO, MySQL y Bootstrap 5.

## Tabla de contenidos
1. [Requisitos](#requisitos)
2. [Instalación](#instalación)
3. [Actor y alcance](#actor-y-alcance)
4. [Estructura del proyecto](#estructura-del-proyecto)
5. [Entidades y modelo de datos](#entidades-y-modelo-de-datos)
6. [Reglas de negocio](#reglas-de-negocio)
7. [Credenciales de prueba](#credenciales-de-prueba)
8. [Casos de prueba](#casos-de-prueba)

---

## Actor y alcance

**Actor del sistema: Encargado de inventario**

Es el único usuario del sistema. Sus responsabilidades son:

- Administrar productos: registrar, consultar, buscar, editar y desactivar.
- Administrar categorías: crear y editar.
- Controlar existencias: registrar entradas y salidas de stock.
- Monitorear alertas de stock bajo.

**Alcance del sistema:**

El sistema gestiona el catálogo de productos y sus movimientos de inventario (entradas y salidas). No incluye módulos de ventas, facturación, ni reportes avanzados. Está diseñado para funcionar en un servidor local (XAMPP) y ser operado por un usuario no técnico.

**Requerimientos funcionales:**

| RF | Descripción |
|---|---|
| RF-01 | Iniciar sesión con un usuario previamente registrado. |
| RF-02 | Registrar productos con código, nombre, categoría, descripción, precio, stock actual y stock mínimo. |
| RF-03 | Consultar el listado de productos. |
| RF-04 | Buscar productos por nombre, código o categoría. |
| RF-05 | Modificar el precio, la descripción, la categoría y las cantidades de un producto. |
| RF-06 | Desactivar productos que ya no se comercializan. |
| RF-07 | Registrar entradas y salidas de existencias. |
| RF-08 | Mostrar una alerta cuando el stock actual sea menor que el stock mínimo. |

**Matriz de trazabilidad (RF → archivo → caso de prueba):**

| RF | Archivo que lo implementa | Caso de prueba | Verificación |
|---|---|---|---|
| RF-01 | `login.php` | CP-01 a CP-05 | Login correcto, clave incorrecta, campos vacíos |
| RF-02 | `productos/crear.php` | CP-06 a CP-11 | Crear válido, código duplicado, negativos, campos cortos |
| RF-03 | `productos/index.php` | CP-30, CP-34 | Listado completo, filtro por estado |
| RF-04 | `productos/index.php` | CP-30 a CP-34 | Búsqueda por nombre, código, categoría, sin resultados |
| RF-05 | `productos/editar.php` | CP-12 a CP-15 | Editar precio, stock, categoría; código inmutable |
| RF-06 | `productos/desactivar.php` | CP-16 a CP-18 | Desactivar (soft delete), reactivar, confirmación |
| RF-07 | `movimientos/registrar.php` | CP-19 a CP-26 | Entrada, salida, cantidad 0, salida > stock, concurrencia |
| RF-08 | `dashboard.php` + `productos/index.php` | CP-27 a CP-29 | Stock bajo mínimo, stock 0, stock suficiente |

**Reglas de negocio y su implementación:**

| # | Regla | Implementación técnica | Caso de prueba |
|---|---|---|---|
| RN-1 | El código no puede repetirse | UNIQUE en SQL + validación PHP en `crear.php` | CP-07 |
| RN-2 | No negativos (precio, stock, cantidad) | CHECK en SQL + `filter_var` en PHP | CP-08, CP-09, CP-21 |
| RN-3 | Movimiento registra fecha, tipo, cantidad, producto, usuario | Campos NOT NULL + FK en `movimiento` | CP-19, CP-23 |
| RN-4 | Salida no deja stock bajo cero | `FOR UPDATE` + validación PHP en `registrar.php` | CP-25, CP-26 |
| RN-5 | Desactivar no elimina | `UPDATE estado = 0` (soft delete) en `desactivar.php` | CP-16 |
| RN-6 | Stock se actualiza automático | `UPDATE stock_actual = stock_actual +/- ?` en transacción | CP-19, CP-23 |

---

## Requisitos

| Herramienta | Versión | Uso |
|---|---|---|
| XAMPP | 8.x | Apache + PHP 8 + MySQL en servidor local |
| Visual Studio Code | actual | Editor de código |
| Git | actual | Versionamiento |
| Navegador web | actual | Acceso a la aplicación |

## Instalación

1. **Instalar XAMPP** y abrir el panel de control. Iniciar Apache y MySQL.

2. **Copiar el proyecto** a la carpeta `htdocs` de XAMPP:
   ```
   C:\xampp\htdocs\TecnoStock\
   ```

3. **Crear la base de datos**:
   - Abrir phpMyAdmin: `http://localhost/phpmyadmin`
   - Pestaña "Importar" → seleccionar `database/tecnostock.sql` → Ejecutar

4. **Crear el usuario administrador**:
   - Abrir en el navegador: `http://localhost/TecnoStock/database/seed.php`
   - Esto crea el usuario con hash bcrypt correcto.

5. **Acceder al sistema**:
   - URL: `http://localhost/TecnoStock/`
   - Correo: `admin@tecnostock.cl`
   - Clave: `admin123`

## Estructura del proyecto

```
TecnoStock/
├── config/
│   └── database.php          Conexión PDO a MySQL
├── database/
│   ├── tecnostock.sql         Script SQL (tablas + datos)
│   └── seed.php               Crear usuario admin (hash bcrypt)
├── includes/
│   ├── header.php             Cabecera HTML + menú de navegación
│   ├── footer.php             Pie de página + scripts JS
│   ├── auth.php               Autenticación y sesiones
│   └── functions.php          Funciones auxiliares (sanitización, flash, formatos)
├── assets/
│   ├── css/style.css          Estilos personalizados
│   └── js/validations.js      Validaciones JavaScript (cliente)
├── productos/
│   ├── index.php              Listado + búsqueda de productos
│   ├── crear.php              Registrar nuevo producto
│   ├── editar.php             Editar producto
│   ├── mostrar.php             Ver detalle + historial de movimientos
│   ├── desactivar.php         Desactivar producto (no elimina)
│   └── activar.php             Reactivar producto
├── categorias/
│   ├── index.php              Listado de categorías
│   ├── crear.php              Crear categoría
│   └── editar.php             Editar categoría
├── movimientos/
│   ├── index.php              Listado de movimientos
│   └── registrar.php          Registrar entrada/salida de stock
├── index.php                  Punto de entrada (redirige a login)
├── login.php                  Inicio de sesión
├── logout.php                 Cierre de sesión
├── dashboard.php              Panel principal (estadísticas + alertas)
├── .gitignore
└── README.md
```

## Entidades y modelo de datos

| Entidad | Atributos | Relación |
|---|---|---|
| **Usuario** | id_usuario (PK), nombre, correo (unique), clave (bcrypt), estado | 1:N con movimiento |
| **Categoría** | id_categoria (PK), nombre, descripción, estado | 1:N con producto |
| **Producto** | id_producto (PK), código (unique), nombre, descripción, precio, stock_actual, stock_minimo, estado, id_categoria (FK) | N:1 con categoría, 1:N con movimiento |
| **Movimiento** | id_movimiento (PK), fecha_hora, tipo (entrada/salida), cantidad, observación, id_producto (FK), id_usuario (FK) | N:1 con producto y usuario |

**Restricciones de base de datos:**
- `producto.codigo` es UNIQUE (código no repetible)
- CHECK: precio, stock_actual, stock_minimo >= 0
- CHECK: cantidad > 0 en movimientos
- FK: producto → categoría, movimiento → producto, movimiento → usuario

## Reglas de negocio

| # | Regla | Implementación |
|---|---|---|
| 1 | El código de producto no puede repetirse | UNIQUE en BD + validación PHP |
| 2 | Precio, stock y cantidad no pueden ser negativos | CHECK en BD + validación JS/PHP |
| 3 | Todo movimiento registra fecha, tipo, cantidad, producto y usuario | Campos NOT NULL + FK |
| 4 | Una salida no puede dejar el stock bajo cero | Validación PHP antes de la transacción |
| 5 | Un producto con movimientos se desactiva, no se elimina | UPDATE estado = 0 (soft delete) |
| 6 | El stock se actualiza automáticamente tras cada movimiento | Transacción PHP + UPDATE en BD |

## Credenciales de prueba

| Campo | Valor |
|---|---|
| Correo | admin@tecnostock.cl |
| Clave | admin123 |

## Casos de prueba

Ver archivo `pruebas/casos_prueba.md` con la documentación completa de pruebas
de flujos normales, errores y casos límite.
