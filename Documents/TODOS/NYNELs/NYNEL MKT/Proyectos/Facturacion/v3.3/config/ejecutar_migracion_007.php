<?php
/**
 * Script para ejecutar la migración 007: Agregar campo idalmacen a tabla compra
 * Fecha: 2025-01-15
 */

// Incluir archivo de conexión
require_once "Conexion.php";

// Colores para consola
define('COLOR_SUCCESS', "\033[0;32m");
define('COLOR_ERROR', "\033[0;31m");
define('COLOR_INFO', "\033[0;36m");
define('COLOR_RESET', "\033[0m");

echo COLOR_INFO . "====================================\n" . COLOR_RESET;
echo COLOR_INFO . "MIGRACIÓN 007: Compra - Almacén\n" . COLOR_RESET;
echo COLOR_INFO . "====================================\n\n" . COLOR_RESET;

try {
    // Leer archivo SQL
    $sqlFile = __DIR__ . '/migracion_007_compra_almacen.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("No se encuentra el archivo de migración: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);

    if ($sql === false) {
        throw new Exception("Error al leer el archivo de migración");
    }

    echo COLOR_INFO . "Archivo de migración cargado correctamente\n" . COLOR_RESET;
    echo COLOR_INFO . "Iniciando ejecución de migración...\n\n" . COLOR_RESET;

    // Obtener conexión global
    global $conexion;

    if (!$conexion) {
        throw new Exception("No se pudo establecer conexión con la base de datos");
    }

    // Separar las consultas por punto y coma
    $queries = explode(';', $sql);
    $executedQueries = 0;
    $errors = [];

    foreach ($queries as $query) {
        // Limpiar query
        $query = trim($query);

        // Saltar queries vacías o comentarios
        if (empty($query) || substr($query, 0, 2) === '--' || substr($query, 0, 2) === '/*') {
            continue;
        }

        // Ejecutar query
        if (!mysqli_multi_query($conexion, $query)) {
            $errors[] = "Error en query: " . mysqli_error($conexion);
            echo COLOR_ERROR . "✗ Error: " . mysqli_error($conexion) . "\n" . COLOR_RESET;
        } else {
            $executedQueries++;

            // Consumir todos los resultados de mysqli_multi_query
            do {
                if ($result = mysqli_store_result($conexion)) {
                    // Mostrar resultados si los hay
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo COLOR_SUCCESS . "✓ ";
                        foreach ($row as $key => $value) {
                            echo "$key: $value ";
                        }
                        echo "\n" . COLOR_RESET;
                    }
                    mysqli_free_result($result);
                }
            } while (mysqli_next_result($conexion));
        }
    }

    echo "\n" . COLOR_INFO . "====================================\n" . COLOR_RESET;
    echo COLOR_SUCCESS . "Migración completada\n" . COLOR_RESET;
    echo COLOR_INFO . "Queries ejecutadas: $executedQueries\n" . COLOR_RESET;

    if (!empty($errors)) {
        echo COLOR_ERROR . "Errores encontrados: " . count($errors) . "\n" . COLOR_RESET;
        foreach ($errors as $error) {
            echo COLOR_ERROR . "  - $error\n" . COLOR_RESET;
        }
    }

    // Verificar que la columna se haya creado correctamente
    echo "\n" . COLOR_INFO . "Verificando estructura de la tabla compra...\n" . COLOR_RESET;

    $verify = mysqli_query($conexion, "SHOW COLUMNS FROM compra WHERE Field = 'idalmacen'");

    if ($verify && mysqli_num_rows($verify) > 0) {
        $column = mysqli_fetch_assoc($verify);
        echo COLOR_SUCCESS . "✓ Columna 'idalmacen' creada correctamente\n" . COLOR_RESET;
        echo COLOR_INFO . "  Tipo: " . $column['Type'] . "\n" . COLOR_RESET;
        echo COLOR_INFO . "  Null: " . $column['Null'] . "\n" . COLOR_RESET;
        echo COLOR_INFO . "  Default: " . ($column['Default'] ?? 'NULL') . "\n" . COLOR_RESET;
    } else {
        echo COLOR_ERROR . "✗ No se pudo verificar la columna 'idalmacen'\n" . COLOR_RESET;
    }

    // Verificar foreign key
    $fkQuery = "SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = 'paginasw_v2'
                AND TABLE_NAME = 'compra'
                AND COLUMN_NAME = 'idalmacen'
                AND REFERENCED_TABLE_NAME IS NOT NULL";

    $fkResult = mysqli_query($conexion, $fkQuery);

    if ($fkResult && mysqli_num_rows($fkResult) > 0) {
        $fk = mysqli_fetch_assoc($fkResult);
        echo COLOR_SUCCESS . "✓ Foreign key creada correctamente\n" . COLOR_RESET;
        echo COLOR_INFO . "  Constraint: " . $fk['CONSTRAINT_NAME'] . "\n" . COLOR_RESET;
        echo COLOR_INFO . "  Referencia: " . $fk['REFERENCED_TABLE_NAME'] . "." . $fk['REFERENCED_COLUMN_NAME'] . "\n" . COLOR_RESET;
    }

    // Verificar índice
    $indexQuery = "SHOW INDEX FROM compra WHERE Key_name = 'idx_almacen_compra'";
    $indexResult = mysqli_query($conexion, $indexQuery);

    if ($indexResult && mysqli_num_rows($indexResult) > 0) {
        echo COLOR_SUCCESS . "✓ Índice 'idx_almacen_compra' creado correctamente\n" . COLOR_RESET;
    }

    echo "\n" . COLOR_SUCCESS . "====================================\n" . COLOR_RESET;
    echo COLOR_SUCCESS . "MIGRACIÓN EXITOSA\n" . COLOR_RESET;
    echo COLOR_SUCCESS . "====================================\n" . COLOR_RESET;

} catch (Exception $e) {
    echo "\n" . COLOR_ERROR . "====================================\n" . COLOR_RESET;
    echo COLOR_ERROR . "ERROR EN MIGRACIÓN\n" . COLOR_RESET;
    echo COLOR_ERROR . "====================================\n" . COLOR_RESET;
    echo COLOR_ERROR . "Mensaje: " . $e->getMessage() . "\n" . COLOR_RESET;
    echo COLOR_ERROR . "Archivo: " . $e->getFile() . "\n" . COLOR_RESET;
    echo COLOR_ERROR . "Línea: " . $e->getLine() . "\n" . COLOR_RESET;
    exit(1);
}

?>
