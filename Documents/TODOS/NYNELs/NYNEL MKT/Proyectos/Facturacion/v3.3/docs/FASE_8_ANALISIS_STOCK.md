# 📊 FASE 8 - ANÁLISIS SISTEMA DE CONTROL DE STOCK EN VENTAS
## Fecha: 15 de Enero de 2025
## Estado: ANÁLISIS COMPLETADO ✅

---

## 🎯 OBJETIVO DE LA FASE

Implementar un sistema de control de stock automático y consistente en TODAS las operaciones de venta (Boleta, Factura, Nota de Pedido) que:
- Descuente automáticamente el stock al crear un comprobante
- Valide stock disponible antes de permitir la venta
- Maneje correctamente las conversiones de unidades de medida
- Registre movimientos en KARDEX para trazabilidad
- Sea transaccional (todo o nada)

---

## 🔍 HALLAZGOS CRÍTICOS DEL ANÁLISIS

### ✅ **BOLETA.PHP - YA TIENE CONTROL DE STOCK**

**Ubicación**: `/v3.3/modelos/Boleta.php:302-338`

**Código Actual**:
```php
// Líneas 302-338 - Método insertar() - DENTRO DEL LOOP DE DETALLES
if ($tipoboleta != 'servicios') {
  $sql_update_articulo = "UPDATE articulo
    SET saldo_finu = saldo_finu - ?,
        ventast = ventast + ?,
        valor_finu = (saldo_iniu + comprast - ventast) * precio_final_kardex,
        stock = saldo_finu,
        valor_fin_kardex=(SELECT valor_final FROM kardex
                         WHERE idarticulo = ? AND transaccion = 'VENTA'
                         ORDER BY idkardex DESC LIMIT 1)
    WHERE idarticulo = ?";

  $stmt_art = $conexion->prepare($sql_update_articulo);
  $stmt_art->bind_param("ddii",
    $cantidadreal[$num_elementos],
    $cantidadreal[$num_elementos],
    $idarticulo[$num_elementos],
    $idarticulo[$num_elementos]
  );

  $stmt_art->execute();
  $stmt_art->close();
}
```

**Características**:
- ✅ Usa prepared statements (seguro)
- ✅ Descuenta con `saldo_finu - cantidad`
- ✅ Incrementa contador `ventast + cantidad`
- ✅ Recalcula valor de inventario
- ✅ Sincroniza `stock = saldo_finu`
- ✅ Actualiza valor según KARDEX
- ✅ Solo aplica si NO es servicio
- ✅ Dentro de transacción MySQLi
- ✅ Tiene rollback en caso de error

**Variables utilizadas**:
- `$cantidadreal[$num_elementos]` - Cantidad real a descontar
- `$idarticulo[$num_elementos]` - ID del artículo

---

### ✅ **FACTURA.PHP - YA TIENE CONTROL DE STOCK**

**Ubicación**: `/v3.3/modelos/Factura.php:392-402`

**Código Actual**:
```php
// Líneas 392-402 - Método insertar() - DENTRO DEL LOOP DE DETALLES
if ($tipofactura != 'servicios') {
  //ACTUALIZA TABLA ARTICULOS SI ES SERVICIO
  $sql_update_articulo = "update
    articulo set saldo_finu = saldo_finu - '$cantidadreal[$num_elementos]',
    ventast = ventast + '$cantidadreal[$num_elementos]',
    valor_finu = (saldo_iniu + comprast - ventast) * precio_final_kardex,
    stock = saldo_finu,
    valor_fin_kardex=(select valor_final from kardex
                     where idarticulo='$idarticulo[$num_elementos]'
                     and transaccion='VENTA'
                     order by idkardex desc limit 1)
    where idarticulo = '$idarticulo[$num_elementos]'";

  ejecutarConsulta($sql_update_articulo);
}
```

**Características**:
- ⚠️ **VULNERABLE A SQL INJECTION** - NO usa prepared statements
- ✅ Descuenta con `saldo_finu - cantidad`
- ✅ Incrementa contador `ventast + cantidad`
- ✅ Recalcula valor de inventario
- ✅ Sincroniza `stock = saldo_finu`
- ✅ Actualiza valor según KARDEX
- ✅ Solo aplica si NO es servicio
- ❌ **NO tiene transacción explícita**
- ❌ **NO tiene rollback en caso de error**

**Variables utilizadas**:
- `$cantidadreal[$num_elementos]` - Cantidad real a descontar
- `$idarticulo[$num_elementos]` - ID del artículo

---

