<?php
/**
 * Modelo: NotaCredito
 * Descripción: Gestión de Notas de Crédito electrónicas
 * Compatibilidad: Sistema de facturación v3.3
 * Estándar: SUNAT Perú
 */

require_once "../config/Conexion.php";

class NotaCredito
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Insertar nueva Nota de Crédito
     *
     * @param int $idempresa ID de la empresa
     * @param int $idfactura_afectada ID de factura afectada (null si es boleta)
     * @param int $idboleta_afectada ID de boleta afectada (null si es factura)
     * @param string $tipo_comprobante_afectado '01' = Factura, '03' = Boleta
     * @param string $numeracion Serie y número (ej: NC01-00000001)
     * @param string $fecha_emision Fecha de emisión (YYYY-MM-DD)
     * @param string $motivo_nota Descripción del motivo
     * @param string $codigo_motivo Código SUNAT del motivo
     * @param int $idcliente ID del cliente
     * @param float $total_operaciones_gravadas Base imponible
     * @param float $sumatoria_igv Total IGV
     * @param float $importe_total Total de la NC
     * @param string $serie_afectado Serie del comprobante afectado
     * @param string $numero_afectado Número del comprobante afectado
     * @param string $fecha_afectado Fecha del comprobante afectado
     * @param int $idusuario ID del usuario que registra
     * @param array $idarticulo Array de IDs de artículos
     * @param array $cantidad Array de cantidades
     * @param array $valor_unitario Array de valores unitarios
     * @param array $precio_venta Array de precios de venta
     * @param array $afectacion_igv Array de códigos de afectación IGV
     * @param array $valor_venta Array de valores de venta
     * @param array $igv_item Array de IGV por ítem
     * @param array $total_item Array de totales por ítem
     * @param array $unidad_medida Array de unidades de medida
     * @param array $codigo_producto Array de códigos de producto
     * @param array $descripcion_item Array de descripciones
     *
     * @return int ID de la nota de crédito insertada o false en caso de error
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
        // Habilitar reporte de errores
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        try {
            error_log("=== INICIO NotaCredito::insertar() ===");
            error_log("Empresa: $idempresa, Cliente: $idcliente, Numeracion: $numeracion");
            // Iniciar transacción
            $sw = true;

            // Obtener hora actual
            $hora_emision = date('H:i:s');
            error_log("Hora emisión: $hora_emision");

            // Insertar cabecera de Nota de Crédito (tabla notacd)
            $fecha_hora = $fecha_emision . ' ' . $hora_emision;
            $sql_cabecera = "INSERT INTO notacd (
                idempresa,
                codigo_nota,
                numeroserienota,
                fecha,
                desc_motivo,
                motivonota,
                codtiponota,
                tipo_doc_mod,
                serie_numero,
                idcomprobante,
                fechacomprobante,
                total_val_venta_og,
                sum_igv,
                importe_total,
                estado,
                nombre,
                tipo_doc_ide,
                numero_doc_ide,
                razon_social,
                tipo_moneda,
                sum_ot_car,
                total_val_venta_oi,
                total_val_venta_oe,
                sum_isc,
                sum_ot,
                adicional,
                icbper,
                tiponotacd,
                descripitem
            ) VALUES (
                '$idempresa',
                '07',
                '$numeracion',
                '$fecha_hora',
                '$motivo_nota',
                '$motivo_nota',
                '$codigo_motivo',
                '$tipo_comprobante_afectado',
                '$serie_afectado-$numero_afectado',
                " . ($idfactura_afectada ? "'$idfactura_afectada'" : ($idboleta_afectada ? "'$idboleta_afectada'" : "NULL")) . ",
                '$fecha_afectado',
                '$total_operaciones_gravadas',
                '$sumatoria_igv',
                '$importe_total',
                '1',
                'NOTA DE CREDITO',
                '6',
                '',
                '',
                'PEN',
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                'CREDITO',
                '$motivo_nota'
            )";

            error_log("SQL INSERT cabecera: " . substr($sql_cabecera, 0, 200) . "...");
            $idnota_credito = ejecutarConsulta_retornarID($sql_cabecera);
            error_log("ID nota crédito insertada: " . ($idnota_credito ? $idnota_credito : "FALSE"));

            if (!$idnota_credito) {
                $sw = false;
                error_log("ERROR: No se pudo insertar la cabecera de NC");
            }

            // Insertar detalles de la Nota de Crédito (tabla detalle_notacd_art)
            $num_elementos = 0;
            if ($sw) {
                while ($num_elementos < count($idarticulo)) {
                    $sql_detalle = "INSERT INTO detalle_notacd_art (
                        idnotacd,
                        idarticulo,
                        nro_orden,
                        cantidad,
                        valor_unitario,
                        precio_venta,
                        valor_venta,
                        igv,
                        aigv,
                        descripitem
                    ) VALUES (
                        '$idnota_credito',
                        '" . $idarticulo[$num_elementos] . "',
                        '" . ($num_elementos + 1) . "',
                        '" . $cantidad[$num_elementos] . "',
                        '" . $valor_unitario[$num_elementos] . "',
                        '" . $precio_venta[$num_elementos] . "',
                        '" . $valor_venta[$num_elementos] . "',
                        '" . $igv_item[$num_elementos] . "',
                        '" . $afectacion_igv[$num_elementos] . "',
                        '" . $descripcion_item[$num_elementos] . "'
                    )";

                    ejecutarConsulta($sql_detalle) or $sw = false;

                    // IMPORTANTE: Actualizar stock - La NC devuelve productos al inventario
                    if ($sw) {
                        $sql_stock = "UPDATE articulo
                            SET stock = stock + " . $cantidad[$num_elementos] . "
                            WHERE idarticulo = '" . $idarticulo[$num_elementos] . "'";
                        ejecutarConsulta($sql_stock) or $sw = false;
                        error_log("Stock actualizado para artículo " . $idarticulo[$num_elementos] . " + " . $cantidad[$num_elementos]);
                    }

                    $num_elementos++;
                }
            }

            // Retornar resultado
            return $sw ? $idnota_credito : false;
        } catch (Exception $e) {
            error_log("Error en NotaCredito::insertar: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
            return false;
        } catch (Error $e) {
            error_log("Error FATAL en NotaCredito::insertar: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
            return false;
        }
    }

    /**
     * Actualizar estado de la Nota de Crédito
     *
     * @param int $idnota_credito ID de la nota de crédito
     * @param string $estado Nuevo estado
     * @param string $hash_cpe Hash del CPE
     * @param string $xml_content Contenido XML
     * @param string $response_sunat Respuesta de SUNAT
     * @param string $codigo_respuesta Código de respuesta
     * @param string $mensaje_respuesta Mensaje de respuesta
     *
     * @return bool true si se actualizó correctamente
     */
    public function actualizarEstado(
        $idnota_credito,
        $estado,
        $hash_cpe = null,
        $xml_content = null,
        $response_sunat = null,
        $codigo_respuesta = null,
        $mensaje_respuesta = null
    ) {
        $sql = "UPDATE notacd SET
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

        $sql .= " WHERE idnota = '$idnota_credito'";

        return ejecutarConsulta($sql);
    }

    /**
     * Listar Notas de Crédito por empresa
     *
     * @param int $idempresa ID de la empresa
     * @param string $fecha_inicio Fecha inicio (opcional)
     * @param string $fecha_fin Fecha fin (opcional)
     *
     * @return mysqli_result Resultado de la consulta
     */
    public function listar($idempresa, $fecha_inicio = null, $fecha_fin = null)
    {
        $sql = "SELECT
            nc.idnota,
            nc.numeroserienota,
            nc.fecha,
            nc.tipo_doc_mod AS tipo_comprobante_afectado,
            nc.serie_numero AS comprobante_afectado,
            p.razon_social AS cliente,
            p.numero_documento,
            nc.importe_total AS total,
            nc.estado,
            nc.motivonota AS motivo_nota
        FROM notacd nc
        INNER JOIN persona p ON nc.idcliente = p.idpersona
        WHERE nc.idempresa = '$idempresa'";

        if ($fecha_inicio && $fecha_fin) {
            $sql .= " AND DATE(nc.fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        }

        $sql .= " ORDER BY nc.fecha DESC, nc.idnota DESC";

        return ejecutarConsulta($sql);
    }

    /**
     * Mostrar detalle de una Nota de Crédito
     *
     * @param int $idnota_credito ID de la nota de crédito
     *
     * @return object Datos de la nota de crédito
     */
    public function mostrar($idnota_credito)
    {
        $sql = "SELECT
            nc.*,
            p.tipo_documento AS tipo_doc_cliente,
            p.numero_documento AS num_doc_cliente,
            p.razon_social,
            p.domicilio_fiscal
        FROM notacd nc
        INNER JOIN persona p ON nc.idcliente = p.idpersona
        WHERE nc.idnota = '$idnota_credito'";

        return ejecutarConsultaSimpleFila($sql);
    }

    /**
     * Listar detalle de items de una Nota de Crédito
     *
     * @param int $idnota_credito ID de la nota de crédito
     *
     * @return mysqli_result Resultado de la consulta
     */
    public function listarDetalle($idnota_credito)
    {
        $sql = "SELECT
            d.*,
            a.nombre,
            a.codigo
        FROM detalle_notacd_art d
        INNER JOIN articulo a ON d.idarticulo = a.idarticulo
        WHERE d.idnotacd = '$idnota_credito'
        ORDER BY d.nro_orden ASC";

        return ejecutarConsulta($sql);
    }

    /**
     * Anular Nota de Crédito
     *
     * @param int $idnota_credito ID de la nota de crédito
     *
     * @return bool true si se anuló correctamente
     */
    public function anular($idnota_credito)
    {
        $sql = "UPDATE notacd SET estado = 'Anulado' WHERE idnota = '$idnota_credito'";
        return ejecutarConsulta($sql);
    }

    /**
     * Obtener datos completos de cabecera NC para reporte PDF
     *
     * @param int $idnota_credito ID de la nota de crédito
     * @param int $idempresa ID de la empresa
     *
     * @return object Datos completos de la NC
     */
    public function notacreditocabecera($idnota_credito, $idempresa)
    {
        $sql = "SELECT
            nc.*,
            p.tipo_documento,
            p.numero_documento,
            p.razon_social AS cliente,
            p.domicilio_fiscal AS direccion
        FROM notacd nc
        INNER JOIN persona p ON nc.idcliente = p.idpersona
        WHERE nc.idnota = '$idnota_credito'
          AND nc.idempresa = '$idempresa'";

        return ejecutarConsulta($sql);
    }

    /**
     * Obtener detalle de items de NC para reporte PDF
     *
     * @param int $idnota_credito ID de la nota de crédito
     *
     * @return mysqli_result Items de la NC
     */
    public function notacreditodetalle($idnota_credito)
    {
        $sql = "SELECT
            d.*,
            a.codigo,
            a.nombre AS articulo,
            a.unidad_medida
        FROM detalle_notacd_art d
        INNER JOIN articulo a ON d.idarticulo = a.idarticulo
        WHERE d.idnotacd = '$idnota_credito'
        ORDER BY d.nro_orden ASC";

        return ejecutarConsulta($sql);
    }

    /**
     * Obtener datos de la empresa para reporte PDF
     *
     * @param int $idempresa ID de la empresa
     *
     * @return mysqli_result Datos de la empresa
     */
    public function datosemp($idempresa)
    {
        $sql = "SELECT * FROM empresa WHERE idempresa = '$idempresa'";
        return ejecutarConsulta($sql);
    }
}
?>
