<?php
/**
 * Controlador AJAX para Caja Chica Mejorada
 * Sistema de Caja Chica con Control de Usuarios y Permisos
 *
 * Operaciones:
 * - aperturarCaja: Apertura de caja con validación de permisos y límites
 * - cerrarCaja: Cierre de caja con arqueo completo
 * - aprobarCierre: Aprobación de cierre pendiente
 * - rechazarCierre: Rechazo de cierre con motivo
 * - resumenDia: Resumen de caja del día actual
 * - listarCierres: Listado de cierres con filtros
 * - detalleCierre: Detalle completo de un cierre
 * - obtenerPermisos: Permisos del usuario actual
 * - configurarLimites: Configurar límites de usuario
 * - asignarRol: Asignar rol a usuario
 * - listarRoles: Listar roles disponibles
 * - listarAuditoria: Auditoría de operaciones
 *
 * @author Claude Code
 * @date 2025-01-15
 */

require_once "../config/Conexion.php";
require_once "../modelos/CajachicaMejorada.php";

$cajachica = new CajachicaMejorada();

// Validar sesión de usuario
if (!isset($_SESSION["idusuario"])) {
    header('Content-type: application/json; charset=utf-8');
    echo json_encode([
        "success" => false,
        "error" => "Sesión no válida. Por favor, inicia sesión nuevamente."
    ]);
    exit();
}

$idusuario_session = $_SESSION["idusuario"];

