<?php
/**
 * Modelo CajachicaMejorada
 * Sistema de Caja Chica con Control de Usuarios y Permisos
 *
 * Features:
 * - Control de usuarios por operación (apertura/cierre/aprobación)
 * - Sistema de roles y permisos granulares
 * - Límites de monto configurables por usuario
 * - Arqueo físico de caja con denominaciones
 * - Aprobación de cierres con doble validación
 * - Auditoría completa de todas las operaciones
 * - Registro de otros medios de pago
 *
 * @author Claude Code
 * @date 2025-01-15
 */

require_once "../config/Conexion.php";

class CajachicaMejorada {

    public function __construct() {
        // Constructor vacío
    }

    // ========================================
    // MÉTODOS DE APERTURA DE CAJA
    // ========================================

    /**
     * Aperturar caja chica
     * Registra saldo inicial con control de usuario
     */
    public function aperturarCaja($saldo_inicial, $idusuario, $observaciones = null) {
        // Verificar si ya existe apertura del día
        if ($this->existeSaldoInicialDiaActual()) {
            return [
                'success' => false,
                'message' => 'Ya existe un saldo inicial registrado para hoy'
            ];
        }

        // Verificar permisos de usuario
        if (!$this->tienePermiso($idusuario, 'APERTURAR_CAJA')) {
            return [
                'success' => false,
                'message' => 'No tienes permisos para aperturar caja'
            ];
        }

        // Verificar límites de apertura
        $limite = $this->obtenerLimitesUsuario($idusuario);
        if ($limite && $saldo_inicial > $limite['limite_apertura']) {
            return [
                'success' => false,
                'message' => "El monto excede tu límite de apertura (S/ {$limite['limite_apertura']})"
            ];
        }

        $ip = $this->obtenerIP();
        $fecha_hora = date('Y-m-d H:i:s');

        $sql = "INSERT INTO saldocaja (
            saldo_inicial,
            fecha_creacion,
            idusuario_apertura,
            fecha_hora_apertura,
            ip_apertura,
            observaciones_apertura,
            estado
        ) VALUES (
            '$saldo_inicial',
            CURRENT_DATE(),
            '$idusuario',
            '$fecha_hora',
            '$ip',
            " . ($observaciones ? "'$observaciones'" : "NULL") . ",
            'abierta'
        )";

        $resultado = ejecutarConsulta($sql);

        if ($resultado) {
            $idsaldoini = ejecutarConsulta_retornarID($sql);

            // Registrar en auditoría
            $this->registrarAuditoria(
                'apertura',
                'saldocaja',
                $idsaldoini,
                $idusuario,
                null,
                [
                    'saldo_inicial' => $saldo_inicial,
                    'observaciones' => $observaciones
                ],
                'Apertura de caja chica'
            );

            return [
                'success' => true,
                'message' => 'Caja aperturada correctamente',
                'idsaldoini' => $idsaldoini
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al aperturar caja'
        ];
    }

    /**
     * Verificar si ya existe saldo inicial para el día actual
     */
    public function existeSaldoInicialDiaActual() {
        $sql = "SELECT COUNT(*) as total FROM saldocaja
                WHERE fecha_creacion = CURRENT_DATE()
                AND estado IN ('abierta', 'cerrada')";
        $resultado = ejecutarConsultaSimpleFila($sql);
        return $resultado['total'] > 0;
    }

    /**
     * Obtener saldo inicial del día actual
     */
    public function obtenerSaldoInicialDiaActual() {
        $sql = "SELECT s.*,
                u.nombre, u.apellidos,
                CASE
                    WHEN s.estado = 'abierta' THEN 'Abierta'
                    WHEN s.estado = 'cerrada' THEN 'Cerrada'
                    ELSE 'Anulada'
                END as estado_texto
                FROM saldocaja s
                LEFT JOIN usuario u ON s.idusuario_apertura = u.idusuario
                WHERE s.fecha_creacion = CURRENT_DATE()
                AND s.estado IN ('abierta', 'cerrada')
                LIMIT 1";
        return ejecutarConsultaSimpleFila($sql);
    }

