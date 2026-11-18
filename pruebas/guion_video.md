# Guion del Video Explicativo — TecnoStock

**Duración estimada:** 4 min 45 seg (máximo 5 min)
**Formato:** Grabación de pantalla + voz en off

---

## Cronograma general

| Tiempo | Sección | Duración |
|---|---|---|
| 0:00 – 0:25 | Introducción y problema | 25 s |
| 0:25 – 0:40 | Stack tecnológico | 15 s |
| 0:40 – 1:05 | Modelo de datos (SQL) | 25 s |
| 1:05 – 1:20 | Inicio de sesión | 15 s |
| 1:20 – 1:35 | Panel principal | 15 s |
| 1:35 – 1:55 | Crear producto | 20 s |
| 1:55 – 2:10 | Listado y búsqueda | 15 s |
| 2:10 – 2:25 | Editar y desactivar | 15 s |
| 2:25 – 2:40 | Registrar entrada | 15 s |
| 2:40 – 2:55 | Registrar salida + alerta stock | 15 s |
| 2:55 – 3:15 | Código: conexión PDO | 20 s |
| 3:15 – 3:40 | Código: transacción de movimiento | 25 s |
| 3:40 – 4:00 | Código: validaciones JavaScript | 20 s |
| 4:00 – 4:20 | Código: restricciones SQL | 20 s |
| 4:20 – 4:35 | Reglas de negocio resumidas | 15 s |
| 4:35 – 4:45 | Cierre | 10 s |
| **Total** | | **4 min 45 s** |

---

## Guion detallado

### [0:00 – 0:25] Introducción y problema

**Pantalla:** Mostrar el caso en el documento o el login del sistema.

**Narración:**
> "Hola. Este es el sistema de inventario TecnoStock, desarrollado para un emprendimiento que vende accesorios tecnológicos. El problema era que registraban sus productos en una planilla, lo que causaba códigos duplicados, errores en las cantidades y poca claridad del stock real. La solución es una aplicación web que centraliza el catálogo y controla las entradas y salidas de existencias."

---

### [0:25 – 0:40] Stack tecnológico

**Pantalla:** Abrir VS Code mostrando la estructura del proyecto.

**Narración:**
> "El proyecto usa HTML5 con Bootstrap para la interfaz, JavaScript para validar en el navegador, PHP 8 con PDO para la lógica del servidor y MySQL para la base de datos. Todo corre en XAMPP, sin frameworks complejos."

---

### [0:40 – 1:05] Modelo de datos

**Pantalla:** Abrir `database/tecnostock.sql` en VS Code, mostrar las 4 tablas. Alternativamente, mostrar el diseño en phpMyAdmin.

**Narración:**
> "La base de datos tiene cuatro tablas: usuario, categoría, producto y movimiento. Producto tiene clave foránea hacia categoría, y movimiento tiene claves foráneas hacia producto y usuario. El código de producto es único. Hay restricciones CHECK para que el precio, el stock y la cantidad no sean negativos. El script SQL es reproducible e incluye datos de prueba."

---

### [1:05 – 1:20] Inicio de sesión

**Pantalla:** Navegador en `http://localhost/TecnoStock/login.php`. Escribir las credenciales y entrar.

**Narración:**
> "El sistema parte con inicio de sesión. La contraseña se guarda con hash bcrypt, no en texto plano. Ingreso con el usuario administrador y accedo al panel."

---

### [1:20 – 1:35] Panel principal

**Pantalla:** Mostrar el dashboard con las tarjetas de estadísticas y la alerta de stock bajo.

**Narración:**
> "El panel muestra el total de productos activos, categorías, el valor del inventario y, en rojo, los productos con stock bajo. Esta alerta aparece automáticamente cuando el stock actual es menor al stock mínimo definido."

---

### [1:35 – 1:55] Crear producto

**Pantalla:** Click en "Nuevo producto", llenar el formulario, guardar.

**Narración:**
> "Para registrar un producto completo el formulario con código, nombre, categoría, descripción, precio, stock actual y stock mínimo. JavaScript valida los campos antes de enviar. En el servidor, PHP vuelve a validar y verifica que el código no esté duplicado antes de guardar con una consulta preparada."

---

### [1:55 – 2:10] Listado y búsqueda

**Pantalla:** Volver al listado, escribir en la barra de búsqueda (ej. "cable"), mostrar resultados.

**Narración:**
> "El listado muestra todos los productos con su stock actual y estado. Puedo buscar por nombre, código o categoría. Los productos con stock bajo se marcan con una etiqueta roja."

---

### [2:10 – 2:25] Editar y desactivar

**Pantalla:** Editar un producto (cambiar precio), luego desactivar otro (con diálogo de confirmación).

