<?php
/**
 * MODELO: SerieComprobante
 * Gestión flexible de series y numeración de comprobantes
 * Sistema multi-empresa con control granular de permisos
 */

require_once "../config/Conexion.php";

class SerieComprobante
{
    public function __construct()
    {
    }

    /**
     * Insertar nueva serie de comprobante
     */
    public function insertar(
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
        $creado_por
    ) {
        global $conexion;

        $sql = "INSERT INTO serie_comprobante (
            idempresa, tipo_documento_sunat, serie, prefijo, sufijo,
            numero_actual, numero_desde, numero_hasta, longitud_numero,
            establecimiento, codigo_establecimiento, descripcion,
            es_electronica, es_contingencia, fecha_inicio_uso, fecha_fin_uso,
            alerta_porcentaje, requiere_autorizacion, creado_por, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVA')";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando INSERT en serie_comprobante: " . $conexion->error);
            return false;
        }

        $stmt->bind_param(
            "issssiiiiissiissiii",
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
            $creado_por
        );

        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Editar serie de comprobante existente
     */
    public function editar(
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
        $actualizado_por
    ) {
        global $conexion;

        $sql = "UPDATE serie_comprobante SET
            serie = ?, prefijo = ?, sufijo = ?,
            numero_desde = ?, numero_hasta = ?, longitud_numero = ?,
            establecimiento = ?, codigo_establecimiento = ?, descripcion = ?,
            es_electronica = ?, es_contingencia = ?,
            fecha_inicio_uso = ?, fecha_fin_uso = ?,
            alerta_porcentaje = ?, requiere_autorizacion = ?,
            actualizado_por = ?
        WHERE idserie_comprobante = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando UPDATE en serie_comprobante: " . $conexion->error);
            return false;
        }