    // ========================================
    // MÉTODOS DE CIERRE DE CAJA
    // ========================================

    /**
     * Cerrar caja chica con arqueo completo
     */
    public function cerrarCaja($data) {
        $idusuario = $data['idusuario'];
        $idsaldoini = $data['idsaldoini'];
        $total_efectivo_contado = $data['total_efectivo_contado'];
        $arqueo = $data['arqueo']; // Array de denominaciones
        $otros_pagos = $data['otros_pagos'] ?? []; // Array de otros medios de pago
        $observaciones = $data['observaciones'] ?? null;

        // Verificar permisos
        if (!$this->tienePermiso($idusuario, 'CERRAR_CAJA')) {
            return [
                'success' => false,
                'message' => 'No tienes permisos para cerrar caja'
            ];
        }

        // Verificar que exista saldo inicial abierto
        $saldo = $this->obtenerSaldoInicialDiaActual();
        if (!$saldo || $saldo['estado'] != 'abierta') {
            return [
                'success' => false,
                'message' => 'No existe una caja abierta para cerrar'
            ];
        }

        // Calcular totales del día
        $totales = $this->calcularTotalesDia();
        $total_ingresos = floatval($totales['total_ingresos']);
        $total_egresos = floatval($totales['total_egresos']);
        $total_ventas = floatval($totales['total_ventas']);
        $saldo_inicial = floatval($saldo['saldo_inicial']);

        // Total en sistema
        $total_sistema = $saldo_inicial + $total_ingresos + $total_ventas - $total_egresos;

        // Total contado físicamente
        $total_otros_pagos = array_sum(array_column($otros_pagos, 'monto'));
        $total_caja_fisica = $total_efectivo_contado + $total_otros_pagos;

        // Diferencia
        $diferencia = $total_caja_fisica - $total_sistema;

        // Verificar límites de diferencia
        $limite = $this->obtenerLimitesUsuario($idusuario);
        $requiere_aprobacion = false;

        if ($limite) {
            if (abs($diferencia) > $limite['limite_diferencia']) {
                $requiere_aprobacion = true;
            }
            if ($limite['requiere_aprobacion_cierre'] == 1) {
                $requiere_aprobacion = true;
            }
            if ($diferencia != 0 && $limite['requiere_aprobacion_diferencia'] == 1) {
                $requiere_aprobacion = true;
            }
        }

        $ip = $this->obtenerIP();
        $fecha_hora = date('Y-m-d H:i:s');
        $estado_aprobacion = $requiere_aprobacion ? 'pendiente' : 'aprobado';

        // Insertar cierre de caja
        $sql = "INSERT INTO cierrecaja (
            fecha_cierre,
            idsaldoini,
            idusuario_cierre,
            fecha_hora_cierre,
            ip_cierre,
            total_ingresos,
            total_egresos,
            total_ventas,
            total_efectivo_contado,
            total_caja,
            diferencia,
            observaciones_cierre,
            estado_aprobacion
        ) VALUES (
            CURRENT_DATE(),
            '$idsaldoini',
            '$idusuario',
            '$fecha_hora',
            '$ip',
            '$total_ingresos',
            '$total_egresos',
            '$total_ventas',
            '$total_efectivo_contado',
            '$total_caja_fisica',
            '$diferencia',
            " . ($observaciones ? "'$observaciones'" : "NULL") . ",
            '$estado_aprobacion'
        )";

        $resultado = ejecutarConsulta($sql);

