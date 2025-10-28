<?php
/**
 * Controlador AJAX para módulo de Importaciones
 * Maneja las operaciones de registro y gestión de importaciones con DUA e Invoice
 *
 * @package    Facturacion
 * @subpackage AJAX Controllers
 * @author     Sistema de Facturación
 * @version    1.0.0
 */

// SEGURIDAD: Usar sesión segura y helpers de validación
require_once "../config/Conexion.php";
require_once "../config/ajax_helper.php";
iniciarSesionSegura();

require_once "../modelos/Importacion.php";

// Instanciar modelo
$importacion = new Importacion();

// Obtener operación
$op = isset($_GET['op']) ? limpiarCadena($_GET['op']) : '';

// =============================================================================
// SWITCH PRINCIPAL - Enrutamiento de operaciones
// =============================================================================

switch ($op) {

    // =========================================================================
    // OPERACIÓN: Guardar o editar importación
    // =========================================================================
    case 'guardaryeditar':
        // SEGURIDAD: Validar token CSRF
        if (!validarCSRFAjax()) {
            echo json_encode([
                'success' => false,
                'error' => 'Token de seguridad inválido'
            ]);
            exit();
        }

        // Recibir y sanitizar parámetros de cabecera
        $idimportacion = isset($_POST['idimportacion']) ? limpiarCadena($_POST['idimportacion']) : '';
        $idempresa = $_SESSION['idempresa'];
        $idusuario = $_SESSION['idusuario'];

        // Datos del DUA
        $numero_dua = isset($_POST['numero_dua']) ? limpiarCadena($_POST['numero_dua']) : '';
        $fecha_dua = isset($_POST['fecha_dua']) ? limpiarCadena($_POST['fecha_dua']) : '';
        $fecha_llegada = isset($_POST['fecha_llegada']) && !empty($_POST['fecha_llegada']) ? limpiarCadena($_POST['fecha_llegada']) : null;
        $aduana = isset($_POST['aduana']) ? limpiarCadena($_POST['aduana']) : '';
        $agente_aduanero = isset($_POST['agente_aduanero']) ? limpiarCadena($_POST['agente_aduanero']) : '';
        $regimen_aduanero = isset($_POST['regimen_aduanero']) ? limpiarCadena($_POST['regimen_aduanero']) : '10';

        // Datos del Invoice
        $numero_invoice = isset($_POST['numero_invoice']) ? limpiarCadena($_POST['numero_invoice']) : '';
        $fecha_invoice = isset($_POST['fecha_invoice']) ? limpiarCadena($_POST['fecha_invoice']) : '';
        $proveedor_extranjero = isset($_POST['proveedor_extranjero']) ? limpiarCadena($_POST['proveedor_extranjero']) : '';
        $pais_origen = isset($_POST['pais_origen']) ? limpiarCadena($_POST['pais_origen']) : 'CN';
        $moneda_invoice = isset($_POST['moneda_invoice']) ? limpiarCadena($_POST['moneda_invoice']) : 'USD';

        // Valores y costos
        $incoterm = isset($_POST['incoterm']) ? limpiarCadena($_POST['incoterm']) : 'FOB';
        $valor_fob = isset($_POST['valor_fob']) ? limpiarCadena($_POST['valor_fob']) : 0.00;
        $valor_flete = isset($_POST['valor_flete']) ? limpiarCadena($_POST['valor_flete']) : 0.00;
        $valor_seguro = isset($_POST['valor_seguro']) ? limpiarCadena($_POST['valor_seguro']) : 0.00;
        $tipo_cambio = isset($_POST['tipo_cambio']) ? limpiarCadena($_POST['tipo_cambio']) : 1.0000;
        $moneda_local = isset($_POST['moneda_local']) ? limpiarCadena($_POST['moneda_local']) : 'PEN';

        // Tributos
        $derechos_aduaneros = isset($_POST['derechos_aduaneros']) ? limpiarCadena($_POST['derechos_aduaneros']) : 0.00;
        $igv_importacion = isset($_POST['igv_importacion']) ? limpiarCadena($_POST['igv_importacion']) : 0.00;
        $ipm = isset($_POST['ipm']) ? limpiarCadena($_POST['ipm']) : 0.00;
        $percepcion_igv = isset($_POST['percepcion_igv']) ? limpiarCadena($_POST['percepcion_igv']) : 0.00;
        $otros_tributos = isset($_POST['otros_tributos']) ? limpiarCadena($_POST['otros_tributos']) : 0.00;

        // Gastos
        $gastos_despacho = isset($_POST['gastos_despacho']) ? limpiarCadena($_POST['gastos_despacho']) : 0.00;
        $gastos_transporte_local = isset($_POST['gastos_transporte_local']) ? limpiarCadena($_POST['gastos_transporte_local']) : 0.00;
        $gastos_almacenaje = isset($_POST['gastos_almacenaje']) ? limpiarCadena($_POST['gastos_almacenaje']) : 0.00;
        $otros_gastos = isset($_POST['otros_gastos']) ? limpiarCadena($_POST['otros_gastos']) : 0.00;

        // Observaciones
        $observaciones = isset($_POST['observaciones']) ? limpiarCadena($_POST['observaciones']) : '';
        $ruta_documentos = isset($_POST['ruta_documentos']) ? limpiarCadena($_POST['ruta_documentos']) : null;

        try {
            if (empty($idimportacion)) {
                // INSERTAR nueva importación
                $rspta = $importacion->insertar(
                    $idempresa, $idusuario, $numero_dua, $fecha_dua, $fecha_llegada, $aduana,
                    $agente_aduanero, $regimen_aduanero, $numero_invoice, $fecha_invoice,
                    $proveedor_extranjero, $pais_origen, $moneda_invoice, $incoterm,
                    $valor_fob, $valor_flete, $valor_seguro, $tipo_cambio, $moneda_local,
                    $derechos_aduaneros, $igv_importacion, $ipm, $percepcion_igv, $otros_tributos,
                    $gastos_despacho, $gastos_transporte_local, $gastos_almacenaje, $otros_gastos,
                    $observaciones, $ruta_documentos
                );

                if ($rspta) {
                    // Obtener ID de la importación recién creada
                    $result = ejecutarConsulta("SELECT LAST_INSERT_ID() as id");
                    $row = $result->fetch_assoc();
                    $nuevo_id = $row['id'];

                    // Auditoría
                    registrarOperacionCreate('importacion', "DUA {$numero_dua}", [
                        'numero_invoice' => $numero_invoice,
                        'proveedor' => $proveedor_extranjero,
                        'valor_fob' => $valor_fob,
                        'valor_cif' => ($valor_fob + $valor_flete + $valor_seguro)
                    ], "Importación DUA {$numero_dua} registrada exitosamente");

                    echo json_encode([
                        'success' => true,
                        'mensaje' => 'Importación registrada correctamente',
                        'idimportacion' => $nuevo_id
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => 'No se pudo registrar la importación'
                    ]);
                }
            } else {
                // ACTUALIZAR importación existente
                $rspta = $importacion->editar(
                    $idimportacion, $numero_dua, $fecha_dua, $fecha_llegada, $aduana,
                    $agente_aduanero, $regimen_aduanero, $numero_invoice, $fecha_invoice,
                    $proveedor_extranjero, $pais_origen, $moneda_invoice, $incoterm,
                    $valor_fob, $valor_flete, $valor_seguro, $tipo_cambio, $moneda_local,
                    $derechos_aduaneros, $igv_importacion, $ipm, $percepcion_igv, $otros_tributos,
                    $gastos_despacho, $gastos_transporte_local, $gastos_almacenaje, $otros_gastos,
                    $observaciones, $ruta_documentos
                );

                if ($rspta) {
                    // Auditoría
                    registrarOperacionUpdate('importacion', $idimportacion, [], "Importación DUA {$numero_dua} actualizada");

                    echo json_encode([
                        'success' => true,
                        'mensaje' => 'Importación actualizada correctamente'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => 'No se pudo actualizar la importación'
                    ]);
                }
            }
        } catch (Exception $e) {
            error_log("Error en guardaryeditar importación: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ]);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Agregar detalle de producto
    // =========================================================================
    case 'agregarDetalle':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idimportacion = isset($_POST['idimportacion']) ? limpiarCadena($_POST['idimportacion']) : '';
        $idarticulo = isset($_POST['idarticulo']) && !empty($_POST['idarticulo']) ? limpiarCadena($_POST['idarticulo']) : null;
        $descripcion_producto = isset($_POST['descripcion_producto']) ? limpiarCadena($_POST['descripcion_producto']) : '';
        $marca = isset($_POST['marca']) ? limpiarCadena($_POST['marca']) : '';
        $modelo = isset($_POST['modelo']) ? limpiarCadena($_POST['modelo']) : '';
        $partida_arancelaria = isset($_POST['partida_arancelaria']) ? limpiarCadena($_POST['partida_arancelaria']) : '';
        $descripcion_partida = isset($_POST['descripcion_partida']) ? limpiarCadena($_POST['descripcion_partida']) : '';
        $cantidad = isset($_POST['cantidad']) ? limpiarCadena($_POST['cantidad']) : 0.00;
        $unidad_medida = isset($_POST['unidad_medida']) ? limpiarCadena($_POST['unidad_medida']) : 'UND';
        $peso_bruto_kg = isset($_POST['peso_bruto_kg']) ? limpiarCadena($_POST['peso_bruto_kg']) : 0.00;
        $peso_neto_kg = isset($_POST['peso_neto_kg']) ? limpiarCadena($_POST['peso_neto_kg']) : 0.00;
        $precio_unitario_fob = isset($_POST['precio_unitario_fob']) ? limpiarCadena($_POST['precio_unitario_fob']) : 0.00;

        $rspta = $importacion->insertarDetalle(
            $idimportacion, $idarticulo, $descripcion_producto, $marca, $modelo,
            $partida_arancelaria, $descripcion_partida, $cantidad, $unidad_medida,
            $peso_bruto_kg, $peso_neto_kg, $precio_unitario_fob
        );

        if ($rspta) {
            echo json_encode(['success' => true, 'mensaje' => 'Producto agregado correctamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo agregar el producto']);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Listar detalles de importación
    // =========================================================================
    case 'listarDetalles':
        $idimportacion = isset($_GET['idimportacion']) ? limpiarCadena($_GET['idimportacion']) : '';

        $rspta = $importacion->listarDetalles($idimportacion);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                'idimportacion_detalle' => $reg->idimportacion_detalle,
                'idarticulo' => $reg->idarticulo,
                'codigo_articulo' => $reg->codigo_articulo,
                'nombre_articulo' => $reg->nombre_articulo,
                'descripcion_producto' => $reg->descripcion_producto,
                'marca' => $reg->marca,
                'modelo' => $reg->modelo,
                'partida_arancelaria' => $reg->partida_arancelaria,
                'descripcion_partida' => $reg->descripcion_partida,
                'cantidad' => $reg->cantidad,
                'unidad_medida' => $reg->unidad_medida,
                'peso_bruto_kg' => $reg->peso_bruto_kg,
                'peso_neto_kg' => $reg->peso_neto_kg,
                'precio_unitario_fob' => $reg->precio_unitario_fob,
                'valor_total_fob' => $reg->valor_total_fob,
                'costo_unitario_final_soles' => $reg->costo_unitario_final_soles,
                'costo_total_final_soles' => $reg->costo_total_final_soles
            );
        }

        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // =========================================================================
    // OPERACIÓN: Eliminar detalle
    // =========================================================================
    case 'eliminarDetalle':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idimportacion_detalle = isset($_POST['idimportacion_detalle']) ? limpiarCadena($_POST['idimportacion_detalle']) : '';

        $rspta = $importacion->eliminarDetalle($idimportacion_detalle);

        if ($rspta) {
            echo json_encode(['success' => true, 'mensaje' => 'Producto eliminado']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo eliminar el producto']);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Distribuir costos proporcionalmente
    // =========================================================================
    case 'distribuirCostos':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idimportacion = isset($_POST['idimportacion']) ? limpiarCadena($_POST['idimportacion']) : '';

        try {
            $rspta = $importacion->distribuirCostos($idimportacion);

            if ($rspta) {
                echo json_encode(['success' => true, 'mensaje' => 'Costos distribuidos correctamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo distribuir los costos']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Calcular totales
    // =========================================================================
    case 'calcularTotales':
        $idimportacion = isset($_GET['idimportacion']) ? limpiarCadena($_GET['idimportacion']) : '';

        $totales = $importacion->calcularTotales($idimportacion);

        if ($totales) {
            echo json_encode(['success' => true, 'data' => $totales]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudieron calcular los totales']);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Listar importaciones
    // =========================================================================
    case 'listar':
        $idempresa = $_SESSION['idempresa'];
        $rspta = $importacion->listar($idempresa);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $badge_estado = ($reg->estado == '1')
                ? '<span class="badge bg-success">Activa</span>'
                : '<span class="badge bg-danger">Anulada</span>';

            $acciones = '<div class="btn-group btn-group-sm" role="group">' .
                '<button class="btn btn-primary" onclick="mostrarImportacion(' . $reg->idimportacion . ')" title="Ver/Editar">' .
                '<i class="fa fa-eye"></i>' .
                '</button>' .
                '<button class="btn btn-info" onclick="verDetalles(' . $reg->idimportacion . ')" title="Detalles">' .
                '<i class="fa fa-list"></i>' .
                '</button>';

            if ($reg->estado == '1') {
                $acciones .= '<button class="btn btn-danger" onclick="anularImportacion(' . $reg->idimportacion . ')" title="Anular">' .
                    '<i class="fa fa-ban"></i>' .
                    '</button>';
            }

            $acciones .= '</div>';

            $data[] = array(
                "0" => $reg->fecha_dua,
                "1" => $reg->numero_dua,
                "2" => $reg->numero_invoice,
                "3" => $reg->proveedor_extranjero,
                "4" => $reg->pais_origen,
                "5" => number_format($reg->valor_fob, 2),
                "6" => number_format($reg->valor_cif, 2),
                "7" => number_format($reg->costo_total_soles, 2),
                "8" => $reg->total_items,
                "9" => $badge_estado,
                "10" => $acciones
            );
        }

        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );

        echo json_encode($results);
        break;

    // =========================================================================
    // OPERACIÓN: Mostrar una importación
    // =========================================================================
    case 'mostrar':
        $idimportacion = isset($_GET['idimportacion']) ? limpiarCadena($_GET['idimportacion']) : '';

        $rspta = $importacion->mostrar($idimportacion);

        if ($rspta) {
            echo json_encode(['success' => true, 'data' => $rspta]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Importación no encontrada']);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Anular importación
    // =========================================================================
    case 'anular':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idimportacion = isset($_POST['idimportacion']) ? limpiarCadena($_POST['idimportacion']) : '';

        $rspta = $importacion->anular($idimportacion);

        if ($rspta) {
            registrarOperacionAnular('importacion', $idimportacion, [], "Importación #{$idimportacion} anulada");
            echo json_encode(['success' => true, 'mensaje' => 'Importación anulada correctamente']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo anular la importación']);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Buscar partida arancelaria
    // =========================================================================
    case 'buscarPartidaArancelaria':
        $codigo = isset($_GET['codigo']) ? limpiarCadena($_GET['codigo']) : '';

        $partida = $importacion->obtenerPartidaArancelaria($codigo);

        if ($partida) {
            echo json_encode(['success' => true, 'data' => $partida]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Partida arancelaria no encontrada']);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Listar partidas arancelarias
    // =========================================================================
    case 'listarPartidasArancelarias':
        $busqueda = isset($_GET['busqueda']) ? limpiarCadena($_GET['busqueda']) : null;

        $rspta = $importacion->listarPartidasArancelarias($busqueda);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                'codigo' => $reg->codigo,
                'descripcion' => $reg->descripcion,
                'tasa_advalorem' => $reg->tasa_advalorem
            );
        }

        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // =========================================================================
    // OPERACIÓN: Generar compra desde importación
    // =========================================================================
    case 'generarCompra':
        if (!validarCSRFAjax()) {
            echo json_encode(['success' => false, 'error' => 'Token inválido']);
            exit();
        }

        $idimportacion = isset($_POST['idimportacion']) ? limpiarCadena($_POST['idimportacion']) : '';
        $idproveedor = isset($_POST['idproveedor']) ? limpiarCadena($_POST['idproveedor']) : '';
        $tipo_comprobante = isset($_POST['tipo_comprobante']) ? limpiarCadena($_POST['tipo_comprobante']) : '01';
        $serie = isset($_POST['serie']) ? limpiarCadena($_POST['serie']) : '';
        $numero = isset($_POST['numero']) ? limpiarCadena($_POST['numero']) : '';

        try {
            $idingreso = $importacion->generarCompraDesdeImportacion($idimportacion, $idproveedor, $tipo_comprobante, $serie, $numero);

            if ($idingreso) {
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Compra generada correctamente',
                    'idingreso' => $idingreso
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo generar la compra']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Estadísticas por período
    // =========================================================================
    case 'estadisticasPeriodo':
        $idempresa = $_SESSION['idempresa'];
        $fecha_inicio = isset($_GET['fecha_inicio']) ? limpiarCadena($_GET['fecha_inicio']) : '';
        $fecha_fin = isset($_GET['fecha_fin']) ? limpiarCadena($_GET['fecha_fin']) : '';

        $estadisticas = $importacion->estadisticasPorPeriodo($idempresa, $fecha_inicio, $fecha_fin);

        if ($estadisticas) {
            echo json_encode(['success' => true, 'data' => $estadisticas]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudieron obtener las estadísticas']);
        }
        break;

    // =========================================================================
    // OPERACIÓN: Top proveedores
    // =========================================================================
    case 'topProveedores':
        $idempresa = $_SESSION['idempresa'];
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;

        $rspta = $importacion->topProveedores($idempresa, $limite);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                'proveedor' => $reg->proveedor_extranjero,
                'pais' => $reg->pais_origen,
                'total_importaciones' => $reg->total_importaciones,
                'total_fob' => $reg->total_fob,
                'total_costo_soles' => $reg->total_costo_soles
            );
        }

        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // =========================================================================
    // OPERACIÓN: Productos más importados
    // =========================================================================
    case 'productosMasImportados':
        $idempresa = $_SESSION['idempresa'];
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;

        $rspta = $importacion->productosMasImportados($idempresa, $limite);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                'partida' => $reg->partida_arancelaria,
                'descripcion' => $reg->descripcion_producto,
                'veces_importado' => $reg->veces_importado,
                'cantidad_total' => $reg->cantidad_total,
                'precio_promedio_fob' => $reg->precio_promedio_fob,
                'costo_total_acumulado' => $reg->costo_total_acumulado
            );
        }

        echo json_encode(['success' => true, 'data' => $data]);
        break;

    // =========================================================================
    // OPERACIÓN NO RECONOCIDA
    // =========================================================================
    default:
        echo json_encode(['success' => false, 'error' => 'Operación no válida']);
        break;
}
?>
