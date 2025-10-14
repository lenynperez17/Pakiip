
<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";
 
Class Compra
{
    //Implementamos nuestro constructor
    public function __construct()
    {
 
    }
 
    /**
     * Inserta una nueva compra con sus detalles en el sistema
     * REFACTORIZADO: Usa prepared statements y transacciones para garantizar integridad
     *
     * @param int $idusuario ID del usuario que registra la compra
     * @param int $idproveedor ID del proveedor
     * @param string $fecha_emision Fecha de emisión de la compra
     * @param string $tipo_comprobante Tipo de comprobante
     * @param string $serie_comprobante Serie del comprobante
     * @param string $num_comprobante Número del comprobante
     * @param string $guia Guía de remisión
     * @param float $subtotal_compra Subtotal de la compra
     * @param float $total_igv Total de IGV
     * @param float $total_compra Total de la compra
     * @param array $idarticulo Array de IDs de artículos
     * @param array $valor_unitario Array de valores unitarios
     * @param array $cantidad Array de cantidades
     * @param array $subtotalBD Array de subtotales
     * @param array $codigo Array de códigos de artículos
     * @param array $unidad_medida Array de unidades de medida
     * @param float $tcambio Tipo de cambio
     * @param string $hora Hora de la compra
     * @param string $moneda Moneda (PEN o USD)
     * @param int $idempresa ID de la empresa
     * @return int|false ID de la compra creada o false si falla
     */
    public function insertar($idusuario, $idproveedor, $fecha_emision, $tipo_comprobante, $serie_comprobante, $num_comprobante, $guia, $subtotal_compra, $total_igv, $total_compra, $idarticulo, $valor_unitario,  $cantidad, $subtotalBD,  $codigo, $unidad_medida, $tcambio, $hora, $moneda, $idempresa)
    {
        global $conexion;

        // SEGURIDAD: Iniciar transacción para garantizar atomicidad
        mysqli_begin_transaction($conexion);

        try {
            // PASO 1: Insertar registro principal de compra con prepared statement
            $fecha_completa = $fecha_emision . ' ' . $hora;

            $sql_compra = "INSERT INTO compra (
                idusuario, idproveedor, fecha, tipo_documento, serie, numero, guia,
                subtotal, igv, total, subtotal_$, igv_$, total_$, tcambio, moneda, idempresa
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '0', '0', '0', ?, ?, ?)";

            $stmt_compra = $conexion->prepare($sql_compra);
            if (!$stmt_compra) {
                error_log("Error preparando INSERT compra: " . $conexion->error);
                mysqli_rollback($conexion);
                return false;
            }

            $stmt_compra->bind_param(
                "iisssssddddsi",
                $idusuario, $idproveedor, $fecha_completa, $tipo_comprobante,
                $serie_comprobante, $num_comprobante, $guia, $subtotal_compra,
                $total_igv, $total_compra, $tcambio, $moneda, $idempresa
            );

            if (!$stmt_compra->execute()) {
                error_log("Error ejecutando INSERT compra: " . $stmt_compra->error);
                $stmt_compra->close();
                mysqli_rollback($conexion);
                return false;
            }

            $idcompranew = $conexion->insert_id;
            $stmt_compra->close();

            // PASO 2: Procesar cada artículo de la compra
            $num_elementos = 0;
            while ($num_elementos < count($idarticulo)) {
                // Convertir USD a PEN si aplica
                if ($moneda == "USD") {
                    $valor_unitario[$num_elementos] = $valor_unitario[$num_elementos] * $tcambio;
                }

                $idarticulo_actual = $idarticulo[$num_elementos];
                $valor_unitario_actual = $valor_unitario[$num_elementos];
                $cantidad_actual = $cantidad[$num_elementos];
                $codigo_actual = $codigo[$num_elementos];
                $unidad_medida_actual = $unidad_medida[$num_elementos];
                $numero_doc = $serie_comprobante . '-' . $num_comprobante;

                // PASO 2.1: Insertar detalle de compra
                $sql_detalle = "INSERT INTO detalle_compra_producto (
                    idcompra, idarticulo, valor_unitario, cantidad, subtotal, valor_unitario_$, subtotal_$
                ) VALUES (?, ?, ?, ?, valor_unitario * ?, '0', '0')";

                $stmt_detalle = $conexion->prepare($sql_detalle);
                if (!$stmt_detalle) {
                    error_log("Error preparando INSERT detalle: " . $conexion->error);
                    mysqli_rollback($conexion);
                    return false;
                }

                $stmt_detalle->bind_param("iiddd", $idcompranew, $idarticulo_actual, $valor_unitario_actual, $cantidad_actual, $cantidad_actual);

                if (!$stmt_detalle->execute()) {
                    error_log("Error ejecutando INSERT detalle: " . $stmt_detalle->error);
                    $stmt_detalle->close();
                    mysqli_rollback($conexion);
                    return false;
                }
                $stmt_detalle->close();

                // PASO 2.2: Insertar en kardex con subconsultas para cálculos
                $sql_kardex = "INSERT INTO kardex (
                    idcomprobante, idarticulo, transaccion, codigo, fecha, tipo_documento,
                    numero_doc, cantidad, costo_1, unidad_medida, saldo_final, costo_2,
                    valor_final, idempresa, tcambio, moneda
                ) VALUES (
                    ?, ?, 'COMPRA', ?, ?, ?, ?, ?, ?, ?,
                    (SELECT saldo_finu + ? FROM articulo WHERE idarticulo = ?),
                    (SELECT (saldo_finu * precio_final_kardex + (? * ?)) / (saldo_finu + ?) FROM articulo WHERE idarticulo = ?),
                    saldo_final * costo_2,
                    ?, ?, ?
                )";

                $stmt_kardex = $conexion->prepare($sql_kardex);
                if (!$stmt_kardex) {
                    error_log("Error preparando INSERT kardex: " . $conexion->error);
                    mysqli_rollback($conexion);
                    return false;
                }

                $stmt_kardex->bind_param(
                    "iissssddsdiiddiids",
                    $idcompranew, $idarticulo_actual, $codigo_actual, $fecha_emision,
                    $tipo_comprobante, $numero_doc, $cantidad_actual, $valor_unitario_actual,
                    $unidad_medida_actual, $cantidad_actual, $idarticulo_actual,
                    $valor_unitario_actual, $cantidad_actual, $cantidad_actual, $idarticulo_actual,
                    $idempresa, $tcambio, $moneda
                );

                if (!$stmt_kardex->execute()) {
                    error_log("Error ejecutando INSERT kardex: " . $stmt_kardex->error);
                    $stmt_kardex->close();
                    mysqli_rollback($conexion);
                    return false;
                }
                $stmt_kardex->close();

                // PASO 2.3: Actualizar artículo (stock, costos, valores)
                $sql_update_articulo = "UPDATE articulo SET
                    valor_fin_kardex = (SELECT valor_final FROM kardex WHERE idarticulo = ? AND transaccion = 'COMPRA' ORDER BY idkardex DESC LIMIT 1),
                    precio_final_kardex = (SELECT costo_2 FROM kardex WHERE idarticulo = ? ORDER BY idkardex DESC LIMIT 1),
                    saldo_finu = saldo_finu + ?,
                    comprast = comprast + ?,
                    valor_finu = ((saldo_iniu + comprast) - ventast) * precio_final_kardex,
                    stock = saldo_finu
                WHERE idarticulo = ?";

                $stmt_update = $conexion->prepare($sql_update_articulo);
                if (!$stmt_update) {
                    error_log("Error preparando UPDATE articulo: " . $conexion->error);
                    mysqli_rollback($conexion);
                    return false;
                }

                $stmt_update->bind_param("iiddi", $idarticulo_actual, $idarticulo_actual, $cantidad_actual, $cantidad_actual, $idarticulo_actual);

                if (!$stmt_update->execute()) {
                    error_log("Error ejecutando UPDATE articulo: " . $stmt_update->error);
                    $stmt_update->close();
                    mysqli_rollback($conexion);
                    return false;
                }
                $stmt_update->close();

                $num_elementos++;
            }

            // PASO 3: Si todo salió bien, hacer COMMIT
            mysqli_commit($conexion);
            return $idcompranew;

        } catch (Exception $e) {
            // En caso de error, hacer ROLLBACK
            error_log("Exception en insertar compra: " . $e->getMessage());
            mysqli_rollback($conexion);
            return false;
        }
    }



    /**
     * Inserta una nueva compra con subart\u00edculos
     * REFACTORIZADO: Usa prepared statements y transacciones
     *
     * @param int $idusuario ID del usuario
     * @param int $idproveedor ID del proveedor
     * @param string $fecha_emision Fecha de emisión
     * @param string $tipo_comprobante Tipo de comprobante
     * @param string $serie_comprobante Serie del comprobante
     * @param string $num_comprobante Número del comprobante
     * @param string $guia Guía de remisión
     * @param float $subtotal_compra Subtotal
     * @param float $total_igv IGV
     * @param float $total_compra Total
     * @param array $idarticulo Array de IDs de artículos
     * @param array $valor_unitario Array de valores unitarios
     * @param array $cantidad Array de cantidades
     * @param array $subtotalBD Array de subtotales
     * @param array $codigo Array de códigos
     * @param array $unidad_medida Array de unidades de medida
     * @param float $tcambio Tipo de cambio
     * @param string $hora Hora
     * @param string $moneda Moneda
     * @param int $idempresa ID de la empresa
     * @param string $codigobarra Código de barra
     * @param int $idarticulonarti ID del artículo padre
     * @param float $totalcantidad Total cantidad
     * @param float $totalcostounitario Total costo unitario
     * @param float $vunitario Valor unitario
     * @param float $factorc Factor de conversión
     * @return int|false ID de la compra creada o false si falla
     */
    public function insertarsubarticulo(
        $idusuario, $idproveedor, $fecha_emision, $tipo_comprobante, $serie_comprobante,
        $num_comprobante, $guia, $subtotal_compra, $total_igv, $total_compra, $idarticulo,
        $valor_unitario, $cantidad, $subtotalBD, $codigo, $unidad_medida, $tcambio, $hora,
        $moneda, $idempresa, $codigobarra, $idarticulonarti, $totalcantidad,
        $totalcostounitario, $vunitario, $factorc)
    {
        global $conexion;

        // SEGURIDAD: Iniciar transacción
        mysqli_begin_transaction($conexion);

        try {
            // PASO 1: Insertar compra principal
            $fecha_completa = $fecha_emision . ' ' . $hora;

            $sql_compra = "INSERT INTO compra (
                idusuario, idproveedor, fecha, tipo_documento, serie, numero, guia,
                subtotal, igv, total, subtotal_$, igv_$, total_$, tcambio, moneda, idempresa
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '0', '0', '0', ?, ?, ?)";

            $stmt_compra = $conexion->prepare($sql_compra);
            if (!$stmt_compra) {
                error_log("Error preparando INSERT compra (subarticulo): " . $conexion->error);
                mysqli_rollback($conexion);
                return false;
            }

            $stmt_compra->bind_param(
                "iisssssddddsi",
                $idusuario, $idproveedor, $fecha_completa, $tipo_comprobante,
                $serie_comprobante, $num_comprobante, $guia, $subtotal_compra,
                $total_igv, $total_compra, $tcambio, $moneda, $idempresa
            );

            if (!$stmt_compra->execute()) {
                error_log("Error ejecutando INSERT compra (subarticulo): " . $stmt_compra->error);
                $stmt_compra->close();
                mysqli_rollback($conexion);
                return false;
            }

            $idcompranew = $conexion->insert_id;
            $stmt_compra->close();

            // PASO 2: Insertar detalle de compra principal (artículo padre con factor)
            $cantidad_convertida = $totalcantidad / $factorc;

            $sql_detalle = "INSERT INTO detalle_compra_producto (
                idcompra, idarticulo, valor_unitario, cantidad, subtotal, valor_unitario_$, subtotal_$
            ) VALUES (?, ?, ?, ?, valor_unitario * ?, '0', '0')";

            $stmt_detalle = $conexion->prepare($sql_detalle);
            if (!$stmt_detalle) {
                error_log("Error preparando INSERT detalle (subarticulo): " . $conexion->error);
                mysqli_rollback($conexion);
                return false;
            }

            $stmt_detalle->bind_param("iiddd", $idcompranew, $idarticulonarti, $vunitario, $cantidad_convertida, $totalcantidad);

            if (!$stmt_detalle->execute()) {
                error_log("Error ejecutando INSERT detalle (subarticulo): " . $stmt_detalle->error);
                $stmt_detalle->close();
                mysqli_rollback($conexion);
                return false;
            }
            $stmt_detalle->close();

            // PASO 3: Procesar cada subarticulo
            $num_elementos = 0;
            while ($num_elementos < count($idarticulo)) {
                // Convertir USD a PEN si aplica
                if ($moneda == "USD") {
                    $valor_unitario[$num_elementos] = $valor_unitario[$num_elementos] * $tcambio;
                }

                $idarticulo_actual = $idarticulo[$num_elementos];
                $valor_unitario_actual = $valor_unitario[$num_elementos];
                $cantidad_actual = $cantidad[$num_elementos];
                $codigo_actual = $codigo[$num_elementos];
                $unidad_medida_actual = $unidad_medida[$num_elementos];
                $numero_doc = $serie_comprobante . '-' . $num_comprobante;
                $cantidad_factorc = $cantidad_actual / $factorc;
                $precio_con_igv = ($valor_unitario_actual * 0.18) + $valor_unitario_actual;

                // PASO 3.1: Insertar en tabla subarticulo
                $sql_subarticulo = "INSERT INTO subarticulo (
                    idarticulo, codigobarra, valorunitario, preciounitario, stock, umventa, estado
                ) VALUES (?, ?, ?, ?, ?, ?, '1')";

                $stmt_subartículo = $conexion->prepare($sql_subarticulo);
                if (!$stmt_subartículo) {
                    error_log("Error preparando INSERT subarticulo: " . $conexion->error);
                    mysqli_rollback($conexion);
                    return false;
                }

                $stmt_subartículo->bind_param("isddds", $idarticulo_actual, $codigo_actual, $valor_unitario_actual, $precio_con_igv, $cantidad_actual, $unidad_medida_actual);

                if (!$stmt_subartículo->execute()) {
                    error_log("Error ejecutando INSERT subarticulo: " . $stmt_subartículo->error);
                    $stmt_subartículo->close();
                    mysqli_rollback($conexion);
                    return false;
                }
                $stmt_subartículo->close();

                // PASO 3.2: Insertar en kardex (con valores en 0 para saldo_final, costo_2, valor_final)
                $sql_kardex = "INSERT INTO kardex (
                    idcomprobante, idarticulo, transaccion, codigo, fecha, tipo_documento,
                    numero_doc, cantidad, costo_1, unidad_medida, saldo_final, costo_2,
                    valor_final, idempresa, tcambio, moneda
                ) VALUES (?, ?, 'COMPRA', ?, ?, ?, ?, ?, ?, ?, '0', '0', '0', ?, ?, ?)";

                $stmt_kardex = $conexion->prepare($sql_kardex);
                if (!$stmt_kardex) {
                    error_log("Error preparando INSERT kardex (subarticulo): " . $conexion->error);
                    mysqli_rollback($conexion);
                    return false;
                }

                $stmt_kardex->bind_param(
                    "iissssddsiids",
                    $idcompranew, $idarticulo_actual, $codigo_actual, $fecha_emision,
                    $tipo_comprobante, $numero_doc, $cantidad_factorc, $valor_unitario_actual,
                    $unidad_medida_actual, $idempresa, $tcambio, $moneda
                );

                if (!$stmt_kardex->execute()) {
                    error_log("Error ejecutando INSERT kardex (subarticulo): " . $stmt_kardex->error);
                    $stmt_kardex->close();
                    mysqli_rollback($conexion);
                    return false;
                }
                $stmt_kardex->close();

                // PASO 3.3: Actualizar artículo
                $sql_update_articulo = "UPDATE articulo SET
                    valor_fin_kardex = (SELECT valor_final FROM kardex WHERE idarticulo = ? AND transaccion = 'COMPRA' ORDER BY idkardex DESC LIMIT 1),
                    precio_final_kardex = (SELECT costo_2 FROM kardex WHERE idarticulo = ? ORDER BY idkardex DESC LIMIT 1),
                    saldo_finu = saldo_finu + ?,
                    comprast = comprast + ?,
                    valor_finu = ((saldo_iniu + comprast) - ventast) * precio_final_kardex,
                    stock = saldo_finu
                WHERE idarticulo = ?";

                $stmt_update = $conexion->prepare($sql_update_articulo);
                if (!$stmt_update) {
                    error_log("Error preparando UPDATE articulo (subarticulo): " . $conexion->error);
                    mysqli_rollback($conexion);
                    return false;
                }

                $stmt_update->bind_param("iiddi", $idarticulo_actual, $idarticulo_actual, $cantidad_factorc, $cantidad_factorc, $idarticulo_actual);

                if (!$stmt_update->execute()) {
                    error_log("Error ejecutando UPDATE articulo (subarticulo): " . $stmt_update->error);
                    $stmt_update->close();
                    mysqli_rollback($conexion);
                    return false;
                }
                $stmt_update->close();

                $num_elementos++;
            }

            // PASO 4: Commit si todo OK
            mysqli_commit($conexion);
            return $idcompranew;

        } catch (Exception $e) {
            error_log("Exception en insertarsubarticulo: " . $e->getMessage());
            mysqli_rollback($conexion);
            return false;
        }
    }

    //Implementamos un método para anular categorías
    public function anular($idcompra)
    {
        // SEGURIDAD: Usar prepared statement para prevenir SQL Injection
        $sql="UPDATE compra SET estado='0' WHERE idcompra=?";
        return ejecutarConsultaPreparada($sql, "i", [$idcompra]);
    }
 
    //Implementar un método para mostrar los datos de un registro a modificar
    public function mostrar($idcompra)
    {
        $sql="select c.idcompra,DATE(c.fecha) as fecha,c.idproveedor,p.razon_social as proveedor,u.idusuario,u.nombre as usuario,c.tipo_documento,c.serie,c.numero,c.total,c.igv,c.estado FROM compra c inner join persona p ON c.idproveedor=p.idpersona inner join usuario u ON c.idusuario=u.idusuario WHERE c.idcompra='$idcompra'";
        return ejecutarConsultaSimpleFila($sql);
    }

    //Implementar un método para mostrar los datos de un registro a modificar
    public function eliminarcompra($idcompra)
    {
        $sql="select c.idcompra,DATE(c.fecha) as fecha,c.idproveedor,p.razon_social as proveedor,u.idusuario,u.nombre as usuario,c.tipo_documento,c.serie,c.numero,c.total,c.igv,c.estado FROM compra c inner join persona p ON c.idproveedor=p.idpersona inner join usuario u ON c.idusuario=u.idusuario WHERE c.idcompra='$idcompra'";
        return ejecutarConsultaSimpleFila($sql);
    }
 
    public function listarDetalle($idcompra)
    {
        $sql="select dc.idcompra, dc.idarticulo, a.nombre, dc.cantidad, dc.valor_unitario, dc.valor_venta, dc.subtotal FROM detalle_compra_producto dc inner join articulo a on dc.idarticulo=a.idarticulo where dc.idcompra='$idcompra'";
        return ejecutarConsulta($sql);
    }
 
    //Implementar un método para listar los registros
    public function listar($idempresa)
    {
        $sql="select 
        c.idcompra, 
        date_format(c.fecha,'%d-%m-%Y') as fecha, 
        c.idproveedor, 
        p.razon_social as proveedor, 
        u.idusuario,
        u.nombre as usuario, 
        ct1.descripcion, 
        c.serie, 
        c.numero, 
        format(c.total,2) as total, 
        c.igv, 
        c.estado 
        from 
        compra c inner join persona p on c.idproveedor=p.idpersona inner join usuario u on c.idusuario=u.idusuario inner join catalogo1 ct1 on c.tipo_documento=ct1.codigo inner join  empresa e on c.idempresa=e.idempresa where e.idempresa='$idempresa'  order by c.idcompra desc ";
        return ejecutarConsulta($sql);      
    }


      public function regcompra($año, $mes, $moneda, $idempresa)
    {

        if (strtoupper($moneda)=='USD') {
            $sql="select
         date_format(c.fecha, '%d') as fecha, c.tipo_documento, c.serie, c.numero, p.numero_documento, p.razon_social, c.subtotal_$ as subtotal, c.igv_$ as igv, c.total_$ as total
          from
            compra c inner join persona p on c.idproveedor=p.idpersona inner join empresa e on c.idempresa=e.idempresa
             where
             year(c.fecha)='$año' and month(c.fecha)='$mes' and UPPER(c.moneda)=UPPER('$moneda') and e.idempresa='$idempresa' and c.estado='1' order by c.fecha asc";
             }
            else
             {
            $sql="select
         date_format(c.fecha, '%d') as fecha, c.tipo_documento, c.serie, c.numero, p.numero_documento, p.razon_social, c.subtotal, c.igv, c.total
          from
            compra c inner join persona p on c.idproveedor=p.idpersona inner join empresa e on c.idempresa=e.idempresa
             where
             year(c.fecha)='$año' and month(c.fecha)='$mes' and UPPER(c.moneda)=UPPER('$moneda')   and e.idempresa='$idempresa' and c.estado='1'  order by c.fecha asc";
             }
        return ejecutarConsulta($sql);
    }

    public function totalregcompra($año, $mes)
    {
    $sql="select sum(c.subtotal) as valor_inafecto, sum(c.igv) as igv, sum(c.total) as total  from compra c inner join persona p on c.idproveedor=p.idpersona where year(c.fecha)='$año' and month(c.fecha)='$mes'";
        return ejecutarConsulta($sql);      
    }

    public function totalregcompraReporte($año, $mes, $moneda, $idempresa)
    {
        if (strtoupper($moneda)=='USD') {
    $sql="select
     sum(c.subtotal_$) as subtotal, sum(c.igv_$) as igv, sum(c.total_$) as total
      from
      compra c inner join persona p on c.idproveedor=p.idpersona inner join empresa e on c.idempresa=e.idempresa where year(c.fecha)='$año' and month(c.fecha)='$mes' and UPPER(c.moneda)=UPPER('$moneda') and e.idempresa='$idempresa' and c.estado='1'";
  }else{
        $sql="select
     sum(c.subtotal) as subtotal, sum(c.igv) as igv, sum(c.total) as total
      from
      compra c inner join persona p on c.idproveedor=p.idpersona inner join empresa e on c.idempresa=e.idempresa where year(c.fecha)='$año' and month(c.fecha)='$mes' and UPPER(c.moneda)=UPPER('$moneda') and e.idempresa='$idempresa' and c.estado='1'";
  }

        return ejecutarConsulta($sql);
    }

     public function compraReporte1($idcompra)
    {
$sql="select 
concat(c.serie,'-' ,
c.numero) as numero, 
date_format(c.fecha, '%d-%m-%Y') as fecha, 
p.razon_social as proveedor, 
u.nombre as usuario, 
ct1.descripcion as tdocumento, 
a.codigo, 
a.nombre, 
dtc.valor_unitario as vunitario, 
dtc.cantidad, 
dtc.subtotal as stotal, 
year(c.fecha) as año,
c.estado,
c.moneda,
c.tcambio,
um.nombreum
from 
compra c inner join detalle_compra_producto dtc on c.idcompra=dtc.idcompra inner join articulo a on dtc.idarticulo=a.idarticulo inner join persona p on c.idproveedor=p.idpersona inner join usuario u on c.idusuario=u.idusuario inner join catalogo1 ct1 on c.tipo_documento=ct1.codigo 
inner join umedida um on a.umedidacompra=um.idunidad
where c.idcompra='$idcompra'";
        return ejecutarConsulta($sql);      
    }


     public function compraReporte2($idcompra)
    {
$sql="select  
format(subtotal,2) as sbt, 
format(igv,2) as igv_, 
format(total,2) as ttl  
from 
compra  
where idcompra='$idcompra'";
        return ejecutarConsulta($sql);      
    }

    public function datosemp($idempresa)
    {

    $sql="select * from empresa where idempresa='$idempresa'";
    return ejecutarConsulta($sql);      
    }

    /**
     * Anula una compra y revierte los cambios en el inventario
     * REFACTORIZADO: Usa prepared statements, transacciones y corrige bug de iteración
     *
     * @param int $idcompra ID de la compra a anular
     * @param string $fechaemision Fecha de emisión de la compra
     * @param int $idempresa ID de la empresa
     * @return bool true si se anula correctamente, false en caso contrario
     */
    public function AnularCompra($idcompra, $fechaemision, $idempresa)
    {
        global $conexion;

        // SEGURIDAD: Iniciar transacción para garantizar atomicidad
        mysqli_begin_transaction($conexion);

        try {
            // PASO 1: Obtener detalles de la compra con prepared statement
            $sql_select = "SELECT dc.idcompra, a.idarticulo, dc.cantidad, dc.valor_unitario, a.codigo, a.unidad_medida
                          FROM detalle_compra_producto dc
                          INNER JOIN articulo a ON dc.idarticulo = a.idarticulo
                          WHERE dc.idcompra = ?";

            $stmt_select = $conexion->prepare($sql_select);
            if (!$stmt_select) {
                error_log("Error preparando SELECT en AnularCompra: " . $conexion->error);
                mysqli_rollback($conexion);
                return false;
            }

            $stmt_select->bind_param("i", $idcompra);

            if (!$stmt_select->execute()) {
                error_log("Error ejecutando SELECT en AnularCompra: " . $stmt_select->error);
                $stmt_select->close();
                mysqli_rollback($conexion);
                return false;
            }

            $resultado = $stmt_select->get_result();
            $stmt_select->close();

            // PASO 2: Procesar cada fila directamente en el while (SIN for loop incorrecto)
            while ($fila = $resultado->fetch_assoc()) {
                $idcompra_detalle = $fila["idcompra"];
                $idarticulo = $fila["idarticulo"];
                $cantidad = $fila["cantidad"];
                $valor_unitario = $fila["valor_unitario"];
                $codigo = $fila["codigo"];
                $unidad_medida = $fila["unidad_medida"];

                // PASO 2.1: Actualizar stock del artículo (restar la cantidad comprada)
                $sql_update_articulo = "UPDATE articulo
                                       SET saldo_finu = saldo_finu - ?,
                                           stock = stock - ?,
                                           comprast = comprast - ?
                                       WHERE idarticulo = ?";

                $stmt_update = $conexion->prepare($sql_update_articulo);
                if (!$stmt_update) {
                    error_log("Error preparando UPDATE articulo en AnularCompra: " . $conexion->error);
                    mysqli_rollback($conexion);
                    return false;
                }

                $stmt_update->bind_param("dddi", $cantidad, $cantidad, $cantidad, $idarticulo);

                if (!$stmt_update->execute()) {
                    error_log("Error ejecutando UPDATE articulo en AnularCompra: " . $stmt_update->error);
                    $stmt_update->close();
                    mysqli_rollback($conexion);
                    return false;
                }
                $stmt_update->close();

                // PASO 2.2: Actualizar valor final del artículo
                $sql_update_valor = "UPDATE articulo
                                    SET valor_finu = (saldo_iniu + comprast - ventast) * costo_compra
                                    WHERE idarticulo = ?";

                $stmt_valor = $conexion->prepare($sql_update_valor);
                if (!$stmt_valor) {
                    error_log("Error preparando UPDATE valor en AnularCompra: " . $conexion->error);
                    mysqli_rollback($conexion);
                    return false;
                }

                $stmt_valor->bind_param("i", $idarticulo);

                if (!$stmt_valor->execute()) {
                    error_log("Error ejecutando UPDATE valor en AnularCompra: " . $stmt_valor->error);
                    $stmt_valor->close();
                    mysqli_rollback($conexion);
                    return false;
                }
                $stmt_valor->close();

                // PASO 2.3: Actualizar kardex (marcar como COMPRA ANULADA)
                $sql_kardex = "UPDATE kardex
                              SET transaccion = 'COMPRA ANULADA'
                              WHERE idcomprobante = ? AND idarticulo = ? AND transaccion = 'COMPRA'";

                $stmt_kardex = $conexion->prepare($sql_kardex);
                if (!$stmt_kardex) {
                    error_log("Error preparando UPDATE kardex en AnularCompra: " . $conexion->error);
                    mysqli_rollback($conexion);
                    return false;
                }

                $stmt_kardex->bind_param("ii", $idcompra_detalle, $idarticulo);

                if (!$stmt_kardex->execute()) {
                    error_log("Error ejecutando UPDATE kardex en AnularCompra: " . $stmt_kardex->error);
                    $stmt_kardex->close();
                    mysqli_rollback($conexion);
                    return false;
                }
                $stmt_kardex->close();

                // PASO 2.4: Actualizar estado del detalle de compra
                $sql_detalle_estado = "UPDATE detalle_compra_producto
                                      SET estado = '3'
                                      WHERE idcompra = ? AND idarticulo = ?";

                $stmt_detalle = $conexion->prepare($sql_detalle_estado);
                if (!$stmt_detalle) {
                    error_log("Error preparando UPDATE detalle en AnularCompra: " . $conexion->error);
                    mysqli_rollback($conexion);
                    return false;
                }

                $stmt_detalle->bind_param("ii", $idcompra_detalle, $idarticulo);

                if (!$stmt_detalle->execute()) {
                    error_log("Error ejecutando UPDATE detalle en AnularCompra: " . $stmt_detalle->error);
                    $stmt_detalle->close();
                    mysqli_rollback($conexion);
                    return false;
                }
                $stmt_detalle->close();
            }

            // PASO 3: Actualizar estado de la compra a anulada (estado = 3)
            $sql_estado_compra = "UPDATE compra SET estado = '3' WHERE idcompra = ?";

            $stmt_estado = $conexion->prepare($sql_estado_compra);
            if (!$stmt_estado) {
                error_log("Error preparando UPDATE compra estado en AnularCompra: " . $conexion->error);
                mysqli_rollback($conexion);
                return false;
            }

            $stmt_estado->bind_param("i", $idcompra);

            if (!$stmt_estado->execute()) {
                error_log("Error ejecutando UPDATE compra estado en AnularCompra: " . $stmt_estado->error);
                $stmt_estado->close();
                mysqli_rollback($conexion);
                return false;
            }
            $stmt_estado->close();

            // PASO 4: Si todo salió bien, hacer COMMIT
            mysqli_commit($conexion);
            return true;

        } catch (Exception $e) {
            // En caso de error, hacer ROLLBACK
            error_log("Exception en AnularCompra: " . $e->getMessage());
            mysqli_rollback($conexion);
            return false;
        }
    }
     
}
 
?>