        if ($resultado) {
            $idcierre = ejecutarConsulta_retornarID($sql);

            // Guardar arqueo de denominaciones
            $this->guardarArqueo($idcierre, $arqueo);

            // Guardar otros medios de pago
            if (!empty($otros_pagos)) {
                $this->guardarOtrosPagos($idcierre, $otros_pagos);
            }

            // Actualizar estado del saldo inicial
            $this->cerrarSaldoInicial($idsaldoini);

            // Registrar en auditoría
            $this->registrarAuditoria(
                'cierre',
                'cierrecaja',
                $idcierre,
                $idusuario,
                null,
                [
                    'total_sistema' => $total_sistema,
                    'total_contado' => $total_caja_fisica,
                    'diferencia' => $diferencia,
                    'requiere_aprobacion' => $requiere_aprobacion
                ],
                'Cierre de caja chica'
            );

            return [
                'success' => true,
                'message' => $requiere_aprobacion
                    ? 'Caja cerrada correctamente. Requiere aprobación de supervisor.'
                    : 'Caja cerrada y aprobada automáticamente.',
                'idcierre' => $idcierre,
                'requiere_aprobacion' => $requiere_aprobacion,
                'diferencia' => $diferencia
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al cerrar caja'
        ];
    }

    /**
     * Guardar arqueo de denominaciones
     */
    private function guardarArqueo($idcierre, $arqueo) {
        foreach ($arqueo as $denom) {
            $denominacion = $denom['denominacion'];
            $cantidad = $denom['cantidad'];
            $subtotal = $denom['subtotal'];

            $sql = "INSERT INTO cajachica_arqueo (idcierre, denominacion, cantidad, subtotal)
                    VALUES ('$idcierre', '$denominacion', '$cantidad', '$subtotal')";
            ejecutarConsulta($sql);
        }
    }

    /**
     * Guardar otros medios de pago
     */
    private function guardarOtrosPagos($idcierre, $otros_pagos) {
        foreach ($otros_pagos as $pago) {
            $tipo_pago = limpiarCadena($pago['tipo_pago']);
            $monto = $pago['monto'];
            $numero_operacion = isset($pago['numero_operacion']) ? limpiarCadena($pago['numero_operacion']) : null;
            $observaciones = isset($pago['observaciones']) ? limpiarCadena($pago['observaciones']) : null;

            $sql = "INSERT INTO cajachica_otros_pagos (idcierre, tipo_pago, monto, numero_operacion, observaciones)
                    VALUES ('$idcierre', '$tipo_pago', '$monto',
                    " . ($numero_operacion ? "'$numero_operacion'" : "NULL") . ",
                    " . ($observaciones ? "'$observaciones'" : "NULL") . ")";
            ejecutarConsulta($sql);
        }
    }

    /**
     * Cerrar saldo inicial
     */
    private function cerrarSaldoInicial($idsaldoini) {
        $sql = "UPDATE saldocaja SET estado = 'cerrada' WHERE idsaldoini = '$idsaldoini'";
        return ejecutarConsulta($sql);
    }

    /**
     * Calcular totales del día
     */
    public function calcularTotalesDia() {
        // Total ventas (facturas + boletas + notas de pedido)
        $sql_ventas = "SELECT SUM(total_venta) as total_venta
            FROM (
                SELECT SUM(importe_total_venta_27) as total_venta
                FROM factura
                WHERE DATE(fecha_emision_01) = CURRENT_DATE AND estado IN ('5','1','6')
                UNION ALL
                SELECT SUM(importe_total_23) as total_venta
                FROM boleta
                WHERE DATE(fecha_emision_01) = CURRENT_DATE AND estado IN ('5','1','6')
                UNION ALL
                SELECT SUM(importe_total_23) as total_venta
                FROM notapedido
                WHERE DATE(fecha_emision_01) = CURRENT_DATE AND estado IN ('5','1','6')
            ) as tbl1";
        $rspta_ventas = ejecutarConsultaSimpleFila($sql_ventas);
        $total_ventas = $rspta_ventas['total_venta'] ?? 0;

        // Total ingresos (de tabla insumos)
        $sql_ingresos = "SELECT COALESCE(SUM(ingreso), 0) as total_ingreso
                         FROM insumos WHERE DATE(fecharegistro) = CURRENT_DATE";
        $rspta_ingresos = ejecutarConsultaSimpleFila($sql_ingresos);
        $total_ingresos = $rspta_ingresos['total_ingreso'];

        // Total egresos (de tabla insumos)
        $sql_egresos = "SELECT COALESCE(SUM(gasto), 0) as total_gasto
                        FROM insumos WHERE DATE(fecharegistro) = CURRENT_DATE";
        $rspta_egresos = ejecutarConsultaSimpleFila($sql_egresos);
        $total_egresos = $rspta_egresos['total_gasto'];

        return [
            'total_ventas' => $total_ventas,
            'total_ingresos' => $total_ingresos,
            'total_egresos' => $total_egresos
        ];
    }

