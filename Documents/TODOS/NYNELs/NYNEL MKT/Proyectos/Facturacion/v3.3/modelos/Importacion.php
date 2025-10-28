<?php
/**
 * Modelo Importacion - Gestión de importaciones con DUA e Invoice
 * Maneja el registro de importaciones de mercancía con sus costos y distribución
 *
 * @package    Facturacion
 * @subpackage Modelos
 * @author     Sistema de Facturación
 * @version    1.0.0
 */

// SEGURIDAD: Prevenir acceso directo
if (strlen(session_id()) < 1) {
    session_start();
}

class Importacion
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Insertar nueva importación
     */
    public function insertar(
        $idempresa, $idusuario, $numero_dua, $fecha_dua, $fecha_llegada, $aduana,
        $agente_aduanero, $regimen_aduanero, $numero_invoice, $fecha_invoice,
        $proveedor_extranjero, $pais_origen, $moneda_invoice, $incoterm,
        $valor_fob, $valor_flete, $valor_seguro, $tipo_cambio, $moneda_local,
        $derechos_aduaneros, $igv_importacion, $ipm, $percepcion_igv, $otros_tributos,
        $gastos_despacho, $gastos_transporte_local, $gastos_almacenaje, $otros_gastos,
        $observaciones, $ruta_documentos
    ) {
        // Calcular costo total en moneda extranjera
        $costo_total_moneda_ext = $valor_fob + $valor_flete + $valor_seguro +
                                   $derechos_aduaneros + $igv_importacion + $ipm +
                                   $percepcion_igv + $otros_tributos;

        // Calcular costo total en soles
        $costo_total_soles = ($costo_total_moneda_ext * $tipo_cambio) +
                             $gastos_despacho + $gastos_transporte_local +
                             $gastos_almacenaje + $otros_gastos;

        $sql = "INSERT INTO importacion (
            idempresa, idusuario, numero_dua, fecha_dua, fecha_llegada, aduana,
            agente_aduanero, regimen_aduanero, numero_invoice, fecha_invoice,
            proveedor_extranjero, pais_origen, moneda_invoice, incoterm,
            valor_fob, valor_flete, valor_seguro, tipo_cambio, moneda_local,
            derechos_aduaneros, igv_importacion, ipm, percepcion_igv, otros_tributos,
            gastos_despacho, gastos_transporte_local, gastos_almacenaje, otros_gastos,
            costo_total_moneda_ext, costo_total_soles, observaciones, ruta_documentos,
            estado
        ) VALUES (
            '$idempresa', '$idusuario', '$numero_dua', '$fecha_dua', " .
            ($fecha_llegada ? "'$fecha_llegada'" : "NULL") . ", '$aduana',
            '$agente_aduanero', '$regimen_aduanero', '$numero_invoice', '$fecha_invoice',
            '$proveedor_extranjero', '$pais_origen', '$moneda_invoice', '$incoterm',
            '$valor_fob', '$valor_flete', '$valor_seguro', '$tipo_cambio', '$moneda_local',
            '$derechos_aduaneros', '$igv_importacion', '$ipm', '$percepcion_igv', '$otros_tributos',
            '$gastos_despacho', '$gastos_transporte_local', '$gastos_almacenaje', '$otros_gastos',
            '$costo_total_moneda_ext', '$costo_total_soles', '$observaciones', " .
            ($ruta_documentos ? "'$ruta_documentos'" : "NULL") . ", 1
        )";

        return ejecutarConsulta($sql);
    }

    /**
     * Editar importación existente
     */
    public function editar(
        $idimportacion, $numero_dua, $fecha_dua, $fecha_llegada, $aduana,
        $agente_aduanero, $regimen_aduanero, $numero_invoice, $fecha_invoice,
        $proveedor_extranjero, $pais_origen, $moneda_invoice, $incoterm,
        $valor_fob, $valor_flete, $valor_seguro, $tipo_cambio, $moneda_local,
        $derechos_aduaneros, $igv_importacion, $ipm, $percepcion_igv, $otros_tributos,
        $gastos_despacho, $gastos_transporte_local, $gastos_almacenaje, $otros_gastos,
        $observaciones, $ruta_documentos
    ) {
        // Recalcular totales
        $costo_total_moneda_ext = $valor_fob + $valor_flete + $valor_seguro +
                                   $derechos_aduaneros + $igv_importacion + $ipm +
                                   $percepcion_igv + $otros_tributos;

        $costo_total_soles = ($costo_total_moneda_ext * $tipo_cambio) +
                             $gastos_despacho + $gastos_transporte_local +
                             $gastos_almacenaje + $otros_gastos;

        $sql = "UPDATE importacion SET
            numero_dua = '$numero_dua',
            fecha_dua = '$fecha_dua',
            fecha_llegada = " . ($fecha_llegada ? "'$fecha_llegada'" : "NULL") . ",
            aduana = '$aduana',
            agente_aduanero = '$agente_aduanero',
            regimen_aduanero = '$regimen_aduanero',
            numero_invoice = '$numero_invoice',
            fecha_invoice = '$fecha_invoice',
            proveedor_extranjero = '$proveedor_extranjero',
            pais_origen = '$pais_origen',
            moneda_invoice = '$moneda_invoice',
            incoterm = '$incoterm',
            valor_fob = '$valor_fob',
            valor_flete = '$valor_flete',
            valor_seguro = '$valor_seguro',
            tipo_cambio = '$tipo_cambio',
            moneda_local = '$moneda_local',
            derechos_aduaneros = '$derechos_aduaneros',
            igv_importacion = '$igv_importacion',
            ipm = '$ipm',
            percepcion_igv = '$percepcion_igv',
            otros_tributos = '$otros_tributos',
            gastos_despacho = '$gastos_despacho',
            gastos_transporte_local = '$gastos_transporte_local',
            gastos_almacenaje = '$gastos_almacenaje',
            otros_gastos = '$otros_gastos',
            costo_total_moneda_ext = '$costo_total_moneda_ext',
            costo_total_soles = '$costo_total_soles',
            observaciones = '$observaciones',
            ruta_documentos = " . ($ruta_documentos ? "'$ruta_documentos'" : "NULL") . "
        WHERE idimportacion = '$idimportacion'";

        return ejecutarConsulta($sql);
    }

    /**
     * Anular importación (cambiar estado a 0)
     */
    public function anular($idimportacion)
    {
        $sql = "UPDATE importacion SET estado = 0 WHERE idimportacion = '$idimportacion'";
        return ejecutarConsulta($sql);
    }

    /**
     * Mostrar datos de una importación específica
     */
    public function mostrar($idimportacion)
    {
        $sql = "SELECT * FROM importacion WHERE idimportacion = '$idimportacion'";
        return ejecutarConsultaSimpleFila($sql);
    }

    /**
     * Listar todas las importaciones de una empresa
     */
    public function listar($idempresa)
    {
        $sql = "SELECT
            i.*,
            u.nombre AS usuario_nombre,
            ing.tipo_comprobante,
            ing.serie_comprobante,
            ing.num_comprobante,
            COUNT(DISTINCT d.idimportacion_detalle) AS total_items
        FROM importacion i
        INNER JOIN usuario u ON i.idusuario = u.idusuario
        LEFT JOIN ingreso ing ON i.idingreso = ing.idingreso
        LEFT JOIN importacion_detalle d ON i.idimportacion = d.idimportacion AND d.estado = 1
        WHERE i.idempresa = '$idempresa'
        GROUP BY i.idimportacion
        ORDER BY i.fecha_dua DESC, i.idimportacion DESC";

        return ejecutarConsulta($sql);
    }

    /**
     * Listar importaciones con filtros
     */
    public function listarConFiltros($idempresa, $fecha_inicio = null, $fecha_fin = null, $proveedor = null, $numero_dua = null)
    {
        $sql = "SELECT
            i.*,
            u.nombre AS usuario_nombre,
            COUNT(DISTINCT d.idimportacion_detalle) AS total_items
        FROM importacion i
        INNER JOIN usuario u ON i.idusuario = u.idusuario
        LEFT JOIN importacion_detalle d ON i.idimportacion = d.idimportacion AND d.estado = 1
        WHERE i.idempresa = '$idempresa'";

        if ($fecha_inicio && $fecha_fin) {
            $sql .= " AND i.fecha_dua BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        }

        if ($proveedor) {
            $sql .= " AND i.proveedor_extranjero LIKE '%$proveedor%'";
        }

        if ($numero_dua) {
            $sql .= " AND i.numero_dua LIKE '%$numero_dua%'";
        }

        $sql .= " GROUP BY i.idimportacion ORDER BY i.fecha_dua DESC";

        return ejecutarConsulta($sql);
    }

    // ==========================================================================
    // MÉTODOS PARA DETALLE DE IMPORTACIÓN
    // ==========================================================================

    /**
     * Insertar detalle de importación
     */
    public function insertarDetalle(
        $idimportacion, $idarticulo, $descripcion_producto, $marca, $modelo,
        $partida_arancelaria, $descripcion_partida, $cantidad, $unidad_medida,
        $peso_bruto_kg, $peso_neto_kg, $precio_unitario_fob
    ) {
        $sql = "INSERT INTO importacion_detalle (
            idimportacion, idarticulo, descripcion_producto, marca, modelo,
            partida_arancelaria, descripcion_partida, cantidad, unidad_medida,
            peso_bruto_kg, peso_neto_kg, precio_unitario_fob, estado
        ) VALUES (
            '$idimportacion', " . ($idarticulo ? "'$idarticulo'" : "NULL") . ",
            '$descripcion_producto', '$marca', '$modelo',
            '$partida_arancelaria', '$descripcion_partida', '$cantidad', '$unidad_medida',
            '$peso_bruto_kg', '$peso_neto_kg', '$precio_unitario_fob', 1
        )";

        return ejecutarConsulta($sql);
    }

    /**
     * Actualizar detalle de importación
     */
    public function actualizarDetalle(
        $idimportacion_detalle, $idarticulo, $descripcion_producto, $marca, $modelo,
        $partida_arancelaria, $descripcion_partida, $cantidad, $unidad_medida,
        $peso_bruto_kg, $peso_neto_kg, $precio_unitario_fob
    ) {
        $sql = "UPDATE importacion_detalle SET
            idarticulo = " . ($idarticulo ? "'$idarticulo'" : "NULL") . ",
            descripcion_producto = '$descripcion_producto',
            marca = '$marca',
            modelo = '$modelo',
            partida_arancelaria = '$partida_arancelaria',
            descripcion_partida = '$descripcion_partida',
            cantidad = '$cantidad',
            unidad_medida = '$unidad_medida',
            peso_bruto_kg = '$peso_bruto_kg',
            peso_neto_kg = '$peso_neto_kg',
            precio_unitario_fob = '$precio_unitario_fob'
        WHERE idimportacion_detalle = '$idimportacion_detalle'";

        return ejecutarConsulta($sql);
    }

    /**
     * Eliminar detalle (cambiar estado a 0)
     */
    public function eliminarDetalle($idimportacion_detalle)
    {
        $sql = "UPDATE importacion_detalle SET estado = 0 WHERE idimportacion_detalle = '$idimportacion_detalle'";
        return ejecutarConsulta($sql);
    }

    /**
     * Listar detalles de una importación
     */
    public function listarDetalles($idimportacion)
    {
        $sql = "SELECT
            d.*,
            a.nombre AS nombre_articulo,
            a.codigo AS codigo_articulo,
            a.stock AS stock_actual,
            p.descripcion AS descripcion_partida_completa,
            p.tasa_advalorem
        FROM importacion_detalle d
        LEFT JOIN articulo a ON d.idarticulo = a.idarticulo
        LEFT JOIN partida_arancelaria p ON d.partida_arancelaria = p.codigo
        WHERE d.idimportacion = '$idimportacion' AND d.estado = 1
        ORDER BY d.idimportacion_detalle";

        return ejecutarConsulta($sql);
    }

    // ==========================================================================
    // MÉTODOS PARA COSTOS ADICIONALES
    // ==========================================================================

    /**
     * Insertar costo adicional
     */
    public function insertarCosto(
        $idimportacion, $tipo_costo, $concepto, $proveedor, $numero_documento,
        $monto, $moneda, $tipo_cambio_aplicado, $fecha_documento, $observaciones
    ) {
        $monto_soles = $monto * $tipo_cambio_aplicado;

        $sql = "INSERT INTO importacion_costos (
            idimportacion, tipo_costo, concepto, proveedor, numero_documento,
            monto, moneda, tipo_cambio_aplicado, monto_soles,
            fecha_documento, observaciones, estado
        ) VALUES (
            '$idimportacion', '$tipo_costo', '$concepto', '$proveedor', '$numero_documento',
            '$monto', '$moneda', '$tipo_cambio_aplicado', '$monto_soles',
            " . ($fecha_documento ? "'$fecha_documento'" : "NULL") . ",
            '$observaciones', 1
        )";

        return ejecutarConsulta($sql);
    }

    /**
     * Listar costos de una importación
     */
    public function listarCostos($idimportacion)
    {
        $sql = "SELECT * FROM importacion_costos
                WHERE idimportacion = '$idimportacion' AND estado = 1
                ORDER BY fecha_registro DESC";

        return ejecutarConsulta($sql);
    }

    /**
     * Eliminar costo adicional
     */
    public function eliminarCosto($idimportacion_costo)
    {
        $sql = "UPDATE importacion_costos SET estado = 0 WHERE idimportacion_costo = '$idimportacion_costo'";
        return ejecutarConsulta($sql);
    }

    // ==========================================================================
    // MÉTODOS DE CÁLCULO Y DISTRIBUCIÓN
    // ==========================================================================

    /**
     * Distribuir costos proporcionalmente entre productos
     * Llama al procedimiento almacenado sp_distribuir_costos_importacion
     */
    public function distribuirCostos($idimportacion)
    {
        $sql = "CALL sp_distribuir_costos_importacion('$idimportacion')";
        return ejecutarConsulta($sql);
    }

    /**
     * Calcular totales de una importación
     */
    public function calcularTotales($idimportacion)
    {
        $sql = "SELECT
            i.valor_fob,
            i.valor_flete,
            i.valor_seguro,
            i.valor_cif,
            i.derechos_aduaneros,
            i.igv_importacion,
            i.ipm,
            i.percepcion_igv,
            i.otros_tributos,
            i.gastos_despacho,
            i.gastos_transporte_local,
            i.gastos_almacenaje,
            i.otros_gastos,
            i.costo_total_moneda_ext,
            i.costo_total_soles,
            i.tipo_cambio,
            COUNT(d.idimportacion_detalle) AS total_items,
            SUM(d.cantidad) AS cantidad_total,
            SUM(d.peso_bruto_kg) AS peso_bruto_total,
            SUM(d.peso_neto_kg) AS peso_neto_total
        FROM importacion i
        LEFT JOIN importacion_detalle d ON i.idimportacion = d.idimportacion AND d.estado = 1
        WHERE i.idimportacion = '$idimportacion'
        GROUP BY i.idimportacion";

        return ejecutarConsultaSimpleFila($sql);
    }

    // ==========================================================================
    // MÉTODOS PARA PARTIDAS ARANCELARIAS
    // ==========================================================================

    /**
     * Listar partidas arancelarias activas
     */
    public function listarPartidasArancelarias($busqueda = null)
    {
        $sql = "SELECT * FROM partida_arancelaria WHERE estado = 1";

        if ($busqueda) {
            $sql .= " AND (codigo LIKE '%$busqueda%' OR descripcion LIKE '%$busqueda%')";
        }

        $sql .= " ORDER BY codigo LIMIT 100";

        return ejecutarConsulta($sql);
    }

    /**
     * Obtener información de una partida arancelaria
     */
    public function obtenerPartidaArancelaria($codigo)
    {
        $sql = "SELECT * FROM partida_arancelaria WHERE codigo = '$codigo' AND estado = 1";
        return ejecutarConsultaSimpleFila($sql);
    }

    /**
     * Insertar nueva partida arancelaria
     */
    public function insertarPartidaArancelaria(
        $codigo, $descripcion, $tasa_advalorem, $tasa_especifica,
        $unidad_tasa_especifica, $aplica_drawback, $tasa_drawback,
        $requiere_permiso, $organismo_permiso
    ) {
        $sql = "INSERT INTO partida_arancelaria (
            codigo, descripcion, tasa_advalorem, tasa_especifica,
            unidad_tasa_especifica, aplica_drawback, tasa_drawback,
            requiere_permiso, organismo_permiso, estado
        ) VALUES (
            '$codigo', '$descripcion', '$tasa_advalorem', '$tasa_especifica',
            '$unidad_tasa_especifica', '$aplica_drawback', '$tasa_drawback',
            '$requiere_permiso', '$organismo_permiso', 1
        )";

        return ejecutarConsulta($sql);
    }

    // ==========================================================================
    // MÉTODOS DE INTEGRACIÓN CON COMPRAS
    // ==========================================================================

    /**
     * Generar compra (ingreso) desde importación
     * Crea un registro en la tabla ingreso con los datos de la importación
     */
    public function generarCompraDesdeImportacion($idimportacion, $idproveedor, $tipo_comprobante, $serie, $numero)
    {
        // Obtener datos de la importación
        $importacion = $this->mostrar($idimportacion);

        if (!$importacion) {
            return false;
        }

        // Crear ingreso
        $sql_ingreso = "INSERT INTO ingreso (
            idempresa, idproveedor, idusuario, tipo_comprobante, serie_comprobante,
            num_comprobante, fecha_hora, impuesto, total_compra, estado
        ) VALUES (
            '{$importacion['idempresa']}', '$idproveedor', '{$importacion['idusuario']}',
            '$tipo_comprobante', '$serie', '$numero', NOW(),
            '{$importacion['igv_importacion']}', '{$importacion['costo_total_soles']}', 'Aceptado'
        )";

        $result = ejecutarConsulta($sql_ingreso);

        if ($result) {
            $idingreso = ejecutarConsulta("SELECT LAST_INSERT_ID() as id");
            $row = $idingreso->fetch_assoc();
            $nuevo_idingreso = $row['id'];

            // Vincular importación con ingreso
            $sql_update = "UPDATE importacion SET idingreso = '$nuevo_idingreso' WHERE idimportacion = '$idimportacion'";
            ejecutarConsulta($sql_update);

            // Insertar detalles en detalle_ingreso
            $detalles = $this->listarDetalles($idimportacion);
            while ($detalle = $detalles->fetch_assoc()) {
                if ($detalle['idarticulo']) {
                    $sql_detalle = "INSERT INTO detalle_ingreso (
                        idingreso, idarticulo, cantidad, precio_compra, precio_venta
                    ) VALUES (
                        '$nuevo_idingreso', '{$detalle['idarticulo']}',
                        '{$detalle['cantidad']}', '{$detalle['costo_unitario_final_soles']}',
                        '{$detalle['costo_unitario_final_soles']}'
                    )";
                    ejecutarConsulta($sql_detalle);
                }
            }

            return $nuevo_idingreso;
        }

        return false;
    }

    // ==========================================================================
    // REPORTES Y ESTADÍSTICAS
    // ==========================================================================

    /**
     * Obtener estadísticas de importaciones por período
     */
    public function estadisticasPorPeriodo($idempresa, $fecha_inicio, $fecha_fin)
    {
        $sql = "SELECT
            COUNT(*) AS total_importaciones,
            SUM(valor_fob) AS total_fob,
            SUM(valor_cif) AS total_cif,
            SUM(costo_total_soles) AS total_costo_soles,
            AVG(tipo_cambio) AS tipo_cambio_promedio,
            SUM(derechos_aduaneros) AS total_derechos,
            SUM(igv_importacion) AS total_igv
        FROM importacion
        WHERE idempresa = '$idempresa'
          AND fecha_dua BETWEEN '$fecha_inicio' AND '$fecha_fin'
          AND estado = 1";

        return ejecutarConsultaSimpleFila($sql);
    }

    /**
     * Top proveedores extranjeros
     */
    public function topProveedores($idempresa, $limite = 10)
    {
        $sql = "SELECT
            proveedor_extranjero,
            pais_origen,
            COUNT(*) AS total_importaciones,
            SUM(valor_fob) AS total_fob,
            SUM(costo_total_soles) AS total_costo_soles
        FROM importacion
        WHERE idempresa = '$idempresa' AND estado = 1
        GROUP BY proveedor_extranjero, pais_origen
        ORDER BY total_costo_soles DESC
        LIMIT $limite";

        return ejecutarConsulta($sql);
    }

    /**
     * Productos más importados
     */
    public function productosMasImportados($idempresa, $limite = 20)
    {
        $sql = "SELECT
            d.partida_arancelaria,
            d.descripcion_producto,
            COUNT(DISTINCT i.idimportacion) AS veces_importado,
            SUM(d.cantidad) AS cantidad_total,
            AVG(d.precio_unitario_fob) AS precio_promedio_fob,
            SUM(d.costo_total_final_soles) AS costo_total_acumulado
        FROM importacion_detalle d
        INNER JOIN importacion i ON d.idimportacion = i.idimportacion
        WHERE i.idempresa = '$idempresa' AND i.estado = 1 AND d.estado = 1
        GROUP BY d.partida_arancelaria, d.descripcion_producto
        ORDER BY cantidad_total DESC
        LIMIT $limite";

        return ejecutarConsulta($sql);
    }
}
