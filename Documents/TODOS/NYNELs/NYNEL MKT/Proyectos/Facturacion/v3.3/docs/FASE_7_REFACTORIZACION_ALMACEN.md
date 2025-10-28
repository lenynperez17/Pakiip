# 📦 FASE 7: REFACTORIZACIÓN DEL MÓDULO ALMACÉN

**Fecha:** 2025-10-16
**Sistema:** Sistema de Facturación Electrónica v3.3
**Tarea:** Refactorizar módulo Almacén con buscador avanzado y mejoras de usabilidad

---

## 📋 ANÁLISIS DEL MÓDULO ACTUAL

### Estructura actual:
- **Vista:** `/v3.3/vistas/almacen.php` (111 líneas)
- **JavaScript:** `/v3.3/vistas/scripts/almacen.js` (208 líneas)
- **Controlador:** `/v3.3/ajax/almacen.php` (86 líneas)
- **Modelo:** `/v3.3/modelos/Almacen.php` (205 líneas)

### Funcionalidades actuales:
✅ CRUD básico (Crear, Leer, Actualizar, Desactivar/Activar)
✅ Modal Bootstrap para agregar/editar
✅ DataTables con paginación
✅ Validación de duplicados
✅ Prepared statements (seguridad)

### Campos actuales de la tabla `almacen`:
```sql
- idalmacen (INT AUTO_INCREMENT PRIMARY KEY)
- nombre (VARCHAR)
- direccion (VARCHAR)
- estado (TINYINT)
- idempresa (INT FK)
```

---

## 🎯 MEJORAS PLANIFICADAS

### 1. **Buscador Avanzado**
Agregar filtros inteligentes:
- Búsqueda por nombre
- Búsqueda por dirección
- Filtro por estado (activo/inactivo)
- Filtro por empresa (multi-empresa)
- Búsqueda en tiempo real (debounce)

### 2. **Información Adicional en Listado**
Agregar columnas útiles:
- Cantidad de productos en almacén
- Valor total del inventario
- Última actualización
- Responsable del almacén
- Icono de estado visual mejorado

### 3. **Campos Adicionales en Formulario**
Extender información del almacén:
- **Teléfono de contacto**
- **Email del almacén**
- **Responsable** (FK a usuario)
- **Tipo de almacén** (Principal, Secundario, Temporal)
- **Capacidad máxima** (opcional)
- **Descripción/Notas**

### 4. **Exportación de Datos**
Agregar botones DataTables:
- Excel (inventario por almacén)
- PDF (reporte de almacenes)
- CSV (datos crudos)
- Copiar (clipboard)

### 5. **Estadísticas Visuales**
Tarjetas informativas:
- Total de almacenes activos
- Total de productos en todos los almacenes
- Valor total del inventario
- Almacenes con bajo stock

---

## 🛠️ IMPLEMENTACIÓN

### PASO 1: Actualizar tabla `almacen`

```sql
-- Agregar nuevos campos a la tabla almacen
ALTER TABLE `almacen`
ADD COLUMN `telefono` VARCHAR(20) DEFAULT NULL AFTER `direccion`,
ADD COLUMN `email` VARCHAR(100) DEFAULT NULL AFTER `telefono`,
ADD COLUMN `idusuario_responsable` INT(11) DEFAULT NULL AFTER `email`,
ADD COLUMN `tipo_almacen` ENUM('PRINCIPAL', 'SECUNDARIO', 'TEMPORAL') DEFAULT 'SECUNDARIO' AFTER `idusuario_responsable`,
ADD COLUMN `capacidad_max` INT(11) DEFAULT NULL COMMENT 'Capacidad máxima en unidades' AFTER `tipo_almacen`,
ADD COLUMN `notas` TEXT DEFAULT NULL AFTER `capacidad_max`,
ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `notas`,
ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
ADD KEY `idx_estado` (`estado`),
ADD KEY `idx_idempresa` (`idempresa`),
ADD KEY `idx_tipo` (`tipo_almacen`);

-- Agregar FK al responsable (usuario)
ALTER TABLE `almacen`
ADD CONSTRAINT `fk_almacen_usuario_responsable`
FOREIGN KEY (`idusuario_responsable`)
REFERENCES `usuario` (`idusuario`)
ON DELETE SET NULL
ON UPDATE CASCADE;
```

### PASO 2: Actualizar Modelo `Almacen.php`

