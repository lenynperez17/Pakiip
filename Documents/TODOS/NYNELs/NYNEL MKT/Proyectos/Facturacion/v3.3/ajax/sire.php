<?php
/**
 * Controlador AJAX para módulo SIRE (Sistema Integrado de Registros Electrónicos)
 * Maneja las operaciones de generación y exportación de archivos RVIE y RCE para SUNAT
 *
 * @package    Facturacion
 * @subpackage AJAX Controllers
 * @author     Sistema de Facturación
 * @version    1.0.0
 */

// SEGURIDAD: Control de buffer de salida
ob_start();

// SEGURIDAD: Validar que existe sesión activa
if (strlen(session_id()) < 1) {
    session_start();
}

// SEGURIDAD: Validar autenticación antes de procesar cualquier solicitud
if (!isset($_SESSION['idusuario']) || empty($_SESSION['idusuario'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Sesión inválida o expirada. Por favor inicie sesión nuevamente.'
    ]);
    exit();
}

// Incluir archivos necesarios
require_once "../modelos/SIRE.php";
require_once "../config/Conexion.php";

// Instanciar modelo SIRE
$sire = new SIRE();

// Obtener parámetros comunes
$op = isset($_GET['op']) ? limpiarcadena($_GET['op']) : (isset($_POST['op']) ? limpiarcadena($_POST['op']) : '');

// =============================================================================
// SWITCH PRINCIPAL - Enrutamiento de operaciones
// =============================================================================

