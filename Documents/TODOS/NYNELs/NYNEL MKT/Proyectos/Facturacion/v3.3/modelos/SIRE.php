<?php
/**
 * Modelo SIRE - Sistema Integrado de Registros Electrónicos
 *
 * Gestiona la generación de archivos TXT para SUNAT según normativa vigente
 * - RVIE: Registro de Ventas e Ingresos Electrónico (Anexo 3)
 * - RCE: Registro de Compras Electrónico (Anexo 11)
 *
 * @author Sistema de Facturación
 * @version 1.0
 * @date 2025-10-15
 */

require_once "../config/Conexion.php";

class SIRE
{
    private $separador = '|'; // Separador de campos según SUNAT
    private $ruta_exportacion = '../files/sire/'; // Carpeta de exportación

    public function __construct()
    {
        // Crear carpeta de exportación si no existe
        if (!file_exists($this->ruta_exportacion)) {
            mkdir($this->ruta_exportacion, 0755, true);
        }
    }

    /**
     * Genera el nombre del archivo según nomenclatura SUNAT
     * Formato: LE + RUC (11) + AÑO (4) + MES (2) + DIA (2) + LIBRO (6) + OPORTUNIDAD (2) + INDICADORES (6)
     * Total: 35 caracteres + .txt
     *
     * @param string $ruc RUC de 11 dígitos
     * @param string $anio Año AAAA
     * @param string $mes Mes MM
     * @param string $tipo_registro RVIE o RCE
     * @param string $cod_oportunidad Código de oportunidad (01-06)
     * @param string $indicadores 6 caracteres de indicadores
     * @return string Nombre del archivo
     */
    public function generarNombreArchivo($ruc, $anio, $mes, $tipo_registro, $cod_oportunidad, $indicadores = '111210')
    {
        // Validar longitud de RUC
        $ruc = str_pad($ruc, 11, '0', STR_PAD_LEFT);

        // Componentes del nombre
        $prefijo = 'LE';
        $dia = '00'; // Siempre 00 para registros mensuales
        $libro = str_pad($tipo_registro, 6, '0', STR_PAD_LEFT); // RVIE o RCE con padding

        // Construir nombre: 35 caracteres
        $nombre = $prefijo . $ruc . $anio . $mes . $dia . $libro . $cod_oportunidad . $indicadores;

        return $nombre . '.txt';
    }

    /**
     * Obtiene configuración SIRE de la empresa
     *
     * @param int $idempresa ID de la empresa
     * @return array|false Configuración o false si no existe
     */
    public function obtenerConfiguracion($idempresa)
    {
        $sql = "SELECT * FROM sire_configuracion WHERE idempresa = '$idempresa' AND estado = 1 LIMIT 1";
        $resultado = ejecutarConsultaSimpleFila($sql);
        return $resultado;
    }

    /**
     * Guarda o actualiza configuración SIRE
     *
     * @param int $idempresa
     * @param string $ruc
     * @param string $periodo_obligado_desde
     * @param int $generar_propuesta_aceptar
     * @param string $moneda_principal
     * @param int $incluir_anulados
     * @param int $usuario_id
     * @return bool
     */
    public function guardarConfiguracion($idempresa, $ruc, $periodo_obligado_desde, $generar_propuesta_aceptar, $moneda_principal, $incluir_anulados, $usuario_id)
    {
        // Verificar si ya existe configuración
        $existe = $this->obtenerConfiguracion($idempresa);

        if ($existe) {
            // Actualizar
            $sql = "UPDATE sire_configuracion SET
                        ruc = '$ruc',
                        periodo_obligado_desde = '$periodo_obligado_desde',
                        generar_propuesta_aceptar = '$generar_propuesta_aceptar',
                        moneda_principal = '$moneda_principal',
                        incluir_anulados = '$incluir_anulados'
                    WHERE idempresa = '$idempresa'";
        } else {
            // Insertar
            $sql = "INSERT INTO sire_configuracion
                        (idempresa, ruc, periodo_obligado_desde, generar_propuesta_aceptar, moneda_principal, incluir_anulados, usuario_creacion)
                    VALUES
                        ('$idempresa', '$ruc', '$periodo_obligado_desde', '$generar_propuesta_aceptar', '$moneda_principal', '$incluir_anulados', '$usuario_id')";
        }

        return ejecutarConsulta($sql);
    }