**Métodos a agregar/modificar:**
- `insertaralmacen()` - Agregar parámetros nuevos
- `editar()` - Agregar parámetros nuevos
- `listarConEstadisticas()` - JOIN con artículos para contar stock
- `buscar($termino, $filtros)` - Búsqueda avanzada
- `obtenerEstadisticas()` - Totales, valores, etc.

### PASO 3: Actualizar Vista `almacen.php`

**Mejoras:**
- Tarjetas de estadísticas en la parte superior
- Barra de búsqueda avanzada
- Filtros por tipo y estado
- Tabla con columnas adicionales
- Modal con formulario extendido

### PASO 4: Actualizar JavaScript `almacen.js`

**Funcionalidades:**
- Búsqueda en tiempo real con debounce
- Aplicar filtros dinámicamente
- Cargar responsables en select
- Exportación de datos
- Validación de campos adicionales

### PASO 5: Actualizar Controlador `ajax/almacen.php`

**Casos nuevos:**
- `buscar` - Búsqueda con filtros
- `estadisticas` - Totales y valores
- `listarResponsables` - Select de usuarios
- Actualizar `guardaryeditar` con nuevos campos
- Actualizar `listar` con JOINs

---

## 📊 WIREFRAME / MOCKUP

```
┌─────────────────────────────────────────────────────────────┐
│ ALMACENES                                    [+ Agregar]    │
├─────────────────────────────────────────────────────────────┤
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐       │
│ │📦 Total  │ │✅ Activos│ │💰 Valor  │ │⚠️  Bajo  │       │
│ │    12    │ │    10    │ │ S/45,300 │ │ Stock: 3 │       │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘       │
├─────────────────────────────────────────────────────────────┤
│ 🔍 Buscar: [___________]  Tipo:[Todos▾] Estado:[Todos▾]   │
├─────────────────────────────────────────────────────────────┤
│ [Copy] [Excel] [CSV] [PDF]                                 │
├─────────────────────────────────────────────────────────────┤
│ Nombre    │ Dirección  │ Tipo      │ Responsable │ Estado │
│ Central   │ Av Lima 123│ Principal │ Juan Pérez  │ ✅ Act │
│ Sucursal 1│ Jr Arequipa│ Secundario│ Ana García  │ ✅ Act │
│ Temporal  │ Jr Puno 45 │ Temporal  │ -           │ ❌ Inact│
└─────────────────────────────────────────────────────────────┘
```

---

## ✅ CHECKLIST DE TESTING

### Funcionalidad Básica:
- [ ] CRUD completo funciona con nuevos campos
- [ ] Validación de duplicados funciona
- [ ] Activar/Desactivar funciona
- [ ] Modal se abre y cierra correctamente

### Búsqueda y Filtros:
- [ ] Búsqueda por nombre funciona
- [ ] Búsqueda por dirección funciona
- [ ] Filtro por tipo funciona
- [ ] Filtro por estado funciona
- [ ] Búsqueda en tiempo real (debounce)

### Estadísticas:
- [ ] Tarjetas muestran valores correctos
- [ ] Conteo de almacenes correcto
- [ ] Valor total del inventario correcto
- [ ] Detección de bajo stock funciona

### Exportación:
- [ ] Exportar a Excel funciona
- [ ] Exportar a PDF funciona
- [ ] Exportar a CSV funciona
- [ ] Copiar al portapapeles funciona

### Validaciones:
- [ ] Campos requeridos se validan
- [ ] Email se valida con formato correcto
- [ ] Teléfono acepta solo números
- [ ] Capacidad máxima acepta solo números positivos

### Seguridad:
- [ ] Prepared statements en todas las queries
- [ ] Sanitización de inputs
- [ ] Modo demo bloquea cambios
- [ ] Permisos verificados (Logistica)

---

## 📝 NOTAS TÉCNICAS

### Consideraciones:
1. **Compatibilidad:** Mantener compatibilidad con módulos que usan `almacen.select()`
2. **Migración:** Crear script SQL de migración con datos existentes
3. **Responsable:** Puede ser NULL si no se asigna
4. **Tipo almacén:** Por defecto SECUNDARIO, solo un PRINCIPAL por empresa
5. **Capacidad:** Campo opcional para control futuro

### Dependencias:
- Tabla `usuario` debe existir para FK
- Tabla `articulo` debe tener FK `idalmacen`
- Tabla `empresa` debe existir para multi-empresa

---

**Estado:** 🚧 EN PLANIFICACIÓN
**Próximo paso:** Crear script SQL de migración
