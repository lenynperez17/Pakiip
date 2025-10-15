# ANÁLISIS COMPLETO DEL SISTEMA ACTUAL VS REQUERIMIENTOS
## Sistema de Facturación Electrónica Perú - Enero 2025

---

## 📊 ESTRUCTURA DE BASE DE DATOS ACTUAL

### TABLAS EXISTENTES ANALIZADAS:

#### 1. **Tabla `compra`** (Líneas 778-797 bd.sql)
```sql
CREATE TABLE `compra` (
  `idcompra` int(11) NOT NULL,
  `idusuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL,                    -- ⚠️ Fecha combinada con hora
  `tipo_documento` varchar(45) NOT NULL,
  `idproveedor` int(11) NOT NULL,
  `serie` varchar(5) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `guia` varchar(20) DEFAULT NULL,
  `subtotal` float(12,2) NOT NULL,
  `igv` float(12,2) NOT NULL,
  `total` float(12,2) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT '1',
  `subtotal_$` float(14,2) DEFAULT NULL,
  `igv_$` float(14,2) DEFAULT NULL,
  `total_$` float(14,2) DEFAULT NULL,
  `tcambio` float(14,3) DEFAULT NULL,
  `moneda` char(5) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL
)
```

**❌ CAMPOS FALTANTES SEGÚN REQUERIMIENTOS:**
- `ruc_emisor` VARCHAR(11) - RUC del proveedor emisor del comprobante
- `fecha_emision` DATE - Fecha de emisión separada (actualmente está combinada con hora)
- `descripcion` TEXT - Descripción general de la compra

---

#### 2. **Tabla `detalle_compra_producto`** (Líneas 1126-1135 bd.sql)
```sql
CREATE TABLE `detalle_compra_producto` (
  `iddetalle` int(11) NOT NULL,
  `idcompra` int(11) DEFAULT NULL,
  `idarticulo` int(11) DEFAULT NULL,
  `valor_unitario` float(12,3) DEFAULT NULL,
  `cantidad` float(12,2) DEFAULT NULL,
  `subtotal` float(12,2) DEFAULT NULL,
  `valor_unitario_$` float(14,2) DEFAULT NULL,
  `subtotal_$` float(14,2) DEFAULT NULL
)
```

**❌ CAMPOS FALTANTES SEGÚN REQUERIMIENTOS:**
- `descripcion_producto` VARCHAR(500) - Descripción del producto comprado
- `unidad_medida_sunat` VARCHAR(3) - Código SUNAT de unidad de medida (ej: NIU, ZZ, KGM)
- `codigo_producto` VARCHAR(50) - Código del producto

---

#### 3. **Tabla `umedida`** (Líneas 5011-5017 bd.sql)
```sql
CREATE TABLE `umedida` (
  `idunidad` int(10) UNSIGNED NOT NULL,
  `nombreum` char(50) DEFAULT NULL,
  `abre` varchar(5) NOT NULL,              -- ⚠️ Abreviatura NO SUNAT
  `estado` tinyint(4) DEFAULT NULL,
  `equivalencia` float(14,2) DEFAULT NULL
)
```

**🔴 PROBLEMA CRÍTICO:**
- Tiene solo 58 unidades básicas (ver líneas 5023-5047)
- **NO tiene códigos SUNAT oficiales del Catálogo 03**
- Usa FK numérica (idunidad) en lugar de código varchar SUNAT

**✅ SOLUCIÓN REQUERIDA:**
Crear nueva tabla `umedida_sunat` con estructura SUNAT oficial:
```sql
CREATE TABLE `umedida_sunat` (
  `codigo_sunat` VARCHAR(3) PRIMARY KEY,  -- Ej: NIU, ZZ, KGM, LTR
  `descripcion` VARCHAR(100) NOT NULL,    -- Ej: UNIDAD (BIENES), KILOGRAMO
  `estado` TINYINT(1) DEFAULT 1
)
```

---

#### 4. **Tabla `articulo`** (Líneas 52-82 bd.sql)
```sql
CREATE TABLE `articulo` (
  `idarticulo` int(11) NOT NULL,
  `idalmacen` int(11) NOT NULL,
  `codigo_proveedor` varchar(45) DEFAULT NULL,
  `codigo` varchar(255) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `idfamilia` int(11) NOT NULL,
  `unidad_medida` int(11) DEFAULT NULL,    -- ⚠️ FK a umedida (debería ser código SUNAT)
  `costo_compra` float(12,2) NOT NULL,
  `stock` float(12,2) DEFAULT NULL,
  `precio_venta` decimal(12,2) DEFAULT NULL,
  `imagen` varchar(50) DEFAULT NULL,
  -- ... otros campos ...
)
```

**⚠️ MEJORAS NECESARIAS:**
- Cambiar `unidad_medida` int a `unidad_medida_sunat` VARCHAR(3)
- Agregar índice en `idalmacen` para búsquedas por sede
- Agregar índice en `idfamilia` para búsquedas por categoría

---

#### 5. **Tabla `almacen`** (Líneas 30-36 bd.sql)
```sql
CREATE TABLE `almacen` (
  `idalmacen` int(11) NOT NULL,
  `nombre` varchar(45) NOT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT '1'
)
```

**✅ ESTRUCTURA CORRECTA** - No requiere cambios

---

#### 6. **Tabla `kardex`** (Líneas encontradas en modelo Compra.php)
```sql
-- Inserción en líneas 118-128 de Compra.php
INSERT INTO kardex (
  idcomprobante, idarticulo, transaccion, codigo, fecha, tipo_documento,
  numero_doc, cantidad, costo_1, unidad_medida, saldo_final, costo_2,
  valor_final, idempresa, tcambio, moneda
)
```

**❌ TABLA EXISTE PERO FALTA:**
- Implementación de **PEPS (First In, First Out)** por sede
- Vista SQL para cálculo automático de valorización

---

### ❌ TABLAS QUE NO EXISTEN Y SE DEBEN CREAR:

#### 7. **Tabla `sire_compras`** - NUEVA
```sql
CREATE TABLE `sire_compras` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `periodo` VARCHAR(7) NOT NULL,              -- AAAA-MM
  `numero_correlativo` VARCHAR(50),
  `fecha_emision` DATE NOT NULL,
  `fecha_vcto_pago` DATE,
  `tipo_comprobante` VARCHAR(2) NOT NULL,     -- Catálogo 01
  `serie` VARCHAR(20),
  `numero` VARCHAR(20),
  `numero_final` VARCHAR(20),
  `tipo_documento_identidad` VARCHAR(1),       -- 6=RUC, 1=DNI, etc.
  `numero_documento_identidad` VARCHAR(15),
  `razon_social` VARCHAR(200),
  `base_imponible` DECIMAL(12,2),
  `igv` DECIMAL(12,2),
  `base_imponible_ng` DECIMAL(12,2),          -- No gravado
  `igv_ng` DECIMAL(12,2),
  `base_imponible_ndo` DECIMAL(12,2),         -- No domiciliado
  `igv_ndo` DECIMAL(12,2),
  `isc` DECIMAL(12,2),
  `icbper` DECIMAL(12,2),
  `otros_tributos` DECIMAL(12,2),
  `total` DECIMAL(12,2),
  `moneda` VARCHAR(3),                         -- PEN, USD
  `tipo_cambio` DECIMAL(10,3),
  `fecha_emision_modificado` DATE,
  `tipo_comprobante_modificado` VARCHAR(2),
  `serie_modificado` VARCHAR(20),
  `numero_modificado` VARCHAR(20),
  `fecha_constancia_deposito` DATE,
  `numero_constancia_deposito` VARCHAR(50),
  `marca_comprobante` VARCHAR(1),              -- 1=Sí, 0=No
  `estado_comprobante` VARCHAR(1),             -- 1=Emitido, 2=Anulado, etc.
  `estado_pago` VARCHAR(1),                    -- 1=Pagado, 2=Pendiente
  `idcompra` INT,                              -- FK a tabla compra
  `idempresa` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_periodo` (`periodo`),
  KEY `idx_fecha_emision` (`fecha_emision`),
  KEY `idx_tipo_documento` (`tipo_documento_identidad`, `numero_documento_identidad`)
)
```