    // ========================================
    // MÉTODOS DE APROBACIÓN
    // ========================================

    /**
     * Aprobar cierre de caja
     */
    public function aprobarCierre($idcierre, $idusuario_aprobacion, $observaciones = null) {
        // Verificar permisos
        if (!$this->tienePermiso($idusuario_aprobacion, 'APROBAR_CIERRE')) {
            return [
                'success' => false,
                'message' => 'No tienes permisos para aprobar cierres'
            ];
        }

        // Obtener datos del cierre
        $cierre = $this->obtenerCierre($idcierre);
        if (!$cierre) {
            return [
                'success' => false,
                'message' => 'Cierre no encontrado'
            ];
        }

        if ($cierre['estado_aprobacion'] != 'pendiente') {
            return [
                'success' => false,
                'message' => 'Este cierre ya fue ' . $cierre['estado_aprobacion']
            ];
        }

        $fecha_aprobacion = date('Y-m-d H:i:s');

        $sql = "UPDATE cierrecaja SET
                estado_aprobacion = 'aprobado',
                idusuario_aprobacion = '$idusuario_aprobacion',
                fecha_aprobacion = '$fecha_aprobacion'
                WHERE idcierre = '$idcierre'";

        $resultado = ejecutarConsulta($sql);

        if ($resultado) {
            // Registrar en auditoría
            $this->registrarAuditoria(
                'aprobacion',
                'cierrecaja',
                $idcierre,
                $idusuario_aprobacion,
                ['estado_aprobacion' => 'pendiente'],
                ['estado_aprobacion' => 'aprobado'],
                'Aprobación de cierre de caja'
            );

            return [
                'success' => true,
                'message' => 'Cierre aprobado correctamente'
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al aprobar cierre'
        ];
    }

    /**
     * Rechazar cierre de caja
     */
    public function rechazarCierre($idcierre, $idusuario_aprobacion, $motivo_rechazo) {
        // Verificar permisos
        if (!$this->tienePermiso($idusuario_aprobacion, 'APROBAR_CIERRE')) {
            return [
                'success' => false,
                'message' => 'No tienes permisos para rechazar cierres'
            ];
        }

        $motivo_rechazo = limpiarCadena($motivo_rechazo);
        $fecha_aprobacion = date('Y-m-d H:i:s');

        $sql = "UPDATE cierrecaja SET
                estado_aprobacion = 'rechazado',
                idusuario_aprobacion = '$idusuario_aprobacion',
                fecha_aprobacion = '$fecha_aprobacion',
                motivo_rechazo = '$motivo_rechazo'
                WHERE idcierre = '$idcierre'";

        $resultado = ejecutarConsulta($sql);

        if ($resultado) {
            // Registrar en auditoría
            $this->registrarAuditoria(
                'rechazo',
                'cierrecaja',
                $idcierre,
                $idusuario_aprobacion,
                ['estado_aprobacion' => 'pendiente'],
                [
                    'estado_aprobacion' => 'rechazado',
                    'motivo_rechazo' => $motivo_rechazo
                ],
                'Rechazo de cierre de caja'
            );

            return [
                'success' => true,
                'message' => 'Cierre rechazado correctamente'
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al rechazar cierre'
        ];
    }

    // ========================================
    // MÉTODOS DE CONSULTA Y REPORTES
    // ========================================

    /**
     * Listar cierres de caja con filtros
     */
    public function listarCierres($filtros = []) {
        $where = "1=1";

        if (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin'])) {
            $where .= " AND c.fecha_cierre BETWEEN '{$filtros['fecha_inicio']}' AND '{$filtros['fecha_fin']}'";
        }

        if (isset($filtros['estado_aprobacion'])) {
            $where .= " AND c.estado_aprobacion = '{$filtros['estado_aprobacion']}'";
        }

        if (isset($filtros['idusuario']) && !isset($filtros['ver_todas'])) {
            $where .= " AND c.idusuario_cierre = '{$filtros['idusuario']}'";
        }

        $sql = "SELECT
                c.*,
                s.saldo_inicial,
                u_cierre.nombre as nombre_cierre, u_cierre.apellidos as apellidos_cierre,
                u_apertura.nombre as nombre_apertura, u_apertura.apellidos as apellidos_apertura,
                u_aprobacion.nombre as nombre_aprobacion, u_aprobacion.apellidos as apellidos_aprobacion,
                CASE
                    WHEN c.estado_aprobacion = 'pendiente' THEN 'Pendiente'
                    WHEN c.estado_aprobacion = 'aprobado' THEN 'Aprobado'
                    WHEN c.estado_aprobacion = 'rechazado' THEN 'Rechazado'
                END as estado_texto,
                CASE
                    WHEN c.diferencia > 0 THEN 'Sobrante'
                    WHEN c.diferencia < 0 THEN 'Faltante'
                    ELSE 'Exacto'
                END as tipo_diferencia
                FROM cierrecaja c
                LEFT JOIN saldocaja s ON c.idsaldoini = s.idsaldoini
                LEFT JOIN usuario u_cierre ON c.idusuario_cierre = u_cierre.idusuario
                LEFT JOIN usuario u_apertura ON s.idusuario_apertura = u_apertura.idusuario
                LEFT JOIN usuario u_aprobacion ON c.idusuario_aprobacion = u_aprobacion.idusuario
                WHERE $where
                ORDER BY c.fecha_cierre DESC, c.fecha_hora_cierre DESC";

        return ejecutarConsulta($sql);
    }

    /**
     * Obtener detalle completo de un cierre
     */
    public function obtenerCierre($idcierre) {
        $sql = "SELECT
                c.*,
                s.saldo_inicial,
                s.fecha_hora_apertura,
                s.observaciones_apertura,
                u_cierre.nombre as nombre_cierre, u_cierre.apellidos as apellidos_cierre,
                u_apertura.nombre as nombre_apertura, u_apertura.apellidos as apellidos_apertura,
                u_aprobacion.nombre as nombre_aprobacion, u_aprobacion.apellidos as apellidos_aprobacion
                FROM cierrecaja c
                LEFT JOIN saldocaja s ON c.idsaldoini = s.idsaldoini
                LEFT JOIN usuario u_cierre ON c.idusuario_cierre = u_cierre.idusuario
                LEFT JOIN usuario u_apertura ON s.idusuario_apertura = u_apertura.idusuario
                LEFT JOIN usuario u_aprobacion ON c.idusuario_aprobacion = u_aprobacion.idusuario
                WHERE c.idcierre = '$idcierre'";

        return ejecutarConsultaSimpleFila($sql);
    }

    /**
     * Obtener arqueo de un cierre
     */
    public function obtenerArqueo($idcierre) {
        $sql = "SELECT * FROM cajachica_arqueo WHERE idcierre = '$idcierre' ORDER BY denominacion DESC";
        return ejecutarConsulta($sql);
    }

    /**
     * Obtener otros pagos de un cierre
     */
    public function obtenerOtrosPagos($idcierre) {
        $sql = "SELECT * FROM cajachica_otros_pagos WHERE idcierre = '$idcierre'";
        return ejecutarConsulta($sql);
    }

    // ========================================
    // MÉTODOS DE PERMISOS Y ROLES
    // ========================================

    /**
     * Verificar si usuario tiene un permiso específico
     */
    public function tienePermiso($idusuario, $codigo_permiso) {
        // Verificar permisos directos
        $sql_directo = "SELECT COUNT(*) as total
                        FROM cajachica_usuario_permiso cup
                        INNER JOIN cajachica_permisos cp ON cup.idpermiso = cp.idpermiso
                        WHERE cup.idusuario = '$idusuario'
                        AND cp.codigo_permiso = '$codigo_permiso'";
        $resultado_directo = ejecutarConsultaSimpleFila($sql_directo);

        if ($resultado_directo['total'] > 0) {
            return true;
        }

        // Verificar permisos a través de roles
        $sql_rol = "SELECT COUNT(*) as total
                    FROM cajachica_usuario_rol cur
                    INNER JOIN cajachica_rol_permiso crp ON cur.idrol = crp.idrol
                    INNER JOIN cajachica_permisos cp ON crp.idpermiso = cp.idpermiso
                    WHERE cur.idusuario = '$idusuario'
                    AND cur.estado = 1
                    AND cp.codigo_permiso = '$codigo_permiso'";
        $resultado_rol = ejecutarConsultaSimpleFila($sql_rol);

        return $resultado_rol['total'] > 0;
    }

    /**
     * Obtener todos los permisos de un usuario
     */
    public function obtenerPermisosUsuario($idusuario) {
        $sql = "SELECT DISTINCT cp.*
                FROM cajachica_permisos cp
                WHERE cp.idpermiso IN (
                    -- Permisos directos
                    SELECT idpermiso FROM cajachica_usuario_permiso
                    WHERE idusuario = '$idusuario'
                    UNION
                    -- Permisos por rol
                    SELECT crp.idpermiso
                    FROM cajachica_usuario_rol cur
                    INNER JOIN cajachica_rol_permiso crp ON cur.idrol = crp.idrol
                    WHERE cur.idusuario = '$idusuario' AND cur.estado = 1
                )
                AND cp.estado = 1
                ORDER BY cp.nombre_permiso";

        return ejecutarConsulta($sql);
    }

    /**
     * Obtener límites de un usuario
     */
    public function obtenerLimitesUsuario($idusuario) {
        $sql = "SELECT * FROM cajachica_limites WHERE idusuario = '$idusuario'";
        return ejecutarConsultaSimpleFila($sql);
    }

    /**
     * Configurar límites de un usuario
     */
    public function configurarLimites($idusuario, $limite_apertura, $limite_diferencia, $requiere_aprobacion_cierre, $requiere_aprobacion_diferencia, $idusuario_configura) {
        $sql = "INSERT INTO cajachica_limites (
                    idusuario,
                    limite_apertura,
                    limite_diferencia,
                    requiere_aprobacion_cierre,
                    requiere_aprobacion_diferencia,
                    idusuario_configura
                ) VALUES (
                    '$idusuario',
                    '$limite_apertura',
                    '$limite_diferencia',
                    '$requiere_aprobacion_cierre',
                    '$requiere_aprobacion_diferencia',
                    '$idusuario_configura'
                )
                ON DUPLICATE KEY UPDATE
                    limite_apertura = '$limite_apertura',
                    limite_diferencia = '$limite_diferencia',
                    requiere_aprobacion_cierre = '$requiere_aprobacion_cierre',
                    requiere_aprobacion_diferencia = '$requiere_aprobacion_diferencia',
                    idusuario_configura = '$idusuario_configura',
                    fecha_actualizacion = CURRENT_TIMESTAMP";

        return ejecutarConsulta($sql);
    }

    /**
     * Asignar rol a usuario
     */
    public function asignarRol($idusuario, $idrol, $idusuario_asigna) {
        $sql = "INSERT INTO cajachica_usuario_rol (idusuario, idrol, idusuario_asigna)
                VALUES ('$idusuario', '$idrol', '$idusuario_asigna')
                ON DUPLICATE KEY UPDATE
                    estado = 1,
                    fecha_asignacion = CURRENT_TIMESTAMP,
                    idusuario_asigna = '$idusuario_asigna'";

        return ejecutarConsulta($sql);
    }

    /**
     * Listar roles disponibles
     */
    public function listarRoles() {
        $sql = "SELECT * FROM cajachica_roles WHERE estado = 1 ORDER BY nombre_rol";
        return ejecutarConsulta($sql);
    }

    /**
     * Obtener roles de un usuario
     */
    public function obtenerRolesUsuario($idusuario) {
        $sql = "SELECT r.*
                FROM cajachica_roles r
                INNER JOIN cajachica_usuario_rol ur ON r.idrol = ur.idrol
                WHERE ur.idusuario = '$idusuario' AND ur.estado = 1 AND r.estado = 1";
        return ejecutarConsulta($sql);
    }

    // ========================================
    // MÉTODOS DE AUDITORÍA
    // ========================================

    /**
     * Registrar operación en auditoría
     */
    private function registrarAuditoria($tipo_operacion, $tabla_afectada, $id_registro, $idusuario, $datos_antes, $datos_despues, $observaciones = null) {
        $ip = $this->obtenerIP();
        $datos_antes_json = $datos_antes ? json_encode($datos_antes, JSON_UNESCAPED_UNICODE) : 'null';
        $datos_despues_json = $datos_despues ? json_encode($datos_despues, JSON_UNESCAPED_UNICODE) : 'null';

        $sql = "INSERT INTO cajachica_auditoria (
                    tipo_operacion,
                    tabla_afectada,
                    id_registro,
                    idusuario,
                    ip_origen,
                    datos_antes,
                    datos_despues,
                    observaciones
                ) VALUES (
                    '$tipo_operacion',
                    '$tabla_afectada',
                    " . ($id_registro ? "'$id_registro'" : "NULL") . ",
                    '$idusuario',
                    '$ip',
                    '$datos_antes_json',
                    '$datos_despues_json',
                    " . ($observaciones ? "'" . limpiarCadena($observaciones) . "'" : "NULL") . "
                )";

        return ejecutarConsulta($sql);
    }

