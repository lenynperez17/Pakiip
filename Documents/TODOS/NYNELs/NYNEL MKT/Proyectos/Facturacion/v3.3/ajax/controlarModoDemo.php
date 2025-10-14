<?php
// AJAX para controlar Modo Demo globalmente
// SEGURIDAD: Solo administradores pueden cambiar esto
require_once "../config/Conexion.php";

if (!isset($_SESSION["nombre"])) {
    echo json_encode(["success" => false, "mensaje" => "No autenticado"]);
    exit;
}

// Solo permitir a usuarios con permisos de configuración
if ($_SESSION['Configuracion'] != 1) {
    echo json_encode(["success" => false, "mensaje" => "Sin permisos"]);
    exit;
}

$accion = isset($_GET["accion"]) ? $_GET["accion"] : "";

// Archivos JS que contienen la variable modoDemo
$archivosJS = [
    "../vistas/scripts/almacen.js",
    "../vistas/scripts/cargarcertificado.js",
    "../vistas/scripts/configNum.js",
    "../vistas/scripts/empresa.js",
    "../vistas/scripts/usuario.js"
];

switch ($accion) {
    case 'activar':
        // Activar modo demo (modoDemo = true)
        $cambios = 0;
        foreach ($archivosJS as $archivo) {
            if (file_exists($archivo)) {
                $contenido = file_get_contents($archivo);
                $contenidoModificado = preg_replace(
                    '/var modoDemo = false;/',
                    'var modoDemo = true;',
                    $contenido,
                    -1,
                    $count
                );
                if ($count > 0) {
                    file_put_contents($archivo, $contenidoModificado);
                    $cambios++;
                }
            }
        }
        echo json_encode([
            "success" => true,
            "mensaje" => "Modo demo ACTIVADO. Se modificaron $cambios archivos. Ahora solo se permite lectura.",
            "estado" => "activo"
        ]);
        break;

    case 'desactivar':
        // Desactivar modo demo (modoDemo = false)
        $cambios = 0;
        foreach ($archivosJS as $archivo) {
            if (file_exists($archivo)) {
                $contenido = file_get_contents($archivo);
                $contenidoModificado = preg_replace(
                    '/var modoDemo = true;/',
                    'var modoDemo = false;',
                    $contenido,
                    -1,
                    $count
                );
                if ($count > 0) {
                    file_put_contents($archivo, $contenidoModificado);
                    $cambios++;
                }
            }
        }
        echo json_encode([
            "success" => true,
            "mensaje" => "Modo demo DESACTIVADO. Se modificaron $cambios archivos. Ahora se permite guardar y editar.",
            "estado" => "inactivo"
        ]);
        break;

    case 'estado':
        // Verificar el estado actual leyendo el primer archivo
        $archivo = $archivosJS[0];
        if (file_exists($archivo)) {
            $contenido = file_get_contents($archivo);
            if (preg_match('/var modoDemo = true;/', $contenido)) {
                echo json_encode([
                    "success" => true,
                    "estado" => "activo",
                    "mensaje" => "Modo demo está ACTIVADO"
                ]);
            } else {
                echo json_encode([
                    "success" => true,
                    "estado" => "inactivo",
                    "mensaje" => "Modo demo está DESACTIVADO"
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al verificar estado"
            ]);
        }
        break;

    default:
        echo json_encode([
            "success" => false,
            "mensaje" => "Acción no válida"
        ]);
        break;
}
?>
