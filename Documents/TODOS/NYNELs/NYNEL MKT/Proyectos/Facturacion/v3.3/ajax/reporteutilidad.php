<?php
// ========================================
// CONTROLADOR AJAX - REPORTE DE UTILIDAD MEJORADO
// Gestión de análisis de ingresos, egresos y utilidad
// ========================================

// SEGURIDAD: Cargar helpers de validación y CSRF
require_once "../config/ajax_helper.php";
require_once "../modelos/Insumos.php";

$insumos = new Insumos();

// ========== PARÁMETROS DE ENTRADA ==========
$fecha_inicio = isset($_POST["fecha_inicio"]) ? limpiarCadena($_POST["fecha_inicio"]) : "";
$fecha_fin = isset($_POST["fecha_fin"]) ? limpiarCadena($_POST["fecha_fin"]) : "";
$categoria = isset($_POST["categoria"]) ? limpiarCadena($_POST["categoria"]) : "";
$tipo_vista = isset($_POST["tipo_vista"]) ? limpiarCadena($_POST["tipo_vista"]) : "diario";
$idutilidad = isset($_POST["idutilidad"]) ? limpiarCadena($_POST["idutilidad"]) : "";

// ========== ACTION HANDLERS (GET) ==========
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Obtener resumen de período
    if ($action == 'resumenPeriodo') {
        $fecha_inicio = isset($_GET['fecha_inicio']) ? limpiarCadena($_GET['fecha_inicio']) : "";
        $fecha_fin = isset($_GET['fecha_fin']) ? limpiarCadena($_GET['fecha_fin']) : "";
        $categoria = isset($_GET['categoria']) ? limpiarCadena($_GET['categoria']) : "";

        // Consulta de resumen
        $sql = "SELECT
            COALESCE(SUM(CASE WHEN tipodato = 'ingreso' THEN ingreso ELSE 0 END), 0) as total_ingresos,
            COALESCE(SUM(CASE WHEN tipodato = 'gasto' THEN gasto ELSE 0 END), 0) as total_egresos,
            COUNT(CASE WHEN tipodato = 'ingreso' THEN 1 END) as cant_ingresos,
            COUNT(CASE WHEN tipodato = 'gasto' THEN 1 END) as cant_egresos
        FROM insumos ins
        INNER JOIN categoriainsumos ci ON ins.idcategoriai = ci.idcategoriai
        WHERE fecharegistro BETWEEN '$fecha_inicio' AND '$fecha_fin'
        AND ci.descripcionc NOT IN ('tarjeta', 'efectivo total')";

        if (!empty($categoria)) {
            $sql .= " AND ci.descripcionc = '$categoria'";
        }

        $rspta = ejecutarConsultaSimpleFila($sql);

        if ($rspta) {
            $total_ingresos = floatval($rspta['total_ingresos']);
            $total_egresos = floatval($rspta['total_egresos']);
            $utilidad = $total_ingresos - $total_egresos;
            $margen = $total_ingresos > 0 ? ($utilidad / $total_ingresos) * 100 : 0;

            echo json_encode([
                'success' => true,
                'total_ingresos' => number_format($total_ingresos, 2, '.', ''),
                'total_egresos' => number_format($total_egresos, 2, '.', ''),
                'utilidad' => number_format($utilidad, 2, '.', ''),
                'margen' => number_format($margen, 2, '.', ''),
                'cant_ingresos' => intval($rspta['cant_ingresos']),
                'cant_egresos' => intval($rspta['cant_egresos'])
            ]);
        } else {
            echo json_encode(['error' => 'No se pudo obtener el resumen']);
        }
        exit;
    }

    // Obtener datos para gráfico de tendencia
    if ($action == 'datosTendencia') {
        $fecha_inicio = isset($_GET['fecha_inicio']) ? limpiarCadena($_GET['fecha_inicio']) : "";
        $fecha_fin = isset($_GET['fecha_fin']) ? limpiarCadena($_GET['fecha_fin']) : "";
        $tipo_vista = isset($_GET['tipo_vista']) ? limpiarCadena($_GET['tipo_vista']) : "diario";

        // Determinar agrupación según tipo de vista
        $agrupar_por = "";
        switch ($tipo_vista) {
            case 'semanal':
                $agrupar_por = "YEARWEEK(fecharegistro)";
                $formato_fecha = "CONCAT(YEAR(fecharegistro), '-S', WEEK(fecharegistro))";
                break;
            case 'mensual':
                $agrupar_por = "DATE_FORMAT(fecharegistro, '%Y-%m')";
                $formato_fecha = "DATE_FORMAT(fecharegistro, '%Y-%m')";
                break;
            case 'diario':
            default:
                $agrupar_por = "DATE(fecharegistro)";
                $formato_fecha = "DATE_FORMAT(fecharegistro, '%d-%m-%Y')";
                break;
        }

        $sql = "SELECT
            $formato_fecha as periodo,
            COALESCE(SUM(CASE WHEN tipodato = 'ingreso' THEN ingreso ELSE 0 END), 0) as ingresos,
            COALESCE(SUM(CASE WHEN tipodato = 'gasto' THEN gasto ELSE 0 END), 0) as egresos,
            (COALESCE(SUM(CASE WHEN tipodato = 'ingreso' THEN ingreso ELSE 0 END), 0) -
             COALESCE(SUM(CASE WHEN tipodato = 'gasto' THEN gasto ELSE 0 END), 0)) as utilidad
        FROM insumos ins
        INNER JOIN categoriainsumos ci ON ins.idcategoriai = ci.idcategoriai
        WHERE fecharegistro BETWEEN '$fecha_inicio' AND '$fecha_fin'
        AND ci.descripcionc NOT IN ('tarjeta', 'efectivo total')
        GROUP BY $agrupar_por
        ORDER BY fecharegistro ASC";

        $rspta = ejecutarConsulta($sql);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                'periodo' => $reg->periodo,
                'ingresos' => number_format($reg->ingresos, 2, '.', ''),
                'egresos' => number_format($reg->egresos, 2, '.', ''),
                'utilidad' => number_format($reg->utilidad, 2, '.', '')
            );
        }

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // Obtener detalle por período
    if ($action == 'detallePeriodo') {
        $fecha_inicio = isset($_GET['fecha_inicio']) ? limpiarCadena($_GET['fecha_inicio']) : "";
        $fecha_fin = isset($_GET['fecha_fin']) ? limpiarCadena($_GET['fecha_fin']) : "";
        $tipo_vista = isset($_GET['tipo_vista']) ? limpiarCadena($_GET['tipo_vista']) : "diario";

        // Determinar agrupación
        $agrupar_por = "";
        $formato_fecha = "";
        switch ($tipo_vista) {
            case 'semanal':
                $agrupar_por = "YEARWEEK(fecharegistro)";
                $formato_fecha = "CONCAT('Semana ', WEEK(fecharegistro), ' - ', YEAR(fecharegistro))";
                break;
            case 'mensual':
                $agrupar_por = "DATE_FORMAT(fecharegistro, '%Y-%m')";
                $formato_fecha = "DATE_FORMAT(fecharegistro, '%M %Y')";
                break;
            case 'diario':
            default:
                $agrupar_por = "DATE(fecharegistro)";
                $formato_fecha = "DATE_FORMAT(fecharegistro, '%d-%m-%Y')";
                break;
        }

        $sql = "SELECT
            $formato_fecha as periodo_formato,
            DATE(fecharegistro) as fecha_real,
            COALESCE(SUM(CASE WHEN tipodato = 'ingreso' THEN ingreso ELSE 0 END), 0) as ingresos,
            COALESCE(SUM(CASE WHEN tipodato = 'gasto' THEN gasto ELSE 0 END), 0) as egresos,
            (COALESCE(SUM(CASE WHEN tipodato = 'ingreso' THEN ingreso ELSE 0 END), 0) -
             COALESCE(SUM(CASE WHEN tipodato = 'gasto' THEN gasto ELSE 0 END), 0)) as utilidad
        FROM insumos ins
        INNER JOIN categoriainsumos ci ON ins.idcategoriai = ci.idcategoriai
        WHERE fecharegistro BETWEEN '$fecha_inicio' AND '$fecha_fin'
        AND ci.descripcionc NOT IN ('tarjeta', 'efectivo total')
        GROUP BY $agrupar_por
        ORDER BY fecharegistro ASC";

        $rspta = ejecutarConsulta($sql);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $ingresos = floatval($reg->ingresos);
            $egresos = floatval($reg->egresos);
            $utilidad = floatval($reg->utilidad);
            $margen = $ingresos > 0 ? ($utilidad / $ingresos) * 100 : 0;

            $data[] = array(
                $reg->periodo_formato,
                'S/ ' . number_format($ingresos, 2),
                'S/ ' . number_format($egresos, 2),
                'S/ ' . number_format($utilidad, 2),
                number_format($margen, 2) . '%',
                '<button class="btn btn-sm btn-info" onclick="verDetalleTransacciones(\'' . $reg->fecha_real . '\', \'' . $reg->periodo_formato . '\')">' .
                '<i class="fa fa-search"></i></button>'
            );
        }

        $results = array("aaData" => $data);
        echo json_encode($results);
        exit;
    }

    // Obtener análisis por categoría
    if ($action == 'detalleCategoria') {
        $fecha_inicio = isset($_GET['fecha_inicio']) ? limpiarCadena($_GET['fecha_inicio']) : "";
        $fecha_fin = isset($_GET['fecha_fin']) ? limpiarCadena($_GET['fecha_fin']) : "";

        $sql = "SELECT
            ci.descripcionc as categoria,
            ins.tipodato,
            COUNT(*) as cantidad,
            COALESCE(SUM(CASE WHEN tipodato = 'ingreso' THEN ingreso ELSE gasto END), 0) as monto_total
        FROM insumos ins
        INNER JOIN categoriainsumos ci ON ins.idcategoriai = ci.idcategoriai
        WHERE fecharegistro BETWEEN '$fecha_inicio' AND '$fecha_fin'
        AND ci.descripcionc NOT IN ('tarjeta', 'efectivo total')
        GROUP BY ci.descripcionc, ins.tipodato
        ORDER BY monto_total DESC";

        $rspta = ejecutarConsulta($sql);
        $data = array();

        // Calcular total para porcentajes
        $sqlTotal = "SELECT
            COALESCE(SUM(CASE WHEN tipodato = 'ingreso' THEN ingreso ELSE 0 END), 0) +
            COALESCE(SUM(CASE WHEN tipodato = 'gasto' THEN gasto ELSE 0 END), 0) as total_general
        FROM insumos ins
        INNER JOIN categoriainsumos ci ON ins.idcategoriai = ci.idcategoriai
        WHERE fecharegistro BETWEEN '$fecha_inicio' AND '$fecha_fin'
        AND ci.descripcionc NOT IN ('tarjeta', 'efectivo total')";

        $totalGeneral = ejecutarConsultaSimpleFila($sqlTotal);
        $total = floatval($totalGeneral['total_general']);

        while ($reg = $rspta->fetch_object()) {
            $monto = floatval($reg->monto_total);
            $cantidad = intval($reg->cantidad);
            $promedio = $cantidad > 0 ? $monto / $cantidad : 0;
            $porcentaje = $total > 0 ? ($monto / $total) * 100 : 0;

            $tipoBadge = $reg->tipodato == 'ingreso'
                ? '<span class="badge bg-success">INGRESO</span>'
                : '<span class="badge bg-danger">EGRESO</span>';

            $data[] = array(
                $reg->categoria,
                $tipoBadge,
                $cantidad,
                'S/ ' . number_format($monto, 2),
                'S/ ' . number_format($promedio, 2),
                number_format($porcentaje, 2) . '%'
            );
        }

        $results = array("aaData" => $data);
        echo json_encode($results);
        exit;
    }

    // Obtener transacciones detalladas por fecha
    if ($action == 'transaccionesPorFecha') {
        $fecha = isset($_GET['fecha']) ? limpiarCadena($_GET['fecha']) : "";
        $tipo = isset($_GET['tipo']) ? limpiarCadena($_GET['tipo']) : "";

        $sql = "SELECT
            DATE_FORMAT(ins.fecharegistro, '%d-%m-%Y %H:%i') as fecha,
            ci.descripcionc as categoria,
            ins.descripcion,
            ins.acredor,
            CASE WHEN ins.tipodato = 'ingreso' THEN ins.ingreso ELSE ins.gasto END as monto
        FROM insumos ins
        INNER JOIN categoriainsumos ci ON ins.idcategoriai = ci.idcategoriai
        WHERE DATE(ins.fecharegistro) = '$fecha'
        AND ci.descripcionc NOT IN ('tarjeta', 'efectivo total')";

        if ($tipo == 'ingreso' || $tipo == 'egreso') {
            $tipo_dato = $tipo == 'ingreso' ? 'ingreso' : 'gasto';
            $sql .= " AND ins.tipodato = '$tipo_dato'";
        }

        $sql .= " ORDER BY ins.fecharegistro DESC";

        $rspta = ejecutarConsulta($sql);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                'fecha' => $reg->fecha,
                'categoria' => $reg->categoria,
                'descripcion' => $reg->descripcion,
                'acreedor' => $reg->acredor ?: '-',
                'monto' => 'S/ ' . number_format($reg->monto, 2)
            );
        }

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // Listar historial de análisis guardados
    if ($action == 'listarHistorial') {
        $rspta = $insumos->listarutilidad();
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $utilidad = floatval($reg->utilidad);
            $estadoBadge = $reg->estado == '1'
                ? '<span class="badge bg-success">Aprobado</span>'
                : '<span class="badge bg-secondary">Pendiente</span>';

            $utilidadClass = $utilidad >= 0 ? 'text-success' : 'text-danger';

            $acciones = '<div class="btn-group btn-group-sm">';
            $acciones .= '<button class="btn btn-info" onclick="verAnalisis(' . $reg->idutilidad . ')" title="Ver">';
            $acciones .= '<i class="fa fa-eye"></i></button>';
            $acciones .= '<button class="btn btn-primary" onclick="reporteutilidad(' . $reg->idutilidad . ')" title="Reporte">';
            $acciones .= '<i class="fa fa-print"></i></button>';

            if ($reg->estado == '0') {
                $acciones .= '<button class="btn btn-success" onclick="aprobarutilidad(' . $reg->idutilidad . ')" title="Aprobar">';
                $acciones .= '<i class="fa fa-check"></i></button>';
            }

            $acciones .= '<button class="btn btn-warning" onclick="recalcularutilidad(' . $reg->idutilidad . ')" title="Recalcular">';
            $acciones .= '<i class="fa fa-refresh"></i></button>';
            $acciones .= '<button class="btn btn-danger" onclick="eliminarutilidad(' . $reg->idutilidad . ')" title="Eliminar">';
            $acciones .= '<i class="fa fa-trash"></i></button>';
            $acciones .= '</div>';

            $data[] = array(
                $reg->idutilidad,
                $reg->fecha1,
                $reg->fecha2,
                'S/ ' . number_format($reg->totalventas, 2),
                'S/ ' . number_format($reg->totalgastos, 2),
                '<span class="' . $utilidadClass . '">S/ ' . number_format($utilidad, 2) . '</span>',
                number_format($reg->porcentaje, 2) . '%',
                $estadoBadge,
                $acciones
            );
        }

        $results = array("aaData" => $data);
        echo json_encode($results);
        exit;
    }

    // Obtener categorías para filtro
    if ($action == 'listarCategorias') {
        $rspta = $insumos->selectcategoria();
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            if (!in_array(strtolower($reg->descripcionc), ['tarjeta', 'efectivo total'])) {
                $data[] = array(
                    'id' => $reg->idcategoriai,
                    'nombre' => $reg->descripcionc
                );
            }
        }

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
}

