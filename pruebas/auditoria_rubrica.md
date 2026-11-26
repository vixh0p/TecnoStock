# Auditoría de rúbrica — TecnoStock

Informe de cumplimiento del Caso 1. Lo mantiene el agente `auditor`; es la evidencia del
criterio 5. Regla: una casilla solo se marca con evidencia reproducible (archivo:línea, comando,
respuesta HTTP o captura). Lo no ejecutado queda como **No verificado**.

- Última auditoría: 2026-11-25, con Apache y MariaDB 10.4 activos y base reimportada.
- Veredicto: **LISTO CON OBSERVACIONES** (MVP 90/90 verificado; falta el enlace del video).
- Evidencia principal: `pruebas/e2e.php` → **40 PASS / 0 FAIL**.

## Puertas eliminatorias

| Puerta | Estado | Evidencia |
|---|---|---|
| G1 Arranca | Cumple | `http://localhost/TecnoStock/` responde 200 con Apache + MariaDB activos |
| G2 Login | Cumple | `admin@tecnostock.cl`/`admin123` → 302 a dashboard; clave mala → error |
| G3 Flujo completo | Cumple | `e2e.php`: crear → listar → buscar → editar → desactivar → entrada → salida → alerta |
| G4 BD reconstruible | Cumple | `mysql --default-character-set=utf8mb4 < database/tecnostock.sql` sin errores; 10 productos, 6 movimientos, tildes correctas |
| G5 Seis reglas de negocio | Cumple | Código único, no negativos, movimiento completo, salida ≤ stock, soft delete, stock automático (e2e + SQL) |
| G6 CSRF + PDO | Cumple | POST sin token bloqueado; `verificarCsrf()` en cada mutación; `prepare()` con `?` |
| G7 Coherencia docs/código | Cumple | README, casos y script alineados tras corregir el encabezado del `.sql` y el paso de `seed.php` |

## Criterios

| Criterio | Pts | Estado | Evidencia | Brecha |
|---|---|---|---|---|
| 1. Análisis y requerimientos | 15 | Cumple | README: alcance, actor, RF-01…RF-08, RN-1…RN-6, matriz RF→archivo→CP | — |
| 2. Base de datos e integridad | 25 | Cumple | 4 tablas, PK/FK/UNIQUE/CHECK/índices, InnoDB/utf8mb4, script reconstruible, stock íntegro | — |
| 3. Funcionalidades del MVP | 30 | Cumple | 40/40 verificaciones HTTP + BD (`pruebas/e2e.php`) | — |
| 4. Interfaz y validaciones | 10 | Cumple (800 px) | Bootstrap 5.3.3, navbar colapsable, cards y tablas responsive sin overflow horizontal, `is-invalid`/`invalid-feedback`, validación JS y PHP, confirmaciones | Revisión a 360 px no verificable con el navegador de la app |
| 5. Pruebas y calidad | 10 | Cumple | `e2e.php` reproducible, CP-01…CP-43, PDO preparado, `error_log`, historial Git | — |
| 6. Video explicativo | 10 | No verificado | `pruebas/guion_video.md` (4:45) listo | Falta grabar y pegar el enlace en el README |

**Puntaje:** 90/90 del MVP verificados. Criterio 6 se califica cuando exista el video accesible.

## Checklist nivel Excelente

- [x] C1: problema, alcance, actor, RF y RN explícitos y trazables a archivo y CP.
- [x] C1: sin funcionalidades prometidas que no existan.
- [x] C2: script reconstruye la base en limpio, sin errores, en MariaDB 10.4.
- [x] C2: PK/FK/UNIQUE/CHECK/índices presentes en el DDL.
- [x] C2: stock íntegro tras entradas y salidas válidas (e2e).
- [x] C3: login, crear, listar, buscar, editar, desactivar, entrada, salida y alerta funcionando.
- [x] C3: salida mayor al stock, cantidad 0 y negativos rechazados.
- [x] C3: producto desactivado no se elimina y no acepta movimientos.
- [ ] C4: pantallas revisadas a 360 px y 1280 px.
- [x] C4: validación JS y PHP con los mismos límites; mensajes claros.
- [x] C5: casos normales, de error y límite con resultado obtenido verificable.
- [x] C5: `php -l` limpio en todos los `.php`.
- [x] C5: cambios commiteados con mensajes comprensibles.
- [ ] C6: video ≤ 5:00 accesible desde el README, con audio y código legibles.
- [ ] C6: el video explica HTML/Bootstrap, JS, PHP/PDO y MySQL ligados a las RN.

## Brechas pendientes

1. `analista`: grabar el video (guion en `pruebas/guion_video.md`), publicarlo y reemplazar el
   texto "pendiente" del README por el enlace.
2. `frontend`: revisión visual responsive a 360 px y 1280 px (opcional para el puntaje máximo,
   pero recomendada antes de grabar).
3. `auditor`: repetir esta auditoría después del video para emitir **LISTO**.

## Historial

- 2026-11-25: auditoría inicial estática → hallazgos F1–F5.
- 2026-11-25: corregidos F1 (enlace de repositorio; video pendiente), F2 (commit `af06757`
  publicado en `origin/main`), F3 (`e2e.php` 40/40 y evidencia real), F4 (encabezado del `.sql`
  y paso de `seed.php`); F5 queda como observación de diseño justificada por RF-05.
