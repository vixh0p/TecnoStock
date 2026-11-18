# TecnoStock — Sistema de Inventario

Sistema web de gestión de inventario para el emprendimiento TecnoStock.
Permite administrar productos, categorías y movimientos de stock (entradas/salidas)
con alertas de stock bajo. Desarrollado con PHP 8 + PDO, MySQL y Bootstrap 5.

## Tabla de contenidos
1. [Requisitos](#requisitos)
2. [Instalación](#instalación)
3. [Estructura del proyecto](#estructura-del-proyecto)
4. [Entidades y modelo de datos](#entidades-y-modelo-de-datos)
5. [Reglas de negocio](#reglas-de-negocio)
6. [Credenciales de prueba](#credenciales-de-prueba)
7. [Casos de prueba](#casos-de-prueba)

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
