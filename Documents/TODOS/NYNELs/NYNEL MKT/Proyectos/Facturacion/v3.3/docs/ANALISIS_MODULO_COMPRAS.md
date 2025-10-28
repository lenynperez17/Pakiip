# 📋 ANÁLISIS DEL MÓDULO DE COMPRAS - ESTADO ACTUAL

**Fecha:** 2025-10-15
**Sistema:** Sistema de Facturación Electrónica v3.3
**Objetivo:** Mejorar módulo Compras con campos SUNAT

---

## 🔍 ARCHIVOS ANALIZADOS

### 1. Vista: `compra.php`
**Ubicación:** `/v3.3/vistas/compra.php`
**Líneas:** 313
**Última modificación:** Reciente (sistema de escaneo QR implementado)

### 2. Controlador AJAX: `ajax/compra.php`
**Ubicación:** `/v3.3/ajax/compra.php`
**Líneas:** 460
**Última modificación:** Actualizado con auditoría de seguridad

### 3. Modelo: `modelos/Compra.php`
**Ubicación:** `/v3.3/modelos/Compra.php`
**Líneas:** 200+ (parcialmente leído)
**Características:** Usa prepared statements y transacciones

### 4. JavaScript: `scripts/compra.js`
**Ubicación:** `/v3.3/vistas/scripts/compra.js`
**Estado:** No leído aún (pendiente)

---

## 📊 ESTRUCTURA ACTUAL DEL FORMULARIO

### Campos de Cabecera (Compra)
```
✅ idcompra (hidden)
✅ idempresa (hidden)
✅ fecha_emision (date)
✅ tipo_comprobante (select: 01=FACTURA, 03=BOLETA, 56=GUÍA REMISIÓN)
✅ serie_comprobante (text)
✅ num_comprobante (text)
✅ moneda (select: PEN=SOLES, USD=DOLARES)
✅ subarticulo (select: 0=No, 1=Si)
✅ idproveedor (select con modal para agregar nuevo)
✅ tcambio (hidden - tipo de cambio)
✅ hora (hidden)
```

### Campos de Detalle (Artículos)
```
✅ idarticulo[] (array)
✅ valor_unitario[] (array)
✅ cantidad[] (array)
✅ subtotalBD[] (array)
✅ codigo[] (array)
✅ unidad_medida[] (array - IDs de tabla umedida)
```

### Campos Calculados
```
✅ subtotal_compra (calculado)
✅ total_igv (calculado)
✅ total_final (calculado)
```

---

## ❌ CAMPOS SUNAT FALTANTES

### En Cabecera de Compra (tabla `compra`):
1. ❌ **ruc_emisor** VARCHAR(11) - RUC del proveedor emisor
2. ❌ **descripcion_compra** TEXT - Descripción general de la compra

### En Detalle de Compra (tabla `detalle_compra_producto`):
1. ❌ **descripcion_producto** VARCHAR(500) - Descripción según comprobante
2. ❌ **unidad_medida_sunat** VARCHAR(3) - Código SUNAT Catálogo 03
3. ❌ **codigo_producto** VARCHAR(50) - Código del producto en comprobante

---

## 🔄 FLUJO ACTUAL DE DATOS

### 1. Usuario llena formulario:
```
compra.php (vista)
  ↓ Submit form
ajax/compra.php?op=guardaryeditar
  ↓ Valida CSRF
  ↓ Captura $_POST
  ↓ Llama modelo
Compra::insertar() o Compra::insertarsubarticulo()
  ↓ BEGIN TRANSACTION
  ↓ INSERT INTO compra
  ↓ FOREACH artículo:
     - INSERT INTO detalle_compra_producto
     - INSERT INTO kardex
     - UPDATE articulo (stock, valores)
  ↓ COMMIT
  ↓ Retorna ID compra
  ↓ Auditoría
Respuesta "Compra registrada"
```

### 2. Modal de artículos:
```
Clic "Agregar Producto"
  ↓
Abre modal #myModal
  ↓
Carga tabla #tblarticulos
  ↓ AJAX: ajax/compra.php?op=listarArticulos
articulo->listarActivos()
  ↓
Renderiza botones "Agregar" con onclick
  ↓
agregarDetalle(idarticulo, nombre, codigo_prov, ...)
  ↓
Agrega fila a tabla #detalles
  ↓
Recalcula subtotales
```

### 3. Escáner QR:
```
Clic "Escanear QR"
  ↓
Abre modal #modalEscanerQR
  ↓
Inicia librería html5-qrcode
  ↓
Captura QR del comprobante
  ↓
Extrae datos (pendiente implementación completa)
  ↓
Llena formulario automáticamente
```

---

## 📋 MODELO DE DATOS ACTUAL

### Método `insertar()`:
```php
public function insertar(
    $idusuario,           // ID usuario
    $idproveedor,         // ID proveedor
    $fecha_emision,       // Fecha emisión (solo fecha)
    $tipo_comprobante,    // 01, 03, 56
    $serie_comprobante,   // Serie
    $num_comprobante,     // Número
    $guia,                // Guía remisión
    $subtotal_compra,     // Subtotal
    $total_igv,           // IGV
    $total_compra,        // Total
    $idarticulo,          // Array IDs artículos
    $valor_unitario,      // Array valores unitarios
    $cantidad,            // Array cantidades
    $subtotalBD,          // Array subtotales
    $codigo,              // Array códigos
    $unidad_medida,       // Array UMs (IDs tabla umedida)
    $tcambio,             // Tipo cambio
    $hora,                // Hora
    $moneda,              // PEN o USD
    $idempresa            // ID empresa
)
```

