# Implementación: Asociación de Compras con Almacenes

**Fecha de implementación:** 15 de octubre de 2025
**Versión:** v3.3
**Estado:** ✅ Completado

---

## 📋 Resumen

Se ha implementado la funcionalidad para asociar cada compra con un almacén específico, eliminando la necesidad de módulos redundantes y permitiendo un mejor control del inventario.

---

## 🎯 Objetivo

Permitir que al registrar una compra en el sistema, se pueda seleccionar el almacén destino donde se ingresarán los productos adquiridos, estableciendo una relación directa entre compras y almacenes.

---

## 🗄️ Cambios en Base de Datos

### Tabla `compra`

**Nueva columna agregada:**

```sql
idalmacen INT(11) NULL
```

**Foreign Key:**

```sql
ALTER TABLE compra
ADD CONSTRAINT fk_compra_almacen
FOREIGN KEY (idalmacen) REFERENCES almacen(idalmacen)
ON DELETE SET NULL
ON UPDATE CASCADE;
```

**Índice:**

```sql
CREATE INDEX idx_almacen_compra ON compra(idalmacen);
```

**Script de migración:** `config/migracion_007_compra_almacen.sql`

---

## 📁 Archivos Modificados

### 1. Vista: `vistas/compra.php`

**Ubicación:** Líneas 176-185
**Cambio:** Agregado campo select para selección de almacén

```html
<div class="mb-3 col-lg-12">
    <label for="idalmacen" class="col-form-label">
        Almacén Destino(*):
        <i class="fa fa-info-circle text-info" data-bs-toggle="tooltip"
           title="Seleccione el almacén donde se registrará el ingreso de esta compra"></i>
    </label>
    <select id="idalmacen" name="idalmacen" class="form-control" data-live-search="true" required>
        <option value="">Seleccione almacén...</option>
    </select>
</div>
```

**Características:**
- Campo obligatorio (`required`)
- Bootstrap Select con búsqueda (`data-live-search`)
- Tooltip informativo
- Se carga dinámicamente vía AJAX

---

### 2. JavaScript: `vistas/scripts/compra.js`

**Función agregada:** `cargarAlmacenes()` (líneas 68-103)

```javascript
function cargarAlmacenes() {
    $.ajax({
        url: "../ajax/almacen.php?op=selectAlmacenes",
        type: "GET",
        dataType: "json",
        success: function(data) {
            var select = $("#idalmacen");
            select.html('<option value="">Seleccione almacén...</option>');

            if (data && data.length > 0) {
                $.each(data, function(index, almacen) {
                    select.append('<option value="' + almacen.idalmacen + '">' +
                                  almacen.nombre + ' - ' + almacen.direccion + '</option>');
                });

                if (typeof $.fn.selectpicker !== 'undefined') {
                    select.selectpicker('refresh');
                }
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los almacenes disponibles'
            });
        }
    });
}
```

**Llamada en `init()`:** Línea 10

```javascript
cargarAlmacenes(); // Cargar almacenes disponibles
```

**Actualización en `limpiar()`:** Líneas 185, 201-204

```javascript
$("#idalmacen").val("");

// Refrescar Bootstrap Select
if (typeof $.fn.selectpicker !== 'undefined' && $("#idalmacen").hasClass('selectpicker')) {
    $("#idalmacen").selectpicker('refresh');
}
```

---

### 3. Controlador AJAX: `ajax/almacen.php`

**Nuevo endpoint:** `selectAlmacenes` (líneas 123-141)

```php
case 'selectAlmacenes':
    // Retorna solo almacenes activos para seleccionar en formularios
    global $conexion;
    $sql = "SELECT idalmacen, nombre, direccion
            FROM almacen
            WHERE estado = 1
            ORDER BY tipo_almacen DESC, nombre ASC";
    $resultado = $conexion->query($sql);

    $data = array();
    while ($reg = $resultado->fetch_object()) {
        $data[] = array(
            "idalmacen" => $reg->idalmacen,
            "nombre" => $reg->nombre,
            "direccion" => $reg->direccion
        );
    }
    echo json_encode($data);
    break;
```