**Narración:**
> "Al editar puedo cambiar el precio, la descripción, la categoría y las cantidades, pero no el código, porque es único e inmutable. Para desactivar un producto que ya no se vende, cambio su estado, no lo elimino. El sistema pide confirmación antes."

---

### [2:25 – 2:40] Registrar entrada

**Pantalla:** Ir a "Movimientos" → "Registrar movimiento". Seleccionar tipo "entrada", un producto, cantidad 10, guardar.

**Narración:**
> "Para registrar una entrada selecciono el tipo, el producto y la cantidad. Al guardar, el sistema inserta el movimiento y suma automáticamente las unidades al stock del producto, todo dentro de una transacción."

---

### [2:40 – 2:55] Registrar salida + alerta stock

**Pantalla:** Registrar una salida que deje el stock bajo el mínimo. Ver la alerta que aparece.

**Narración:**
> "Ahora registro una salida. El sistema valida que la cantidad no supere el stock disponible para no dejar stock negativo. Si el resultado queda bajo el mínimo, muestra una alerta de advertencia, y el producto aparece destacado en el panel."

---

### [2:55 – 3:15] Código: conexión PDO

**Pantalla:** Abrir `config/database.php` en VS Code. Mostrar `getConexion()`.

**Narración:**
> "Revisemos el código. La conexión usa PDO con manejo de errores por excepciones y fetch asociativo. Las consultas preparadas están activas, lo que previene inyección SQL en todas las operaciones."

---

### [3:15 – 3:40] Código: transacción de movimiento

**Pantalla:** Abrir `movimientos/registrar.php`. Mostrar el bloque de la transacción (`beginTransaction`, `INSERT`, `UPDATE`, `commit`, `rollBack`).

**Narración:**
> "Al registrar un movimiento uso una transacción: primero inserto el registro con la fecha, tipo, cantidad, producto y usuario responsable. Luego actualizo el stock sumando o restando según el tipo. Si algo falla, hago rollBack y el stock no se modifica. Antes de la transacción valido que una salida no supere el stock disponible."

---

### [3:40 – 4:00] Código: validaciones JavaScript

**Pantalla:** Abrir `assets/js/validations.js`. Mostrar la validación del formulario de producto.

**Narración:**
> "En JavaScript valido en el navegador: el código y nombre tienen longitud mínima, el precio no puede ser negativo y los stocks deben ser enteros no negativos. Estas validaciones se repiten en PHP, porque las del cliente se pueden saltar."

---

### [4:00 – 4:20] Código: restricciones SQL

**Pantalla:** Volver a `tecnostock.sql`, mostrar las restricciones CHECK, UNIQUE y FOREIGN KEY.

**Narración:**
> "En la base de datos, el código de producto tiene restricción UNIQUE para que no se repita. Las restricciones CHECK garantizan que el precio, el stock y la cantidad sean no negativos. Las claves foráneas mantienen la integridad referencial entre producto, categoría y movimiento."

---

### [4:20 – 4:35] Reglas de negocio resumidas

**Pantalla:** Mostrar el README.md sección "Reglas de negocio" o el listado de productos.

**Narración:**
> "Las reglas de negocio del caso están cubiertas: el código es único, no se permiten valores negativos, una salida no deja stock bajo cero, el stock se actualiza automáticamente, y desactivar un producto no lo elimina físicamente."

---

### [4:35 – 4:45] Cierre

**Pantalla:** Mostrar el panel completo del sistema.

**Narración:**
> "El repositorio está en GitHub con el código completo, el script SQL y los casos de prueba. Gracias por su atención."

---

## Lista de verificación para grabar

Antes de grabar, tener listo:

- [ ] XAMPP corriendo (Apache + MySQL activos)
- [ ] Base de datos `tecnostock` importada con datos de prueba
- [ ] Usuario admin creado con `database/seed.php`
- [ ] VS Code abierto con el proyecto
- [ ] Navegador abierto en `http://localhost/TecnoStock/`
- [ ] phpMyAdmin disponible para mostrar la BD si se necesita
- [ ] Pantalla a 1080p para que el código sea legible
- [ ] Micrófono con buena calidad de audio
- [ ] Cerrar notificaciones y aplicaciones de fondo

## Notas para el cálculo de tiempo

- Velocidad de habla estimada: 130-150 palabras por minuto en español.
- Narración total: aproximadamente 580 palabras en 285 segundos.
- Margen de 15 segundos (4:45 a 5:00) por si alguna sección se extiende.
- Si sobra tiempo, usarlo en la transacción de movimiento (sección más importante).
- Si falta tiempo, reducir la sección de stack tecnológico o el cierre.
