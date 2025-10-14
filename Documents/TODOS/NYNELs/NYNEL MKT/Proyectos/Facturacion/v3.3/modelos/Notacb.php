<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";
 
Class Notacb
{
    //Implementamos nuestro constructor
    public function __construct()
    {
 
    }
 
   
    
function buscarComprobante($idempresa, $moneda){
    
    $sql="select  b.idboleta, 
    p.tipo_documento, 
    p.numero_documento, 
    p.razon_social, 
    p.domicilio_fiscal as domicilio, 
    b.tipo_documento_06 as tipocomp, 
    b.numeracion_07 as numerodoc, 
    b.monto_15_2 as subtotal, 
    b.sumatoria_igv_18_1 as igv, 
    b.importe_total_23 as total,
    b.tipo_moneda_24 as tmoneda, 
    date_format(b.fecha_emision_01, '%d-%m-%Y') as fecha1, 
    date_format(b.fecha_emision_01, '%Y/%m/%d %h:%i %p') as fecha2 
    from 
    boleta b inner join persona p on b.idcliente= p.idpersona inner join empresa e on b.idempresa=e.idempresa 
    where 
    p.tipo_persona='cliente' and b.estado='5' and e.idempresa='$idempresa' and b.tipo_moneda_24='$moneda' order by b.fecha_emision_01 desc";
        return ejecutarConsulta($sql); 
}

function buscarComprobanteBoletaServicio($idempresa){
    
    $sql="select  b.idboleta, 
    p.tipo_documento, 
    p.numero_documento, 
    p.razon_social, 
    p.domicilio_fiscal as domicilio, 
    b.tipo_documento_06 as tipocomp, 
    b.numeracion_07 as numerodoc, 
    b.monto_15_2 as subtotal, 
    b.sumatoria_igv_18_1 as igv, 
    b.importe_total_23 as total, 
    date_format(b.fecha_emision_01, '%d-%m-%Y') as fecha1, 
    date_format(b.fecha_emision_01, '%Y/%m/%d %h:%i %p') as fecha2 
    from 
    boletaservicio b inner join persona p on b.idcliente= p.idpersona inner join empresa e on b.idempresa=e.idempresa where p.tipo_persona='cliente' and b.estado='5' and e.idempresa='$idempresa' order by b.fecha_emision_01 desc";
        return ejecutarConsulta($sql); 
}

  

  function buscarComprobanteId($idcomprobante)
  {
    
    $sql="select
    idarticulo,
    precio_unitario,
    unidad_medida,
    stock,
    precio_venta,
    codigo_proveedor,
    idboleta, 
    tipo_documento, 
    numero_documento, 
    razon_social, 
    domicilio, 
    tipocomp, 
    numerodoc,  
    cantidad, 
    codigo,
    descripcion, 
    vui, 
    igvi, 
    pvi, 
    vvi, 
    subtotal, 
    igv, 
    total 
    from 
    (
    select
    a.idarticulo,
    (a.precio_venta * 1.18) as precio_unitario,
    a.unidad_medida, 
    a.stock,
    a.precio_venta, 
    a.codigo_proveedor,
    b.idboleta, 
    p.tipo_documento, 
    p.numero_documento, 
    p.razon_social, 
    p.domicilio_fiscal as domicilio, 
    b.tipo_documento_06 as tipocomp, 
    b.numeracion_07 as numerodoc,  
    format(db.cantidad_item_12,2) as cantidad, 
    a.codigo, a.nombre as descripcion, 
    db.valor_uni_item_31 as vui, 
    db.afectacion_igv_item_monto_27_1 as igvi, 
    db.precio_uni_item_14_2 as pvi, 
    db.valor_venta_item_32 as vvi, 
    b.monto_15_2 as subtotal, 
    b.sumatoria_igv_18_1 as igv, 
    b.importe_total_23 as total  
    from
    boleta b inner join detalle_boleta_producto db on b.idboleta=db.idboleta inner join articulo a on db.idarticulo=a.idarticulo inner join persona p on b.idcliente= p.idpersona where p.tipo_persona='cliente' and b.idboleta='$idcomprobante' and b.estado='5'
    )
    as tabla";
        return ejecutarConsulta($sql); 
    
}


function buscarComprobanteIdBoletaServicio($idcomprobante)
  {
    
    $sql="select
    idboleta, 
    tipo_documento, 
    numero_documento, 
    razon_social, 
    domicilio, 
    tipocomp, 
    numerodoc,  
    cantidad, 
    codigo,
    descripcion, 
    vui, 
    igvi, 
    pvi, 
    vvi, 
    subtotal, 
    igv, 
    total 
    from 
    (
    select
    b.idboleta, 
    p.tipo_documento, 
    p.numero_documento, 
    p.razon_social, 
    p.domicilio_fiscal as domicilio, 
    b.tipo_documento_06 as tipocomp, 
    b.numeracion_07 as numerodoc,  
    format(db.cantidad_item_12,2) as cantidad, 
    a.codigo, 
    a.descripcion, 
    db.valor_uni_item_31 as vui, 
    db.afectacion_igv_item_monto_27_1 as igvi, 
    db.precio_uni_item_14_2 as pvi, 
    db.valor_venta_item_32 as vvi, 
    b.monto_15_2 as subtotal, 
    b.sumatoria_igv_18_1 as igv, 
    b.importe_total_23 as total  
    from
    boletaservicio b inner join detalle_boleta_producto_ser db on b.idboleta=db.idboleta inner join servicios_inmuebles a on db.idarticulo=a.id inner join persona p on b.idcliente= p.idpersona where p.tipo_persona='cliente' and b.idboleta='$idcomprobante' and b.estado='5')
    as tabla";
        return ejecutarConsulta($sql); 
    
}



/**
 * @deprecated Este método NO debe usarse para anular boletas.
 *
 * NORMATIVA SUNAT: Las anulaciones de boletas DEBEN realizarse mediante
 * Notas de Crédito (comprobante tipo 07), no mediante cambio directo de estado.
 *
 * Usar este método viola el procedimiento establecido por SUNAT y puede generar
 * inconsistencias en la contabilidad electrónica y en las declaraciones tributarias.
 *
 * SOLUCIÓN CORRECTA: Emitir una Nota de Crédito por el 100% del monto de la boleta
 * con motivo "01 - Anulación de la operación".
 *
 * @see Notacb::insertar() Para crear la Nota de Crédito correspondiente
 * @param int $idboleta ID de la boleta a anular
 * @return bool Resultado de la operación
 */
public function anularBoleta($idboleta)
{

     $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }
      $query="select idboleta, idarticulo  from detalle_boleta_producto where idboleta='$idboleta'";

    $resultado = mysqli_query($connect,$query);

    $Idb=array();
    $Ida=array();
    $sw=true;

    while ($fila = mysqli_fetch_assoc($resultado)) {
    for($i=0; $i < count($resultado) ; $i++){
        $Idb[$i] = $fila["idboleta"];  
        $Ida[$i] = $fila["idarticulo"];  

    $sql_update_articulo="update detalle_boleta_producto de inner join articulo a  on de.idarticulo=a.idarticulo 
    set 
    a.saldo_finu=a.saldo_finu + de.cantidad_item_12, 
    a.stock=a.stock + de.cantidad_item_12,
    a.ventast=a.ventast - de.cantidad_item_12 
    where
    de.idboleta='$Idb[$i]' and de.idarticulo='$Ida[$i]'";


    $sql_update_articulo_2="update detalle_boleta_producto de inner join articulo a  on de.idarticulo=a.idarticulo 
    set  
    a.valor_finu=(a.saldo_iniu + a.comprast - a.ventast) * a.costo_compra  
    where 
    de.idboleta='$Idb[$i]' and de.idarticulo='$Ida[$i]'";

    $sqlbajaboleta="update boleta set estado='0' where idboleta='$Idb[$i]'";
    
        }
        
         ejecutarConsulta($sql_update_articulo) or $sw=false;
         ejecutarConsulta($sql_update_articulo_2) or $sw=false;
         ejecutarConsulta($sqlbajaboleta) or $sw=false;      
         
        }
    return $sw;    
}

