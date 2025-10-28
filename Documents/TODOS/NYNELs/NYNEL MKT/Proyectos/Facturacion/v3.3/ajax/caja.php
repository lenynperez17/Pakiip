<?php
// SEGURIDAD: Cargar helpers de validación y CSRF
require_once "../config/ajax_helper.php";
require_once "../modelos/Caja.php";

$caja = new Caja();

// ==================== RECEPCIÓN DE PARÁMETROS ====================

// Parámetros para apertura de caja
$idcaja = isset($_POST["idcaja"]) ? limpiarCadena($_POST["idcaja"]) : "";
$idusuario = isset($_POST["idusuario"]) ? limpiarCadena($_POST["idusuario"]) : "";
$monto_inicial = isset($_POST["monto_inicial"]) && $_POST["monto_inicial"] !== "" ? limpiarCadena($_POST["monto_inicial"]) : "0.00";
$idempresa = isset($_POST["idempresa"]) ? limpiarCadena($_POST["idempresa"]) : "1";
$turno = isset($_POST["turno"]) ? limpiarCadena($_POST["turno"]) : "COMPLETO";

// Parámetros para cierre de caja
$monto_declarado = isset($_POST["monto_declarado"]) && $_POST["monto_declarado"] !== "" ? limpiarCadena($_POST["monto_declarado"]) : "0.00";
$observaciones = isset($_POST["observaciones"]) ? limpiarCadena($_POST["observaciones"]) : "";

// Parámetros para movimientos
$tipo_movimiento = isset($_POST["tipo_movimiento"]) ? limpiarCadena($_POST["tipo_movimiento"]) : "";
$concepto = isset($_POST["concepto"]) ? limpiarCadena($_POST["concepto"]) : "";
$monto = isset($_POST["monto"]) && $_POST["monto"] !== "" ? limpiarCadena($_POST["monto"]) : "0.00";
$tipo_pago = isset($_POST["tipo_pago"]) ? limpiarCadena($_POST["tipo_pago"]) : "EFECTIVO";
$referencia = isset($_POST["referencia"]) ? limpiarCadena($_POST["referencia"]) : "";
$idcomprobante = isset($_POST["idcomprobante"]) ? limpiarCadena($_POST["idcomprobante"]) : null;

// Parámetros para filtros de listado
$fecha_inicio = isset($_POST["fecha_inicio"]) ? limpiarCadena($_POST["fecha_inicio"]) : "";
$fecha_fin = isset($_POST["fecha_fin"]) ? limpiarCadena($_POST["fecha_fin"]) : "";
$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";

// Parámetros para arqueo de caja
$billetes_200 = isset($_POST["billetes_200"]) && $_POST["billetes_200"] !== "" ? limpiarCadena($_POST["billetes_200"]) : "0";
$billetes_100 = isset($_POST["billetes_100"]) && $_POST["billetes_100"] !== "" ? limpiarCadena($_POST["billetes_100"]) : "0";
$billetes_50 = isset($_POST["billetes_50"]) && $_POST["billetes_50"] !== "" ? limpiarCadena($_POST["billetes_50"]) : "0";
$billetes_20 = isset($_POST["billetes_20"]) && $_POST["billetes_20"] !== "" ? limpiarCadena($_POST["billetes_20"]) : "0";
$billetes_10 = isset($_POST["billetes_10"]) && $_POST["billetes_10"] !== "" ? limpiarCadena($_POST["billetes_10"]) : "0";
$monedas_5 = isset($_POST["monedas_5"]) && $_POST["monedas_5"] !== "" ? limpiarCadena($_POST["monedas_5"]) : "0";
$monedas_2 = isset($_POST["monedas_2"]) && $_POST["monedas_2"] !== "" ? limpiarCadena($_POST["monedas_2"]) : "0";
$monedas_1 = isset($_POST["monedas_1"]) && $_POST["monedas_1"] !== "" ? limpiarCadena($_POST["monedas_1"]) : "0";
$monedas_050 = isset($_POST["monedas_050"]) && $_POST["monedas_050"] !== "" ? limpiarCadena($_POST["monedas_050"]) : "0";
$monedas_020 = isset($_POST["monedas_020"]) && $_POST["monedas_020"] !== "" ? limpiarCadena($_POST["monedas_020"]) : "0";
$monedas_010 = isset($_POST["monedas_010"]) && $_POST["monedas_010"] !== "" ? limpiarCadena($_POST["monedas_010"]) : "0";