// ========== OP HANDLERS (POST) ==========
if (isset($_GET["op"])) {
    switch ($_GET["op"]) {

        case 'guardarAnalisis':
            // SEGURIDAD: Validar token CSRF
            if (!validarCSRFAjax()) {
                echo json_encode(["error" => "Token de seguridad inválido"]);
                exit();
            }

            // Validar fechas
            if (empty($fecha_inicio) || empty($fecha_fin)) {
                echo json_encode(["error" => "Las fechas son obligatorias"]);
                exit();
            }

            // Calcular utilidad
            $rspta = $insumos->calcularuti($fecha_inicio, $fecha_fin);

            if ($rspta) {
                // ========== AUDITORÍA: Registrar análisis ==========
                registrarAuditoria('utilidadgi', 0, [
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin
                ], "Análisis de utilidad generado: {$fecha_inicio} a {$fecha_fin}");

                echo json_encode([
                    "success" => true,
                    "message" => "Análisis guardado correctamente"
                ]);
            } else {
                echo json_encode(["error" => "No se pudo guardar el análisis"]);
            }
            break;

        case 'aprobarAnalisis':
            // SEGURIDAD: Validar token CSRF
            if (!validarCSRFAjax()) {
                echo json_encode(["error" => "Token de seguridad inválido"]);
                exit();
            }

            $rspta = $insumos->aprobarutilidad($idutilidad);

            if ($rspta) {
                registrarAuditoria('utilidadgi', $idutilidad, [], "Análisis de utilidad aprobado");
                echo json_encode([
                    "success" => true,
                    "message" => "Análisis aprobado correctamente"
                ]);
            } else {
                echo json_encode(["error" => "No se pudo aprobar el análisis"]);
            }
            break;

        case 'eliminarAnalisis':
            // SEGURIDAD: Validar token CSRF
            if (!validarCSRFAjax()) {
                echo json_encode(["error" => "Token de seguridad inválido"]);
                exit();
            }

            $rspta = $insumos->eliminarutilidad($idutilidad);

            if ($rspta) {
                registrarAuditoria('utilidadgi', $idutilidad, [], "Análisis de utilidad eliminado");
                echo json_encode([
                    "success" => true,
                    "message" => "Análisis eliminado correctamente"
                ]);
            } else {
                echo json_encode(["error" => "No se pudo eliminar el análisis"]);
            }
            break;
    }
}
?>
