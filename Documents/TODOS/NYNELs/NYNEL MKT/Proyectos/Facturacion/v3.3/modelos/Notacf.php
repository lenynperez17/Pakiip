<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";
 
Class Notacf
{
    //Implementamos nuestro constructor
    public function __construct()
    {
 
    }
 
    //Implementar un método para listar los registros y mostrar en el select
    public function selectD()
    {
        $sql="select codigo, descripcion from catalogo9";
        return ejecutarConsulta($sql);      
    }
    
    
function buscarComprobante($idempresa, $moneda){
    
    $sql="select
     idfactura, 
     tipo_documento as tdcliente, 
     numero_documento as ndcliente, 
     razon_social as rzcliente,
     domicilio_fiscal as domcliente, 
     tipo_documento_07 as tipocomp, 
     numeracion_08 as numerodoc, 
     total_operaciones_gravadas_monto_18_2 as subtotal, 
     sumatoria_igv_22_1 as igv, 
     importe_total_venta_27 as total, 
     date_format(fecha_emision_01, '%d-%m-%Y') as fecha1, 
     fecha2,
     tmoneda
     from
     (
     select f.idfactura, 
     p.tipo_documento, 
     p.numero_documento, 
     p.razon_social,
     p.domicilio_fiscal, 
     f.tipo_documento_07, 
     f.numeracion_08, 
     f.total_operaciones_gravadas_monto_18_2, 
     f.sumatoria_igv_22_1, 
     f.importe_total_venta_27, 
     f.fecha_emision_01, 
     f.fecha_emision_01 as fecha2,
     f.tipo_moneda_28 as tmoneda
     from 
     factura f inner join persona p on f.idcliente=p.idpersona  inner join empresa e on f.idempresa=e.idempresa 
     where 
     p.tipo_persona='cliente' and f.estado='5' and e.idempresa='$idempresa' and f.tipo_moneda_28='$moneda'
     
   ) as tabla order by fecha_emision_01 desc";
        return ejecutarConsulta($sql); 
    
}


function buscarComprobanteServicioFactura($idempresa){
    
    $sql="select
     idfactura, 
     tipo_documento as tdcliente, 
     numero_documento as ndcliente, 
     razon_social as rzcliente,
     domicilio_fiscal as domcliente, 
     tipo_documento_07 as tipocomp, 
     numeracion_08 as numerodoc, 
     total_operaciones_gravadas_monto_18_2 as subtotal, 
     sumatoria_igv_22_1 as igv, 
     importe_total_venta_27 as total, 
     date_format(fecha_emision_01, '%d-%m-%Y') as fecha1, 
     fecha2
     from
     (
     select f.idfactura, 
     p.tipo_documento, 
     p.numero_documento, 
     p.razon_social,
     p.domicilio_fiscal, 
     f.tipo_documento_07, 
     f.numeracion_08, 
     f.total_operaciones_gravadas_monto_18_2, 
     f.sumatoria_igv_22_1, 
     f.importe_total_venta_27, 
     f.fecha_emision_01, 
     f.fecha_emision_01 as fecha2
     from 
     facturaservicio f inner join persona p on f.idcliente=p.idpersona  inner join empresa e on f.idempresa=e.idempresa where p.tipo_persona='cliente' and f.estado='5' and e.idempresa='$idempresa'
   ) as tabla order by fecha_emision_01 desc";
        return ejecutarConsulta($sql); 
    
}

function buscarComprobanteId($idcomprobante){
    
    $sql="select  
    idfactura, 
    tipo_documento, 
    numero_documento, 
    razon_social,
    domicilio_fiscal as domicilio, 
    tipo_documento_07 as tipocomp, 
    numeracion_08 as numerodoc, 
    cantidad_item_12 as cantidad, 
    codigo,
    idarticulo,
    codigo_proveedor,
    nombre as descripcion, 
    format(precio_venta,2) as precio_venta, 
    stock, 
    unidad_medida, 
    precio_unitario,
    valor_uni_item_14 as vui, 
    afectacion_igv_item_16_1 as igvi, 
    precio_venta_item_15_2 as pvi, 
    valor_venta_item_21 as vvi, 
    total_operaciones_gravadas_monto_18_2 as subtotal, 
    sumatoria_igv_22_1 as igv, 
    importe_total_venta_27 as total,
    tmoneda,
    descarti
    from ( 
    select 
    f.idfactura, 
    p.tipo_documento, 
    p.numero_documento, 
    p.razon_social,
    p.domicilio_fiscal, 
    f.tipo_documento_07, 
    f.numeracion_08, 
    df.cantidad_item_12, 
    a.codigo,
    a.idarticulo,
    a.codigo_proveedor,
    a.nombre, 
    a.precio_venta, 
    a.stock, 
    a.unidad_medida, 
    (a.precio_venta * 1.18) as precio_unitario,
    df.valor_uni_item_14, 
    df.afectacion_igv_item_16_1, 
    df.precio_venta_item_15_2, 
    df.valor_venta_item_21, 
    f.total_operaciones_gravadas_monto_18_2, 
    f.sumatoria_igv_22_1, 
    f.importe_total_venta_27, 
    f.tipo_moneda_28 as tmoneda,
    df.descdet as descarti
    from
    factura f inner join detalle_fac_art df on f.idfactura=df.idfactura inner join articulo a on df.idarticulo=a.idarticulo inner join persona p on f.idcliente=p.idpersona 
    where p.tipo_persona='cliente'  and f.idfactura='$idcomprobante' and f.estado='5')
    as tabla";
        return ejecutarConsulta($sql); 
    
}


function buscarComprobanteIdFacturaServicio($idcomprobante){
    
    $sql="select  
    idfactura, 
    tipo_documento, 
    numero_documento, 
    razon_social,
    domicilio_fiscal as domicilio, 
    tipo_documento_07 as tipocomp, 
    numeracion_08 as numerodoc, 
    cantidad_item_12 as cantidad, 
    codigo,
    idarticulo,
    codigo_proveedor,
    nombre as descripcion, 
    format(precio_venta,2) as precio_venta, 
    stock, 
    unidad_medida, 
    precio_unitario,
    valor_uni_item_14 as vui, 
    afectacion_igv_item_16_1 as igvi, 
    precio_venta_item_15_2 as pvi, 
    valor_venta_item_21 as vvi, 
    total_operaciones_gravadas_monto_18_2 as subtotal, 
    sumatoria_igv_22_1 as igv, 
    importe_total_venta_27 as total 
    from ( 
    select 
    f.idfactura, 
    p.tipo_documento, 
    p.numero_documento, 
    p.razon_social,
    p.domicilio_fiscal, 
    f.tipo_documento_07, 
    f.numeracion_08, 
    df.cantidad_item_12, 
    a.codigo,
    a.id as idarticulo,
    a.codigo as codigo_proveedor,
    a.descripcion as nombre, 
    a.valor as precio_venta, 
    a.estado as stock, 
    a.descripcion as unidad_medida, 
    (a.valor * 1.18) as precio_unitario,
    df.valor_uni_item_14, 
    df.afectacion_igv_item_16_1, 
    df.precio_venta_item_15_2, 
    df.valor_venta_item_21, 
    f.total_operaciones_gravadas_monto_18_2, 
    f.sumatoria_igv_22_1, 
    f.importe_total_venta_27 
    from
    facturaservicio f inner join detalle_fac_art_ser df on f.idfactura=df.idfactura inner join servicios_inmuebles a on df.idarticulo=a.id inner join persona p on f.idcliente=p.idpersona where p.tipo_persona='cliente'  and f.idfactura='$idcomprobante' and f.estado='5')
    as tabla";
        return ejecutarConsulta($sql); 
    
}


/**
 * @deprecated Este método NO debe usarse para anular facturas.
 *
 * NORMATIVA SUNAT: Las anulaciones de facturas DEBEN realizarse mediante
 * Notas de Crédito (comprobante tipo 07), no mediante cambio directo de estado.
 *
 * Usar este método viola el procedimiento establecido por SUNAT y puede generar
 * inconsistencias en la contabilidad electrónica y en las declaraciones tributarias.
 *
 * SOLUCIÓN CORRECTA: Emitir una Nota de Crédito por el 100% del monto de la factura
 * con motivo "01 - Anulación de la operación".
 *
 * @see Notacf::insertar() Para crear la Nota de Crédito correspondiente
 * @param int $idfactura ID de la factura a anular
 * @return bool Resultado de la operación
 */
//Implementamos un método para anular la factura
public function anularFactura($idfactura)
{

      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

    $query="select idfactura, idarticulo  from detalle_fac_art where idfactura = '$idfactura'";
    $resultado = mysqli_query($connect,$query);

    $Idf=array();
    $Ida=array();
    $sw=true;
    $num_elementos=0;

    while ($fila = mysqli_fetch_assoc($resultado)) {
    for($i=0; $i < count($resultado) ; $i++){
        $Idf[$i] = $fila["idfactura"];  
        $Ida[$i] = $fila["idarticulo"];  

    $sql_update_articulo="update
     detalle_fac_art de inner join 
     articulo a  on de.idarticulo = a.idarticulo
     set
        a.saldo_finu = a.saldo_finu + de.cantidad_item_12, 
        a.stock = a.stock + de.cantidad_item_12, 
        a.ventast = a.ventast - de.cantidad_item_12        
        where
        de.idfactura='$Idf[$i]' and de.idarticulo='$Ida[$i]'";

        $sql_update_articulo_2="update
     detalle_fac_art de inner join articulo a  on de.idarticulo = a.idarticulo
      set
      a.valor_finu=(a.saldo_iniu + a.comprast - a.ventast) * a.costo_compra 
      where
      de.idfactura='$Idf[$i]' and de.idarticulo='$Ida[$i]'";

       $sqlbajafactura="update factura set estado='0' where idfactura='$Idf[$i]'";
       


        } //Fin for
         ejecutarConsulta($sql_update_articulo) or $sw=false;
         ejecutarConsulta($sql_update_articulo_2) or $sw=false;
         ejecutarConsulta($sqlbajafactura) or $sw=false;      

           
       
        } //Fin while
        
    return $sw; 
   
    }



/**
 * @deprecated Este método NO debe usarse para anular ítems de facturas.
 *
 * NORMATIVA SUNAT: Las anulaciones parciales de facturas DEBEN realizarse mediante
 * Notas de Crédito (comprobante tipo 07), no mediante modificación directa de ítems.
 *
 * Usar este método viola el procedimiento establecido por SUNAT y puede generar:
 * - Inconsistencias en la contabilidad electrónica
 * - Problemas en las declaraciones tributarias
 * - Descuadres en el inventario vs comprobantes electrónicos
 *
 * SOLUCIÓN CORRECTA: Emitir una Nota de Crédito parcial especificando los ítems
 * a anular con motivo "02 - Anulación por error en la descripción" o similar.
 *
 * @see Notacf::insertar() Para crear la Nota de Crédito correspondiente
 * @param int $idfactura ID de la factura
 * @param array $idarticulo IDs de artículos a anular
 * @param array $cantidad Cantidades a anular por artículo
 * @return bool Resultado de la operación
 */
//Implementamos un método para anular la factura
public function anularFacturaxItem($idfactura, $idarticulo, $cantidad)
{

      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }
      

    $Idf=array();
    $Ida=array();
    $sw=true;
    $num_elementos=0;    

    while ($num_elementos < count($idarticulo))
    {

    $query="select 
    idfactura, 
    idarticulo 
    from 
    detalle_fac_art 
    where 
    idfactura ='$idfactura' 
    and 
    idarticulo='$idarticulo[$num_elementos]'";
    $resultado = mysqli_query($connect, $query);
    

    while ($fila = mysqli_fetch_assoc($resultado)) {
    for($i=0; $i < count($resultado) ; $i++){
        $Idf[$i] = $fila["idfactura"];  
        $Ida[$i] = $fila["idarticulo"];  

    $sql_update_articulo="update
     detalle_fac_art de 
     inner join
     articulo a  
     on de.idarticulo = a.idarticulo
     set
        a.saldo_finu = a.saldo_finu + '$cantidad[$num_elementos]', 
        a.stock = a.stock + '$cantidad[$num_elementos]', 
        a.ventast = a.ventast - '$cantidad[$num_elementos]'
        where
        de.idfactura='$Idf[$i]' and de.idarticulo='$Ida[$i]'";

          $sql_update_articulo_2="update
     detalle_fac_art de 
     inner join
     articulo a  
     on de.idarticulo = a.idarticulo
     set
        a.valor_finu=(a.saldo_iniu + '$cantidad[$num_elementos]') * a.costo_compra 
        where
        de.idfactura='$Idf[$i]' and de.idarticulo='$Ida[$i]'";

        }
         ejecutarConsulta($sql_update_articulo) or $sw=false;
         ejecutarConsulta($sql_update_articulo_2) or $sw=false;



         
        }
        $num_elementos=$num_elementos + 1;
    //return $sw; 
      }    



    }
    
}
?>