#### 8. **Tabla `sire_ventas`** - NUEVA
```sql
CREATE TABLE `sire_ventas` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `periodo` VARCHAR(7) NOT NULL,
  `numero_correlativo` VARCHAR(50),
  `fecha_emision` DATE NOT NULL,
  `fecha_vcto_pago` DATE,
  `tipo_comprobante` VARCHAR(2) NOT NULL,
  `serie` VARCHAR(20),
  `numero` VARCHAR(20),
  `numero_final` VARCHAR(20),
  `tipo_documento_identidad` VARCHAR(1),
  `numero_documento_identidad` VARCHAR(15),
  `razon_social` VARCHAR(200),
  `valor_exportacion` DECIMAL(12,2),
  `base_imponible` DECIMAL(12,2),
  `descuento_bi` DECIMAL(12,2),
  `igv` DECIMAL(12,2),
  `descuento_igv` DECIMAL(12,2),
  `exonerado` DECIMAL(12,2),
  `inafecto` DECIMAL(12,2),
  `isc` DECIMAL(12,2),
  `base_ivap` DECIMAL(12,2),
  `ivap` DECIMAL(12,2),
  `icbper` DECIMAL(12,2),
  `otros_tributos` DECIMAL(12,2),
  `total` DECIMAL(12,2),
  `moneda` VARCHAR(3),
  `tipo_cambio` DECIMAL(10,3),
  `fecha_emision_modificado` DATE,
  `tipo_comprobante_modificado` VARCHAR(2),
  `serie_modificado` VARCHAR(20),
  `numero_modificado` VARCHAR(20),
  `estado_comprobante` VARCHAR(1),
  `estado_pago` VARCHAR(1),
  `idventa` INT,                               -- FK según tipo (factura, boleta, NC, ND)
  `tipo_venta` VARCHAR(20),                    -- 'FACTURA', 'BOLETA', 'NC', 'ND'
  `idempresa` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_periodo` (`periodo`),
  KEY `idx_fecha_emision` (`fecha_emision`)
)
```

#### 9. **Tabla `importaciones`** - NUEVA
```sql
CREATE TABLE `importaciones` (
  `id_importacion` INT PRIMARY KEY AUTO_INCREMENT,
  `numero_dua` VARCHAR(50) NOT NULL,           -- Declaración Única de Aduanas
  `fecha_dua` DATE NOT NULL,
  `aduana` VARCHAR(100),                       -- Aduana de ingreso
  `regimen_aduanero` VARCHAR(10),              -- 40=Importación definitiva

  -- DATOS PROVEEDOR INTERNACIONAL
  `proveedor_internacional` VARCHAR(200),
  `pais_origen` VARCHAR(50),
  `direccion_proveedor` TEXT,

  -- DATOS INVOICE COMERCIAL
  `numero_invoice` VARCHAR(50),
  `fecha_invoice` DATE,
  `valor_fob_usd` DECIMAL(14,2),               -- Valor FOB en USD
  `flete_usd` DECIMAL(14,2),
  `seguro_usd` DECIMAL(14,2),
  `valor_cif_usd` DECIMAL(14,2),               -- CIF = FOB + Flete + Seguro

  -- DATOS NACIONALIZACIÓN
  `tipo_cambio` DECIMAL(10,3),
  `valor_cif_pen` DECIMAL(14,2),               -- CIF en soles
  `arancel` DECIMAL(14,2),                     -- Ad Valorem
  `igv_importacion` DECIMAL(14,2),
  `ipm` DECIMAL(14,2),                         -- Impuesto Promoción Municipal
  `otros_tributos` DECIMAL(14,2),
  `total_derechos` DECIMAL(14,2),              -- Total tributos pagados

  -- DATOS LOGÍSTICOS
  `agente_aduanas` VARCHAR(200),
  `bl_conocimiento_embarque` VARCHAR(50),      -- Bill of Lading
  `fecha_llegada` DATE,
  `fecha_numeracion` DATE,
  `fecha_pago` DATE,

  -- OBSERVACIONES
  `observaciones` TEXT,
  `estado` TINYINT(1) DEFAULT 1,
  `idusuario` INT NOT NULL,
  `idempresa` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  KEY `idx_numero_dua` (`numero_dua`),
  KEY `idx_fecha_dua` (`fecha_dua`),
  KEY `idx_numero_invoice` (`numero_invoice`)
)
```

#### 10. **Tabla `detalle_importacion`** - NUEVA
```sql
CREATE TABLE `detalle_importacion` (
  `id_detalle` INT PRIMARY KEY AUTO_INCREMENT,
  `id_importacion` INT NOT NULL,
  `idarticulo` INT,
  `codigo_producto` VARCHAR(50),
  `descripcion_producto` VARCHAR(500) NOT NULL,
  `partida_arancelaria` VARCHAR(20),           -- Código HS
  `cantidad` DECIMAL(12,2) NOT NULL,
  `unidad_medida_sunat` VARCHAR(3),            -- NIU, KGM, etc.
  `valor_unitario_fob` DECIMAL(14,5),
  `valor_total_fob` DECIMAL(14,2),
  `peso_neto_kg` DECIMAL(12,3),
  `peso_bruto_kg` DECIMAL(12,3),
  `pais_origen` VARCHAR(50),

  FOREIGN KEY (`id_importacion`) REFERENCES `importaciones`(`id_importacion`) ON DELETE CASCADE,
  FOREIGN KEY (`idarticulo`) REFERENCES `articulo`(`idarticulo`) ON DELETE SET NULL
)
```

#### 11. **Tabla `series_comprobantes`** - NUEVA (Gestión flexible de series)
```sql
CREATE TABLE `series_comprobantes` (
  `id_serie` INT PRIMARY KEY AUTO_INCREMENT,
  `tipo_comprobante` VARCHAR(2) NOT NULL,      -- 01, 03, 07, 08, 09
  `serie` VARCHAR(4) NOT NULL,                 -- F001, B001, etc.
  `correlativo_actual` INT DEFAULT 0,
  `descripcion` VARCHAR(100),
  `estado` TINYINT(1) DEFAULT 1,               -- 1=Activa, 0=Inactiva
  `idempresa` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_serie` (`tipo_comprobante`, `serie`, `idempresa`)
)
```

---

## 📋 ANÁLISIS FUNCIONAL: MÓDULOS EXISTENTES VS REQUERIMIENTOS

### MÓDULO COMPRAS - Estado Actual

#### ✅ **LO QUE YA FUNCIONA:**
1. Registro de compras con proveedor, fecha, comprobante
2. Detalle de productos con cantidad y valor unitario
3. Actualización automática de stock en tabla `articulo`
4. Registro en `kardex` de cada movimiento
5. Soporte de moneda PEN y USD con tipo de cambio
6. Anulación de compras con reversión de stock

#### ❌ **LO QUE FALTA SEGÚN REQUERIMIENTOS:**

**Del usuario:**
> "Al momento de registrar la compra que tenga RUC – fecha de emisión – serie – numero correlativo- moneda- stock – descripción del producto, valor del producto y unidades de medidas de SUNAT."

**Análisis:**
- ✅ Serie: Ya existe
- ✅ Número correlativo: Ya existe
- ✅ Moneda: Ya existe
- ✅ Stock: Ya se actualiza automáticamente
- ✅ Valor del producto: Ya existe (valor_unitario)
- ❌ **RUC del emisor**: NO EXISTE - Se debe agregar
- ⚠️ **Fecha de emisión separada**: Existe pero combinada con hora
- ❌ **Descripción del producto en detalle**: NO EXISTE
- ❌ **Unidades de medida SUNAT**: Existe pero incompleta

**Archivos a modificar:**
- `/v3.3/vistas/compra.php` - Agregar campo RUC emisor en formulario
- `/v3.3/modelos/Compra.php` - Agregar parámetro ruc_emisor en método insertar()
- `/v3.3/ajax/compra.php` - Capturar y pasar RUC emisor
- `bd.sql` - Migración para agregar campos

---

### MÓDULO ALMACÉN - Estado Actual

#### ✅ **LO QUE YA FUNCIONA:**
1. Tabla almacén con sedes (PRINCIPAL, ATE)
2. Relación articulo → almacén
3. Tabla `detalle_articulo_almacen` para multi-almacén

#### ❌ **LO QUE FALTA SEGÚN REQUERIMIENTOS:**

**Del usuario:**
> "ALMACÉN: que tenga un buscador de productos, por sede, por familia o categoría y que muestre automáticamente el stock."

**Análisis:**
- ❌ Buscador multi-criterio: NO EXISTE
- ❌ Filtro por sede: NO EXISTE
- ❌ Filtro por familia/categoría: NO EXISTE
- ❌ Visualización automática de stock: NO EXISTE

**Archivos a crear/modificar:**
- `/v3.3/vistas/almacen.php` - Refactorizar completamente con buscador
- `/v3.3/modelos/Articulo.php` - Agregar métodos buscarPorSede(), buscarPorFamilia()
- `/v3.3/ajax/articulo.php` - Endpoints para búsqueda avanzada

---

### MÓDULO INVENTARIO/KARDEX - Estado Actual

#### ✅ **LO QUE YA FUNCIONA:**
1. Tabla kardex registra movimientos (COMPRA, VENTA, VENTA ANULADA)
2. Cálculo de costo promedio ponderado
3. Actualización de saldo_final y costo_2

#### ❌ **LO QUE FALTA SEGÚN REQUERIMIENTOS:**

**Del usuario:**
> "Inventario valorizado con Kardex PEPS (First In, First Out)"

**Análisis:**
- ❌ Método PEPS: NO IMPLEMENTADO (actualmente usa promedio ponderado)
- ❌ Vista SQL para inventario valorizado: NO EXISTE
- ❌ Reporte de inventario valorizado: NO EXISTE

**Solución requerida:**
- Modificar lógica de kardex para implementar PEPS
- Crear stored procedures para cálculo PEPS
- Crear vista `/v3.3/vistas/inventario_valorizado.php`

---

### MÓDULO CAJA - Estado Actual

#### ✅ **LO QUE YA FUNCIONA:**
1. Tabla caja con saldos
2. Tabla entrada_caja y salida_caja

#### ❌ **LO QUE FALTA SEGÚN REQUERIMIENTOS:**

**Del usuario:**
> "CAJA: se registre todos los ingresos y egresos diarios con apertura y cierre de caja diario."

**Análisis:**
- ❌ Apertura de caja diaria: NO EXISTE
- ❌ Cierre de caja diaria: NO EXISTE
- ⚠️ Registro de ingresos/egresos: EXISTE pero sin estructura diaria

**Archivos a modificar:**
- Crear tabla `apertura_cierre_caja`
- Modificar `/v3.3/vistas/caja.php`

---

### MÓDULO POS - Estado Actual

**Del usuario:**
> "POS: que aparezcan los productos por sede, que muestre su imagen, stock y precio de venta."

**Análisis:**
- ❌ Filtro por sede: NO EXISTE
- ⚠️ Imagen de producto: Campo existe en BD pero posiblemente no se muestra
- ❌ Stock visible: NO EXISTE
- ⚠️ Precio: Existe pero sin filtro por sede

---

### MÓDULO SIRE - Estado Actual

**Del usuario:**
> "SIRE (Sistema Integrado de Registros Electrónicos): Registro de Compras y Ventas, con exportación a Excel y TXT"

**Análisis:**
- ❌ MÓDULO NO EXISTE
- ❌ Tablas NO EXISTEN
- ❌ Reportes NO EXISTEN

**Implementación completa requerida**

---

### MÓDULO IMPORTACIONES - Estado Actual

**Del usuario:**
> "Crear módulo de Importaciones con registro de DUA (Declaración Única de Aduanas) y Commercial Invoice"

**Análisis:**
- ❌ MÓDULO NO EXISTE
- ❌ Tablas NO EXISTEN
- ❌ Formularios NO EXISTEN

**Implementación completa requerida**

---

## 🎯 PRIORIZACIÓN DE IMPLEMENTACIÓN

### FASE 1: BASE DE DATOS (CRÍTICO - 1-2 días)
1. ✅ Crear tabla `umedida_sunat` con Catálogo 03 completo
2. ✅ Migrar campos en tabla `compra` (ruc_emisor, fecha_emision separada)
3. ✅ Migrar campos en tabla `detalle_compra_producto` (descripcion, unidad_medida_sunat)
4. ✅ Crear tabla `series_comprobantes`
5. ✅ Crear tablas SIRE (`sire_compras`, `sire_ventas`)
6. ✅ Crear tablas Importaciones (`importaciones`, `detalle_importacion`)

### FASE 2: MÓDULO COMPRAS MEJORADO (ALTA PRIORIDAD - 2-3 días)
1. Modificar formulario compra.php (agregar RUC emisor)
2. Modificar modelo Compra.php (nuevos campos)
3. Modificar ajax/compra.php (captura de datos)
4. Implementar selector de unidad de medida SUNAT
5. Testing completo de flujo de compras

### FASE 3: MÓDULO UNIDADES DE MEDIDA (ALTA PRIORIDAD - 1 día)
1. Crear vista `umedida.php` para gestión de unidades SUNAT
2. Insertar las 200+ unidades del Catálogo 03
3. Crear componente selector reutilizable

### FASE 4: MÓDULO ALMACÉN REFACTORIZADO (MEDIA PRIORIDAD - 2 días)
1. Refactorizar vista almacen.php
2. Implementar buscador multi-criterio
3. Filtros por sede, familia, categoría
4. Visualización de stock en tiempo real

### FASE 5: KARDEX PEPS (ALTA PRIORIDAD - 3 días)
1. Investigar algoritmo PEPS
2. Modificar stored procedures
3. Crear vista inventario_valorizado.php
4. Reportes de valorización

### FASE 6: MÓDULO CAJA MEJORADO (MEDIA PRIORIDAD - 2 días)
1. Crear tabla apertura_cierre_caja
2. Implementar flujo diario
3. Reportes de caja diaria

### FASE 7: MÓDULO SIRE (ALTA PRIORIDAD - 4-5 días)
1. Implementar vistas de registro
2. Lógica de generación automática desde compras/ventas
3. Exportación a Excel (PHPExcel)
4. Exportación a TXT formato SUNAT

### FASE 8: MÓDULO IMPORTACIONES (MEDIA PRIORIDAD - 3-4 días)
1. Formulario de registro DUA
2. Formulario de Invoice
3. Vinculación con compras locales
4. Reportes de importaciones

### FASE 9: MEJORAS MENORES (BAJA PRIORIDAD - 1-2 días)
1. Renombrar "Realizar Ventas" → "Ventas Realizadas"
2. Mejorar POS con filtro de sede
3. Control de usuarios en Caja Chica
4. Utilidad semanal con gastos

### FASE 10: REFACTORIZACIÓN GENERAL (CONTINUA)
1. Aplicar mejores prácticas PSR
2. Separación de responsabilidades
3. Documentación completa
4. Testing automatizado

---

## 📦 CATÁLOGO 03 SUNAT - UNIDADES DE MEDIDA

**Total: 244 unidades oficiales**

### UNIDADES MÁS COMUNES (TOP 30):
| Código | Descripción | Uso |
|--------|-------------|-----|
| NIU | UNIDAD (BIENES) | Productos generales |
| ZZ | UNIDAD (SERVICIOS) | Servicios |
| KGM | KILOGRAMO | Peso |
| GRM | GRAMO | Peso pequeño |
| LTR | LITRO | Volumen líquido |
| MLT | MILILITRO | Volumen pequeño |
| MTR | METRO | Longitud |
| CMT | CENTIMETRO | Longitud pequeña |
| MTK | METRO CUADRADO | Área |
| MTQ | METRO CUBICO | Volumen |
| BX | CAJA | Empaque |
| PK | PAQUETE | Empaque |
| DZN | DOCENA | Agrupación 12 |
| GRO | GRUESA | Agrupación 144 |
| MIL | MILLAR | Agrupación 1000 |
| TNE | TONELADA | Peso grande |
| GLI | GALON UK | Volumen 4.546L |
| GLL | GALON US | Volumen 3.785L |
| FOT | PIE | Longitud |
| INH | PULGADA | Longitud pequeña |
| ONZ | ONZA | Peso |
| LBR | LIBRA | Peso |
| STN | TON (UK) | Peso 1016 kg |
| STI | STICK (CIGARRILLOS) | Empaque |
| BLL | BARRIL | Contenedor |
| BG | BOLSA | Empaque |
| BO | BOTELLA | Contenedor |
| CT | CARTON | Empaque |
| CMQ | CENTIMETRO CUBICO | Volumen |
| KWH | KILOWATT HORA | Energía |

**Archivo de inserción SQL completo se creará en siguiente paso.**

---

## 🔍 RESUMEN EJECUTIVO

### ✅ SISTEMA TIENE:
- Estructura básica funcional de compras y ventas
- Kardex con registro de movimientos
- Multi-almacén implementado
- Soporte de múltiples monedas
- Comprobantes electrónicos (Facturas, Boletas, NC, ND, Guías)

### ❌ SISTEMA NECESITA:
1. **Unidades de medida SUNAT completas** (244 unidades)
2. **Campos SUNAT en compras** (RUC emisor, descripción)
3. **Módulo SIRE completo** (registro + exportación)
4. **Módulo Importaciones** (DUA + Invoice)
5. **KARDEX PEPS** en lugar de promedio ponderado
6. **Almacén con búsqueda avanzada**
7. **Caja con apertura/cierre diaria**
8. **Series de comprobantes flexibles**

### 📈 IMPACTO ESTIMADO:
- **Complejidad**: Alta
- **Tiempo estimado**: 20-25 días de desarrollo
- **Archivos a modificar**: ~30 archivos
- **Archivos nuevos**: ~15 archivos
- **Líneas de código**: ~8,000 líneas nuevas
- **Migraciones SQL**: 10 scripts

---

**Siguiente paso:** Crear scripts SQL de migración y comenzar implementación por fases.

---

**Última actualización**: 2025-01-15
**Autor**: Claude Code
**Proyecto**: Sistema de Facturación Electrónica Perú