    /**
     * Listar auditoría
     */
    public function listarAuditoria($filtros = []) {
        $where = "1=1";

        if (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin'])) {
            $where .= " AND DATE(a.fecha_operacion) BETWEEN '{$filtros['fecha_inicio']}' AND '{$filtros['fecha_fin']}'";
        }

        if (isset($filtros['tipo_operacion'])) {
            $where .= " AND a.tipo_operacion = '{$filtros['tipo_operacion']}'";
        }

        if (isset($filtros['idusuario'])) {
            $where .= " AND a.idusuario = '{$filtros['idusuario']}'";
        }

        $sql = "SELECT a.*,
                u.nombre, u.apellidos
                FROM cajachica_auditoria a
                LEFT JOIN usuario u ON a.idusuario = u.idusuario
                WHERE $where
                ORDER BY a.fecha_operacion DESC
                LIMIT 1000";

        return ejecutarConsulta($sql);
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    /**
     * Obtener IP del cliente
     */
    private function obtenerIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        }
    }

    /**
     * Obtener resumen de caja del día
     */
    public function obtenerResumenDia() {
        $saldo = $this->obtenerSaldoInicialDiaActual();
        $totales = $this->calcularTotalesDia();

        $saldo_inicial = $saldo ? floatval($saldo['saldo_inicial']) : 0;
        $total_sistema = $saldo_inicial + $totales['total_ingresos'] + $totales['total_ventas'] - $totales['total_egresos'];

        return [
            'saldo_inicial' => $saldo_inicial,
            'total_ventas' => $totales['total_ventas'],
            'total_ingresos' => $totales['total_ingresos'],
            'total_egresos' => $totales['total_egresos'],
            'total_sistema' => $total_sistema,
            'estado_caja' => $saldo ? $saldo['estado'] : 'sin_aperturar',
            'usuario_apertura' => $saldo ? ($saldo['nombre'] . ' ' . $saldo['apellidos']) : null
        ];
    }
}