// Otros totales del arqueo
$total_tarjetas = isset($_POST["total_tarjetas"]) && $_POST["total_tarjetas"] !== "" ? limpiarCadena($_POST["total_tarjetas"]) : "0.00";
$total_transferencias = isset($_POST["total_transferencias"]) && $_POST["total_transferencias"] !== "" ? limpiarCadena($_POST["total_transferencias"]) : "0.00";
$total_yape = isset($_POST["total_yape"]) && $_POST["total_yape"] !== "" ? limpiarCadena($_POST["total_yape"]) : "0.00";
$total_plin = isset($_POST["total_plin"]) && $_POST["total_plin"] !== "" ? limpiarCadena($_POST["total_plin"]) : "0.00";
$total_otros = isset($_POST["total_otros"]) && $_POST["total_otros"] !== "" ? limpiarCadena($_POST["total_otros"]) : "0.00";
$notas_arqueo = isset($_POST["notas_arqueo"]) ? limpiarCadena($_POST["notas_arqueo"]) : "";

// ==================== VERIFICAR MODO ACCIÓN (action vs op) ====================

// Primero verificar si viene por action (GET)
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // ==================== ACTIONS DE CONSULTA (GET) ====================

    if ($action == 'cajaAbierta') {
        // Obtener información de la caja abierta del usuario actual
        $rspta = $caja->obtenerCajaAbierta($idusuario);

        header('Content-type: application/json');
        echo json_encode($rspta);
        exit;
    }

    if ($action == 'resumenDiario') {
        // Obtener resumen del día para el dashboard
        $rspta = $caja->obtenerResumen($idempresa);

        header('Content-type: application/json');
        echo json_encode($rspta);
        exit;
    }

    if ($action == 'listarMovimientos') {
        // Listar movimientos de una caja específica
        $idcaja_param = isset($_GET['idcaja']) ? limpiarCadena($_GET['idcaja']) : "";

        if (empty($idcaja_param)) {
            echo json_encode(["error" => "ID de caja requerido"]);
            exit;
        }

        $rspta = $caja->listarMovimientos($idcaja_param);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                'idmovimiento' => $reg->idmovimiento,
                'tipo_movimiento' => $reg->tipo_movimiento,
                'concepto' => $reg->concepto,
                'monto' => number_format($reg->monto, 2),
                'tipo_pago' => $reg->tipo_pago,
                'referencia' => $reg->referencia,
                'fecha_movimiento' => $reg->fecha_movimiento,
                'usuario' => isset($reg->usuario) ? $reg->usuario : 'Sistema'
            );
        }

        $results = array(
            "aaData" => $data
        );

        header('Content-type: application/json');
        echo json_encode($results);
        exit;
    }

    if ($action == 'listarCajas') {
        // Listar todas las cajas con filtros opcionales
        $fecha_inicio_param = isset($_GET['fecha_inicio']) ? limpiarCadena($_GET['fecha_inicio']) : null;
        $fecha_fin_param = isset($_GET['fecha_fin']) ? limpiarCadena($_GET['fecha_fin']) : null;
        $estado_param = isset($_GET['estado']) ? limpiarCadena($_GET['estado']) : null;
        $idusuario_param = isset($_GET['idusuario']) ? limpiarCadena($_GET['idusuario']) : null;

        $rspta = $caja->listar($fecha_inicio_param, $fecha_fin_param, $estado_param, $idusuario_param);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $estado_badge = '';
            if ($reg->estado == 1 && $reg->fecha_cierre == null) {
                $estado_badge = '<span class="badge bg-success">Abierta</span>';
            } elseif ($reg->estado == 1 && $reg->fecha_cierre != null) {
                $estado_badge = '<span class="badge bg-primary">Cerrada</span>';
            } else {
                $estado_badge = '<span class="badge bg-danger">Inactiva</span>';
            }

            $diferencia_badge = '';
            if ($reg->diferencia > 0) {
                $diferencia_badge = '<span class="text-success">+' . number_format($reg->diferencia, 2) . '</span>';
            } elseif ($reg->diferencia < 0) {
                $diferencia_badge = '<span class="text-danger">' . number_format($reg->diferencia, 2) . '</span>';
            } else {
                $diferencia_badge = '<span class="text-muted">0.00</span>';
            }

            $data[] = array(
                '0' => $reg->idcaja,
                '1' => date('d/m/Y', strtotime($reg->fecha)),
                '2' => $reg->turno,
                '3' => isset($reg->usuario) ? $reg->usuario : 'N/A',
                '4' => number_format($reg->montoi, 2),
                '5' => number_format($reg->monto_sistema, 2),
                '6' => number_format($reg->montof, 2),
                '7' => $diferencia_badge,
                '8' => $estado_badge,
                '9' => ($reg->estado == 1 && $reg->fecha_cierre == null) ?
                    '<button class="btn btn-sm btn-info" onclick="verDetalleCaja(' . $reg->idcaja . ')">
                        <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="cerrarCajaModal(' . $reg->idcaja . ')">
                        <i class="fa fa-lock"></i> Cerrar
                    </button>' :
                    '<button class="btn btn-sm btn-info" onclick="verDetalleCaja(' . $reg->idcaja . ')">
                        <i class="fa fa-eye"></i> Ver
                    </button>'
            );
        }

        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );

        header('Content-type: application/json');
        echo json_encode($results);
        exit;
    }

} // Fin de actions (GET)