        $stmt->bind_param(
            "sssiiisssiiissiii",
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
            $actualizado_por,
            $idserie_comprobante
        );

        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Cambiar estado de una serie
     */
    public function cambiarEstado($idserie_comprobante, $nuevo_estado, $motivo_suspension = null, $usuario_id = null)
    {
        global $conexion;

        $sql = "UPDATE serie_comprobante SET
            estado = ?,
            motivo_suspension = ?,
            actualizado_por = ?
        WHERE idserie_comprobante = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando cambiarEstado en serie_comprobante: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("ssii", $nuevo_estado, $motivo_suspension, $usuario_id, $idserie_comprobante);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Activar serie
     */
    public function activar($idserie_comprobante, $usuario_id)
    {
        return $this->cambiarEstado($idserie_comprobante, 'ACTIVA', null, $usuario_id);
    }

    /**
     * Suspender serie
     */
    public function suspender($idserie_comprobante, $motivo, $usuario_id)
    {
        return $this->cambiarEstado($idserie_comprobante, 'SUSPENDIDA', $motivo, $usuario_id);
    }

    /**
     * Desactivar serie
     */
    public function desactivar($idserie_comprobante, $usuario_id)
    {
        return $this->cambiarEstado($idserie_comprobante, 'INACTIVA', null, $usuario_id);
    }

    /**
     * Eliminar serie (solo si no tiene comprobantes emitidos)
     */
    public function eliminar($idserie_comprobante)
    {
        global $conexion;

        // Verificar que no haya comprobantes emitidos
        // Esto debe verificarse en las tablas factura, boleta, etc.
        // Por ahora, solo permitimos eliminar si numero_actual es 0

        $sql_verificar = "SELECT numero_actual FROM serie_comprobante WHERE idserie_comprobante = ?";
        $stmt_verificar = $conexion->prepare($sql_verificar);
        $stmt_verificar->bind_param("i", $idserie_comprobante);
        $stmt_verificar->execute();
        $result = $stmt_verificar->get_result();
        $row = $result->fetch_assoc();
        $stmt_verificar->close();

        if ($row['numero_actual'] > 0) {
            error_log("No se puede eliminar serie con comprobantes emitidos");
            return false;
        }

        // Eliminar serie
        $sql = "DELETE FROM serie_comprobante WHERE idserie_comprobante = ?";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando DELETE en serie_comprobante: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("i", $idserie_comprobante);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Mostrar serie específica
     */
    public function mostrar($idserie_comprobante)
    {
        global $conexion;

        $sql = "SELECT * FROM serie_comprobante WHERE idserie_comprobante = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando SELECT en serie_comprobante: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("i", $idserie_comprobante);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    /**
     * Listar todas las series con estadísticas
     */
    public function listarConEstadisticas($idempresa = null)
    {
        global $conexion;

        if ($idempresa) {
            $sql = "SELECT * FROM v_series_estadisticas WHERE idempresa = ? ORDER BY tipo_documento_sunat, serie";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $idempresa);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $stmt->close();
        } else {
            $sql = "SELECT * FROM v_series_estadisticas ORDER BY idempresa, tipo_documento_sunat, serie";
            $resultado = $conexion->query($sql);
        }

        return $resultado;
    }

    /**
     * Listar series activas de un tipo de documento específico para una empresa
     */
    public function listarPorTipoDocumento($idempresa, $tipo_documento_sunat)
    {
        global $conexion;

        $sql = "SELECT * FROM serie_comprobante
                WHERE idempresa = ? AND tipo_documento_sunat = ? AND estado = 'ACTIVA'
                ORDER BY serie";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando listarPorTipoDocumento: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("is", $idempresa, $tipo_documento_sunat);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    /**
     * Obtener siguiente número de comprobante llamando al stored procedure
     */
    public function obtenerSiguienteNumero($idserie_comprobante)
    {
        global $conexion;

        $numero_completo = '';
        $numero = 0;
        $resultado = '';

        $sql = "CALL sp_obtener_siguiente_numero(?, @numero_completo, @numero, @resultado)";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando sp_obtener_siguiente_numero: " . $conexion->error);
            return ['success' => false, 'error' => 'Error preparando procedimiento'];
        }

        $stmt->bind_param("i", $idserie_comprobante);
        $stmt->execute();
        $stmt->close();

        // Obtener valores de salida
        $result = $conexion->query("SELECT @numero_completo AS numero_completo, @numero AS numero, @resultado AS resultado");
        $row = $result->fetch_assoc();

        if ($row['resultado'] === 'OK') {
            return [
                'success' => true,
                'numero_completo' => $row['numero_completo'],
                'numero' => $row['numero'],
                'mensaje' => 'Número generado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'error' => $row['resultado']
            ];
        }
    }

    /**
     * Resetear numeración de una serie
     */
    public function resetearNumeracion($idserie_comprobante, $nuevo_numero, $motivo, $usuario_id)
    {
        global $conexion;

        $sql = "CALL sp_resetear_numeracion_serie(?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando sp_resetear_numeracion_serie: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("iisi", $idserie_comprobante, $nuevo_numero, $motivo, $usuario_id);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Asignar serie a usuario
     */
    public function asignarSerieAUsuario(
        $idusuario,
        $idserie_comprobante,
        $es_predeterminada = 0,
        $puede_modificar = 0,
        $puede_emitir = 1,
        $asignado_por = null
    ) {
        global $conexion;

        // Si es predeterminada, desmarcar otras series predeterminadas del mismo tipo
        if ($es_predeterminada == 1) {
            $sql_desmarca = "UPDATE usuario_serie us
                             INNER JOIN serie_comprobante sc ON us.idserie_comprobante = sc.idserie_comprobante
                             INNER JOIN serie_comprobante sc2 ON sc2.idserie_comprobante = ?
                             SET us.es_predeterminada = 0
                             WHERE us.idusuario = ? AND sc.tipo_documento_sunat = sc2.tipo_documento_sunat";

            $stmt_desmarca = $conexion->prepare($sql_desmarca);
            $stmt_desmarca->bind_param("ii", $idserie_comprobante, $idusuario);
            $stmt_desmarca->execute();
            $stmt_desmarca->close();
        }

        $sql = "INSERT INTO usuario_serie (
            idusuario, idserie_comprobante, es_predeterminada,
            puede_modificar, puede_emitir, asignado_por
        ) VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            es_predeterminada = VALUES(es_predeterminada),
            puede_modificar = VALUES(puede_modificar),
            puede_emitir = VALUES(puede_emitir),
            asignado_por = VALUES(asignado_por)";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando asignarSerieAUsuario: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("iiiiii",
            $idusuario,
            $idserie_comprobante,
            $es_predeterminada,
            $puede_modificar,
            $puede_emitir,
            $asignado_por
        );

        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Quitar asignación de serie a usuario
     */
    public function quitarSerieAUsuario($idusuario, $idserie_comprobante)
    {
        global $conexion;

        $sql = "DELETE FROM usuario_serie
                WHERE idusuario = ? AND idserie_comprobante = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando quitarSerieAUsuario: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("ii", $idusuario, $idserie_comprobante);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Listar series asignadas a un usuario
     */
    public function listarSeriesDeUsuario($idusuario)
    {
        global $conexion;

        $sql = "SELECT * FROM v_usuario_series WHERE idusuario = ? ORDER BY tipo_documento_sunat, serie";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando listarSeriesDeUsuario: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("i", $idusuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    /**
     * Obtener serie predeterminada de un usuario para un tipo de documento
     */
    public function obtenerSeriePredeterminada($idusuario, $tipo_documento_sunat)
    {
        global $conexion;

        $sql = "SELECT sc.*
                FROM serie_comprobante sc
                INNER JOIN usuario_serie us ON sc.idserie_comprobante = us.idserie_comprobante
                WHERE us.idusuario = ?
                AND sc.tipo_documento_sunat = ?
                AND us.es_predeterminada = 1
                AND us.puede_emitir = 1
                AND sc.estado = 'ACTIVA'
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando obtenerSeriePredeterminada: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("is", $idusuario, $tipo_documento_sunat);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    /**
     * Listar series disponibles para un usuario y tipo de documento
     */
    public function listarSeriesDisponiblesUsuario($idusuario, $tipo_documento_sunat)
    {
        global $conexion;

        $sql = "SELECT sc.*, us.es_predeterminada
                FROM serie_comprobante sc
                INNER JOIN usuario_serie us ON sc.idserie_comprobante = us.idserie_comprobante
                WHERE us.idusuario = ?
                AND sc.tipo_documento_sunat = ?
                AND us.puede_emitir = 1
                AND us.estado = 1
                AND sc.estado = 'ACTIVA'
                ORDER BY us.es_predeterminada DESC, sc.serie";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando listarSeriesDisponiblesUsuario: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("is", $idusuario, $tipo_documento_sunat);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    /**
     * Obtener historial de cambios de una serie
     */
    public function obtenerHistorial($idserie_comprobante, $limite = 50)
    {
        global $conexion;

        $sql = "SELECT h.*, u.nombre AS usuario_nombre
                FROM serie_historial h
                LEFT JOIN usuario u ON h.usuario_id = u.idusuario
                WHERE h.idserie_comprobante = ?
                ORDER BY h.fecha_accion DESC
                LIMIT ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando obtenerHistorial: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("ii", $idserie_comprobante, $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    /**
     * Listar alertas pendientes
     */
    public function listarAlertasPendientes($idempresa = null)
    {
        global $conexion;

        if ($idempresa) {
            $sql = "SELECT a.*, sc.serie, sc.tipo_documento_sunat
                    FROM serie_alerta a
                    INNER JOIN serie_comprobante sc ON a.idserie_comprobante = sc.idserie_comprobante
                    WHERE sc.idempresa = ? AND a.estado_alerta IN ('PENDIENTE', 'NOTIFICADA')
                    ORDER BY a.fecha_alerta DESC";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $idempresa);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $stmt->close();
        } else {
            $sql = "SELECT a.*, sc.serie, sc.tipo_documento_sunat, e.nombre_comercial
                    FROM serie_alerta a
                    INNER JOIN serie_comprobante sc ON a.idserie_comprobante = sc.idserie_comprobante
                    INNER JOIN empresa e ON sc.idempresa = e.idempresa
                    WHERE a.estado_alerta IN ('PENDIENTE', 'NOTIFICADA')
                    ORDER BY a.fecha_alerta DESC";

            $resultado = $conexion->query($sql);
        }

        return $resultado;
    }

    /**
     * Atender alerta
     */
    public function atenderAlerta($idserie_alerta, $atendida_por, $comentarios = null)
    {
        global $conexion;

        $sql = "UPDATE serie_alerta SET
            estado_alerta = 'ATENDIDA',
            atendida_por = ?,
            fecha_atencion = NOW(),
            comentarios = ?
        WHERE idserie_alerta = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando atenderAlerta: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("isi", $atendida_por, $comentarios, $idserie_alerta);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Verificar disponibilidad de números en una serie
     */
    public function verificarDisponibilidad($idserie_comprobante)
    {
        global $conexion;

        $sql = "SELECT
            numero_actual,
            numero_desde,
            numero_hasta,
            IFNULL(numero_hasta - numero_actual, -1) AS numeros_disponibles,
            CASE
                WHEN numero_hasta IS NULL THEN NULL
                ELSE ROUND(((numero_actual - numero_desde) / (numero_hasta - numero_desde)) * 100, 2)
            END AS porcentaje_usado,
            estado,
            alerta_activa
        FROM serie_comprobante
        WHERE idserie_comprobante = ?";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando verificarDisponibilidad: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("i", $idserie_comprobante);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $stmt->close();

        return $resultado;
    }

    /**
     * Generar combo select HTML de series para un usuario y tipo de documento
     */
    public function generarSelectSeries($idusuario, $tipo_documento_sunat, $nombre_campo = 'idserie_comprobante', $id_campo = null)
    {
        $id_campo = $id_campo ?? $nombre_campo;
        $resultado = $this->listarSeriesDisponiblesUsuario($idusuario, $tipo_documento_sunat);

        $html = "<select class='form-control' name='$nombre_campo' id='$id_campo' required>";
        $html .= "<option value=''>Seleccione serie...</option>";

        while ($row = $resultado->fetch_assoc()) {
            $selected = $row['es_predeterminada'] == 1 ? 'selected' : '';
            $html .= "<option value='{$row['idserie_comprobante']}' $selected>{$row['serie']}</option>";
        }

        $html .= "</select>";

        return $html;
    }

    /**
     * Validar si un usuario tiene permiso para usar una serie
     */
    public function validarPermisoUsuario($idusuario, $idserie_comprobante, $tipo_permiso = 'puede_emitir')
    {
        global $conexion;

        $sql = "SELECT $tipo_permiso
                FROM usuario_serie
                WHERE idusuario = ? AND idserie_comprobante = ? AND estado = 1";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando validarPermisoUsuario: " . $conexion->error);
            return false;
        }

        $stmt->bind_param("ii", $idusuario, $idserie_comprobante);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row && $row[$tipo_permiso] == 1;
    }
}
?>