/**
 * @deprecated Este método NO debe usarse para anular ítems de boletas.
 *
 * NORMATIVA SUNAT: Las anulaciones parciales de boletas DEBEN realizarse mediante
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
 * @see Notacb::insertar() Para crear la Nota de Crédito correspondiente
 * @param int $idboleta ID de la boleta
 * @param array $idarticulo IDs de artículos a anular
 * @param array $cantidad Cantidades a anular por artículo
 * @return bool Resultado de la operación
 */
public function anularBoletaxItem($idboleta, $idarticulo, $cantidad)
{

     $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

    $Idb=array();
    $Ida=array();
    $sw=true;
    $num_elementos=0;    

    while ($num_elementos < count($idarticulo))
    {
      $query="select idboleta, idarticulo  
      from detalle_boleta_producto 
      where 
      idboleta='$idboleta' and 
      idarticulo='$idarticulo[$num_elementos]'";

    $resultado = mysqli_query($connect,$query);

    while ($fila = mysqli_fetch_assoc($resultado)) {
    for($i=0; $i < count($resultado) ; $i++){
        $Idb[$i] = $fila["idboleta"];  
        $Ida[$i] = $fila["idarticulo"];  

    $sql_update_articulo="update detalle_boleta_producto de inner join articulo a  on de.idarticulo=a.idarticulo 
    set  
    a.saldo_finu=a.saldo_finu + '$cantidad[$num_elementos]', 
    a.stock=a.stock + '$cantidad[$num_elementos]', 
    a.ventast=a.ventast - '$cantidad[$num_elementos]'
    where 
    de.idboleta='$Idb[$i]' and de.idarticulo='$Ida[$i]'";

    $sql_update_articulo_2="update detalle_boleta_producto de inner join articulo a  on de.idarticulo=a.idarticulo 
    set  
    a.valor_finu=(a.saldo_iniu + a.comprast - a.ventast) * a.costo_compra
    where 
    de.idboleta='$Idb[$i]' and de.idarticulo='$Ida[$i]'";

        
        }
         ejecutarConsulta($sql_update_articulo) or $sw=false;
        ejecutarConsulta($sql_update_articulo_2) or $sw=false;
        }
    $num_elementos=$num_elementos + 1;
   
      }      
}






}
?>