switch ($op) {

    // =========================================================================
    // OPERACIÓN: Guardar o actualizar configuración SIRE
    // =========================================================================
    case 'guardarConfiguracion':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Token de seguridad inválido'
            ]);
            exit();
        }

        // SEGURIDAD: Validar empresa en sesión
        if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo identificar la empresa'
            ]);
            exit();
        }

        try {
            // Recibir y sanitizar parámetros
            $idempresa = $_SESSION['idempresa'];
            $ruc = isset($_POST['ruc']) ? limpiarcadena($_POST['ruc']) : '';
            $periodo_obligado_desde = isset($_POST['periodo_obligado_desde']) ? limpiarcadena($_POST['periodo_obligado_desde']) : '';
            $generar_propuesta_aceptar = isset($_POST['generar_propuesta_aceptar']) ? (int)$_POST['generar_propuesta_aceptar'] : 1;
            $moneda_principal = isset($_POST['moneda_principal']) ? limpiarcadena($_POST['moneda_principal']) : '1';
            $incluir_anulados = isset($_POST['incluir_anulados']) ? (int)$_POST['incluir_anulados'] : 0;
            $indicador_reemplaza = isset($_POST['indicador_reemplaza']) ? limpiarcadena($_POST['indicador_reemplaza']) : '1';
            $indicador_estado = isset($_POST['indicador_estado']) ? limpiarcadena($_POST['indicador_estado']) : '1';
            $indicador_moneda = isset($_POST['indicador_moneda']) ? limpiarcadena($_POST['indicador_moneda']) : '1';
            $indicador_libro_simplif = isset($_POST['indicador_libro_simplif']) ? limpiarcadena($_POST['indicador_libro_simplif']) : '2';
            $indicador_entidad = isset($_POST['indicador_entidad']) ? limpiarcadena($_POST['indicador_entidad']) : '1';
            $indicador_genera_sin_mov = isset($_POST['indicador_genera_sin_mov']) ? limpiarcadena($_POST['indicador_genera_sin_mov']) : '0';
            $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

            // Validaciones
            if (empty($ruc) || strlen($ruc) != 11) {
                echo json_encode([
                    'success' => false,
                    'error' => 'El RUC debe tener 11 dígitos'
                ]);
                exit();
            }

            if (empty($periodo_obligado_desde)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Debe especificar el período desde el cual está obligado'
                ]);
                exit();
            }

            // Guardar configuración
            $rspta = $sire->guardarConfiguracion(
                $idempresa,
                $ruc,
                $periodo_obligado_desde,
                $generar_propuesta_aceptar,
                $moneda_principal,
                $incluir_anulados,
                $indicador_reemplaza,
                $indicador_estado,
                $indicador_moneda,
                $indicador_libro_simplif,
                $indicador_entidad,
                $indicador_genera_sin_mov,
                $estado
            );

            if ($rspta) {
                // Registrar en audit log
                error_log("SIRE: Configuración guardada por usuario {$_SESSION['idusuario']} para empresa {$idempresa}");

                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Configuración SIRE guardada correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo guardar la configuración'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en guardarConfiguracion SIRE: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ]);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Obtener configuración actual
    // =========================================================================
    case 'obtenerConfiguracion':
        // SEGURIDAD: Validar empresa
        if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo identificar la empresa'
            ]);
            exit();
        }

        try {
            $idempresa = $_SESSION['idempresa'];
            $config = $sire->obtenerConfiguracion($idempresa);

            if ($config) {
                echo json_encode([
                    'success' => true,
                    'data' => $config
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No se encontró configuración SIRE para esta empresa',
                    'requiere_config' => true
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en obtenerConfiguracion SIRE: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener configuración: ' . $e->getMessage()
            ]);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Generar archivo RVIE (Registro de Ventas e Ingresos)
    // =========================================================================
    case 'generarRVIE':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Token de seguridad inválido'
            ]);
            exit();
        }

        // SEGURIDAD: Validar empresa
        if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo identificar la empresa'
            ]);
            exit();
        }

        try {
            // Recibir parámetros
            $idempresa = $_SESSION['idempresa'];
            $periodo_anio = isset($_POST['periodo_anio']) ? limpiarcadena($_POST['periodo_anio']) : '';
            $periodo_mes = isset($_POST['periodo_mes']) ? limpiarcadena($_POST['periodo_mes']) : '';
            $cod_oportunidad = isset($_POST['cod_oportunidad']) ? limpiarcadena($_POST['cod_oportunidad']) : '01';
            $usuario_id = $_SESSION['idusuario'];

            // Validaciones
            if (empty($periodo_anio) || strlen($periodo_anio) != 4) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Año inválido. Debe ser formato AAAA'
                ]);
                exit();
            }

            if (empty($periodo_mes) || strlen($periodo_mes) != 2) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Mes inválido. Debe ser formato MM'
                ]);
                exit();
            }

            // Generar RVIE
            $resultado = $sire->generarRVIE($idempresa, $periodo_anio, $periodo_mes, $cod_oportunidad, $usuario_id);

            if ($resultado['success']) {
                // Registrar en audit log
                error_log("SIRE: RVIE generado por usuario {$usuario_id} - Archivo: {$resultado['archivo']} - Registros: {$resultado['total_registros']}");

                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Archivo RVIE generado correctamente',
                    'archivo' => $resultado['archivo'],
                    'total_registros' => $resultado['total_registros'],
                    'ruta_descarga' => '../files/sire/' . $resultado['archivo']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $resultado['error'] ?? 'Error al generar archivo RVIE'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en generarRVIE: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error al generar RVIE: ' . $e->getMessage()
            ]);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Generar archivo RCE (Registro de Compras y Gastos)
    // =========================================================================
    case 'generarRCE':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Token de seguridad inválido'
            ]);
            exit();
        }

        // SEGURIDAD: Validar empresa
        if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo identificar la empresa'
            ]);
            exit();
        }

        try {
            // Recibir parámetros
            $idempresa = $_SESSION['idempresa'];
            $periodo_anio = isset($_POST['periodo_anio']) ? limpiarcadena($_POST['periodo_anio']) : '';
            $periodo_mes = isset($_POST['periodo_mes']) ? limpiarcadena($_POST['periodo_mes']) : '';
            $cod_oportunidad = isset($_POST['cod_oportunidad']) ? limpiarcadena($_POST['cod_oportunidad']) : '01';
            $usuario_id = $_SESSION['idusuario'];

            // Validaciones
            if (empty($periodo_anio) || strlen($periodo_anio) != 4) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Año inválido. Debe ser formato AAAA'
                ]);
                exit();
            }

            if (empty($periodo_mes) || strlen($periodo_mes) != 2) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Mes inválido. Debe ser formato MM'
                ]);
                exit();
            }

            // Generar RCE
            $resultado = $sire->generarRCE($idempresa, $periodo_anio, $periodo_mes, $cod_oportunidad, $usuario_id);

            if ($resultado['success']) {
                // Registrar en audit log
                error_log("SIRE: RCE generado por usuario {$usuario_id} - Archivo: {$resultado['archivo']} - Registros: {$resultado['total_registros']}");

                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Archivo RCE generado correctamente',
                    'archivo' => $resultado['archivo'],
                    'total_registros' => $resultado['total_registros'],
                    'ruta_descarga' => '../files/sire/' . $resultado['archivo']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $resultado['error'] ?? 'Error al generar archivo RCE'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en generarRCE: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error al generar RCE: ' . $e->getMessage()
            ]);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Listar exportaciones generadas
    // =========================================================================
    case 'listarExportaciones':
        // SEGURIDAD: Validar empresa
        if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo identificar la empresa'
            ]);
            exit();
        }

        try {
            $idempresa = $_SESSION['idempresa'];

            // Parámetros opcionales de filtro
            $tipo_registro = isset($_GET['tipo_registro']) ? limpiarcadena($_GET['tipo_registro']) : null;
            $periodo_anio = isset($_GET['periodo_anio']) ? limpiarcadena($_GET['periodo_anio']) : null;
            $periodo_mes = isset($_GET['periodo_mes']) ? limpiarcadena($_GET['periodo_mes']) : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $exportaciones = $sire->listarExportaciones($idempresa, $tipo_registro, $periodo_anio, $periodo_mes, $limit, $offset);

            echo json_encode([
                'success' => true,
                'data' => $exportaciones,
                'total' => count($exportaciones)
            ]);

        } catch (Exception $e) {
            error_log("Error en listarExportaciones SIRE: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error al listar exportaciones: ' . $e->getMessage()
            ]);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Descargar archivo TXT generado
    // =========================================================================
    case 'descargarArchivo':
        // SEGURIDAD: Validar empresa
        if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo identificar la empresa'
            ]);
            exit();
        }

        try {
            $nombre_archivo = isset($_GET['archivo']) ? limpiarcadena($_GET['archivo']) : '';

            if (empty($nombre_archivo)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Nombre de archivo no especificado'
                ]);
                exit();
            }

            // SEGURIDAD: Validar que el archivo pertenece a la empresa actual
            $idempresa = $_SESSION['idempresa'];
            $sql = "SELECT idsire_exportacion, nombre_archivo, tipo_registro
                    FROM sire_exportacion
                    WHERE nombre_archivo = '$nombre_archivo' AND idempresa = $idempresa";

            $resultado = ejecutarConsultaSimpleFila($sql);

            if (!$resultado) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Archivo no encontrado o no autorizado'
                ]);
                exit();
            }

            // Descargar archivo
            $descarga = $sire->descargarArchivo($nombre_archivo);

            if ($descarga['success']) {
                // Limpiar buffer de salida
                ob_end_clean();

                // Headers para descarga
                header('Content-Type: text/plain; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
                header('Content-Length: ' . $descarga['size']);
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');

                // Enviar contenido del archivo
                echo $descarga['contenido'];

                // Registrar descarga
                error_log("SIRE: Archivo descargado - {$nombre_archivo} por usuario {$_SESSION['idusuario']}");

                exit();
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => $descarga['error'] ?? 'Error al descargar archivo'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en descargarArchivo SIRE: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error al descargar archivo: ' . $e->getMessage()
            ]);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Obtener códigos paramétricos de SUNAT
    // =========================================================================
    case 'obtenerCodigosSunat':
        try {
            $tabla = isset($_GET['tabla']) ? limpiarcadena($_GET['tabla']) : '';

            if (empty($tabla)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Debe especificar la tabla de códigos'
                ]);
                exit();
            }

            $codigos = $sire->obtenerCodigosSunat($tabla);

            echo json_encode([
                'success' => true,
                'data' => $codigos,
                'total' => count($codigos)
            ]);

        } catch (Exception $e) {
            error_log("Error en obtenerCodigosSunat: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener códigos SUNAT: ' . $e->getMessage()
            ]);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Verificar estado de exportación en SUNAT (futuro)
    // =========================================================================
    case 'verificarEstadoSunat':
        // TODO: Implementar cuando SUNAT proporcione API de consulta
        echo json_encode([
            'success' => false,
            'error' => 'Funcionalidad en desarrollo. Consultar estado en SUNAT Operaciones en Línea.'
        ]);
        break;

    // =========================================================================
    // OPERACIÓN: Eliminar exportación (solo si no ha sido enviada a SUNAT)
    // =========================================================================
    case 'eliminarExportacion':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Token de seguridad inválido'
            ]);
            exit();
        }

        // SEGURIDAD: Validar empresa
        if (!isset($_SESSION['idempresa']) || empty($_SESSION['idempresa'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo identificar la empresa'
            ]);
            exit();
        }

        try {
            $idsire_exportacion = isset($_POST['idsire_exportacion']) ? (int)$_POST['idsire_exportacion'] : 0;
            $idempresa = $_SESSION['idempresa'];

            // Validar que la exportación existe y pertenece a la empresa
            $sql = "SELECT idsire_exportacion, nombre_archivo, estado_exportacion
                    FROM sire_exportacion
                    WHERE idsire_exportacion = $idsire_exportacion AND idempresa = $idempresa";

            $exportacion = ejecutarConsultaSimpleFila($sql);

            if (!$exportacion) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Exportación no encontrada o no autorizada'
                ]);
                exit();
            }

            // No permitir eliminar si ya fue enviada a SUNAT
            if (in_array($exportacion['estado_exportacion'], ['ENVIADO', 'ACEPTADO'])) {
                echo json_encode([
                    'success' => false,
                    'error' => 'No se puede eliminar una exportación que ya fue enviada a SUNAT'
                ]);
                exit();
            }

            // Eliminar archivo físico si existe
            $ruta_archivo = '../files/sire/' . $exportacion['nombre_archivo'];
            if (file_exists($ruta_archivo)) {
                unlink($ruta_archivo);
            }

            // Eliminar registro de base de datos
            $sql_delete = "DELETE FROM sire_exportacion WHERE idsire_exportacion = $idsire_exportacion";
            $resultado = ejecutarConsulta($sql_delete);

            if ($resultado) {
                error_log("SIRE: Exportación eliminada - ID: {$idsire_exportacion} por usuario {$_SESSION['idusuario']}");

                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Exportación eliminada correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo eliminar la exportación'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en eliminarExportacion SIRE: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error al eliminar exportación: ' . $e->getMessage()
            ]);
        }
        break;

    // =========================================================================
    // OPERACIÓN NO RECONOCIDA
    // =========================================================================
    default:
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Operación no válida'
        ]);
        break;
}

// Limpiar buffer de salida
ob_end_flush();
?>
