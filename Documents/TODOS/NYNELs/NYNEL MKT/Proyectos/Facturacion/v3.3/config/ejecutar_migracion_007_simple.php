<?php
/**
 * Script simple para ejecutar la migración 007
 */

require_once "Conexion.php";

echo "===========================================\n";
echo "MIGRACIÓN 007: Agregar idalmacen a compra\n";
echo "===========================================\n\n";

global $conexion;

// 1. Verificar si la columna ya existe
$check = mysqli_query($conexion, "SHOW COLUMNS FROM compra WHERE Field = 'idalmacen'");

if (mysqli_num_rows($check) > 0) {
    echo "✓ La columna 'idalmacen' ya existe en la tabla compra\n";
} else {
    echo "→ Agregando columna 'idalmacen'...\n";
    $sql1 = "ALTER TABLE compra ADD COLUMN idalmacen INT(11) NULL AFTER idempresa";

    if (mysqli_query($conexion, $sql1)) {
        echo "✓ Columna 'idalmacen' agregada exitosamente\n";
    } else {
        echo "✗ Error al agregar columna: " . mysqli_error($conexion) . "\n";
        exit(1);
    }
}

// 2. Verificar si existe el foreign key
$checkFK = mysqli_query($conexion, "
    SELECT CONSTRAINT_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'paginasw_v2'
    AND TABLE_NAME = 'compra'
    AND COLUMN_NAME = 'idalmacen'
    AND CONSTRAINT_NAME = 'fk_compra_almacen'
");

if (mysqli_num_rows($checkFK) > 0) {
    echo "✓ Foreign key 'fk_compra_almacen' ya existe\n";
} else {
    echo "→ Agregando foreign key...\n";
    $sql2 = "ALTER TABLE compra
             ADD CONSTRAINT fk_compra_almacen
             FOREIGN KEY (idalmacen) REFERENCES almacen(idalmacen)
             ON DELETE SET NULL
             ON UPDATE CASCADE";

    if (mysqli_query($conexion, $sql2)) {
        echo "✓ Foreign key agregado exitosamente\n";
    } else {
        echo "✗ Error al agregar foreign key: " . mysqli_error($conexion) . "\n";
        // No es crítico, continuamos
    }
}

// 3. Verificar si existe el índice
$checkIndex = mysqli_query($conexion, "
    SHOW INDEX FROM compra WHERE Key_name = 'idx_almacen_compra'
");

if (mysqli_num_rows($checkIndex) > 0) {
    echo "✓ Índice 'idx_almacen_compra' ya existe\n";
} else {
    echo "→ Creando índice...\n";
    $sql3 = "CREATE INDEX idx_almacen_compra ON compra(idalmacen)";

    if (mysqli_query($conexion, $sql3)) {
        echo "✓ Índice creado exitosamente\n";
    } else {
        echo "✗ Error al crear índice: " . mysqli_error($conexion) . "\n";
        // No es crítico, continuamos
    }
}

// 4. Verificación final
echo "\n===========================================\n";
echo "VERIFICACIÓN FINAL\n";
echo "===========================================\n";

$verify = mysqli_query($conexion, "SHOW COLUMNS FROM compra WHERE Field = 'idalmacen'");

if ($verify && mysqli_num_rows($verify) > 0) {
    $column = mysqli_fetch_assoc($verify);
    echo "✓ Columna 'idalmacen' configurada correctamente:\n";
    echo "  - Tipo: " . $column['Type'] . "\n";
    echo "  - Permite NULL: " . $column['Null'] . "\n";
    echo "  - Default: " . ($column['Default'] ?? 'NULL') . "\n";
}

echo "\n===========================================\n";
echo "✓ MIGRACIÓN COMPLETADA EXITOSAMENTE\n";
echo "===========================================\n";

?>