    /**
     * Obtiene datos de ventas del período para RVIE
     *
     * @param int $idempresa
     * @param string $periodo_anio AAAA
     * @param string $periodo_mes MM
     * @param bool $incluir_anulados
     * @return mysqli_result
     */
    public function obtenerDatosVentas($idempresa, $periodo_anio, $periodo_mes, $incluir_anulados = false)
    {
        $fecha_inicio = "$periodo_anio-$periodo_mes-01";
        $fecha_fin = date('Y-m-t', strtotime($fecha_inicio)); // Último día del mes

        $filtro_anulados = $incluir_anulados ? "" : " AND f.CodigoRptaSunat != '3' "; // 3 = Anulado

        // UNION de Facturas y Boletas
        $sql = "
            SELECT
                CONCAT('$periodo_anio', LPAD('$periodo_mes', 2, '0')) AS periodo,
                '' AS correlativo_asiento,
                DATE_FORMAT(f.fecha_emision_01, '%d/%m/%Y') AS fecha_emision,
                DATE_FORMAT(f.fecha_vencimiento, '%d/%m/%Y') AS fecha_vencimiento,
                '01' AS tipo_comprobante,
                f.serie_05 AS serie_comprobante,
                f.numeracion_06 AS numero_comprobante,
                '' AS numero_final_rango,
                CASE p.tipo_documento
                    WHEN 'DNI' THEN '1'
                    WHEN 'RUC' THEN '6'
                    WHEN 'CARNET DE EXTRANJERIA' THEN '4'
                    WHEN 'PASAPORTE' THEN '7'
                    ELSE '0'
                END AS tipo_documento_cliente,
                p.numero_documento AS numero_documento_cliente,
                p.razon_social AS razon_social_cliente,
                FORMAT(f.valor_exportacion, 2) AS valor_exportacion,
                FORMAT(f.base_imponible_total_17, 2) AS base_imponible_gravada,
                FORMAT(f.descuento_base_imponible_19, 2) AS descuento_base_imponible,
                FORMAT(f.igv_total_18, 2) AS igv_ipm,
                FORMAT(f.descuento_igv_20, 2) AS descuento_igv_ipm,
                FORMAT(f.monto_exonerado, 2) AS monto_exonerado,
                FORMAT(f.monto_inafecto, 2) AS monto_inafecto,
                FORMAT(f.isc_total_21, 2) AS isc,
                '0.00' AS base_imponible_arroz_pilado,
                '0.00' AS ivap_arroz_pilado,
                FORMAT(f.icbper_total_22, 2) AS icbper,
                FORMAT(f.otros_conceptos_23, 2) AS otros_tributos,
                FORMAT(f.importe_total_venta_27, 2) AS importe_total,
                CASE f.tipo_moneda_24
                    WHEN 'PEN' THEN 'PEN'
                    WHEN 'USD' THEN 'USD'
                    ELSE 'PEN'
                END AS codigo_moneda,
                FORMAT(f.tcambio, 3) AS tipo_cambio,
                '' AS fecha_emision_modificado,
                '' AS tipo_comprobante_modificado,
                '' AS serie_comprobante_modificado,
                '' AS numero_comprobante_modificado,
                '' AS identificacion_contrato,
                '' AS error_tipo_1,
                '' AS error_tipo_2,
                '' AS error_tipo_3,
                '' AS error_tipo_4,
                '' AS medio_pago,
                CASE
                    WHEN f.CodigoRptaSunat = '3' THEN '2'
                    ELSE '1'
                END AS estado_comprobante,
                f.idfactura AS id_referencia,
                'FACTURA' AS tipo_origen
            FROM factura f
            INNER JOIN persona p ON f.idcliente = p.idpersona
            WHERE f.idempresa = '$idempresa'
                AND f.fecha_emision_01 BETWEEN '$fecha_inicio' AND '$fecha_fin'
                $filtro_anulados

            UNION ALL

            SELECT
                CONCAT('$periodo_anio', LPAD('$periodo_mes', 2, '0')) AS periodo,
                '' AS correlativo_asiento,
                DATE_FORMAT(b.fecha_emision_01, '%d/%m/%Y') AS fecha_emision,
                '' AS fecha_vencimiento,
                '03' AS tipo_comprobante,
                b.serie_05 AS serie_comprobante,
                b.numeracion_07 AS numero_comprobante,
                '' AS numero_final_rango,
                CASE p.tipo_documento
                    WHEN 'DNI' THEN '1'
                    WHEN 'RUC' THEN '6'
                    WHEN 'CARNET DE EXTRANJERIA' THEN '4'
                    WHEN 'PASAPORTE' THEN '7'
                    ELSE '0'
                END AS tipo_documento_cliente,
                p.numero_documento AS numero_documento_cliente,
                p.razon_social AS razon_social_cliente,
                '0.00' AS valor_exportacion,
                FORMAT(b.base_imponible_total_18, 2) AS base_imponible_gravada,
                '0.00' AS descuento_base_imponible,
                FORMAT(b.igv_total_19, 2) AS igv_ipm,
                '0.00' AS descuento_igv_ipm,
                FORMAT(b.monto_exonerado, 2) AS monto_exonerado,
                FORMAT(b.monto_inafecto, 2) AS monto_inafecto,
                '0.00' AS isc,
                '0.00' AS base_imponible_arroz_pilado,
                '0.00' AS ivap_arroz_pilado,
                FORMAT(b.icbper_total_20, 2) AS icbper,
                '0.00' AS otros_tributos,
                FORMAT(b.importe_total_23, 2) AS importe_total,
                CASE b.tipo_moneda_24
                    WHEN 'PEN' THEN 'PEN'
                    WHEN 'USD' THEN 'USD'
                    ELSE 'PEN'
                END AS codigo_moneda,
                FORMAT(b.tcambio, 3) AS tipo_cambio,
                '' AS fecha_emision_modificado,
                '' AS tipo_comprobante_modificado,
                '' AS serie_comprobante_modificado,
                '' AS numero_comprobante_modificado,
                '' AS identificacion_contrato,
                '' AS error_tipo_1,
                '' AS error_tipo_2,
                '' AS error_tipo_3,
                '' AS error_tipo_4,
                '' AS medio_pago,
                CASE
                    WHEN b.CodigoRptaSunat = '3' THEN '2'
                    ELSE '1'
                END AS estado_comprobante,
                b.idboleta AS id_referencia,
                'BOLETA' AS tipo_origen
            FROM boleta b
            INNER JOIN persona p ON b.idcliente = p.idpersona
            WHERE b.idempresa = '$idempresa'
                AND b.fecha_emision_01 BETWEEN '$fecha_inicio' AND '$fecha_fin'
                $filtro_anulados

            ORDER BY fecha_emision, tipo_comprobante, serie_comprobante, numero_comprobante
        ";

        return ejecutarConsulta($sql);
    }