## 📊 COMPARATIVA BOLETA vs FACTURA

| ASPECTO | BOLETA.PHP ✅ | FACTURA.PHP ⚠️ | ACCIÓN REQUERIDA |
|---------|--------------|----------------|------------------|
| **Control de stock** | SÍ implementado | SÍ implementado | ✅ Ya existe |
| **Prepared statements** | ✅ Usa | ❌ NO usa | 🔴 MIGRAR A PREPARED |
| **Transacción explícita** | ✅ mysqli_begin_transaction | ❌ Sin transacción | 🔴 AGREGAR |
| **Rollback en errores** | ✅ mysqli_rollback | ❌ Sin rollback | 🔴 AGREGAR |
| **Validación de stock** | ❌ No valida | ❌ No valida | 🔴 AGREGAR |
| **Registro en KARDEX** | ⚠️ Comentado (línea 261) | ✅ Registra (línea 345) | 🟡 ACTIVAR EN BOLETA |
| **Manejo de errores** | ✅ Try-catch | ❌ Sin manejo | 🔴 AGREGAR |
| **Actualiza subarticulo** | ❌ No actualiza | ❌ No actualiza | 🟡 EVALUAR NECESIDAD |
| **Conversión UM** | ❌ No maneja | ❌ No maneja | 🟡 EVALUAR NECESIDAD |

---

## 🔴 PROBLEMAS IDENTIFICADOS

### 1. **FACTURA.PHP VULNERABLE A SQL INJECTION**
**Severidad**: CRÍTICA 🔴

**Problema**:
```php
// LÍNEA 394-400 - CÓDIGO VULNERABLE
$sql_update_articulo = "update articulo
  set saldo_finu = saldo_finu - '$cantidadreal[$num_elementos]',
  ...";
ejecutarConsulta($sql_update_articulo);
```

**Impacto**:
- Inyección SQL posible si datos vienen manipulados
- No cumple estándares de seguridad modernos
- Inconsistente con Boleta.php que SÍ usa prepared statements

**Solución**:
Migrar a prepared statements como en Boleta.php.

---

### 2. **FALTA VALIDACIÓN DE STOCK DISPONIBLE**
**Severidad**: ALTA 🟡

**Problema Actual**:
Ambos modelos permiten ventas incluso con stock insuficiente:
```php
// Se descuenta sin validar:
saldo_finu = saldo_finu - $cantidad
// ¿Qué pasa si saldo_finu < $cantidad? → Stock negativo ❌
```

**Escenario de Fallo**:
```
Stock actual: 5 unidades
Usuario vende: 10 unidades
Resultado:    -5 unidades (stock negativo) 💥
```

**Impacto**:
- Stock negativo en base de datos
- Inventario descuadrado
- Problemas en reportes contables
- Pérdida de control de existencias

**Solución**:
Agregar validación ANTES de insertar:
```php
// Validar stock disponible
$stock_actual = obtenerStockArticulo($idarticulo);
if ($cantidad > $stock_actual) {
  throw new Exception("Stock insuficiente");
}
```

---

### 3. **KARDEX DESACTIVADO EN BOLETA.PHP**
**Severidad**: MEDIA 🟡

**Código Comentado**:
```php
// Líneas 261-274 - Boleta.php
/* $sql_kardex = "INSERT INTO kardex (
  idcomprobante, idarticulo, transaccion, codigo, fecha, tipo_documento,
  numero_doc, cantidad, costo_1, unidad_medida, saldo_final, costo_2,
  valor_final, idempresa, tcambio, moneda
) VALUES (?, ?, 'VENTA', ?, ?, '03', ?, ?, ?, ?, '', '', '', ?, ?, ?)";

$stmt_kar->execute();
$stmt_kar->close(); */
```

**Problema**:
- KARDEX no se registra en Boletas
- Solo se registra en Facturas
- Inconsistencia en trazabilidad de inventario

**Impacto**:
- No se puede rastrear ventas por Boleta en KARDEX
- Reportes de inventario incompletos
- Dificulta implementar PEPS (First In First Out)

**Solución**:
Descomentar y activar el código de KARDEX en Boleta.php.

---

### 4. **FALTA TRANSACCIÓN EN FACTURA.PHP**
**Severidad**: CRÍTICA 🔴

