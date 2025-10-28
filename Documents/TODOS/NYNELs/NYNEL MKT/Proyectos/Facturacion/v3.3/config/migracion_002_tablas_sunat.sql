-- =====================================================
-- MIGRACIÓN 002: TABLAS SUNAT COMPLETAS
-- Sistema de Facturación Electrónica Perú
-- Fecha: 2025-01-15
-- Incluye: COMPRAS, SIRE, IMPORTACIONES, SERIES
-- =====================================================

-- =====================================================
-- PARTE 1: MODIFICACIONES A TABLAS EXISTENTES
-- =====================================================

-- 1.1 Agregar campos SUNAT a tabla COMPRA
ALTER TABLE `compra`
ADD COLUMN `ruc_emisor` VARCHAR(11) DEFAULT NULL COMMENT 'RUC del proveedor emisor del comprobante' AFTER `idproveedor`,
ADD COLUMN `fecha_emision` DATE DEFAULT NULL COMMENT 'Fecha de emisión del comprobante (sin hora)' AFTER `fecha`,
ADD COLUMN `descripcion_compra` TEXT DEFAULT NULL COMMENT 'Descripción general de la compra' AFTER `guia`;

-- 1.2 Crear índices para optimización de búsquedas
ALTER TABLE `compra`
ADD INDEX `idx_ruc_emisor` (`ruc_emisor`),
ADD INDEX `idx_fecha_emision` (`fecha_emision`),
ADD INDEX `idx_tipo_documento` (`tipo_documento`);

-- 1.3 Agregar campos SUNAT a tabla DETALLE_COMPRA_PRODUCTO
ALTER TABLE `detalle_compra_producto`
ADD COLUMN `descripcion_producto` VARCHAR(500) DEFAULT NULL COMMENT 'Descripción del producto comprado' AFTER `idarticulo`,
ADD COLUMN `unidad_medida_sunat` VARCHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código SUNAT de unidad de medida (Catálogo 03)' AFTER `subtotal`,
ADD COLUMN `codigo_producto` VARCHAR(50) DEFAULT NULL COMMENT 'Código del producto' AFTER `descripcion_producto`;

-- 1.4 Crear FK a nueva tabla umedida_sunat
ALTER TABLE `detalle_compra_producto`
ADD CONSTRAINT `fk_detalle_compra_umedida`
FOREIGN KEY (`unidad_medida_sunat`)
REFERENCES `umedida_sunat`(`codigo_sunat`)
ON UPDATE CASCADE ON DELETE SET NULL;

-- 1.5 Modificar tabla ARTICULO para usar código SUNAT
-- Primero agregar nueva columna
ALTER TABLE `articulo`
ADD COLUMN `unidad_medida_compra_sunat` VARCHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Unidad de medida SUNAT para compras' AFTER `unidad_medida`,
ADD COLUMN `unidad_medida_venta_sunat` VARCHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Unidad de medida SUNAT para ventas' AFTER `unidad_medida_compra_sunat`;

-- Crear FKs a umedida_sunat
ALTER TABLE `articulo`
ADD CONSTRAINT `fk_articulo_um_compra`
FOREIGN KEY (`unidad_medida_compra_sunat`)
REFERENCES `umedida_sunat`(`codigo_sunat`)
ON UPDATE CASCADE ON DELETE SET NULL,
ADD CONSTRAINT `fk_articulo_um_venta`
FOREIGN KEY (`unidad_medida_venta_sunat`)
REFERENCES `umedida_sunat`(`codigo_sunat`)
ON UPDATE CASCADE ON DELETE SET NULL;

-- =====================================================
-- PARTE 2: NUEVAS TABLAS SIRE (Sistema Integrado de Registros Electrónicos)
-- =====================================================

-- 2.1 Tabla SIRE_COMPRAS
DROP TABLE IF EXISTS `sire_compras`;