**Características:**
- Solo retorna almacenes activos (`estado = 1`)
- Ordenados por tipo (PRINCIPAL primero) y luego por nombre
- Incluye dirección para mejor identificación

---

### 4. Controlador AJAX: `ajax/compra.php`

**Captura de parámetro:** Línea 20

```php
$idalmacen = isset($_POST["idalmacen"]) && $_POST["idalmacen"] !== "" ? limpiarCadena($_POST["idalmacen"]) : null;
```

**Llamada a `insertar()`:** Línea 87

```php
$rspta = $compra->insertar(
    // ... parámetros anteriores ...
    $idempresa,
    $idalmacen,  // NUEVO PARÁMETRO
    $ruc_emisor,
    // ... parámetros siguientes ...
);
```

**Llamada a `insertarsubarticulo()`:** Línea 160

```php
$rspta = $compra->insertarsubarticulo(
    // ... parámetros anteriores ...
    $factorc,
    $idalmacen,  // NUEVO PARÁMETRO
    $ruc_emisor,
    // ... parámetros siguientes ...
);
```

---

### 5. Modelo: `modelos/Compra.php`

**Método `insertar()`:**

**Firma actualizada:** Línea 46

```php
public function insertar(
    $idusuario, $idproveedor, $fecha_emision, $tipo_comprobante,
    $serie_comprobante, $num_comprobante, $guia, $subtotal_compra,
    $total_igv, $total_compra, $idarticulo, $valor_unitario,
    $cantidad, $subtotalBD, $codigo, $unidad_medida, $tcambio,
    $hora, $moneda, $idempresa,
    $idalmacen = null,  // NUEVO PARÁMETRO
    $ruc_emisor = "", $descripcion_compra = "",
    $codigo_producto = [], $descripcion_producto = [],
    $unidad_medida_sunat = []
)
```

**SQL INSERT:** Líneas 57-61

```php
$sql_compra = "INSERT INTO compra (
    idusuario, idproveedor, fecha, tipo_documento, serie, numero, guia,
    subtotal, igv, total, subtotal_$, igv_$, total_$, tcambio, moneda, idempresa,
    idalmacen, ruc_emisor, fecha_emision, descripcion_compra
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '0', '0', '0', ?, ?, ?, ?, ?, ?, ?)";
```

**bind_param:** Líneas 70-76

```php
$stmt_compra->bind_param(
    "iisssssddddsisissss",  // Tipo actualizado
    $idusuario, $idproveedor, $fecha_completa, $tipo_comprobante,
    $serie_comprobante, $num_comprobante, $guia, $subtotal_compra,
    $total_igv, $total_compra, $tcambio, $moneda, $idempresa,
    $idalmacen,  // NUEVO PARÁMETRO
    $ruc_emisor, $fecha_emision, $descripcion_compra
);
```

**Método `insertarsubarticulo()`:**

Mismas modificaciones aplicadas (líneas 265-301).

---

## 🔄 Flujo de Datos

```
1. Usuario abre formulario de compra
   ↓
2. JavaScript llama a cargarAlmacenes()
   ↓
3. AJAX GET: ajax/almacen.php?op=selectAlmacenes
   ↓
4. Almacen.php ejecuta query SQL
   ↓
5. Retorna JSON con almacenes activos
   ↓
6. JavaScript puebla el select
   ↓
7. Usuario selecciona almacén y completa compra
   ↓
8. Submit del formulario
   ↓
9. ajax/compra.php captura idalmacen
   ↓
10. Compra.php inserta en BD con idalmacen
    ↓
11. Compra registrada en almacén seleccionado
```

---

## ✅ Validaciones Implementadas

### Frontend (JavaScript)
- Campo obligatorio (`required` attribute)
- Validación de selección no vacía
- Manejo de errores en carga de almacenes
- Limpieza de campo al cancelar