**Problema**:
Factura.php NO usa transacciones explícitas:
```php
// Boleta.php - CORRECTO ✅
mysqli_begin_transaction($conexion);
try {
  // operaciones...
  mysqli_commit($conexion);
} catch (Exception $e) {
  mysqli_rollback($conexion);
}

// Factura.php - INCORRECTO ❌
$sql = "insert into factura...";
ejecutarConsulta($sql);  // Sin transacción
$sql_detalle = "insert into detalle...";
ejecutarConsulta($sql_detalle);  // Sin transacción
$sql_update = "update articulo...";
ejecutarConsulta($sql_update);  // Sin transacción
```

**Escenario de Fallo**:
```
1. Se inserta factura ✅
2. Se inserta detalle ✅
3. Se actualiza stock ❌ (error de red)

RESULTADO:
- Factura registrada en BD
- Stock NO descontado
- Inventario descuadrado 💥
```

**Impacto**:
- Operaciones parciales en caso de error
- Stock puede quedar sin actualizar
- Datos inconsistentes en base de datos

**Solución**:
Migrar a transacciones como Boleta.php.

---

## 🎯 PLAN DE MEJORAS PROPUESTO

### FASE 8.1 - SEGURIDAD CRÍTICA (PRIORIDAD MÁXIMA)
**Objetivo**: Eliminar vulnerabilidades de seguridad

**Tareas**:
1. ✅ Migrar Factura.php a prepared statements
2. ✅ Implementar transacciones en Factura.php
3. ✅ Agregar try-catch y rollback en Factura.php

**Archivos a modificar**:
- `/v3.3/modelos/Factura.php` - Método `insertar()`

**Beneficios**:
- Seguridad contra SQL Injection
- Consistencia transaccional
- Manejo robusto de errores

---

### FASE 8.2 - VALIDACIÓN DE STOCK (PRIORIDAD ALTA)
**Objetivo**: Prevenir ventas con stock insuficiente

**Tareas**:
1. ✅ Crear método `validarStockDisponible()` en Articulo.php
2. ✅ Agregar validación en Boleta::insertar()
3. ✅ Agregar validación en Factura::insertar()
4. ✅ Agregar validación en NotaPedido::insertar()

**Archivos a modificar**:
- `/v3.3/modelos/Articulo.php` - Nuevo método
- `/v3.3/modelos/Boleta.php` - Agregar validación
- `/v3.3/modelos/Factura.php` - Agregar validación
- `/v3.3/modelos/NotaPedido.php` - Agregar validación

**Beneficios**:
- Evitar stock negativo
- Alertas tempranas al usuario
- Inventario siempre consistente

---

### FASE 8.3 - ACTIVAR KARDEX EN BOLETA (PRIORIDAD MEDIA)
**Objetivo**: Trazabilidad completa de movimientos de inventario

**Tareas**:
1. ✅ Descomentar código KARDEX en Boleta.php
2. ✅ Verificar que campos coincidan con tabla kardex
3. ✅ Testear registro de KARDEX en Boleta

**Archivos a modificar**:
- `/v3.3/modelos/Boleta.php` - Líneas 261-274

**Beneficios**:
- Trazabilidad completa
- Reportes de inventario precisos
- Base para implementar PEPS (FASE 9)

---

### FASE 8.4 - CONVERSIÓN DE UNIDADES (PRIORIDAD BAJA)
**Objetivo**: Manejar correctamente conversiones (ej: vender por caja, descontar por unidad)

**Tareas**:
1. ⏳ Analizar campo `factorc` (factor de conversión)
2. ⏳ Implementar lógica de conversión en descuento
3. ⏳ Agregar validación de stock considerando conversión

**Archivos a modificar**:
- `/v3.3/modelos/Articulo.php` - Método de conversión
- `/v3.3/modelos/Boleta.php` - Aplicar conversión
- `/v3.3/modelos/Factura.php` - Aplicar conversión

**Beneficios**:
- Ventas en unidades diferentes a compras
- Flexibilidad en presentación de productos
- Stock preciso con conversiones

---

## 📋 CHECKLIST DE IMPLEMENTACIÓN

### FASE 8.1 - SEGURIDAD CRÍTICA
- [ ] Migrar `Factura::insertar()` a prepared statements
- [ ] Agregar `mysqli_begin_transaction()` en Factura.php
- [ ] Implementar try-catch con rollback
- [ ] Agregar `$this->lastError` para manejo de errores
- [ ] Testear rollback en caso de error simulado
- [ ] Comparar rendimiento antes/después

### FASE 8.2 - VALIDACIÓN DE STOCK
- [ ] Crear `Articulo::validarStockDisponible($idarticulo, $cantidad)`
- [ ] Integrar validación en `Boleta::insertar()` ANTES del INSERT
- [ ] Integrar validación en `Factura::insertar()` ANTES del INSERT
- [ ] Retornar mensaje específico si stock insuficiente
- [ ] Testear con stock suficiente (debe pasar)
- [ ] Testear con stock insuficiente (debe rechazar)

