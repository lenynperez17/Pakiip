<?php
// Incluir conexión a base de datos
require "../config/Conexion.php";

/**
 * Clase Caja
 * Gestión profesional de apertura/cierre de caja con control de usuarios
 * Implementa sistema de turnos, movimientos detallados y arqueos
 */
class Caja
{
    private $lastError = '';

    public function __construct()
    {
    }

    /**
     * Obtener último error
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Aperturar caja diaria
     *
     * @param int $idusuario ID del usuario que apertura
     * @param float $monto_inicial Monto inicial de apertura
     * @param int $idempresa ID de la empresa
     * @param string $turno Turno: MAÑANA, TARDE, NOCHE, COMPLETO
     * @return int|false ID de la caja aperturada o false si falla
     */
    public function aperturarCaja($idusuario, $monto_inicial, $idempresa, $turno = 'COMPLETO')
    {
        global $conexion;

        // SEGURIDAD: Verificar que no haya caja abierta para este usuario
        $sql_verificar = "SELECT idcaja FROM caja
                         WHERE idusuario = ? AND estado = 1 AND fecha_cierre IS NULL
                         ORDER BY idcaja DESC LIMIT 1";

        $stmt_ver = $conexion->prepare($sql_verificar);
        if (!$stmt_ver) {
            $this->lastError = "Error preparando verificación: " . $conexion->error;
            return false;
        }

        $stmt_ver->bind_param("i", $idusuario);
        $stmt_ver->execute();
        $result = $stmt_ver->get_result();

        if ($result->num_rows > 0) {
            $this->lastError = "Ya existe una caja abierta para este usuario. Debe cerrarla primero.";
            $stmt_ver->close();
            return false;
        }
        $stmt_ver->close();

        // TRANSACCIÓN: Iniciar
        mysqli_begin_transaction($conexion);

        try {
            $fecha_actual = date('Y-m-d');
            $fecha_hora_apertura = date('Y-m-d H:i:s');

            // PASO 1: Insertar registro en tabla caja
            $sql_caja = "INSERT INTO caja (
                fecha, montoi, montof, estado, idempresa, idusuario,
                fecha_apertura, turno, monto_sistema, diferencia
            ) VALUES (?, ?, 0.00, 1, ?, ?, ?, ?, 0.00, 0.00)";

            $stmt_caja = $conexion->prepare($sql_caja);
            if (!$stmt_caja) {
                $this->lastError = "Error preparando INSERT caja: " . $conexion->error;
                mysqli_rollback($conexion);
                return false;
            }

            $stmt_caja->bind_param("sdiiss", $fecha_actual, $monto_inicial, $idempresa, $idusuario, $fecha_hora_apertura, $turno);

            if (!$stmt_caja->execute()) {
                $this->lastError = "Error ejecutando INSERT caja: " . $stmt_caja->error;
                $stmt_caja->close();
                mysqli_rollback($conexion);
                return false;
            }

            $idcaja_new = $conexion->insert_id;
            $stmt_caja->close();

            // PASO 2: Registrar saldo inicial en saldocaja
            $sql_saldo = "INSERT INTO saldocaja (
                saldo_inicial, caja_abierta, fecha_creacion, idusuario, idempresa, fecha_apertura
            ) VALUES (?, b'1', ?, ?, ?, ?)";

            $stmt_saldo = $conexion->prepare($sql_saldo);
            if (!$stmt_saldo) {
                $this->lastError = "Error preparando INSERT saldocaja: " . $conexion->error;
                mysqli_rollback($conexion);
                return false;
            }

            $stmt_saldo->bind_param("dsiis", $monto_inicial, $fecha_actual, $idusuario, $idempresa, $fecha_hora_apertura);

            if (!$stmt_saldo->execute()) {
                $this->lastError = "Error ejecutando INSERT saldocaja: " . $stmt_saldo->error;
                $stmt_saldo->close();
                mysqli_rollback($conexion);
                return false;
            }
            $stmt_saldo->close();

