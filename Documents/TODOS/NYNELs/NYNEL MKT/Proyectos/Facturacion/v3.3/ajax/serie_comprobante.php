<?php
/**
 * CONTROLADOR AJAX: Serie Comprobante
 * Gestión de series y numeración de comprobantes
 */

require_once "../modelos/SerieComprobante.php";
require_once "../config/security_headers.php";

// Aplicar headers de seguridad
aplicarSecurityHeaders();

$serieComprobante = new SerieComprobante();

// Obtener empresa y usuario de sesión
$idempresa = isset($_SESSION["idempresa"]) ? $_SESSION["idempresa"] : 0;
$idusuario = isset($_SESSION["idusuario"]) ? $_SESSION["idusuario"] : 0;

// Obtener operación
$op = isset($_REQUEST['op']) ? limpiarCadena($_REQUEST['op']) : '';

switch ($op) {

    // ============================================
    // OPERACIONES CRUD DE SERIES
    // ============================================

    case 'guardaryeditar':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            exit();
        }

        $idserie_comprobante = isset($_POST['idserie_comprobante']) ? limpiarCadena($_POST['idserie_comprobante']) : '';
        $tipo_documento_sunat = isset($_POST['tipo_documento_sunat']) ? limpiarCadena($_POST['tipo_documento_sunat']) : '';
        $serie = isset($_POST['serie']) ? strtoupper(limpiarCadena($_POST['serie'])) : '';
        $prefijo = isset($_POST['prefijo']) ? limpiarCadena($_POST['prefijo']) : null;
        $sufijo = isset($_POST['sufijo']) ? limpiarCadena($_POST['sufijo']) : null;
        $numero_actual = isset($_POST['numero_actual']) ? limpiarCadena($_POST['numero_actual']) : 0;
        $numero_desde = isset($_POST['numero_desde']) ? limpiarCadena($_POST['numero_desde']) : 1;
        $numero_hasta = isset($_POST['numero_hasta']) ? limpiarCadena($_POST['numero_hasta']) : null;
        $longitud_numero = isset($_POST['longitud_numero']) ? limpiarCadena($_POST['longitud_numero']) : 8;
        $establecimiento = isset($_POST['establecimiento']) ? limpiarCadena($_POST['establecimiento']) : null;
        $codigo_establecimiento = isset($_POST['codigo_establecimiento']) ? limpiarCadena($_POST['codigo_establecimiento']) : '0000';
        $descripcion = isset($_POST['descripcion']) ? limpiarCadena($_POST['descripcion']) : null;
        $es_electronica = isset($_POST['es_electronica']) ? limpiarCadena($_POST['es_electronica']) : 1;
        $es_contingencia = isset($_POST['es_contingencia']) ? limpiarCadena($_POST['es_contingencia']) : 0;
        $fecha_inicio_uso = isset($_POST['fecha_inicio_uso']) ? limpiarCadena($_POST['fecha_inicio_uso']) : null;
        $fecha_fin_uso = isset($_POST['fecha_fin_uso']) ? limpiarCadena($_POST['fecha_fin_uso']) : null;
        $alerta_porcentaje = isset($_POST['alerta_porcentaje']) ? limpiarCadena($_POST['alerta_porcentaje']) : 90;
        $requiere_autorizacion = isset($_POST['requiere_autorizacion']) ? limpiarCadena($_POST['requiere_autorizacion']) : 0;

        if (empty($idserie_comprobante)) {
            // Insertar nueva serie
            $rspta = $serieComprobante->insertar(
                $idempresa,
                $tipo_documento_sunat,
                $serie,
                $prefijo,
                $sufijo,
                $numero_actual,
                $numero_desde,
                $numero_hasta,
                $longitud_numero,
                $establecimiento,
                $codigo_establecimiento,
                $descripcion,
                $es_electronica,
                $es_contingencia,
                $fecha_inicio_uso,
                $fecha_fin_uso,
                $alerta_porcentaje,
                $requiere_autorizacion,
                $idusuario
            );

            if ($rspta) {
                registrarOperacionCreate('serie_comprobante', "Serie {$serie} tipo {$tipo_documento_sunat}", [
                    'tipo_documento' => $tipo_documento_sunat,
                    'serie' => $serie,
                    'numero_desde' => $numero_desde,
                    'numero_hasta' => $numero_hasta
                ]);

                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Serie creada correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo crear la serie. Verifique que no exista una serie duplicada.'
                ]);
            }
        } else {
            // Editar serie existente
            $rspta = $serieComprobante->editar(
                $idserie_comprobante,
                $serie,
                $prefijo,
                $sufijo,
                $numero_desde,
                $numero_hasta,
                $longitud_numero,
                $establecimiento,
                $codigo_establecimiento,
                $descripcion,
                $es_electronica,
                $es_contingencia,
                $fecha_inicio_uso,
                $fecha_fin_uso,
                $alerta_porcentaje,
                $requiere_autorizacion,
                $idusuario
            );

            if ($rspta) {
                registrarOperacionUpdate('serie_comprobante', $idserie_comprobante, "Serie {$serie}", [
                    'serie' => $serie,
                    'numero_hasta' => $numero_hasta
                ]);

                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Serie actualizada correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo actualizar la serie'
                ]);
            }
        }
        break;

    case 'mostrar':
        $idserie_comprobante = isset($_GET['idserie_comprobante']) ? limpiarCadena($_GET['idserie_comprobante']) : 0;

        $rspta = $serieComprobante->mostrar($idserie_comprobante);
        if ($rspta) {
            $data = $rspta->fetch_assoc();
            echo json_encode($data);
        } else {
            echo json_encode(['error' => 'No se encontró la serie']);
        }
        break;

    case 'listar':
        $rspta = $serieComprobante->listarConEstadisticas($idempresa);
        $data = array();

        while ($reg = $rspta->fetch_assoc()) {
            $data[] = $reg;
        }

        echo json_encode($data);
        break;

    case 'activar':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idserie_comprobante = isset($_POST['idserie_comprobante']) ? limpiarCadena($_POST['idserie_comprobante']) : 0;

        $rspta = $serieComprobante->activar($idserie_comprobante, $idusuario);
        if ($rspta) {
            registrarOperacionUpdate('serie_comprobante', $idserie_comprobante, 'Activar serie', ['estado' => 'ACTIVA']);
            echo json_encode(['success' => true, 'mensaje' => 'Serie activada']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo activar la serie']);
        }
        break;

    case 'desactivar':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idserie_comprobante = isset($_POST['idserie_comprobante']) ? limpiarCadena($_POST['idserie_comprobante']) : 0;

        $rspta = $serieComprobante->desactivar($idserie_comprobante, $idusuario);
        if ($rspta) {
            registrarOperacionUpdate('serie_comprobante', $idserie_comprobante, 'Desactivar serie', ['estado' => 'INACTIVA']);
            echo json_encode(['success' => true, 'mensaje' => 'Serie desactivada']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo desactivar la serie']);
        }
        break;

    case 'suspender':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idserie_comprobante = isset($_POST['idserie_comprobante']) ? limpiarCadena($_POST['idserie_comprobante']) : 0;
        $motivo = isset($_POST['motivo']) ? limpiarCadena($_POST['motivo']) : '';

        if (empty($motivo)) {
            echo json_encode(['success' => false, 'error' => 'Debe especificar el motivo de suspensión']);
            exit();
        }

        $rspta = $serieComprobante->suspender($idserie_comprobante, $motivo, $idusuario);
        if ($rspta) {
            registrarOperacionUpdate('serie_comprobante', $idserie_comprobante, 'Suspender serie', [
                'estado' => 'SUSPENDIDA',
                'motivo' => $motivo
            ]);
            echo json_encode(['success' => true, 'mensaje' => 'Serie suspendida']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo suspender la serie']);
        }
        break;

    case 'eliminar':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idserie_comprobante = isset($_POST['idserie_comprobante']) ? limpiarCadena($_POST['idserie_comprobante']) : 0;

        $rspta = $serieComprobante->eliminar($idserie_comprobante);
        if ($rspta) {
            registrarOperacionDelete('serie_comprobante', $idserie_comprobante, 'Eliminar serie');
            echo json_encode(['success' => true, 'mensaje' => 'Serie eliminada']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo eliminar la serie. Verifique que no tenga comprobantes emitidos.']);
        }
        break;

    // ============================================
    // OPERACIONES DE NUMERACIÓN
    // ============================================

    case 'obtenerSiguienteNumero':
        $idserie_comprobante = isset($_POST['idserie_comprobante']) ? limpiarCadena($_POST['idserie_comprobante']) : 0;

        $resultado = $serieComprobante->obtenerSiguienteNumero($idserie_comprobante);
        echo json_encode($resultado);
        break;

    case 'resetearNumeracion':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idserie_comprobante = isset($_POST['idserie_comprobante']) ? limpiarCadena($_POST['idserie_comprobante']) : 0;
        $nuevo_numero = isset($_POST['nuevo_numero']) ? limpiarCadena($_POST['nuevo_numero']) : 0;
        $motivo = isset($_POST['motivo']) ? limpiarCadena($_POST['motivo']) : '';

        if (empty($motivo)) {
            echo json_encode(['success' => false, 'error' => 'Debe especificar el motivo del reseteo']);
            exit();
        }

        $rspta = $serieComprobante->resetearNumeracion($idserie_comprobante, $nuevo_numero, $motivo, $idusuario);
        if ($rspta) {
            registrarOperacionUpdate('serie_comprobante', $idserie_comprobante, 'Resetear numeración', [
                'nuevo_numero' => $nuevo_numero,
                'motivo' => $motivo
            ]);
            echo json_encode(['success' => true, 'mensaje' => 'Numeración reseteada correctamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo resetear la numeración']);
        }
        break;

    case 'verificarDisponibilidad':
        $idserie_comprobante = isset($_GET['idserie_comprobante']) ? limpiarCadena($_GET['idserie_comprobante']) : 0;

        $rspta = $serieComprobante->verificarDisponibilidad($idserie_comprobante);
        if ($rspta) {
            $data = $rspta->fetch_assoc();
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo verificar disponibilidad']);
        }
        break;

    // ============================================
    // OPERACIONES DE ASIGNACIÓN A USUARIOS
    // ============================================

    case 'asignarSerieAUsuario':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idusuario_asignar = isset($_POST['idusuario']) ? limpiarCadena($_POST['idusuario']) : 0;
        $idserie_comprobante = isset($_POST['idserie_comprobante']) ? limpiarCadena($_POST['idserie_comprobante']) : 0;
        $es_predeterminada = isset($_POST['es_predeterminada']) ? limpiarCadena($_POST['es_predeterminada']) : 0;
        $puede_modificar = isset($_POST['puede_modificar']) ? limpiarCadena($_POST['puede_modificar']) : 0;
        $puede_emitir = isset($_POST['puede_emitir']) ? limpiarCadena($_POST['puede_emitir']) : 1;

        $rspta = $serieComprobante->asignarSerieAUsuario(
            $idusuario_asignar,
            $idserie_comprobante,
            $es_predeterminada,
            $puede_modificar,
            $puede_emitir,
            $idusuario
        );

        if ($rspta) {
            registrarOperacionCreate('usuario_serie', "Asignar serie a usuario {$idusuario_asignar}", [
                'idusuario' => $idusuario_asignar,
                'idserie_comprobante' => $idserie_comprobante,
                'es_predeterminada' => $es_predeterminada
            ]);
            echo json_encode(['success' => true, 'mensaje' => 'Serie asignada al usuario']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo asignar la serie']);
        }
        break;

    case 'quitarSerieAUsuario':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idusuario_quitar = isset($_POST['idusuario']) ? limpiarCadena($_POST['idusuario']) : 0;
        $idserie_comprobante = isset($_POST['idserie_comprobante']) ? limpiarCadena($_POST['idserie_comprobante']) : 0;

        $rspta = $serieComprobante->quitarSerieAUsuario($idusuario_quitar, $idserie_comprobante);
        if ($rspta) {
            registrarOperacionDelete('usuario_serie', 0, "Quitar serie a usuario {$idusuario_quitar}");
            echo json_encode(['success' => true, 'mensaje' => 'Asignación eliminada']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo quitar la asignación']);
        }
        break;

    case 'listarSeriesUsuario':
        $idusuario_listar = isset($_GET['idusuario']) ? limpiarCadena($_GET['idusuario']) : $idusuario;

        $rspta = $serieComprobante->listarSeriesDeUsuario($idusuario_listar);
        $data = array();

        while ($reg = $rspta->fetch_assoc()) {
            $data[] = $reg;
        }

        echo json_encode($data);
        break;

    case 'listarSeriesDisponibles':
        $tipo_documento_sunat = isset($_GET['tipo_documento_sunat']) ? limpiarCadena($_GET['tipo_documento_sunat']) : '';

        if (empty($tipo_documento_sunat)) {
            echo json_encode(['error' => 'Tipo de documento requerido']);
            exit();
        }

        $rspta = $serieComprobante->listarSeriesDisponiblesUsuario($idusuario, $tipo_documento_sunat);
        $data = array();

        while ($reg = $rspta->fetch_assoc()) {
            $data[] = $reg;
        }

        echo json_encode($data);
        break;

    case 'obtenerSeriePredeterminada':
        $tipo_documento_sunat = isset($_GET['tipo_documento_sunat']) ? limpiarCadena($_GET['tipo_documento_sunat']) : '';

        if (empty($tipo_documento_sunat)) {
            echo json_encode(['error' => 'Tipo de documento requerido']);
            exit();
        }

        $rspta = $serieComprobante->obtenerSeriePredeterminada($idusuario, $tipo_documento_sunat);
        if ($rspta && $rspta->num_rows > 0) {
            $data = $rspta->fetch_assoc();
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No hay serie predeterminada configurada']);
        }
        break;

    case 'selectSeriesUsuario':
        $tipo_documento_sunat = isset($_GET['tipo_documento_sunat']) ? limpiarCadena($_GET['tipo_documento_sunat']) : '';
        $nombre_campo = isset($_GET['nombre_campo']) ? limpiarCadena($_GET['nombre_campo']) : 'idserie_comprobante';

        echo $serieComprobante->generarSelectSeries($idusuario, $tipo_documento_sunat, $nombre_campo);
        break;

    // ============================================
    // HISTORIAL Y ALERTAS
    // ============================================

    case 'obtenerHistorial':
        $idserie_comprobante = isset($_GET['idserie_comprobante']) ? limpiarCadena($_GET['idserie_comprobante']) : 0;
        $limite = isset($_GET['limite']) ? limpiarCadena($_GET['limite']) : 50;

        $rspta = $serieComprobante->obtenerHistorial($idserie_comprobante, $limite);
        $data = array();

        while ($reg = $rspta->fetch_assoc()) {
            $data[] = $reg;
        }

        echo json_encode($data);
        break;

    case 'listarAlertas':
        $rspta = $serieComprobante->listarAlertasPendientes($idempresa);
        $data = array();

        while ($reg = $rspta->fetch_assoc()) {
            $data[] = $reg;
        }

        echo json_encode($data);
        break;

    case 'atenderAlerta':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idserie_alerta = isset($_POST['idserie_alerta']) ? limpiarCadena($_POST['idserie_alerta']) : 0;
        $comentarios = isset($_POST['comentarios']) ? limpiarCadena($_POST['comentarios']) : null;

        $rspta = $serieComprobante->atenderAlerta($idserie_alerta, $idusuario, $comentarios);
        if ($rspta) {
            echo json_encode(['success' => true, 'mensaje' => 'Alerta atendida']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo atender la alerta']);
        }
        break;

    // ============================================
    // LISTADOS PARA SELECTS
    // ============================================

    case 'listarTiposDocumento':
        global $conexion;

        $sql = "SELECT codigo, descripcion FROM catalogo1
                WHERE codigo IN ('01', '03', '07', '08', '09', '12', '20', '30', '50', '99')
                ORDER BY codigo";

        $resultado = $conexion->query($sql);
        $data = array();

        while ($reg = $resultado->fetch_assoc()) {
            $data[] = $reg;
        }

        echo json_encode($data);
        break;

    case 'selectUsuarios':
        global $conexion;

        $sql = "SELECT idusuario, nombre, login FROM usuario WHERE condicion = 1 ORDER BY nombre";
        $resultado = $conexion->query($sql);

        echo '<option value="">Seleccione usuario...</option>';
        while ($reg = $resultado->fetch_assoc()) {
            echo '<option value="' . $reg['idusuario'] . '">' . $reg['nombre'] . ' (' . $reg['login'] . ')</option>';
        }
        break;

    default:
        echo json_encode(['error' => 'Operación no válida']);
        break;
}
?>