### FASE 8.3 - ACTIVAR KARDEX
- [ ] Descomentar líneas 261-274 en Boleta.php
- [ ] Verificar campos de tabla `kardex`
- [ ] Ajustar query si hay diferencias de schema
- [ ] Testear INSERT en kardex con Boleta
- [ ] Comparar registros KARDEX de Boleta vs Factura

### FASE 8.4 - CONVERSIÓN DE UNIDADES (FUTURO)
- [ ] Analizar uso actual de `factorc`
- [ ] Definir lógica de conversión (multiplicar/dividir)
- [ ] Implementar método `convertirUnidad()`
- [ ] Aplicar conversión en descuento de stock
- [ ] Testear con productos de diferentes UM

---

## 🧪 CASOS DE PRUEBA CRÍTICOS

### TEST 1: Venta con Stock Suficiente
```
Producto: "Laptop Dell"
Stock actual: 10 unidades
Venta: 2 unidades

RESULTADO ESPERADO:
✅ Boleta registrada
✅ Stock actualizado: 8 unidades
✅ KARDEX registrado
✅ Transacción commit
```

### TEST 2: Venta con Stock Insuficiente
```
Producto: "Laptop Dell"
Stock actual: 3 unidades
Venta: 5 unidades

RESULTADO ESPERADO:
❌ Boleta NO registrada
❌ Stock NO modificado: 3 unidades
❌ KARDEX NO registrado
❌ Error retornado: "Stock insuficiente"
```

### TEST 3: Error en Mitad de Transacción
```
Escenario: Simular error en UPDATE articulo

RESULTADO ESPERADO:
❌ Boleta NO registrada (rollback)
❌ Detalle NO registrado (rollback)
❌ Stock NO modificado (rollback)
✅ Base de datos consistente
```

### TEST 4: Venta de Servicios (Sin Stock)
```
Producto: "Consultoría Legal" (tipoboleta='servicios')
Venta: 1 servicio

RESULTADO ESPERADO:
✅ Boleta registrada
❌ Stock NO descontado (servicios no tienen stock)
✅ KARDEX NO registrado
✅ Transacción commit
```

---

## 📊 ESQUEMA DE BASE DE DATOS

### Tabla `articulo` (Campos relacionados a stock)
```sql
CREATE TABLE `articulo` (
  `idarticulo` int(11) NOT NULL AUTO_INCREMENT,
  `stock` decimal(10,2) DEFAULT 0,           -- Stock actual
  `saldo_iniu` decimal(10,2) DEFAULT 0,      -- Saldo inicial
  `saldo_finu` decimal(10,2) DEFAULT 0,      -- Saldo final
  `comprast` decimal(10,2) DEFAULT 0,        -- Total comprado
  `ventast` decimal(10,2) DEFAULT 0,         -- Total vendido
  `precio_final_kardex` decimal(10,2),       -- Precio unitario KARDEX
  `valor_finu` decimal(10,2),                -- Valor final inventario
  `valor_fin_kardex` decimal(10,2),          -- Valor final según KARDEX
  `factorc` decimal(10,2) DEFAULT 1,         -- Factor de conversión UM
  PRIMARY KEY (`idarticulo`)
);
```

### Tabla `kardex` (Registro de movimientos)
```sql
CREATE TABLE `kardex` (
  `idkardex` int(11) NOT NULL AUTO_INCREMENT,
  `idcomprobante` int(11),                   -- ID de boleta/factura
  `idarticulo` int(11),                      -- ID del artículo
  `transaccion` varchar(20),                 -- VENTA, COMPRA, AJUSTE
  `fecha` date,                              -- Fecha del movimiento
  `tipo_documento` varchar(2),               -- 01=Factura, 03=Boleta
  `numero_doc` varchar(20),                  -- F001-00001
  `cantidad` decimal(10,2),                  -- Cantidad movida
  `unidad_medida` varchar(10),               -- NIU, KGM, etc.
  `saldo_final` decimal(10,2),               -- Stock resultante
  `valor_final` decimal(10,2),               -- Valor inventario
  PRIMARY KEY (`idkardex`)
);
```

---

## 🔧 CÓDIGO DE REFERENCIA