### SQL INSERT compra (actual):
```sql
INSERT INTO compra (
    idusuario, idproveedor, fecha, tipo_documento, serie, numero, guia,
    subtotal, igv, total, subtotal_$, igv_$, total_$, tcambio, moneda, idempresa
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '0', '0', '0', ?, ?, ?)
```

### SQL INSERT detalle (actual):
```sql
INSERT INTO detalle_compra_producto (
    idcompra, idarticulo, valor_unitario, cantidad, subtotal, valor_unitario_$, subtotal_$
) VALUES (?, ?, ?, ?, valor_unitario * ?, '0', '0')
```

---

## 🎯 MEJORAS NECESARIAS

### FASE 1: Agregar Campos SUNAT a la Vista ✅ (En Progreso)
**Archivo:** `compra.php`
**Cambios:**
1. Agregar campo `ruc_emisor` (auto-llenado desde proveedor)
2. Agregar campo `descripcion_compra` (textarea opcional)
3. Mostrar mensaje informativo sobre campos SUNAT

**Ubicación sugerida:** Después del campo `idproveedor`, antes de botones Guardar/Cancelar

### FASE 2: Actualizar Controlador AJAX
**Archivo:** `ajax/compra.php`
**Cambios:**
1. Capturar `$ruc_emisor` de `$_POST`
2. Capturar `$descripcion_compra` de `$_POST`
3. Pasar nuevos parámetros al modelo
4. Actualizar auditoría para incluir nuevos campos

### FASE 3: Actualizar Modelo
**Archivo:** `modelos/Compra.php`
**Método:** `insertar()` y `insertarsubarticulo()`
**Cambios:**
1. Agregar parámetros `$ruc_emisor` y `$descripcion_compra`
2. Modificar SQL INSERT de compra para incluir nuevos campos:
```sql
INSERT INTO compra (
    ..., ruc_emisor, fecha_emision, descripcion_compra
) VALUES (
    ..., ?, ?, ?
)
```

### FASE 4: Mejorar Detalle con Campos SUNAT
**Archivos:** `compra.php`, `compra.js`, `ajax/compra.php`, `Compra.php`
**Cambios:**
1. Agregar columnas a tabla de detalles:
   - Descripción producto (editable)
   - UM SUNAT (select con catálogo 03)
   - Código producto (text)
2. Capturar arrays adicionales en AJAX
3. Insertar en `detalle_compra_producto` con nuevos campos

### FASE 5: Integración con Escáner QR
**Archivo:** `compra.js`
**Funcionalidad:**
1. Parsear QR del comprobante
2. Extraer automáticamente:
   - RUC emisor
   - Tipo comprobante
   - Serie
   - Número
   - Fecha emisión
   - Total
3. Llenar formulario automáticamente

---

## 🔒 SEGURIDAD Y VALIDACIONES

### Validaciones Actuales ✅:
- Token CSRF en todos los formularios
- Prepared statements en queries
- Transacciones para integridad
- Auditoría de operaciones
- Limpieza de inputs con `limpiarCadena()`

### Validaciones a Agregar:
- RUC debe ser numérico de 11 dígitos
- Descripción máximo 500 caracteres
- UM SUNAT debe existir en catálogo
- Código producto máximo 50 caracteres

---

## 📊 COMPATIBILIDAD CON BASE DE DATOS

### Columnas ya creadas (migración exitosa):
✅ compra.ruc_emisor VARCHAR(11)
✅ compra.fecha_emision DATE
✅ compra.descripcion_compra TEXT
✅ detalle_compra_producto.descripcion_producto VARCHAR(500)
✅ detalle_compra_producto.unidad_medida_sunat VARCHAR(3) utf8mb4
✅ detalle_compra_producto.codigo_producto VARCHAR(50)

### Foreign Keys activos:
✅ fk_detalle_compra_umedida_sunat (detalle_compra_producto → umedida_sunat)

**CONCLUSIÓN:** La base de datos está 100% lista para recibir los nuevos datos.

---

## 🚀 PLAN DE IMPLEMENTACIÓN

### Orden de Ejecución Recomendado:
1. ✅ **Completado:** Migración de base de datos
2. 🔄 **En progreso:** Agregar campos a vista compra.php
3. ⏭️ **Siguiente:** Actualizar ajax/compra.php
4. ⏭️ Actualizar modelo Compra.php
5. ⏭️ Actualizar compra.js para validaciones
6. ⏭️ Mejorar tabla de detalles
7. ⏭️ Integrar escáner QR con auto-llenado
8. ⏭️ Testing completo
9. ⏭️ Documentación de usuario

---

## 📝 NOTAS TÉCNICAS

### Consideraciones Importantes:
1. El sistema ya maneja dos tipos de compras:
   - Compra normal: `insertar()`
   - Compra con subartículos: `insertarsubarticulo()`

2. Ambos métodos deben ser actualizados

3. El campo `fecha` en tabla `compra` almacena DATETIME, pero usaremos `fecha_emision` (DATE) para SUNAT

4. El sistema ya tiene modal para agregar proveedor rápido

5. Existe función `cambioproveedor()` en JS que podría auto-llenar RUC

### Funciones JavaScript Relevantes:
- `agregarDetalle()` - Agregar artículo a tabla
- `modificarSubototales()` - Recalcular totales
- `cambioproveedor()` - Evento cambio de proveedor
- `mayus()` - Convertir a mayúsculas
- `EnterSerie()` / `EnterNumero()` - Navegación con Enter

---

**FIN DEL ANÁLISIS**
**Estado:** Listo para implementar mejoras
**Prioridad:** Alta (SUNAT compliance)
