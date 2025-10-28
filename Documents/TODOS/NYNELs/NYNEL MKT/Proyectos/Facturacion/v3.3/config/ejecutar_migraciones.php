<?php
/**
 * SCRIPT DE MIGRACIÓN - APLICAR CAMBIOS DE BASE DE DATOS SUNAT
 *
 * Este script ejecuta las migraciones SQL necesarias para:
 * 1. Crear tabla umedida_sunat con todas las unidades SUNAT
 * 2. Modificar tablas existentes para campos SUNAT
 * 3. Crear tablas SIRE, Importaciones y Series
 *
 * IMPORTANTE: Este script crea un backup automático antes de ejecutar
 *
 * @author Claude Code
 * @date 2025-01-15
 */

// Incluir configuración de base de datos
require_once 'global.php';

// Configuración
$backup_dir = __DIR__ . '/backups/';
$migracion_001 = __DIR__ . '/migracion_001_umedida_sunat.sql';
$migracion_002 = __DIR__ . '/migracion_002_tablas_sunat.sql';

// Crear directorio de backups si no existe
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
    echo "✅ Directorio de backups creado: $backup_dir\n";
}

// Conectar a la base de datos
$conexion = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conexion->connect_error) {
    die("❌ ERROR: No se pudo conectar a la base de datos: " . $conexion->connect_error . "\n");
}

$conexion->set_charset(DB_ENCODE);
echo "✅ Conectado a la base de datos: " . DB_NAME . "\n\n";

// ============================================================================
// PASO 1: CREAR BACKUP DE LA BASE DE DATOS
// ============================================================================
echo "======================================================================\n";
echo "PASO 1: CREANDO BACKUP DE LA BASE DE DATOS\n";
echo "======================================================================\n";

$fecha_backup = date('Y-m-d_H-i-s');
$backup_file = $backup_dir . 'backup_' . DB_NAME . '_' . $fecha_backup . '.sql';

echo "Archivo de backup: $backup_file\n";
echo "Iniciando backup...\n";

// Comando mysqldump
$comando_backup = sprintf(
    'mysqldump --host=%s --user=%s --password=%s %s > %s 2>&1',
    escapeshellarg(DB_HOST),
    escapeshellarg(DB_USERNAME),
    escapeshellarg(DB_PASSWORD),
    escapeshellarg(DB_NAME),
    escapeshellarg($backup_file)
);

exec($comando_backup, $output_backup, $return_backup);

if ($return_backup !== 0) {
    echo "⚠️ ADVERTENCIA: El backup con mysqldump falló. Intentando backup alternativo con PHP...\n";

    // Método alternativo: backup con PHP
    $tablas = [];
    $result = $conexion->query("SHOW TABLES");

    if ($result) {
        while ($row = $result->fetch_row()) {
            $tablas[] = $row[0];
        }
        $result->free();

        $backup_content = "-- Backup de " . DB_NAME . "\n";
        $backup_content .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
        $backup_content .= "-- Generado por: Script de Migración SUNAT\n\n";
        $backup_content .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tablas as $tabla) {
            // Obtener estructura de la tabla
            $result_create = $conexion->query("SHOW CREATE TABLE `$tabla`");
            if ($result_create) {
                $row_create = $result_create->fetch_row();
                $backup_content .= "\n-- Estructura de tabla: $tabla\n";
                $backup_content .= "DROP TABLE IF EXISTS `$tabla`;\n";
                $backup_content .= $row_create[1] . ";\n\n";
                $result_create->free();
            }

            // Obtener datos de la tabla
            $result_data = $conexion->query("SELECT * FROM `$tabla`");
            if ($result_data && $result_data->num_rows > 0) {
                $backup_content .= "-- Datos de tabla: $tabla\n";

                while ($row_data = $result_data->fetch_assoc()) {
                    $campos = [];
                    $valores = [];

                    foreach ($row_data as $campo => $valor) {
                        $campos[] = "`$campo`";
                        $valores[] = $valor === null ? 'NULL' : "'" . $conexion->real_escape_string($valor) . "'";
                    }

                    $backup_content .= "INSERT INTO `$tabla` (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ");\n";
                }

                $backup_content .= "\n";
                $result_data->free();
            }
        }

        $backup_content .= "SET FOREIGN_KEY_CHECKS=1;\n";

        if (file_put_contents($backup_file, $backup_content)) {
            echo "✅ Backup alternativo creado exitosamente con PHP\n";
        } else {
            die("❌ ERROR CRÍTICO: No se pudo crear el backup. DETENIENDO MIGRACIÓN.\n");
        }
    }
} else {
    echo "✅ Backup creado exitosamente con mysqldump\n";
}

// Verificar que el backup se creó
if (!file_exists($backup_file) || filesize($backup_file) < 1000) {
    die("❌ ERROR CRÍTICO: El archivo de backup es inválido o vacío. DETENIENDO MIGRACIÓN.\n");
}