### Boleta.php - UPDATE Stock CORRECTO ✅
```php
// Líneas 302-338
if ($tipoboleta != 'servicios') {
  $sql_update_articulo = "UPDATE articulo
    SET saldo_finu = saldo_finu - ?,
        ventast = ventast + ?,
        valor_finu = (saldo_iniu + comprast - ventast) * precio_final_kardex,
        stock = saldo_finu,
        valor_fin_kardex=(SELECT valor_final FROM kardex
                         WHERE idarticulo = ? AND transaccion = 'VENTA'
                         ORDER BY idkardex DESC LIMIT 1)
    WHERE idarticulo = ?";

  $stmt_art = $conexion->prepare($sql_update_articulo);
  if (!$stmt_art) {
    $this->lastError = $conexion->error;
    error_log("Error preparando UPDATE articulo: " . $conexion->error);
    mysqli_rollback($conexion);
    return false;
  }

  $stmt_art->bind_param("ddii",
    $cantidadreal[$num_elementos],
    $cantidadreal[$num_elementos],
    $idarticulo[$num_elementos],
    $idarticulo[$num_elementos]
  );

  if (!$stmt_art->execute()) {
    $this->lastError = $stmt_art->error;
    error_log("Error ejecutando UPDATE articulo: " . $stmt_art->error);
    $stmt_art->close();
    mysqli_rollback($conexion);
    return false;
  }
  $stmt_art->close();
}
```

### Factura.php - UPDATE Stock VULNERABLE ⚠️
```php
// Líneas 392-402
if ($tipofactura != 'servicios') {
  $sql_update_articulo = "update
    articulo set saldo_finu = saldo_finu - '$cantidadreal[$num_elementos]',
    ventast = ventast + '$cantidadreal[$num_elementos]',
    valor_finu = (saldo_iniu + comprast - ventast) * precio_final_kardex,
    stock = saldo_finu,
    valor_fin_kardex=(select valor_final from kardex
                     where idarticulo='$idarticulo[$num_elementos]'
                     and transaccion='VENTA'
                     order by idkardex desc limit 1)
    where idarticulo = '$idarticulo[$num_elementos]'";

  ejecutarConsulta($sql_update_articulo);  // ⚠️ SQL INJECTION POSIBLE
}
```

---

## ✅ CONCLUSIONES DEL ANÁLISIS

1. **✅ STOCK AUTOMÁTICO YA EXISTE**
   - Boleta.php: Implementación profesional con prepared statements
   - Factura.php: Implementación funcional pero vulnerable

2. **🔴 VULNERABILIDAD CRÍTICA EN FACTURA.PHP**
   - SQL injection posible en UPDATE de stock
   - Sin transacciones explícitas
   - Sin manejo de errores robusto

3. **🟡 FALTA VALIDACIÓN DE STOCK**
   - Permite ventas con stock insuficiente
   - Riesgo de stock negativo

4. **🟡 KARDEX INCOMPLETO**
   - Boleta: Código comentado
   - Factura: SÍ registra en KARDEX
   - Inconsistencia en trazabilidad

5. **⏳ CONVERSIÓN DE UNIDADES PENDIENTE**
   - Campo `factorc` existe pero no se usa
   - Necesario para productos con múltiples UM

---

## 📅 CRONOGRAMA PROPUESTO

| FASE | DESCRIPCIÓN | PRIORIDAD | TIEMPO ESTIMADO |
|------|-------------|-----------|-----------------|
| 8.1 | Seguridad Crítica - Factura.php | 🔴 URGENTE | 2-3 horas |
| 8.2 | Validación de Stock | 🟡 ALTA | 1-2 horas |
| 8.3 | Activar KARDEX en Boleta | 🟢 MEDIA | 30 min |
| 8.4 | Conversión de Unidades | ⚪ BAJA | 3-4 horas |

**TOTAL ESTIMADO**: 6-10 horas de desarrollo

---

## 🚀 PRÓXIMOS PASOS

1. ✅ **Aprobar este análisis** - Documento completo de situación actual
2. ⏳ **Iniciar FASE 8.1** - Migrar Factura.php a prepared statements
3. ⏳ **Implementar validación** - Evitar stock negativo
4. ⏳ **Activar KARDEX** - Trazabilidad completa
5. ⏳ **Testear exhaustivamente** - Casos de éxito y fallo
6. ⏳ **Documentar cambios** - FASE_8_COMPLETADO.md

---

**Timestamp**: 2025-01-15 (continuación de sesión)
**Desarrollado por**: Claude (Sonnet 4.5)
**Proyecto**: Sistema de Facturación v3.3 - NYNEL MKT
**Fase**: ANÁLISIS COMPLETADO - LISTO PARA IMPLEMENTACIÓN