    /**
     * Genera archivo TXT de Registro de Ventas (RVIE)
     *
     * @param int $idempresa
     * @param string $periodo_anio
     * @param string $periodo_mes
     * @param string $cod_oportunidad
     * @param int $usuario_id
     * @return array ['success' => bool, 'mensaje' => string, 'archivo' => string, 'total_registros' => int]
     */
    public function generarRVIE($idempresa, $periodo_anio, $periodo_mes, $cod_oportunidad, $usuario_id)
    {
        // Obtener configuración
        $config = $this->obtenerConfiguracion($idempresa);
        if (!$config) {
            return ['success' => false, 'mensaje' => 'No existe configuración SIRE para esta empresa'];
        }

        // Obtener datos de ventas
        $datos = $this->obtenerDatosVentas($idempresa, $periodo_anio, $periodo_mes, $config['incluir_anulados']);

        // Generar nombre de archivo
        $indicadores = '1' . // Operación: 1=Operativo
                       ($datos->num_rows > 0 ? '1' : '0') . // Contenido: 1=Con info, 0=Sin info
                       $config['moneda_principal'] . // Moneda: 1=Soles, 2=Dólares
                       '2' . // Generación: 2=Sistema
                       '10'; // Correlativo: 10 (inicial)

        $nombre_archivo = $this->generarNombreArchivo(
            $config['ruc'],
            $periodo_anio,
            $periodo_mes,
            '00' . '0' . '140100', // Código libro RVIE según tabla 13
            $cod_oportunidad,
            $indicadores
        );

        // Crear contenido del archivo
        $contenido = '';
        $total_registros = 0;

        while ($row = $datos->fetch_assoc()) {
            // Construir línea según Anexo 3
            $linea = implode($this->separador, [
                $row['periodo'],
                $row['correlativo_asiento'],
                $row['fecha_emision'],
                $row['fecha_vencimiento'],
                $row['tipo_comprobante'],
                $row['serie_comprobante'],
                $row['numero_comprobante'],
                $row['numero_final_rango'],
                $row['tipo_documento_cliente'],
                $row['numero_documento_cliente'],
                $row['razon_social_cliente'],
                str_replace(',', '', $row['valor_exportacion']),
                str_replace(',', '', $row['base_imponible_gravada']),
                str_replace(',', '', $row['descuento_base_imponible']),
                str_replace(',', '', $row['igv_ipm']),
                str_replace(',', '', $row['descuento_igv_ipm']),
                str_replace(',', '', $row['monto_exonerado']),
                str_replace(',', '', $row['monto_inafecto']),
                str_replace(',', '', $row['isc']),
                str_replace(',', '', $row['base_imponible_arroz_pilado']),
                str_replace(',', '', $row['ivap_arroz_pilado']),
                str_replace(',', '', $row['icbper']),
                str_replace(',', '', $row['otros_tributos']),
                str_replace(',', '', $row['importe_total']),
                $row['codigo_moneda'],
                str_replace(',', '', $row['tipo_cambio']),
                $row['fecha_emision_modificado'],
                $row['tipo_comprobante_modificado'],
                $row['serie_comprobante_modificado'],
                $row['numero_comprobante_modificado'],
                $row['identificacion_contrato'],
                $row['error_tipo_1'],
                $row['error_tipo_2'],
                $row['error_tipo_3'],
                $row['error_tipo_4'],
                $row['medio_pago'],
                $row['estado_comprobante']
            ]);

            $contenido .= $linea . "\r\n";
            $total_registros++;
        }

        // Guardar archivo
        $ruta_completa = $this->ruta_exportacion . $nombre_archivo;
        $guardado = file_put_contents($ruta_completa, $contenido);

        if ($guardado === false) {
            return ['success' => false, 'mensaje' => 'Error al guardar archivo TXT'];
        }

        // Registrar exportación en BD
        $sql_export = "INSERT INTO sire_exportacion
                        (idempresa, tipo_registro, periodo_anio, periodo_mes, codigo_oportunidad, nombre_archivo, ruta_archivo, total_registros,
                         indicador_operacion, indicador_contenido, indicador_moneda, indicador_generacion, correlativo_ajuste, usuario_generacion)
                       VALUES
                        ('$idempresa', 'RVIE', '$periodo_anio', '$periodo_mes', '$cod_oportunidad', '$nombre_archivo', '$ruta_completa', '$total_registros',
                         '1', '" . ($total_registros > 0 ? '1' : '0') . "', '{$config['moneda_principal']}', '2', '10', '$usuario_id')";

        ejecutarConsulta($sql_export);

        return [
            'success' => true,
            'mensaje' => 'Archivo RVIE generado exitosamente',
            'archivo' => $nombre_archivo,
            'ruta' => $ruta_completa,
            'total_registros' => $total_registros
        ];
    }