CREATE TABLE `sire_compras` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `periodo` VARCHAR(7) NOT NULL COMMENT 'Periodo AAAA-MM',
  `numero_correlativo` VARCHAR(50) DEFAULT NULL,
  `fecha_emision` DATE NOT NULL COMMENT 'Fecha de emisión del comprobante',
  `fecha_vcto_pago` DATE DEFAULT NULL COMMENT 'Fecha de vencimiento o pago',

  -- DATOS DEL COMPROBANTE
  `tipo_comprobante` VARCHAR(2) NOT NULL COMMENT 'Código Catálogo 01 SUNAT',
  `serie` VARCHAR(20) DEFAULT NULL,
  `numero` VARCHAR(20) DEFAULT NULL,
  `numero_final` VARCHAR(20) DEFAULT NULL COMMENT 'Para rangos de comprobantes',

  -- DATOS DEL PROVEEDOR
  `tipo_documento_identidad` VARCHAR(1) DEFAULT NULL COMMENT '6=RUC, 1=DNI, 4=CE, etc.',
  `numero_documento_identidad` VARCHAR(15) DEFAULT NULL,
  `razon_social` VARCHAR(200) DEFAULT NULL,

  -- IMPORTES GRAVADOS
  `base_imponible` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Base imponible gravada',
  `igv` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'IGV de operaciones gravadas',

  -- IMPORTES NO GRAVADOS
  `base_imponible_ng` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Base imponible no gravada',
  `igv_ng` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'IGV de operaciones no gravadas',

  -- IMPORTES NO DOMICILIADO
  `base_imponible_ndo` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Base imponible no domiciliado',
  `igv_ndo` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'IGV no domiciliado',

  -- OTROS TRIBUTOS
  `isc` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Impuesto Selectivo al Consumo',
  `icbper` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Impuesto a las Bolsas Plásticas',
  `otros_tributos` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Otros conceptos, tasas, cargos',
  `total` DECIMAL(12,2) NOT NULL COMMENT 'Importe total del comprobante',

  -- MONEDA
  `moneda` VARCHAR(3) DEFAULT 'PEN' COMMENT 'PEN, USD, EUR',
  `tipo_cambio` DECIMAL(10,3) DEFAULT 1.000,

  -- COMPROBANTE MODIFICADO (Para NC y ND)
  `fecha_emision_modificado` DATE DEFAULT NULL,
  `tipo_comprobante_modificado` VARCHAR(2) DEFAULT NULL,
  `serie_modificado` VARCHAR(20) DEFAULT NULL,
  `numero_modificado` VARCHAR(20) DEFAULT NULL,

  -- DETRACCION
  `fecha_constancia_deposito` DATE DEFAULT NULL,
  `numero_constancia_deposito` VARCHAR(50) DEFAULT NULL,

  -- ESTADO Y MARCA
  `marca_comprobante` VARCHAR(1) DEFAULT '0' COMMENT '1=Con detracciones, 0=Sin detracciones',
  `estado_comprobante` VARCHAR(1) DEFAULT '1' COMMENT '1=Emitido, 2=Anulado, 9=Pendiente',
  `estado_pago` VARCHAR(1) DEFAULT '2' COMMENT '1=Pagado, 2=Por pagar',

  -- RELACIONES
  `idcompra` INT DEFAULT NULL COMMENT 'FK a tabla compra',
  `idempresa` INT NOT NULL,

  -- AUDITORIA
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- INDICES
  KEY `idx_periodo` (`periodo`),
  KEY `idx_fecha_emision` (`fecha_emision`),
  KEY `idx_tipo_documento` (`tipo_documento_identidad`, `numero_documento_identidad`),
  KEY `idx_tipo_comprobante` (`tipo_comprobante`),
  KEY `idx_idcompra` (`idcompra`),
  KEY `idx_idempresa` (`idempresa`),

  -- FK
  FOREIGN KEY (`idcompra`) REFERENCES `compra`(`idcompra`) ON DELETE SET NULL,
  FOREIGN KEY (`idempresa`) REFERENCES `empresa`(`idempresa`) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='SIRE - Registro de Compras Electrónicas';

-- 2.2 Tabla SIRE_VENTAS
DROP TABLE IF EXISTS `sire_ventas`;