// ========================================
// ACCIONES GET (Consultas)
// ========================================

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    header('Content-type: application/json; charset=utf-8');

    switch ($action) {

        // ========== RESUMEN DEL DÍA ==========
        case 'resumenDia':
            try {
                $resumen = $cajachica->obtenerResumenDia();
                echo json_encode([
                    "success" => true,
                    "data" => $resumen
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al obtener resumen: " . $e->getMessage()
                ]);
            }
            break;

        // ========== LISTAR CIERRES ==========
        case 'listarCierres':
            try {
                $filtros = [];

                if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])) {
                    $filtros['fecha_inicio'] = limpiarCadena($_GET['fecha_inicio']);
                    $filtros['fecha_fin'] = limpiarCadena($_GET['fecha_fin']);
                }

                if (isset($_GET['estado_aprobacion']) && $_GET['estado_aprobacion'] != '') {
                    $filtros['estado_aprobacion'] = limpiarCadena($_GET['estado_aprobacion']);
                }

                // Solo ver propias cajas a menos que tenga permiso
                if (!$cajachica->tienePermiso($idusuario_session, 'VER_TODAS_CAJAS')) {
                    $filtros['idusuario'] = $idusuario_session;
                } else {
                    $filtros['ver_todas'] = true;
                }

                $rspta = $cajachica->listarCierres($filtros);
                $data = array();

                while ($reg = $rspta->fetch_object()) {
                    $data[] = array(
                        "idcierre" => $reg->idcierre,
                        "fecha_cierre" => $reg->fecha_cierre,
                        "fecha_hora_cierre" => $reg->fecha_hora_cierre,
                        "saldo_inicial" => number_format($reg->saldo_inicial, 2, '.', ''),
                        "total_ingresos" => number_format($reg->total_ingresos, 2, '.', ''),
                        "total_egresos" => number_format($reg->total_egresos, 2, '.', ''),
                        "total_ventas" => number_format($reg->total_ventas, 2, '.', ''),
                        "total_caja" => number_format($reg->total_caja, 2, '.', ''),
                        "diferencia" => number_format($reg->diferencia, 2, '.', ''),
                        "tipo_diferencia" => $reg->tipo_diferencia,
                        "estado_aprobacion" => $reg->estado_aprobacion,
                        "estado_texto" => $reg->estado_texto,
                        "usuario_apertura" => $reg->nombre_apertura . ' ' . $reg->apellidos_apertura,
                        "usuario_cierre" => $reg->nombre_cierre . ' ' . $reg->apellidos_cierre,
                        "usuario_aprobacion" => $reg->idusuario_aprobacion ? ($reg->nombre_aprobacion . ' ' . $reg->apellidos_aprobacion) : '-',
                        "observaciones" => $reg->observaciones_cierre,
                        "motivo_rechazo" => $reg->motivo_rechazo
                    );
                }

                echo json_encode([
                    "success" => true,
                    "aaData" => $data
                ]);

            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al listar cierres: " . $e->getMessage()
                ]);
            }
            break;

        // ========== DETALLE DE CIERRE ==========
        case 'detalleCierre':
            try {
                $idcierre = isset($_GET['idcierre']) ? limpiarCadena($_GET['idcierre']) : null;

                if (!$idcierre) {
                    echo json_encode(["success" => false, "error" => "ID de cierre no proporcionado"]);
                    exit;
                }

                $cierre = $cajachica->obtenerCierre($idcierre);

                if (!$cierre) {
                    echo json_encode(["success" => false, "error" => "Cierre no encontrado"]);
                    exit;
                }

                // Obtener arqueo
                $rspta_arqueo = $cajachica->obtenerArqueo($idcierre);
                $arqueo = array();
                while ($reg = $rspta_arqueo->fetch_object()) {
                    $arqueo[] = [
                        "denominacion" => $reg->denominacion,
                        "cantidad" => $reg->cantidad,
                        "subtotal" => number_format($reg->subtotal, 2, '.', '')
                    ];
                }

                // Obtener otros pagos
                $rspta_otros = $cajachica->obtenerOtrosPagos($idcierre);
                $otros_pagos = array();
                while ($reg = $rspta_otros->fetch_object()) {
                    $otros_pagos[] = [
                        "tipo_pago" => $reg->tipo_pago,
                        "monto" => number_format($reg->monto, 2, '.', ''),
                        "numero_operacion" => $reg->numero_operacion,
                        "observaciones" => $reg->observaciones
                    ];
                }

                // Formatear cierre
                $cierre_formateado = [
                    "idcierre" => $cierre['idcierre'],
                    "fecha_cierre" => $cierre['fecha_cierre'],
                    "fecha_hora_apertura" => $cierre['fecha_hora_apertura'],
                    "fecha_hora_cierre" => $cierre['fecha_hora_cierre'],
                    "saldo_inicial" => number_format($cierre['saldo_inicial'], 2, '.', ''),
                    "total_ingresos" => number_format($cierre['total_ingresos'], 2, '.', ''),
                    "total_egresos" => number_format($cierre['total_egresos'], 2, '.', ''),
                    "total_ventas" => number_format($cierre['total_ventas'], 2, '.', ''),
                    "total_efectivo_contado" => number_format($cierre['total_efectivo_contado'], 2, '.', ''),
                    "total_caja" => number_format($cierre['total_caja'], 2, '.', ''),
                    "diferencia" => number_format($cierre['diferencia'], 2, '.', ''),
                    "estado_aprobacion" => $cierre['estado_aprobacion'],
                    "usuario_apertura" => $cierre['nombre_apertura'] . ' ' . $cierre['apellidos_apertura'],
                    "usuario_cierre" => $cierre['nombre_cierre'] . ' ' . $cierre['apellidos_cierre'],
                    "usuario_aprobacion" => $cierre['idusuario_aprobacion'] ? ($cierre['nombre_aprobacion'] . ' ' . $cierre['apellidos_aprobacion']) : null,
                    "fecha_aprobacion" => $cierre['fecha_aprobacion'],
                    "observaciones_apertura" => $cierre['observaciones_apertura'],
                    "observaciones_cierre" => $cierre['observaciones_cierre'],
                    "motivo_rechazo" => $cierre['motivo_rechazo'],
                    "arqueo" => $arqueo,
                    "otros_pagos" => $otros_pagos
                ];

                echo json_encode([
                    "success" => true,
                    "data" => $cierre_formateado
                ]);

            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al obtener detalle: " . $e->getMessage()
                ]);
            }
            break;

        // ========== OBTENER PERMISOS DEL USUARIO ==========
        case 'obtenerPermisos':
            try {
                $rspta = $cajachica->obtenerPermisosUsuario($idusuario_session);
                $permisos = array();

                while ($reg = $rspta->fetch_object()) {
                    $permisos[] = $reg->codigo_permiso;
                }

                // Obtener límites
                $limites = $cajachica->obtenerLimitesUsuario($idusuario_session);

                echo json_encode([
                    "success" => true,
                    "permisos" => $permisos,
                    "limites" => $limites
                ]);

            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al obtener permisos: " . $e->getMessage()
                ]);
            }
            break;

        // ========== LISTAR ROLES ==========
        case 'listarRoles':
            try {
                $rspta = $cajachica->listarRoles();
                $roles = array();

                while ($reg = $rspta->fetch_object()) {
                    $roles[] = [
                        "idrol" => $reg->idrol,
                        "nombre_rol" => $reg->nombre_rol,
                        "codigo_rol" => $reg->codigo_rol,
                        "descripcion" => $reg->descripcion,
                        "limite_apertura_default" => number_format($reg->limite_apertura_default, 2, '.', ''),
                        "limite_diferencia_default" => number_format($reg->limite_diferencia_default, 2, '.', ''),
                        "requiere_aprobacion" => $reg->requiere_aprobacion
                    ];
                }

                echo json_encode([
                    "success" => true,
                    "data" => $roles
                ]);

            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al listar roles: " . $e->getMessage()
                ]);
            }
            break;

        // ========== LISTAR AUDITORÍA ==========
        case 'listarAuditoria':
            try {
                $filtros = [];

                if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])) {
                    $filtros['fecha_inicio'] = limpiarCadena($_GET['fecha_inicio']);
                    $filtros['fecha_fin'] = limpiarCadena($_GET['fecha_fin']);
                }

                if (isset($_GET['tipo_operacion']) && $_GET['tipo_operacion'] != '') {
                    $filtros['tipo_operacion'] = limpiarCadena($_GET['tipo_operacion']);
                }

                $rspta = $cajachica->listarAuditoria($filtros);
                $data = array();

                while ($reg = $rspta->fetch_object()) {
                    $data[] = [
                        "idauditoria" => $reg->idauditoria,
                        "tipo_operacion" => $reg->tipo_operacion,
                        "tabla_afectada" => $reg->tabla_afectada,
                        "id_registro" => $reg->id_registro,
                        "usuario" => $reg->nombre . ' ' . $reg->apellidos,
                        "fecha_operacion" => $reg->fecha_operacion,
                        "ip_origen" => $reg->ip_origen,
                        "observaciones" => $reg->observaciones
                    ];
                }

                echo json_encode([
                    "success" => true,
                    "aaData" => $data
                ]);

            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al listar auditoría: " . $e->getMessage()
                ]);
            }
            break;

        default:
            echo json_encode([
                "success" => false,
                "error" => "Acción no reconocida"
            ]);
            break;
    }

    exit();
}