    /**
     * Obtiene datos de compras del período para RCE
     *
     * @param int $idempresa
     * @param string $periodo_anio
     * @param string $periodo_mes
     * @param bool $incluir_anulados
     * @return mysqli_result
     */
    public function obtenerDatosCompras($idempresa, $periodo_anio, $periodo_mes, $incluir_anulados = false)
    {
        $fecha_inicio = "$periodo_anio-$periodo_mes-01";
        $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));

        $filtro_anulados = $incluir_anulados ? "" : " AND c.estado != 'Anulado' ";

        $sql = "
            SELECT
                CONCAT('$periodo_anio', LPAD('$periodo_mes', 2, '0')) AS periodo,
                '' AS correlativo_asiento,
                DATE_FORMAT(c.fecha_hora, '%d/%m/%Y') AS fecha_emision,
                '' AS fecha_vencimiento,
                '' AS fecha_vcto_detraccion,
                c.tipo_comprobante AS tipo_comprobante,
                c.serie_comprobante AS serie_comprobante,
                c.num_comprobante AS numero_comprobante,
                '' AS numero_final_rango,
                CASE pr.tipo_documento
                    WHEN 'DNI' THEN '1'
                    WHEN 'RUC' THEN '6'
                    WHEN 'CARNET DE EXTRANJERIA' THEN '4'
                    WHEN 'PASAPORTE' THEN '7'
                    ELSE '0'
                END AS tipo_documento_proveedor,
                pr.numero_documento AS numero_documento_proveedor,
                pr.razon_social AS razon_social_proveedor,
                FORMAT(c.base_imponible, 2) AS base_imponible_gravada,
                FORMAT(c.igv, 2) AS igv,
                '0.00' AS base_imponible_gravada_no_domiciliado,
                '0.00' AS igv_no_domiciliado,
                '0.00' AS base_imponible_gravada_dua,
                '0.00' AS igv_dua,
                '0.00' AS monto_exonerado,
                '0.00' AS monto_inafecto,
                '0.00' AS isc,
                '0.00' AS icbper,
                '0.00' AS otros_tributos,
                FORMAT(c.total_compra, 2) AS importe_total,
                'PEN' AS codigo_moneda,
                '1.000' AS tipo_cambio,
                '' AS fecha_emision_modificado,
                '' AS tipo_comprobante_modificado,
                '' AS serie_comprobante_modificado,
                '' AS numero_comprobante_modificado,
                '' AS fecha_emision_detraccion,
                '' AS numero_constancia_detraccion,
                '0' AS marca_retencion,
                '' AS clasificacion_bienes_servicios,
                '' AS identificacion_contrato,
                '' AS error_tipo_1,
                '' AS error_tipo_2,
                '' AS error_tipo_3,
                '' AS error_tipo_4,
                '' AS medio_pago,
                CASE
                    WHEN c.estado = 'Anulado' THEN '2'
                    ELSE '1'
                END AS estado_comprobante,
                c.idingreso AS id_referencia
            FROM ingreso c
            INNER JOIN persona pr ON c.idproveedor = pr.idpersona
            WHERE c.idempresa = '$idempresa'
                AND c.fecha_hora BETWEEN '$fecha_inicio' AND '$fecha_fin'
                $filtro_anulados
            ORDER BY fecha_emision, serie_comprobante, numero_comprobante
        ";

        return ejecutarConsulta($sql);
    }

    /**
     * Genera archivo TXT de Registro de Compras (RCE)
     *
     * @param int $idempresa
     * @param string $periodo_anio
     * @param string $periodo_mes
     * @param string $cod_oportunidad
     * @param int $usuario_id
     * @return array
     */
    public function generarRCE($idempresa, $periodo_anio, $periodo_mes, $cod_oportunidad, $usuario_id)
    {
        // Obtener configuración
        $config = $this->obtenerConfiguracion($idempresa);
        if (!$config) {
            return ['success' => false, 'mensaje' => 'No existe configuración SIRE para esta empresa'];
        }

        // Obtener datos de compras
        $datos = $this->obtenerDatosCompras($idempresa, $periodo_anio, $periodo_mes, $config['incluir_anulados']);

        // Generar nombre de archivo
        $indicadores = '1' .
                       ($datos->num_rows > 0 ? '1' : '0') .
                       $config['moneda_principal'] .
                       '2' .
                       '10';

        $nombre_archivo = $this->generarNombreArchivo(
            $config['ruc'],
            $periodo_anio,
            $periodo_mes,
            '00' . '0' . '080100', // Código libro RCE
            $cod_oportunidad,
            $indicadores
        );

        // Crear contenido del archivo
        $contenido = '';
        $total_registros = 0;

        while ($row = $datos->fetch_assoc()) {
            // Construir línea según Anexo 11
            $linea = implode($this->separador, [
                $row['periodo'],
                $row['correlativo_asiento'],
                $row['fecha_emision'],
                $row['fecha_vencimiento'],
                $row['fecha_vcto_detraccion'],
                $row['tipo_comprobante'],
                $row['serie_comprobante'],
                $row['numero_comprobante'],
                $row['numero_final_rango'],
                $row['tipo_documento_proveedor'],
                $row['numero_documento_proveedor'],
                $row['razon_social_proveedor'],
                str_replace(',', '', $row['base_imponible_gravada']),
                str_replace(',', '', $row['igv']),
                str_replace(',', '', $row['base_imponible_gravada_no_domiciliado']),
                str_replace(',', '', $row['igv_no_domiciliado']),
                str_replace(',', '', $row['base_imponible_gravada_dua']),
                str_replace(',', '', $row['igv_dua']),
                str_replace(',', '', $row['monto_exonerado']),
                str_replace(',', '', $row['monto_inafecto']),
                str_replace(',', '', $row['isc']),
                str_replace(',', '', $row['icbper']),
                str_replace(',', '', $row['otros_tributos']),
                str_replace(',', '', $row['importe_total']),
                $row['codigo_moneda'],
                str_replace(',', '', $row['tipo_cambio']),
                $row['fecha_emision_modificado'],
                $row['tipo_comprobante_modificado'],
                $row['serie_comprobante_modificado'],
                $row['numero_comprobante_modificado'],
                $row['fecha_emision_detraccion'],
                $row['numero_constancia_detraccion'],
                $row['marca_retencion'],
                $row['clasificacion_bienes_servicios'],
                $row['identificacion_contrato'],
                $row['error_tipo_1'],
                $row['error_tipo_2'],
                $row['error_tipo_3'],
                $row['error_tipo_4'],
                $row['medio_pago'],
                $row['estado_comprobante']
            ]);

            $contenido .= $linea . "\r\n";
            $total_registros++;
        }

        // Guardar archivo
        $ruta_completa = $this->ruta_exportacion . $nombre_archivo;
        $guardado = file_put_contents($ruta_completa, $contenido);

        if ($guardado === false) {
            return ['success' => false, 'mensaje' => 'Error al guardar archivo TXT'];
        }

        // Registrar exportación
        $sql_export = "INSERT INTO sire_exportacion
                        (idempresa, tipo_registro, periodo_anio, periodo_mes, codigo_oportunidad, nombre_archivo, ruta_archivo, total_registros,
                         indicador_operacion, indicador_contenido, indicador_moneda, indicador_generacion, correlativo_ajuste, usuario_generacion)
                       VALUES
                        ('$idempresa', 'RCE', '$periodo_anio', '$periodo_mes', '$cod_oportunidad', '$nombre_archivo', '$ruta_completa', '$total_registros',
                         '1', '" . ($total_registros > 0 ? '1' : '0') . "', '{$config['moneda_principal']}', '2', '10', '$usuario_id')";

        ejecutarConsulta($sql_export);

        return [
            'success' => true,
            'mensaje' => 'Archivo RCE generado exitosamente',
            'archivo' => $nombre_archivo,
            'ruta' => $ruta_completa,
            'total_registros' => $total_registros
        ];
    }

    /**
     * Lista exportaciones realizadas
     *
     * @param int $idempresa
     * @param int $limit
     * @return mysqli_result
     */
    public function listarExportaciones($idempresa, $limit = 50)
    {
        $sql = "SELECT
                    e.*,
                    u.nombre AS usuario_nombre,
                    CONCAT(e.periodo_anio, '-', e.periodo_mes) AS periodo_formato
                FROM sire_exportacion e
                LEFT JOIN usuario u ON e.usuario_generacion = u.idusuario
                WHERE e.idempresa = '$idempresa'
                ORDER BY e.fecha_generacion DESC
                LIMIT $limit";

        return ejecutarConsulta($sql);
    }

    /**
     * Obtiene códigos SUNAT de una tabla específica
     *
     * @param string $tabla_sunat Nombre de la tabla (ej: TABLA_10, TABLA_2)
     * @return mysqli_result
     */
    public function obtenerCodigosSunat($tabla_sunat)
    {
        $sql = "SELECT * FROM sire_codigos_sunat
                WHERE tabla_sunat = '$tabla_sunat' AND estado = 1
                ORDER BY codigo ASC";

        return ejecutarConsulta($sql);
    }

    /**
     * Descarga un archivo TXT generado
     *
     * @param int $idsire_exportacion
     * @return array ['success' => bool, 'archivo' => string, 'ruta' => string, 'contenido' => string]
     */
    public function descargarArchivo($idsire_exportacion)
    {
        $sql = "SELECT nombre_archivo, ruta_archivo FROM sire_exportacion WHERE idsire_exportacion = '$idsire_exportacion'";
        $resultado = ejecutarConsultaSimpleFila($sql);

        if (!$resultado) {
            return ['success' => false, 'mensaje' => 'Exportación no encontrada'];
        }

        if (!file_exists($resultado['ruta_archivo'])) {
            return ['success' => false, 'mensaje' => 'Archivo no encontrado en el sistema'];
        }

        $contenido = file_get_contents($resultado['ruta_archivo']);

        return [
            'success' => true,
            'archivo' => $resultado['nombre_archivo'],
            'ruta' => $resultado['ruta_archivo'],
            'contenido' => $contenido
        ];
    }
}