CREATE TABLE `sire_ventas` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `periodo` VARCHAR(7) NOT NULL COMMENT 'Periodo AAAA-MM',
  `numero_correlativo` VARCHAR(50) DEFAULT NULL,
  `fecha_emision` DATE NOT NULL COMMENT 'Fecha de emisión del comprobante',
  `fecha_vcto_pago` DATE DEFAULT NULL,

  -- DATOS DEL COMPROBANTE
  `tipo_comprobante` VARCHAR(2) NOT NULL COMMENT 'Código Catálogo 01: 01, 03, 07, 08',
  `serie` VARCHAR(20) DEFAULT NULL,
  `numero` VARCHAR(20) DEFAULT NULL,
  `numero_final` VARCHAR(20) DEFAULT NULL,

  -- DATOS DEL CLIENTE
  `tipo_documento_identidad` VARCHAR(1) DEFAULT NULL COMMENT '6=RUC, 1=DNI, etc.',
  `numero_documento_identidad` VARCHAR(15) DEFAULT NULL,
  `razon_social` VARCHAR(200) DEFAULT NULL,

  -- IMPORTES EXPORTACION
  `valor_exportacion` DECIMAL(12,2) DEFAULT 0.00,

  -- IMPORTES GRAVADOS
  `base_imponible` DECIMAL(12,2) DEFAULT 0.00,
  `descuento_bi` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Descuento base imponible',
  `igv` DECIMAL(12,2) DEFAULT 0.00,
  `descuento_igv` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Descuento IGV',

  -- IMPORTES NO GRAVADOS
  `exonerado` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Operaciones exoneradas',
  `inafecto` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Operaciones inafectas',

  -- OTROS TRIBUTOS
  `isc` DECIMAL(12,2) DEFAULT 0.00,
  `base_ivap` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Base IVAP (Arroz pilado)',
  `ivap` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Impuesto a la Venta de Arroz Pilado',
  `icbper` DECIMAL(12,2) DEFAULT 0.00,
  `otros_tributos` DECIMAL(12,2) DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL,

  -- MONEDA
  `moneda` VARCHAR(3) DEFAULT 'PEN',
  `tipo_cambio` DECIMAL(10,3) DEFAULT 1.000,

  -- COMPROBANTE MODIFICADO
  `fecha_emision_modificado` DATE DEFAULT NULL,
  `tipo_comprobante_modificado` VARCHAR(2) DEFAULT NULL,
  `serie_modificado` VARCHAR(20) DEFAULT NULL,
  `numero_modificado` VARCHAR(20) DEFAULT NULL,

  -- ESTADO
  `estado_comprobante` VARCHAR(1) DEFAULT '1' COMMENT '1=Emitido, 2=Anulado, 9=Pendiente',
  `estado_pago` VARCHAR(1) DEFAULT '2' COMMENT '1=Cobrado, 2=Por cobrar, 3=Crédito',

  -- RELACIONES (Dinámicas según tipo)
  `idventa` INT DEFAULT NULL COMMENT 'FK a factura, boleta, NC o ND según tipo_venta',
  `tipo_venta` VARCHAR(20) DEFAULT NULL COMMENT 'FACTURA, BOLETA, NC, ND',
  `idempresa` INT NOT NULL,

  -- AUDITORIA
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- INDICES
  KEY `idx_periodo` (`periodo`),
  KEY `idx_fecha_emision` (`fecha_emision`),
  KEY `idx_tipo_comprobante` (`tipo_comprobante`),
  KEY `idx_idempresa` (`idempresa`),
  KEY `idx_tipo_venta` (`tipo_venta`, `idventa`),

  -- FK
  FOREIGN KEY (`idempresa`) REFERENCES `empresa`(`idempresa`) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='SIRE - Registro de Ventas Electrónicas';

-- =====================================================
-- PARTE 3: NUEVAS TABLAS IMPORTACIONES
-- =====================================================

-- 3.1 Tabla IMPORTACIONES (Cabecera)
DROP TABLE IF EXISTS `importaciones`;