// ========================================
// OPERACIONES POST (Modificaciones)
// ========================================

if (isset($_POST['op']) || isset($_GET["op"])) {
    $op = isset($_POST['op']) ? $_POST['op'] : $_GET["op"];
    header('Content-type: application/json; charset=utf-8');

    // Validar token CSRF para operaciones POST críticas
    $operaciones_criticas = ['aperturarCaja', 'cerrarCaja', 'aprobarCierre', 'rechazarCierre'];
    if (in_array($op, $operaciones_criticas)) {
        if (!validarCSRFAjax()) {
            echo json_encode([
                "success" => false,
                "error" => "Token de seguridad inválido. Por favor, recarga la página."
            ]);
            exit();
        }
    }

    switch ($op) {

        // ========== APERTURAR CAJA ==========
        case 'aperturarCaja':
            try {
                $saldo_inicial = isset($_POST["saldo_inicial"]) ? limpiarCadena($_POST["saldo_inicial"]) : 0;
                $observaciones = isset($_POST["observaciones"]) ? limpiarCadena($_POST["observaciones"]) : null;

                if (!is_numeric($saldo_inicial) || $saldo_inicial < 0) {
                    echo json_encode([
                        "success" => false,
                        "error" => "El saldo inicial debe ser un número válido mayor o igual a 0"
                    ]);
                    exit;
                }

                $resultado = $cajachica->aperturarCaja($saldo_inicial, $idusuario_session, $observaciones);
                echo json_encode($resultado);

            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al aperturar caja: " . $e->getMessage()
                ]);
            }
            break;

        // ========== CERRAR CAJA ==========
        case 'cerrarCaja':
            try {
                $idsaldoini = isset($_POST["idsaldoini"]) ? limpiarCadena($_POST["idsaldoini"]) : null;
                $total_efectivo_contado = isset($_POST["total_efectivo_contado"]) ? limpiarCadena($_POST["total_efectivo_contado"]) : 0;
                $observaciones = isset($_POST["observaciones"]) ? limpiarCadena($_POST["observaciones"]) : null;

                // Arqueo de denominaciones
                $arqueo = [];
                $denominaciones = ['200', '100', '50', '20', '10', '5', '2', '1', '0.5', '0.2', '0.1'];

                foreach ($denominaciones as $denom) {
                    $key = 'denom_' . str_replace('.', '_', $denom);
                    $cantidad = isset($_POST[$key]) ? intval($_POST[$key]) : 0;
                    $subtotal = $cantidad * floatval($denom);

                    if ($cantidad > 0) {
                        $arqueo[] = [
                            'denominacion' => $denom,
                            'cantidad' => $cantidad,
                            'subtotal' => $subtotal
                        ];
                    }
                }

                // Otros medios de pago
                $otros_pagos = [];
                $tipos_pago = ['tarjeta', 'yape', 'plin', 'transferencia', 'otros'];

                foreach ($tipos_pago as $tipo) {
                    $monto = isset($_POST["pago_$tipo"]) ? floatval($_POST["pago_$tipo"]) : 0;
                    $numero_op = isset($_POST["numero_op_$tipo"]) ? limpiarCadena($_POST["numero_op_$tipo"]) : null;

                    if ($monto > 0) {
                        $otros_pagos[] = [
                            'tipo_pago' => $tipo,
                            'monto' => $monto,
                            'numero_operacion' => $numero_op,
                            'observaciones' => null
                        ];
                    }
                }

                $data = [
                    'idusuario' => $idusuario_session,
                    'idsaldoini' => $idsaldoini,
                    'total_efectivo_contado' => $total_efectivo_contado,
                    'arqueo' => $arqueo,
                    'otros_pagos' => $otros_pagos,
                    'observaciones' => $observaciones
                ];

                $resultado = $cajachica->cerrarCaja($data);
                echo json_encode($resultado);

            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al cerrar caja: " . $e->getMessage()
                ]);
            }
            break;

        // ========== APROBAR CIERRE ==========
        case 'aprobarCierre':
            try {
                $idcierre = isset($_POST["idcierre"]) ? limpiarCadena($_POST["idcierre"]) : null;
                $observaciones = isset($_POST["observaciones"]) ? limpiarCadena($_POST["observaciones"]) : null;

                if (!$idcierre) {
                    echo json_encode(["success" => false, "error" => "ID de cierre no proporcionado"]);
                    exit;
                }

                $resultado = $cajachica->aprobarCierre($idcierre, $idusuario_session, $observaciones);
                echo json_encode($resultado);

            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al aprobar cierre: " . $e->getMessage()
                ]);
            }
            break;

        // ========== RECHAZAR CIERRE ==========
        case 'rechazarCierre':
            try {
                $idcierre = isset($_POST["idcierre"]) ? limpiarCadena($_POST["idcierre"]) : null;
                $motivo_rechazo = isset($_POST["motivo_rechazo"]) ? limpiarCadena($_POST["motivo_rechazo"]) : '';

                if (!$idcierre) {
                    echo json_encode(["success" => false, "error" => "ID de cierre no proporcionado"]);
                    exit;
                }

                if (trim($motivo_rechazo) == '') {
                    echo json_encode(["success" => false, "error" => "Debes proporcionar un motivo de rechazo"]);
                    exit;
                }

                $resultado = $cajachica->rechazarCierre($idcierre, $idusuario_session, $motivo_rechazo);
                echo json_encode($resultado);

            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al rechazar cierre: " . $e->getMessage()
                ]);
            }
            break;

        // ========== CONFIGURAR LÍMITES ==========
        case 'configurarLimites':
            try {
                $idusuario_target = isset($_POST["idusuario"]) ? limpiarCadena($_POST["idusuario"]) : null;
                $limite_apertura = isset($_POST["limite_apertura"]) ? floatval($_POST["limite_apertura"]) : 0;
                $limite_diferencia = isset($_POST["limite_diferencia"]) ? floatval($_POST["limite_diferencia"]) : 0;
                $requiere_aprobacion_cierre = isset($_POST["requiere_aprobacion_cierre"]) ? 1 : 0;
                $requiere_aprobacion_diferencia = isset($_POST["requiere_aprobacion_diferencia"]) ? 1 : 0;

                // Verificar permisos (solo administradores o supervisores)
                if (!$cajachica->tienePermiso($idusuario_session, 'APROBAR_CIERRE')) {
                    echo json_encode([
                        "success" => false,
                        "error" => "No tienes permisos para configurar límites"
                    ]);
                    exit;
                }

                $resultado = $cajachica->configurarLimites(
                    $idusuario_target,
                    $limite_apertura,
                    $limite_diferencia,
                    $requiere_aprobacion_cierre,
                    $requiere_aprobacion_diferencia,
                    $idusuario_session
                );

                if ($resultado) {
                    echo json_encode([
                        "success" => true,
                        "message" => "Límites configurados correctamente"
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "error" => "Error al configurar límites"
                    ]);
                }

            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al configurar límites: " . $e->getMessage()
                ]);
            }
            break;

        // ========== ASIGNAR ROL ==========
        case 'asignarRol':
            try {
                $idusuario_target = isset($_POST["idusuario"]) ? limpiarCadena($_POST["idusuario"]) : null;
                $idrol = isset($_POST["idrol"]) ? limpiarCadena($_POST["idrol"]) : null;

                // Verificar permisos
                if (!$cajachica->tienePermiso($idusuario_session, 'APROBAR_CIERRE')) {
                    echo json_encode([
                        "success" => false,
                        "error" => "No tienes permisos para asignar roles"
                    ]);
                    exit;
                }

                $resultado = $cajachica->asignarRol($idusuario_target, $idrol, $idusuario_session);

                if ($resultado) {
                    echo json_encode([
                        "success" => true,
                        "message" => "Rol asignado correctamente"
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "error" => "Error al asignar rol"
                    ]);
                }

            } catch (Exception $e) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error al asignar rol: " . $e->getMessage()
                ]);
            }
            break;

        default:
            echo json_encode([
                "success" => false,
                "error" => "Operación no reconocida"
            ]);
            break;
    }

    exit();
}

// Si no hay acción ni operación, retornar error
header('Content-type: application/json; charset=utf-8');
echo json_encode([
    "success" => false,
    "error" => "No se especificó ninguna acción u operación"
]);