// ==================== OPERACIONES PRINCIPALES (POST) ====================

if (isset($_GET["op"])) {
    switch ($_GET["op"]) {

        case 'aperturarCaja':
            // SEGURIDAD: Validar token CSRF
            if (!validarCSRFAjax()) {
                echo json_encode(["error" => "Token de seguridad inválido"]);
                exit();
            }

            // SEGURIDAD: Validar datos de entrada
            $idusuario = validarEntero($idusuario, 1);
            $monto_inicial = validarDecimal($monto_inicial, 0);
            $idempresa = validarEntero($idempresa, 1);

            if ($idusuario === false || $monto_inicial === false || $idempresa === false) {
                echo json_encode(["error" => "Datos inválidos para apertura de caja"]);
                exit();
            }

            // Validar turno (ENUM)
            $turnos_validos = ['MAÑANA', 'TARDE', 'NOCHE', 'COMPLETO'];
            if (!in_array($turno, $turnos_validos)) {
                echo json_encode(["error" => "Turno inválido. Use: MAÑANA, TARDE, NOCHE o COMPLETO"]);
                exit();
            }

            // Ejecutar apertura
            $resultado = $caja->aperturarCaja($idusuario, $monto_inicial, $idempresa, $turno);

            if ($resultado) {
                // ========== AUDITORÍA: Registrar apertura de caja ==========
                registrarOperacionCreate('caja', $resultado, [
                    'usuario_id' => $idusuario,
                    'monto_inicial' => $monto_inicial,
                    'empresa_id' => $idempresa,
                    'turno' => $turno,
                    'fecha' => date('Y-m-d H:i:s')
                ], "Caja aperturada - Usuario ID: {$idusuario}, Turno: {$turno}, Monto inicial: S/ {$monto_inicial}");

                echo json_encode([
                    "success" => true,
                    "message" => "Caja aperturada correctamente",
                    "idcaja" => $resultado
                ]);
            } else {
                // ========== AUDITORÍA: Registrar intento fallido ==========
                registrarAuditoria('CREATE', 'caja', [
                    'descripcion' => "Intento fallido de aperturar caja - Usuario ID: {$idusuario}",
                    'resultado' => 'FALLIDO',
                    'codigo_error' => 'ERROR_APERTURA_CAJA',
                    'mensaje_error' => $caja->lastError,
                    'metadata' => [
                        'usuario_id' => $idusuario,
                        'monto_inicial' => $monto_inicial,
                        'turno' => $turno
                    ]
                ]);

                echo json_encode([
                    "error" => "No se pudo aperturar la caja. " . $caja->lastError
                ]);
            }
            break;

        case 'cerrarCaja':
            // SEGURIDAD: Validar token CSRF
            if (!validarCSRFAjax()) {
                echo json_encode(["error" => "Token de seguridad inválido"]);
                exit();
            }

            // SEGURIDAD: Validar datos de entrada
            $idcaja = validarEntero($idcaja, 1);
            $monto_declarado = validarDecimal($monto_declarado, 0);

            if ($idcaja === false || $monto_declarado === false) {
                echo json_encode(["error" => "Datos inválidos para cierre de caja"]);
                exit();
            }

            // Ejecutar cierre
            $resultado = $caja->cerrarCaja($idcaja, $monto_declarado, $observaciones);

            if ($resultado) {
                // ========== AUDITORÍA: Registrar cierre de caja ==========
                registrarAuditoria('UPDATE', 'caja', [
                    'registro_id' => $idcaja,
                    'descripcion' => "Caja cerrada - ID: {$idcaja}",
                    'metadata' => [
                        'monto_declarado' => $monto_declarado,
                        'observaciones' => $observaciones,
                        'fecha_cierre' => date('Y-m-d H:i:s')
                    ]
                ]);

                echo json_encode([
                    "success" => true,
                    "message" => "Caja cerrada correctamente"
                ]);
            } else {
                // ========== AUDITORÍA: Registrar intento fallido ==========
                registrarAuditoria('UPDATE', 'caja', [
                    'registro_id' => $idcaja,
                    'descripcion' => "Intento fallido de cerrar caja - ID: {$idcaja}",
                    'resultado' => 'FALLIDO',
                    'codigo_error' => 'ERROR_CIERRE_CAJA',
                    'mensaje_error' => $caja->lastError,
                    'metadata' => [
                        'monto_declarado' => $monto_declarado
                    ]
                ]);

                echo json_encode([
                    "error" => "No se pudo cerrar la caja. " . $caja->lastError
                ]);
            }
            break;

        case 'registrarMovimiento':
            // SEGURIDAD: Validar token CSRF
            if (!validarCSRFAjax()) {
                echo json_encode(["error" => "Token de seguridad inválido"]);
                exit();
            }

            // SEGURIDAD: Validar datos de entrada
            $idcaja = validarEntero($idcaja, 1);
            $monto = validarDecimal($monto, 0);
            $idusuario = isset($idusuario) && $idusuario !== "" ? validarEntero($idusuario, 1) : null;
            $idcomprobante = isset($idcomprobante) && $idcomprobante !== "" ? validarEntero($idcomprobante, 1) : null;

            if ($idcaja === false || $monto === false) {
                echo json_encode(["error" => "Datos inválidos para registrar movimiento"]);
                exit();
            }

            // Validar tipo_movimiento (ENUM)
            $tipos_movimiento_validos = ['INGRESO', 'EGRESO', 'VENTA', 'COMPRA', 'AJUSTE'];
            if (!in_array($tipo_movimiento, $tipos_movimiento_validos)) {
                echo json_encode(["error" => "Tipo de movimiento inválido"]);
                exit();
            }

            // Validar tipo_pago (ENUM)
            $tipos_pago_validos = ['EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'YAPE', 'PLIN', 'OTRO'];
            if (!in_array($tipo_pago, $tipos_pago_validos)) {
                echo json_encode(["error" => "Tipo de pago inválido"]);
                exit();
            }

            // Validar concepto no vacío
            if (empty($concepto)) {
                echo json_encode(["error" => "El concepto del movimiento es obligatorio"]);
                exit();
            }

            // Ejecutar registro de movimiento
            $resultado = $caja->registrarMovimiento(
                $idcaja,
                $tipo_movimiento,
                $concepto,
                $monto,
                $tipo_pago,
                $referencia,
                $idusuario,
                $idcomprobante
            );

            if ($resultado) {
                // ========== AUDITORÍA: Registrar movimiento de caja ==========
                registrarOperacionCreate('caja_movimientos', $resultado, [
                    'caja_id' => $idcaja,
                    'tipo_movimiento' => $tipo_movimiento,
                    'concepto' => $concepto,
                    'monto' => $monto,
                    'tipo_pago' => $tipo_pago,
                    'referencia' => $referencia,
                    'usuario_id' => $idusuario,
                    'comprobante_id' => $idcomprobante
                ], "Movimiento de caja registrado - Tipo: {$tipo_movimiento}, Concepto: {$concepto}, Monto: S/ {$monto}");

                echo json_encode([
                    "success" => true,
                    "message" => "Movimiento registrado correctamente",
                    "idmovimiento" => $resultado
                ]);
            } else {
                // ========== AUDITORÍA: Registrar intento fallido ==========
                registrarAuditoria('CREATE', 'caja_movimientos', [
                    'descripcion' => "Intento fallido de registrar movimiento - Caja ID: {$idcaja}",
                    'resultado' => 'FALLIDO',
                    'codigo_error' => 'ERROR_REGISTRAR_MOVIMIENTO',
                    'mensaje_error' => $caja->lastError,
                    'metadata' => [
                        'tipo_movimiento' => $tipo_movimiento,
                        'concepto' => $concepto,
                        'monto' => $monto
                    ]
                ]);

                echo json_encode([
                    "error" => "No se pudo registrar el movimiento. " . $caja->lastError
                ]);
            }
            break;

        case 'guardarArqueo':
            // SEGURIDAD: Validar token CSRF
            if (!validarCSRFAjax()) {
                echo json_encode(["error" => "Token de seguridad inválido"]);
                exit();
            }

            // SEGURIDAD: Validar datos de entrada
            $idcaja = validarEntero($idcaja, 1);

            if ($idcaja === false) {
                echo json_encode(["error" => "ID de caja inválido"]);
                exit();
            }

            // Calcular total efectivo
            $total_efectivo = (
                ($billetes_200 * 200) +
                ($billetes_100 * 100) +
                ($billetes_50 * 50) +
                ($billetes_20 * 20) +
                ($billetes_10 * 10) +
                ($monedas_5 * 5) +
                ($monedas_2 * 2) +
                ($monedas_1 * 1) +
                ($monedas_050 * 0.50) +
                ($monedas_020 * 0.20) +
                ($monedas_010 * 0.10)
            );

            // Calcular total general
            $total_general = $total_efectivo + $total_tarjetas + $total_transferencias +
                             $total_yape + $total_plin + $total_otros;

            // Preparar datos del arqueo
            $datos_arqueo = array(
                'idcaja' => $idcaja,
                'billetes_200' => $billetes_200,
                'billetes_100' => $billetes_100,
                'billetes_50' => $billetes_50,
                'billetes_20' => $billetes_20,
                'billetes_10' => $billetes_10,
                'monedas_5' => $monedas_5,
                'monedas_2' => $monedas_2,
                'monedas_1' => $monedas_1,
                'monedas_050' => $monedas_050,
                'monedas_020' => $monedas_020,
                'monedas_010' => $monedas_010,
                'total_efectivo' => $total_efectivo,
                'total_tarjetas' => $total_tarjetas,
                'total_transferencias' => $total_transferencias,
                'total_yape' => $total_yape,
                'total_plin' => $total_plin,
                'total_otros' => $total_otros,
                'total_general' => $total_general,
                'notas' => $notas_arqueo
            );

            // Ejecutar guardado de arqueo (método a implementar en modelo)
            $resultado = $caja->guardarArqueo($datos_arqueo);

            if ($resultado) {
                // ========== AUDITORÍA: Registrar arqueo de caja ==========
                registrarOperacionCreate('caja_arqueos', $resultado, [
                    'caja_id' => $idcaja,
                    'total_efectivo' => $total_efectivo,
                    'total_general' => $total_general,
                    'fecha' => date('Y-m-d H:i:s')
                ], "Arqueo de caja realizado - Caja ID: {$idcaja}, Total: S/ {$total_general}");

                echo json_encode([
                    "success" => true,
                    "message" => "Arqueo guardado correctamente",
                    "idarqueo" => $resultado,
                    "total_efectivo" => number_format($total_efectivo, 2),
                    "total_general" => number_format($total_general, 2)
                ]);
            } else {
                // ========== AUDITORÍA: Registrar intento fallido ==========
                registrarAuditoria('CREATE', 'caja_arqueos', [
                    'descripcion' => "Intento fallido de guardar arqueo - Caja ID: {$idcaja}",
                    'resultado' => 'FALLIDO',
                    'codigo_error' => 'ERROR_GUARDAR_ARQUEO',
                    'mensaje_error' => $caja->lastError,
                    'metadata' => [
                        'total_efectivo' => $total_efectivo,
                        'total_general' => $total_general
                    ]
                ]);

                echo json_encode([
                    "error" => "No se pudo guardar el arqueo. " . $caja->lastError
                ]);
            }
            break;

        case 'mostrar':
            // Obtener información detallada de una caja específica
            $idcaja = validarEntero($idcaja, 1);

            if ($idcaja === false) {
                echo json_encode(["error" => "ID de caja inválido"]);
                exit();
            }

            $rspta = $caja->mostrar($idcaja);

            header('Content-type: application/json');
            echo json_encode($rspta);
            break;

        default:
            echo json_encode(["error" => "Operación no válida"]);
            break;

    } // Cierre del switch
} // Cierre del if (isset($_GET["op"]))
?>