            // PASO 3: Registrar movimiento inicial (si monto > 0)
            if ($monto_inicial > 0) {
                $sql_mov = "INSERT INTO caja_movimientos (
                    idcaja, tipo_movimiento, concepto, monto, tipo_pago,
                    referencia, idusuario, fecha_movimiento
                ) VALUES (?, 'INGRESO', 'Apertura de caja - Saldo inicial', ?, 'EFECTIVO', ?, ?, ?)";

                $stmt_mov = $conexion->prepare($sql_mov);
                if (!$stmt_mov) {
                    $this->lastError = "Error preparando INSERT movimiento: " . $conexion->error;
                    mysqli_rollback($conexion);
                    return false;
                }

                $referencia = "APERTURA-" . $idcaja_new;
                $stmt_mov->bind_param("idsisstr", $idcaja_new, $monto_inicial, $referencia, $idusuario, $fecha_hora_apertura);

                if (!$stmt_mov->execute()) {
                    $this->lastError = "Error ejecutando INSERT movimiento: " . $stmt_mov->error;
                    $stmt_mov->close();
                    mysqli_rollback($conexion);
                    return false;
                }
                $stmt_mov->close();
            }

            // COMMIT si todo salió bien
            mysqli_commit($conexion);
            return $idcaja_new;

        } catch (Exception $e) {
            $this->lastError = "Excepción en aperturarCaja: " . $e->getMessage();
            mysqli_rollback($conexion);
            return false;
        }
    }

    /**
     * Cerrar caja diaria
     *
     * @param int $idcaja ID de la caja a cerrar
     * @param float $monto_declarado Monto declarado por el cajero
     * @param string $observaciones Observaciones del cierre
     * @return bool true si cierra correctamente, false si falla
     */
    public function cerrarCaja($idcaja, $monto_declarado, $observaciones = '')
    {
        global $conexion;

        // PASO 1: Obtener información de la caja
        $sql_caja = "SELECT idusuario, montoi, fecha_apertura FROM caja WHERE idcaja = ? AND estado = 1";
        $stmt_caja = $conexion->prepare($sql_caja);
        if (!$stmt_caja) {
            $this->lastError = "Error preparando SELECT caja: " . $conexion->error;
            return false;
        }

        $stmt_caja->bind_param("i", $idcaja);
        $stmt_caja->execute();
        $result = $stmt_caja->get_result();

        if ($result->num_rows === 0) {
            $this->lastError = "Caja no encontrada o ya está cerrada";
            $stmt_caja->close();
            return false;
        }

        $caja_data = $result->fetch_assoc();
        $idusuario = $caja_data['idusuario'];
        $monto_inicial = $caja_data['montoi'];
        $stmt_caja->close();

        // PASO 2: Calcular totales del sistema (ingresos - egresos)
        $sql_totales = "
            SELECT
                COALESCE(SUM(CASE WHEN tipo_movimiento IN ('INGRESO', 'VENTA') THEN monto ELSE 0 END), 0) as total_ingresos,
                COALESCE(SUM(CASE WHEN tipo_movimiento IN ('EGRESO', 'COMPRA') THEN monto ELSE 0 END), 0) as total_egresos
            FROM caja_movimientos
            WHERE idcaja = ? AND estado = 1";

        $stmt_totales = $conexion->prepare($sql_totales);
        if (!$stmt_totales) {
            $this->lastError = "Error preparando cálculo totales: " . $conexion->error;
            return false;
        }

        $stmt_totales->bind_param("i", $idcaja);
        $stmt_totales->execute();
        $result_totales = $stmt_totales->get_result();
        $totales = $result_totales->fetch_assoc();

        $total_ingresos = $totales['total_ingresos'];
        $total_egresos = $totales['total_egresos'];
        $monto_sistema = $monto_inicial + $total_ingresos - $total_egresos;
        $diferencia = $monto_declarado - $monto_sistema;

        $stmt_totales->close();

        // TRANSACCIÓN: Iniciar
        mysqli_begin_transaction($conexion);

        try {
            $fecha_hora_cierre = date('Y-m-d H:i:s');

            // PASO 3: Actualizar registro de caja
            $sql_update_caja = "UPDATE caja SET
                montof = ?,
                monto_sistema = ?,
                diferencia = ?,
                observaciones = ?,
                fecha_cierre = ?,
                estado = 0
            WHERE idcaja = ?";

            $stmt_update = $conexion->prepare($sql_update_caja);
            if (!$stmt_update) {
                $this->lastError = "Error preparando UPDATE caja: " . $conexion->error;
                mysqli_rollback($conexion);
                return false;
            }

            $stmt_update->bind_param("dddssi", $monto_declarado, $monto_sistema, $diferencia, $observaciones, $fecha_hora_cierre, $idcaja);

            if (!$stmt_update->execute()) {
                $this->lastError = "Error ejecutando UPDATE caja: " . $stmt_update->error;
                $stmt_update->close();
                mysqli_rollback($conexion);
                return false;
            }
            $stmt_update->close();

            // PASO 4: Insertar registro en cierrecaja
            $sql_cierre = "INSERT INTO cierrecaja (
                idcaja, idusuario, fecha_cierre, monto_inicial, total_ingresos,
                total_egresos, total_sistema, total_declarado, diferencia,
                total_final, observaciones, fecha_hora_cierre
            ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt_cierre = $conexion->prepare($sql_cierre);
            if (!$stmt_cierre) {
                $this->lastError = "Error preparando INSERT cierrecaja: " . $conexion->error;
                mysqli_rollback($conexion);
                return false;
            }

            $stmt_cierre->bind_param("iidddddddss",
                $idcaja, $idusuario, $monto_inicial, $total_ingresos, $total_egresos,
                $monto_sistema, $monto_declarado, $diferencia, $monto_declarado,
                $observaciones, $fecha_hora_cierre
            );

            if (!$stmt_cierre->execute()) {
                $this->lastError = "Error ejecutando INSERT cierrecaja: " . $stmt_cierre->error;
                $stmt_cierre->close();
                mysqli_rollback($conexion);
                return false;
            }
            $stmt_cierre->close();

            // PASO 5: Actualizar saldocaja
            $sql_update_saldo = "UPDATE saldocaja SET
                caja_abierta = b'0',
                fecha_cierre = ?
            WHERE idusuario = ? AND caja_abierta = b'1'
            ORDER BY idsaldoini DESC LIMIT 1";

            $stmt_saldo = $conexion->prepare($sql_update_saldo);
            if (!$stmt_saldo) {
                $this->lastError = "Error preparando UPDATE saldocaja: " . $conexion->error;
                mysqli_rollback($conexion);
                return false;
            }

            $stmt_saldo->bind_param("si", $fecha_hora_cierre, $idusuario);

            if (!$stmt_saldo->execute()) {
                $this->lastError = "Error ejecutando UPDATE saldocaja: " . $stmt_saldo->error;
                $stmt_saldo->close();
                mysqli_rollback($conexion);
                return false;
            }
            $stmt_saldo->close();

            // COMMIT si todo salió bien
            mysqli_commit($conexion);
            return true;

        } catch (Exception $e) {
            $this->lastError = "Excepción en cerrarCaja: " . $e->getMessage();
            mysqli_rollback($conexion);
            return false;
        }
    }

    /**
     * Registrar movimiento de caja (ingreso o egreso)
     *
     * @param int $idcaja ID de la caja
     * @param string $tipo_movimiento INGRESO, EGRESO, VENTA, COMPRA, AJUSTE
     * @param string $concepto Descripción del movimiento
     * @param float $monto Monto del movimiento
     * @param string $tipo_pago EFECTIVO, TARJETA, TRANSFERENCIA, YAPE, PLIN, OTRO
     * @param string $referencia Número de documento/operación
     * @param int $idusuario Usuario que registra
     * @param int $idcomprobante ID del comprobante relacionado (opcional)
     * @return int|false ID del movimiento o false si falla
     */
    public function registrarMovimiento($idcaja, $tipo_movimiento, $concepto, $monto, $tipo_pago = 'EFECTIVO', $referencia = '', $idusuario = null, $idcomprobante = null)
    {
        global $conexion;

        // Validar que la caja esté abierta
        $sql_verificar = "SELECT estado FROM caja WHERE idcaja = ? AND estado = 1";
        $stmt_ver = $conexion->prepare($sql_verificar);
        if (!$stmt_ver) {
            $this->lastError = "Error preparando verificación: " . $conexion->error;
            return false;
        }

        $stmt_ver->bind_param("i", $idcaja);
        $stmt_ver->execute();
        $result = $stmt_ver->get_result();

        if ($result->num_rows === 0) {
            $this->lastError = "Caja no encontrada o está cerrada";
            $stmt_ver->close();
            return false;
        }
        $stmt_ver->close();

        // Insertar movimiento
        $sql_mov = "INSERT INTO caja_movimientos (
            idcaja, tipo_movimiento, concepto, monto, tipo_pago,
            referencia, idcomprobante, idusuario, fecha_movimiento
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt_mov = $conexion->prepare($sql_mov);
        if (!$stmt_mov) {
            $this->lastError = "Error preparando INSERT movimiento: " . $conexion->error;
            return false;
        }

        $stmt_mov->bind_param("issdssi", $idcaja, $tipo_movimiento, $concepto, $monto, $tipo_pago, $referencia, $idcomprobante, $idusuario);

        if (!$stmt_mov->execute()) {
            $this->lastError = "Error ejecutando INSERT movimiento: " . $stmt_mov->error;
            $stmt_mov->close();
            return false;
        }

        $idmovimiento = $conexion->insert_id;
        $stmt_mov->close();

        return $idmovimiento;
    }

    /**
     * Obtener caja abierta de un usuario
     *
     * @param int $idusuario ID del usuario
     * @return array|false Datos de la caja o false si no hay caja abierta
     */
    public function obtenerCajaAbierta($idusuario)
    {
        $sql = "SELECT
            c.idcaja,
            c.fecha,
            c.montoi as monto_inicial,
            c.fecha_apertura,
            c.turno,
            c.idempresa,
            u.nombre as usuario,
            COALESCE(SUM(CASE WHEN cm.tipo_movimiento IN ('INGRESO', 'VENTA') THEN cm.monto ELSE 0 END), 0) as total_ingresos,
            COALESCE(SUM(CASE WHEN cm.tipo_movimiento IN ('EGRESO', 'COMPRA') THEN cm.monto ELSE 0 END), 0) as total_egresos,
            (c.montoi +
             COALESCE(SUM(CASE WHEN cm.tipo_movimiento IN ('INGRESO', 'VENTA') THEN cm.monto ELSE 0 END), 0) -
             COALESCE(SUM(CASE WHEN cm.tipo_movimiento IN ('EGRESO', 'COMPRA') THEN cm.monto ELSE 0 END), 0)
            ) as saldo_actual
        FROM caja c
        INNER JOIN usuario u ON c.idusuario = u.idusuario
        LEFT JOIN caja_movimientos cm ON c.idcaja = cm.idcaja AND cm.estado = 1
        WHERE c.idusuario = ? AND c.estado = 1 AND c.fecha_cierre IS NULL
        GROUP BY c.idcaja
        ORDER BY c.idcaja DESC
        LIMIT 1";

        return ejecutarConsultaPreparada($sql, "i", [$idusuario]);
    }

    /**
     * Listar movimientos de una caja
     *
     * @param int $idcaja ID de la caja
     * @return mysqli_result|false Resultado de la consulta
     */
    public function listarMovimientos($idcaja)
    {
        $sql = "SELECT
            cm.idmovimiento,
            cm.tipo_movimiento,
            cm.concepto,
            cm.monto,
            cm.tipo_pago,
            cm.referencia,
            cm.fecha_movimiento,
            u.nombre as usuario,
            cm.estado
        FROM caja_movimientos cm
        LEFT JOIN usuario u ON cm.idusuario = u.idusuario
        WHERE cm.idcaja = ?
        ORDER BY cm.fecha_movimiento DESC";

        return ejecutarConsultaPreparada($sql, "i", [$idcaja]);
    }

    /**
     * Listar cajas (filtrado por empresa y estado)
     *
     * @param int $idempresa ID de la empresa
     * @param int $estado Estado de la caja (1=Abierta, 0=Cerrada, null=Todas)
     * @return mysqli_result|false Resultado de la consulta
     */
    public function listar($idempresa, $estado = null)
    {
        if ($estado === null) {
            $sql = "SELECT
                c.idcaja,
                c.fecha,
                c.montoi as monto_inicial,
                c.montof as monto_final,
                c.monto_sistema,
                c.diferencia,
                c.fecha_apertura,
                c.fecha_cierre,
                c.turno,
                c.estado,
                u.nombre as usuario
            FROM caja c
            LEFT JOIN usuario u ON c.idusuario = u.idusuario
            WHERE c.idempresa = ?
            ORDER BY c.idcaja DESC";

            return ejecutarConsultaPreparada($sql, "i", [$idempresa]);
        } else {
            $sql = "SELECT
                c.idcaja,
                c.fecha,
                c.montoi as monto_inicial,
                c.montof as monto_final,
                c.monto_sistema,
                c.diferencia,
                c.fecha_apertura,
                c.fecha_cierre,
                c.turno,
                c.estado,
                u.nombre as usuario
            FROM caja c
            LEFT JOIN usuario u ON c.idusuario = u.idusuario
            WHERE c.idempresa = ? AND c.estado = ?
            ORDER BY c.idcaja DESC";

            return ejecutarConsultaPreparada($sql, "ii", [$idempresa, $estado]);
        }
    }

    /**
     * Obtener resumen de caja (para dashboard)
     *
     * @param int $idempresa ID de la empresa
     * @return array|false Resumen con totales
     */
    public function obtenerResumen($idempresa)
    {
        $sql = "SELECT
            COUNT(CASE WHEN estado = 1 THEN 1 END) as cajas_abiertas,
            COUNT(CASE WHEN estado = 0 THEN 1 END) as cajas_cerradas,
            COALESCE(SUM(CASE WHEN estado = 1 THEN montoi ELSE 0 END), 0) as total_en_cajas_abiertas,
            COALESCE(SUM(CASE WHEN estado = 0 THEN montof ELSE 0 END), 0) as total_cerrado_hoy
        FROM caja
        WHERE idempresa = ? AND DATE(fecha) = CURDATE()";

        $result = ejecutarConsultaPreparada($sql, "i", [$idempresa]);
        if ($result && $row = $result->fetch_assoc()) {
            return $row;
        }
        return false;
    }

    /**
     * Mostrar información detallada de una caja específica
     *
     * @param int $idcaja ID de la caja
     * @return array|false Datos de la caja o false si no existe
     */
    public function mostrar($idcaja)
    {
        $sql = "SELECT
            c.idcaja,
            c.fecha,
            c.montoi as monto_inicial,
            c.montof as monto_final,
            c.monto_sistema,
            c.diferencia,
            c.observaciones,
            c.fecha_apertura,
            c.fecha_cierre,
            c.turno,
            c.estado,
            c.idempresa,
            c.idusuario,
            u.nombre as usuario,
            u.login,
            COALESCE(SUM(CASE WHEN cm.tipo_movimiento IN ('INGRESO', 'VENTA') THEN cm.monto ELSE 0 END), 0) as total_ingresos,
            COALESCE(SUM(CASE WHEN cm.tipo_movimiento IN ('EGRESO', 'COMPRA') THEN cm.monto ELSE 0 END), 0) as total_egresos,
            COUNT(cm.idmovimiento) as total_movimientos
        FROM caja c
        LEFT JOIN usuario u ON c.idusuario = u.idusuario
        LEFT JOIN caja_movimientos cm ON c.idcaja = cm.idcaja AND cm.estado = 1
        WHERE c.idcaja = ?
        GROUP BY c.idcaja";

        $result = ejecutarConsultaPreparada($sql, "i", [$idcaja]);
        if ($result && $row = $result->fetch_assoc()) {
            return $row;
        }
        return false;
    }

    /**
     * Guardar arqueo de caja (conteo físico de denominaciones)
     *
     * @param array $datos_arqueo Array con todos los datos del arqueo
     * @return int|false ID del arqueo o false si falla
     */
    public function guardarArqueo($datos_arqueo)
    {
        global $conexion;

        // Validar que la caja exista y esté abierta
        $sql_verificar = "SELECT estado FROM caja WHERE idcaja = ?";
        $stmt_ver = $conexion->prepare($sql_verificar);
        if (!$stmt_ver) {
            $this->lastError = "Error preparando verificación: " . $conexion->error;
            return false;
        }

        $stmt_ver->bind_param("i", $datos_arqueo['idcaja']);
        $stmt_ver->execute();
        $result = $stmt_ver->get_result();

        if ($result->num_rows === 0) {
            $this->lastError = "Caja no encontrada";
            $stmt_ver->close();
            return false;
        }
        $stmt_ver->close();

        // Insertar arqueo
        $sql_arqueo = "INSERT INTO caja_arqueos (
            idcaja, billetes_200, billetes_100, billetes_50, billetes_20, billetes_10,
            monedas_5, monedas_2, monedas_1, monedas_050, monedas_020, monedas_010,
            total_efectivo, total_tarjetas, total_transferencias, total_yape, total_plin,
            total_otros, total_general, notas, fecha_arqueo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt_arqueo = $conexion->prepare($sql_arqueo);
        if (!$stmt_arqueo) {
            $this->lastError = "Error preparando INSERT arqueo: " . $conexion->error;
            return false;
        }

        $stmt_arqueo->bind_param(
            "iiiiiiiiiiiiddddddds",
            $datos_arqueo['idcaja'],
            $datos_arqueo['billetes_200'],
            $datos_arqueo['billetes_100'],
            $datos_arqueo['billetes_50'],
            $datos_arqueo['billetes_20'],
            $datos_arqueo['billetes_10'],
            $datos_arqueo['monedas_5'],
            $datos_arqueo['monedas_2'],
            $datos_arqueo['monedas_1'],
            $datos_arqueo['monedas_050'],
            $datos_arqueo['monedas_020'],
            $datos_arqueo['monedas_010'],
            $datos_arqueo['total_efectivo'],
            $datos_arqueo['total_tarjetas'],
            $datos_arqueo['total_transferencias'],
            $datos_arqueo['total_yape'],
            $datos_arqueo['total_plin'],
            $datos_arqueo['total_otros'],
            $datos_arqueo['total_general'],
            $datos_arqueo['notas']
        );

        if (!$stmt_arqueo->execute()) {
            $this->lastError = "Error ejecutando INSERT arqueo: " . $stmt_arqueo->error;
            $stmt_arqueo->close();
            return false;
        }

        $idarqueo = $conexion->insert_id;
        $stmt_arqueo->close();

        return $idarqueo;
    }
}
?>
