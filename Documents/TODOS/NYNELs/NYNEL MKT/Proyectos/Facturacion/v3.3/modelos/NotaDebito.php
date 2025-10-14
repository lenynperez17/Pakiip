<?php
/**
 * Modelo: NotaDebito
 * Descripción: Gestión de Notas de Débito electrónicas
 * Compatibilidad: Sistema de facturación v3.3
 * Estándar: SUNAT Perú
 */

require_once "../config/Conexion.php";

class NotaDebito
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Insertar nueva Nota de Débito
     * Parámetros similares a NotaCredito
     *
     * @return int ID de la nota de débito insertada o false en caso de error
     */
    public function insertar(
        $idempresa,
        $idfactura_afectada,
        $idboleta_afectada,
        $tipo_comprobante_afectado,
        $numeracion,
        $fecha_emision,
        $motivo_nota,
        $codigo_motivo,
        $idcliente,
        $total_operaciones_gravadas,
        $sumatoria_igv,
        $importe_total,
        $serie_afectado,
        $numero_afectado,
        $fecha_afectado,
        $idusuario,
        $idarticulo,
        $cantidad,
        $valor_unitario,
        $precio_venta,
        $afectacion_igv,
        $valor_venta,
        $igv_item,
        $total_item,
        $unidad_medida,
        $codigo_producto,
        $descripcion_item
    ) {
        try {
            // Iniciar transacción
            $sw = true;

            // Obtener hora actual
            $hora_emision = date('H:i:s');

            // Insertar cabecera de Nota de Débito
            $sql_cabecera = "INSERT INTO nota_debito (
                idempresa,
                idfactura_afectada,
                idboleta_afectada,
                tipo_comprobante_afectado,
                tipo_documento,
                numeracion_08,
                fecha_emision_01,
                hora_emision,
                motivo_nota,
                codigo_motivo_11,
                idcliente,
                total_operaciones_gravadas_16,
                sumatoria_igv_19,
                importe_total_venta_27,
                serie_comprobante_afectado,
                numero_comprobante_afectado,
                fecha_comprobante_afectado,
                estado,
                idusuario
            ) VALUES (
                '$idempresa',
                " . ($idfactura_afectada ? "'$idfactura_afectada'" : "NULL") . ",
                " . ($idboleta_afectada ? "'$idboleta_afectada'" : "NULL") . ",
                '$tipo_comprobante_afectado',
                '08',
                '$numeracion',
                '$fecha_emision',
                '$hora_emision',
                '$motivo_nota',
                '$codigo_motivo',
                '$idcliente',
                '$total_operaciones_gravadas',
                '$sumatoria_igv',
                '$importe_total',
                '$serie_afectado',
                '$numero_afectado',
                '$fecha_afectado',
                'Pendiente',
                '$idusuario'
            )";

            $idnota_debito = ejecutarConsulta_retornarID($sql_cabecera);

            if (!$idnota_debito) {
                $sw = false;
            }

            // Insertar detalles de la Nota de Débito
            $num_elementos = 0;
            if ($sw) {
                while ($num_elementos < count($idarticulo)) {
                    $sql_detalle = "INSERT INTO detalle_nota_debito (
                        idnota_debito,
                        idarticulo,
                        numero_orden_item_29,
                        cantidad_item_12,
                        unidad_medida_item_13,
                        valor_uni_item_14,
                        precio_venta_item_15_2,
                        afectacion_igv_item_16_1,
                        valor_venta_item_32,
                        igv_item,
                        total_item,
                        dcto_item,
                        codigo_producto,
                        descripcion_item
                    ) VALUES (
                        '$idnota_debito',
                        '" . $idarticulo[$num_elementos] . "',
                        '" . ($num_elementos + 1) . "',
                        '" . $cantidad[$num_elementos] . "',
                        '" . $unidad_medida[$num_elementos] . "',
                        '" . $valor_unitario[$num_elementos] . "',
                        '" . $precio_venta[$num_elementos] . "',
                        '" . $afectacion_igv[$num_elementos] . "',
                        '" . $valor_venta[$num_elementos] . "',
                        '" . $igv_item[$num_elementos] . "',
                        '" . $total_item[$num_elementos] . "',
                        '0.00',
                        '" . $codigo_producto[$num_elementos] . "',
                        '" . $descripcion_item[$num_elementos] . "'
                    )";

                    ejecutarConsulta($sql_detalle) or $sw = false;
                    $num_elementos++;
                }
            }

            // Retornar resultado
            return $sw ? $idnota_debito : false;
        } catch (Exception $e) {
            error_log("Error en NotaDebito::insertar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar estado de la Nota de Débito
     */
    public function actualizarEstado(
        $idnota_debito,
        $estado,
        $hash_cpe = null,
        $xml_content = null,
        $response_sunat = null,
        $codigo_respuesta = null,
        $mensaje_respuesta = null
    ) {
        $sql = "UPDATE nota_debito SET
            estado = '$estado'";

        if ($hash_cpe !== null) {
            $sql .= ", hash_cpe = '$hash_cpe'";
        }
        if ($xml_content !== null) {
            global $conexion;
            $xml_escaped = mysqli_real_escape_string($conexion, $xml_content);
            $sql .= ", xml_content = '$xml_escaped'";
        }
        if ($response_sunat !== null) {
            global $conexion;
            $response_escaped = mysqli_real_escape_string($conexion, $response_sunat);
            $sql .= ", response_sunat = '$response_escaped'";
        }
        if ($codigo_respuesta !== null) {
            $sql .= ", codigo_respuesta_sunat = '$codigo_respuesta'";
        }
        if ($mensaje_respuesta !== null) {
            $sql .= ", mensaje_respuesta_sunat = '$mensaje_respuesta'";
        }

        $sql .= " WHERE idnota_debito = '$idnota_debito'";

        return ejecutarConsulta($sql);
    }

    /**
     * Listar Notas de Débito por empresa
     */
    public function listar($idempresa, $fecha_inicio = null, $fecha_fin = null)
    {
        $sql = "SELECT
            nd.idnota_debito,
            nd.numeracion_08,
            nd.fecha_emision_01,
            nd.tipo_comprobante_afectado,
            CONCAT(nd.serie_comprobante_afectado, '-', nd.numero_comprobante_afectado) AS comprobante_afectado,
            p.razon_social AS cliente,
            p.numero_documento,
            nd.importe_total_venta_27 AS total,
            nd.estado,
            nd.motivo_nota
        FROM nota_debito nd
        INNER JOIN persona p ON nd.idcliente = p.idpersona
        WHERE nd.idempresa = '$idempresa'";

        if ($fecha_inicio && $fecha_fin) {
            $sql .= " AND nd.fecha_emision_01 BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        }

        $sql .= " ORDER BY nd.fecha_emision_01 DESC, nd.idnota_debito DESC";

        return ejecutarConsulta($sql);
    }

    /**
     * Mostrar detalle de una Nota de Débito
     */
    public function mostrar($idnota_debito)
    {
        $sql = "SELECT
            nd.*,
            p.tipo_documento AS tipo_doc_cliente,
            p.numero_documento AS num_doc_cliente,
            p.razon_social,
            p.domicilio_fiscal
        FROM nota_debito nd
        INNER JOIN persona p ON nd.idcliente = p.idpersona
        WHERE nd.idnota_debito = '$idnota_debito'";

        return ejecutarConsultaSimpleFila($sql);
    }

    /**
     * Listar detalle de items de una Nota de Débito
     */
    public function listarDetalle($idnota_debito)
    {
        $sql = "SELECT
            d.*,
            a.nombre,
            a.codigo
        FROM detalle_nota_debito d
        INNER JOIN articulo a ON d.idarticulo = a.idarticulo
        WHERE d.idnota_debito = '$idnota_debito'
        ORDER BY d.numero_orden_item_29 ASC";

        return ejecutarConsulta($sql);
    }

    /**
     * Anular Nota de Débito
     */
    public function anular($idnota_debito)
    {
        $sql = "UPDATE nota_debito SET estado = 'Anulado' WHERE idnota_debito = '$idnota_debito'";
        return ejecutarConsulta($sql);
    }
}
?>