CREATE TABLE `importaciones` (
  `id_importacion` INT PRIMARY KEY AUTO_INCREMENT,

  -- DATOS DUA (Declaración Única de Aduanas)
  `numero_dua` VARCHAR(50) NOT NULL COMMENT 'Número de DUA',
  `fecha_dua` DATE NOT NULL COMMENT 'Fecha de numeración de DUA',
  `aduana` VARCHAR(100) DEFAULT NULL COMMENT 'Aduana de ingreso (Callao, Aérea, Marítima, etc.)',
  `regimen_aduanero` VARCHAR(10) DEFAULT '40' COMMENT '40=Importación definitiva, 31=Importación temporal',

  -- DATOS PROVEEDOR INTERNACIONAL
  `proveedor_internacional` VARCHAR(200) NOT NULL COMMENT 'Nombre del proveedor extranjero',
  `pais_origen` VARCHAR(50) DEFAULT NULL COMMENT 'País de origen de la mercancía',
  `direccion_proveedor` TEXT DEFAULT NULL,

  -- DATOS INVOICE COMERCIAL
  `numero_invoice` VARCHAR(50) DEFAULT NULL COMMENT 'Número de factura comercial',
  `fecha_invoice` DATE DEFAULT NULL,
  `valor_fob_usd` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Valor FOB en dólares',
  `flete_usd` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Flete internacional',
  `seguro_usd` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Seguro de transporte',
  `valor_cif_usd` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'CIF = FOB + Flete + Seguro',

  -- DATOS NACIONALIZACIÓN
  `tipo_cambio` DECIMAL(10,3) DEFAULT 1.000 COMMENT 'Tipo de cambio SUNAT del día',
  `valor_cif_pen` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Valor CIF en soles',
  `arancel` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Ad Valorem (arancel de importación)',
  `igv_importacion` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'IGV 18% sobre (CIF + Arancel)',
  `ipm` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Impuesto de Promoción Municipal 2%',
  `otros_tributos` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'ISC, sobretasa, etc.',
  `total_derechos` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Total de tributos aduaneros pagados',

  -- DATOS LOGÍSTICOS
  `agente_aduanas` VARCHAR(200) DEFAULT NULL COMMENT 'Nombre del agente de aduanas',
  `bl_conocimiento_embarque` VARCHAR(50) DEFAULT NULL COMMENT 'Bill of Lading',
  `fecha_llegada` DATE DEFAULT NULL COMMENT 'Fecha de llegada al puerto/aeropuerto',
  `fecha_numeracion` DATE DEFAULT NULL COMMENT 'Fecha de numeración DUA',
  `fecha_pago` DATE DEFAULT NULL COMMENT 'Fecha de pago de tributos',

  -- OBSERVACIONES Y ESTADO
  `observaciones` TEXT DEFAULT NULL,
  `estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Anulado',

  -- RELACIONES
  `idusuario` INT NOT NULL COMMENT 'Usuario que registró',
  `idempresa` INT NOT NULL,

  -- AUDITORIA
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- INDICES
  KEY `idx_numero_dua` (`numero_dua`),
  KEY `idx_fecha_dua` (`fecha_dua`),
  KEY `idx_numero_invoice` (`numero_invoice`),
  KEY `idx_proveedor` (`proveedor_internacional`),
  KEY `idx_idempresa` (`idempresa`),

  -- FK
  FOREIGN KEY (`idusuario`) REFERENCES `usuario`(`idusuario`),
  FOREIGN KEY (`idempresa`) REFERENCES `empresa`(`idempresa`) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de Importaciones - Cabecera DUA';

-- 3.2 Tabla DETALLE_IMPORTACION (Ítems)
DROP TABLE IF EXISTS `detalle_importacion`;

CREATE TABLE `detalle_importacion` (
  `id_detalle` INT PRIMARY KEY AUTO_INCREMENT,
  `id_importacion` INT NOT NULL,

  -- PRODUCTO
  `idarticulo` INT DEFAULT NULL COMMENT 'FK a artículo si existe en sistema',
  `codigo_producto` VARCHAR(50) DEFAULT NULL,
  `descripcion_producto` VARCHAR(500) NOT NULL,
  `partida_arancelaria` VARCHAR(20) DEFAULT NULL COMMENT 'Código HS (Harmonized System)',

  -- CANTIDADES
  `cantidad` DECIMAL(12,2) NOT NULL,
  `unidad_medida_sunat` VARCHAR(3) DEFAULT NULL COMMENT 'Código Catálogo 03',

  -- VALORES
  `valor_unitario_fob` DECIMAL(14,5) DEFAULT 0.00 COMMENT 'Precio unitario FOB',
  `valor_total_fob` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Total FOB del ítem',

  -- PESO
  `peso_neto_kg` DECIMAL(12,3) DEFAULT NULL,
  `peso_bruto_kg` DECIMAL(12,3) DEFAULT NULL,
  `pais_origen` VARCHAR(50) DEFAULT NULL COMMENT 'País de origen del ítem',

  -- FK
  FOREIGN KEY (`id_importacion`) REFERENCES `importaciones`(`id_importacion`) ON DELETE CASCADE,
  FOREIGN KEY (`idarticulo`) REFERENCES `articulo`(`idarticulo`) ON DELETE SET NULL,
  FOREIGN KEY (`unidad_medida_sunat`) REFERENCES `umedida_sunat`(`codigo_sunat`) ON DELETE SET NULL,

  -- INDICES
  KEY `idx_id_importacion` (`id_importacion`),
  KEY `idx_idarticulo` (`idarticulo`),
  KEY `idx_partida_arancelaria` (`partida_arancelaria`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Detalle de Importaciones - Ítems por DUA';

-- =====================================================
-- PARTE 4: TABLA SERIES DE COMPROBANTES
-- =====================================================

DROP TABLE IF EXISTS `series_comprobantes`;

CREATE TABLE `series_comprobantes` (
  `id_serie` INT PRIMARY KEY AUTO_INCREMENT,
  `tipo_comprobante` VARCHAR(2) NOT NULL COMMENT 'Código Catálogo 01: 01=Factura, 03=Boleta, etc.',
  `serie` VARCHAR(4) NOT NULL COMMENT 'Serie: F001, B001, etc.',
  `correlativo_actual` INT DEFAULT 0 COMMENT 'Último número correlativo usado',
  `descripcion` VARCHAR(100) DEFAULT NULL COMMENT 'Descripción de la serie',
  `estado` TINYINT(1) DEFAULT 1 COMMENT '1=Activa, 0=Inactiva',
  `idempresa` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- CONSTRAINT UNIQUE
  UNIQUE KEY `unique_serie` (`tipo_comprobante`, `serie`, `idempresa`),

  -- INDICES
  KEY `idx_tipo_comprobante` (`tipo_comprobante`),
  KEY `idx_estado` (`estado`),
  KEY `idx_idempresa` (`idempresa`),

  -- FK
  FOREIGN KEY (`idempresa`) REFERENCES `empresa`(`idempresa`) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Gestión de Series de Comprobantes';

-- =====================================================
-- PARTE 5: DATOS INICIALES
-- =====================================================

-- 5.1 Insertar series por defecto para Facturas
INSERT INTO `series_comprobantes`
(`tipo_comprobante`, `serie`, `correlativo_actual`, `descripcion`, `estado`, `idempresa`)
VALUES
('01', 'F001', 0, 'Facturas - Serie Principal', 1, 1),
('01', 'F002', 0, 'Facturas - Serie Secundaria', 1, 1);

-- 5.2 Insertar series por defecto para Boletas
INSERT INTO `series_comprobantes`
(`tipo_comprobante`, `serie`, `correlativo_actual`, `descripcion`, `estado`, `idempresa`)
VALUES
('03', 'B001', 0, 'Boletas - Serie Principal', 1, 1),
('03', 'B002', 0, 'Boletas - Serie Secundaria', 1, 1);

-- 5.3 Insertar series por defecto para Notas de Crédito
INSERT INTO `series_comprobantes`
(`tipo_comprobante`, `serie`, `correlativo_actual`, `descripcion`, `estado`, `idempresa`)
VALUES
('07', 'FC01', 0, 'Notas de Crédito - Facturas', 1, 1),
('07', 'BC01', 0, 'Notas de Crédito - Boletas', 1, 1);

-- 5.4 Insertar series por defecto para Notas de Débito
INSERT INTO `series_comprobantes`
(`tipo_comprobante`, `serie`, `correlativo_actual`, `descripcion`, `estado`, `idempresa`)
VALUES
('08', 'FD01', 0, 'Notas de Débito - Facturas', 1, 1),
('08', 'BD01', 0, 'Notas de Débito - Boletas', 1, 1);

-- 5.5 Insertar series por defecto para Guías de Remisión
INSERT INTO `series_comprobantes`
(`tipo_comprobante`, `serie`, `correlativo_actual`, `descripcion`, `estado`, `idempresa`)
VALUES
('09', 'T001', 0, 'Guías de Remisión - Principal', 1, 1);

-- =====================================================
-- PARTE 6: VERIFICACIONES
-- =====================================================

-- Verificar tablas creadas
SELECT
    'sire_compras' as tabla, COUNT(*) as registros FROM sire_compras
UNION ALL
SELECT 'sire_ventas', COUNT(*) FROM sire_ventas
UNION ALL
SELECT 'importaciones', COUNT(*) FROM importaciones
UNION ALL
SELECT 'detalle_importacion', COUNT(*) FROM detalle_importacion
UNION ALL
SELECT 'series_comprobantes', COUNT(*) FROM series_comprobantes;

-- Verificar columnas agregadas a COMPRA
SHOW COLUMNS FROM `compra` LIKE 'ruc_emisor';
SHOW COLUMNS FROM `compra` LIKE 'fecha_emision';
SHOW COLUMNS FROM `compra` LIKE 'descripcion_compra';

-- Verificar columnas agregadas a DETALLE_COMPRA_PRODUCTO
SHOW COLUMNS FROM `detalle_compra_producto` LIKE 'descripcion_producto';
SHOW COLUMNS FROM `detalle_compra_producto` LIKE 'unidad_medida_sunat';
SHOW COLUMNS FROM `detalle_compra_producto` LIKE 'codigo_producto';

-- =====================================================
-- FIN MIGRACIÓN 002
-- RESUMEN:
-- - Modificadas: 2 tablas (compra, detalle_compra_producto, articulo)
-- - Creadas: 5 tablas (sire_compras, sire_ventas, importaciones, detalle_importacion, series_comprobantes)
-- - Series por defecto: 9 series insertadas
-- =====================================================