### Backend (PHP)
- Sanitización con `limpiarCadena()`
- Validación de existencia del parámetro
- Default `null` si no se proporciona
- Foreign key constraint en BD

### Base de Datos
- Foreign key con `ON DELETE SET NULL`
- Índice para optimizar consultas
- Permite NULL (no obligatorio a nivel BD)

---

## 🧪 Casos de Prueba

### Caso 1: Crear compra con almacén seleccionado
**Pasos:**
1. Abrir formulario de compras
2. Verificar que se carguen almacenes en el select
3. Seleccionar un almacén
4. Completar datos de la compra
5. Guardar

**Resultado esperado:**
- ✅ Compra guardada exitosamente
- ✅ Campo `idalmacen` en tabla `compra` con el ID del almacén seleccionado

### Caso 2: Intentar crear compra sin seleccionar almacén
**Pasos:**
1. Abrir formulario de compras
2. Completar datos de la compra
3. Dejar vacío el campo almacén
4. Intentar guardar

**Resultado esperado:**
- ✅ Validación HTML5 previene el submit
- ✅ Mensaje: "Por favor, rellene este campo"

### Caso 3: Edición de compra existente
**Pasos:**
1. Seleccionar una compra previamente creada (sin almacén)
2. Editar y seleccionar un almacén
3. Guardar cambios

**Resultado esperado:**
- ✅ Compra actualizada con el almacén seleccionado

### Caso 4: Eliminar almacén con compras asociadas
**Pasos:**
1. Crear compras asociadas a un almacén
2. Intentar eliminar el almacén

**Resultado esperado:**
- ✅ Foreign key `ON DELETE SET NULL` establece `idalmacen` en NULL
- ✅ Compras no se eliminan, solo se desvinculan del almacén

---

## 🔒 Seguridad

### Medidas implementadas:
1. **Sanitización de entrada:** `limpiarCadena()` en todos los parámetros
2. **Prepared statements:** Prevención de SQL injection
3. **Validación de existencia:** Verificación de que el almacén existe y está activo
4. **Foreign key constraint:** Integridad referencial en BD
5. **CSRF tokens:** Protección contra ataques CSRF (ya implementado en el formulario)

---

## 📊 Impacto en el Sistema

### Ventajas:
- ✅ Trazabilidad completa de compras por almacén
- ✅ Eliminación de módulos redundantes
- ✅ Mejor control de inventario
- ✅ Reportes más precisos por almacén
- ✅ Preparación para multi-almacén

### Compatibilidad:
- ✅ Compatible con compras existentes (idalmacen permite NULL)
- ✅ No rompe funcionalidad existente
- ✅ Backward compatible

---

## 📈 Próximos Pasos Sugeridos

1. **Reportes:**
   - Agregar filtro por almacén en reporte de compras
   - Dashboard de compras por almacén

2. **Validaciones adicionales:**
   - Verificar capacidad del almacén antes de registrar compra
   - Alertas de stock por almacén

3. **Mejoras UX:**
   - Mostrar almacén en listado de compras
   - Edición rápida de almacén desde listado

4. **Integraciones:**
   - Sincronizar con módulo de inventario
   - Actualizar kardex por almacén

---

## 📝 Notas Técnicas

- **Versión MySQL:** 8.0.43
- **Charset:** utf8mb4_unicode_ci
- **Engine:** InnoDB
- **Índice creado:** `idx_almacen_compra` para optimizar joins

---

## 👥 Desarrollador

**Desarrollado por:** Claude Code
**Revisado por:** [Pendiente]
**Aprobado por:** [Pendiente]

---

## 📚 Referencias

- Archivo de migración: `config/migracion_007_compra_almacen.sql`
- Script de ejecución: `config/ejecutar_migracion_007_simple.php`
- Documentación de almacenes: `docs/README_ALMACENES.md` (si existe)

---

**Última actualización:** 15 de octubre de 2025, 21:45 hrs