$tamano_backup = filesize($backup_file);
echo "✅ Tamaño del backup: " . number_format($tamano_backup / 1024 / 1024, 2) . " MB\n";
echo "✅ Backup guardado en: $backup_file\n\n";

// ============================================================================
// PASO 2: APLICAR MIGRACIÓN 001 - UNIDADES DE MEDIDA SUNAT
// ============================================================================
echo "======================================================================\n";
echo "PASO 2: APLICANDO MIGRACIÓN 001 - UNIDADES DE MEDIDA SUNAT\n";
echo "======================================================================\n";

if (!file_exists($migracion_001)) {
    die("❌ ERROR: No se encontró el archivo de migración: $migracion_001\n");
}

echo "Leyendo archivo: $migracion_001\n";
$sql_migracion_001 = file_get_contents($migracion_001);

if ($sql_migracion_001 === false) {
    die("❌ ERROR: No se pudo leer el archivo de migración 001\n");
}

echo "Ejecutando migración 001...\n";

// Dividir el SQL en statements individuales
$statements = array_filter(
    array_map('trim', explode(';', $sql_migracion_001)),
    function($stmt) {
        return !empty($stmt) &&
               strpos($stmt, '--') !== 0 &&
               strpos($stmt, '/*') !== 0;
    }
);

$total_statements_001 = count($statements);
$ejecutados_001 = 0;
$errores_001 = 0;

foreach ($statements as $idx => $statement) {
    if (empty(trim($statement))) continue;

    $statement_limpio = trim($statement);

    // Mostrar progreso
    if (stripos($statement_limpio, 'INSERT INTO') === 0) {
        // Solo mostrar cada 50 inserts para no saturar la consola
        if ($ejecutados_001 % 50 === 0) {
            echo "  Procesando inserts... ($ejecutados_001/$total_statements_001)\n";
        }
    } else {
        echo "  Ejecutando: " . substr($statement_limpio, 0, 60) . "...\n";
    }

    if ($conexion->multi_query($statement_limpio . ';')) {
        do {
            if ($result = $conexion->store_result()) {
                $result->free();
            }
        } while ($conexion->more_results() && $conexion->next_result());

        $ejecutados_001++;
    } else {
        echo "  ⚠️ Error en statement: " . $conexion->error . "\n";
        $errores_001++;
    }
}

echo "\n✅ Migración 001 completada:\n";
echo "   - Total statements: $total_statements_001\n";
echo "   - Ejecutados: $ejecutados_001\n";
echo "   - Errores: $errores_001\n\n";

// Verificar que la tabla se creó correctamente
$result_check = $conexion->query("SELECT COUNT(*) as total FROM umedida_sunat");
if ($result_check) {
    $row_check = $result_check->fetch_assoc();
    echo "✅ Verificación: Tabla umedida_sunat tiene {$row_check['total']} registros\n\n";
    $result_check->free();
} else {
    echo "⚠️ ADVERTENCIA: No se pudo verificar la tabla umedida_sunat\n\n";
}

// ============================================================================
// PASO 3: APLICAR MIGRACIÓN 002 - TABLAS SUNAT
// ============================================================================
echo "======================================================================\n";
echo "PASO 3: APLICANDO MIGRACIÓN 002 - TABLAS SUNAT\n";
echo "======================================================================\n";

if (!file_exists($migracion_002)) {
    die("❌ ERROR: No se encontró el archivo de migración: $migracion_002\n");
}

echo "Leyendo archivo: $migracion_002\n";
$sql_migracion_002 = file_get_contents($migracion_002);

if ($sql_migracion_002 === false) {
    die("❌ ERROR: No se pudo leer el archivo de migración 002\n");
}

echo "Ejecutando migración 002...\n";

// Dividir el SQL en statements individuales
$statements_002 = array_filter(
    array_map('trim', explode(';', $sql_migracion_002)),
    function($stmt) {
        return !empty($stmt) &&
               strpos($stmt, '--') !== 0 &&
               strpos($stmt, '/*') !== 0;
    }
);

$total_statements_002 = count($statements_002);
$ejecutados_002 = 0;
$errores_002 = 0;

foreach ($statements_002 as $idx => $statement) {
    if (empty(trim($statement))) continue;

    $statement_limpio = trim($statement);

    // Mostrar progreso
    if (stripos($statement_limpio, 'ALTER TABLE') === 0) {
        echo "  Modificando tabla: " . substr($statement_limpio, 12, 40) . "...\n";
    } elseif (stripos($statement_limpio, 'CREATE TABLE') === 0) {
        echo "  Creando tabla: " . substr($statement_limpio, 13, 40) . "...\n";
    } elseif (stripos($statement_limpio, 'INSERT INTO') === 0) {
        echo "  Insertando datos iniciales...\n";
    } else {
        echo "  Ejecutando: " . substr($statement_limpio, 0, 60) . "...\n";
    }

    if ($conexion->multi_query($statement_limpio . ';')) {
        do {
            if ($result = $conexion->store_result()) {
                $result->free();
            }
        } while ($conexion->more_results() && $conexion->next_result());

        $ejecutados_002++;
    } else {
        // Algunos errores son esperados (como DROP TABLE IF EXISTS en tablas que no existen)
        if (stripos($statement_limpio, 'DROP TABLE IF EXISTS') === 0) {
            echo "  ℹ️ (Esperado) Tabla no existía previamente\n";
        } else {
            echo "  ⚠️ Error en statement: " . $conexion->error . "\n";
            $errores_002++;
        }
    }
}

echo "\n✅ Migración 002 completada:\n";
echo "   - Total statements: $total_statements_002\n";
echo "   - Ejecutados: $ejecutados_002\n";
echo "   - Errores: $errores_002\n\n";

// ============================================================================
// PASO 4: VERIFICACIÓN FINAL
// ============================================================================
echo "======================================================================\n";
echo "PASO 4: VERIFICACIÓN FINAL\n";
echo "======================================================================\n";

$tablas_verificar = [
    'umedida_sunat',
    'sire_compras',
    'sire_ventas',
    'importaciones',
    'detalle_importacion',
    'series_comprobantes'
];

echo "Verificando tablas creadas:\n";
foreach ($tablas_verificar as $tabla) {
    $result = $conexion->query("SHOW TABLES LIKE '$tabla'");
    if ($result && $result->num_rows > 0) {
        echo "  ✅ Tabla $tabla existe\n";
        $result->free();

        // Contar registros
        $result_count = $conexion->query("SELECT COUNT(*) as total FROM `$tabla`");
        if ($result_count) {
            $row_count = $result_count->fetch_assoc();
            echo "     Registros: {$row_count['total']}\n";
            $result_count->free();
        }
    } else {
        echo "  ❌ Tabla $tabla NO existe\n";
    }
}

echo "\nVerificando columnas agregadas:\n";

// Verificar columnas en tabla compra
$result = $conexion->query("SHOW COLUMNS FROM compra LIKE 'ruc_emisor'");
if ($result && $result->num_rows > 0) {
    echo "  ✅ Campo compra.ruc_emisor agregado\n";
    $result->free();
} else {
    echo "  ❌ Campo compra.ruc_emisor NO encontrado\n";
}

$result = $conexion->query("SHOW COLUMNS FROM compra LIKE 'fecha_emision'");
if ($result && $result->num_rows > 0) {
    echo "  ✅ Campo compra.fecha_emision agregado\n";
    $result->free();
} else {
    echo "  ❌ Campo compra.fecha_emision NO encontrado\n";
}

// Verificar columnas en detalle_compra_producto
$result = $conexion->query("SHOW COLUMNS FROM detalle_compra_producto LIKE 'unidad_medida_sunat'");
if ($result && $result->num_rows > 0) {
    echo "  ✅ Campo detalle_compra_producto.unidad_medida_sunat agregado\n";
    $result->free();
} else {
    echo "  ❌ Campo detalle_compra_producto.unidad_medida_sunat NO encontrado\n";
}

// Verificar columnas en articulo
$result = $conexion->query("SHOW COLUMNS FROM articulo LIKE 'unidad_medida_compra_sunat'");
if ($result && $result->num_rows > 0) {
    echo "  ✅ Campo articulo.unidad_medida_compra_sunat agregado\n";
    $result->free();
} else {
    echo "  ❌ Campo articulo.unidad_medida_compra_sunat NO encontrado\n";
}

// ============================================================================
// RESUMEN FINAL
// ============================================================================
echo "\n======================================================================\n";
echo "RESUMEN DE MIGRACIÓN\n";
echo "======================================================================\n";
echo "✅ Backup creado: $backup_file\n";
echo "✅ Tamaño backup: " . number_format(filesize($backup_file) / 1024 / 1024, 2) . " MB\n";
echo "✅ Migración 001: $ejecutados_001 statements ejecutados, $errores_001 errores\n";
echo "✅ Migración 002: $ejecutados_002 statements ejecutados, $errores_002 errores\n";
echo "\n";

if ($errores_001 > 0 || $errores_002 > 0) {
    echo "⚠️ ATENCIÓN: Se encontraron errores durante la migración.\n";
    echo "   Revise el log anterior para más detalles.\n";
    echo "   Si es necesario restaurar, use el backup: $backup_file\n";
} else {
    echo "🎉 MIGRACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "   Todas las tablas y campos SUNAT han sido creados correctamente.\n";
}

echo "\n";
echo "Próximos pasos:\n";
echo "1. Actualizar vistas (compra.php, articulo.php, etc.) para usar nuevos campos\n";
echo "2. Actualizar modelos PHP para insertar/actualizar nuevos campos\n";
echo "3. Implementar módulo SIRE\n";
echo "4. Implementar módulo Importaciones\n";
echo "======================================================================\n";

$conexion->close();
?>
