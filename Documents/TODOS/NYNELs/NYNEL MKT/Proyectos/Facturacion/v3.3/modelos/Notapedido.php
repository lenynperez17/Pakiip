<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";



Class Notapedido
{
    //Implementamos nuestro constructor
    public function __construct()
    {

    }

    //Implementamos un método para insertar registros para boleta
    public function insertar($idusuario, $fecha_emision_01, $firma_digital_36, $idempresa, $tipo_documento_06, $numeracion_07, $idcl, $codigo_tipo_15_1, $monto_15_2, $sumatoria_igv_18_1, $sumatoria_igv_18_2,  $sumatoria_igv_18_3,  $sumatoria_igv_18_4, $sumatoria_igv_18_5, $importe_total_23, $codigo_leyenda_26_1, $descripcion_leyenda_26_2, $tipo_documento_25_1, $guia_remision_25,  $version_ubl_37, $version_estructura_38, $tipo_moneda_24, $tasa_igv,  $idarticulo, $numero_orden_item_29, $cantidad_item_12, $codigo_precio_14_1, $precio_unitario, $igvBD, $igvBD5, $afectacion_igv_3, $afectacion_igv_4, $afectacion_igv_5, $afectacion_igv_6, $igvBD2, $vvu, $subtotalBD, $codigo, $unidad_medida, $idserie, $SerieReal, $numero_boleta, $tipodocuCliente, $rucCliente, $RazonSocial, $hora, $descdet, $vendedorsitio, $idnota, $tiponota, $cantidadreal, $faltante, $adelanto, $ncotizacion, $ambtra, $efectivo, $visa, $yape, $plin, $mastercard, $deposito)

    {

        $sql="insert into
        notapedido (idusuario,
          fecha_emision_01,
          firma_digital_36,
          idempresa,
          tipo_documento_06,
          numeracion_07,
          idcliente,
          codigo_tipo_15_1,
          monto_15_2,
          sumatoria_igv_18_1,
          sumatoria_igv_18_2,
          codigo_tributo_18_3,
          nombre_tributo_18_4,
          codigo_internacional_18_5,
          importe_total_23,
          codigo_leyenda_26_1,
          descripcion_leyenda_26_2,
          tipo_documento_25_1,
          guia_remision_25,
          version_ubl_37,
          version_estructura_38,
          tipo_moneda_24,
          tasa_igv,
          estado,
          tipodocuCliente,
          rucCliente,
          RazonSocial,
          tdescuento,
          vendedorsitio,
          tiponota,
          adelanto,
          faltante,
          ncotizacion,
          ambtra,
          efectivo,
          visa,
          yape,
          plin,
          mastercard,
          deposito
        )

        values

        ('$idusuario',
        '$fecha_emision_01 $hora',
        '$firma_digital_36',
        '$idempresa',
        '$tipo_documento_06',
        '$SerieReal-$numero_boleta',
        '$idcl',
        '$codigo_tipo_15_1',
        '$monto_15_2',
        '$sumatoria_igv_18_1',
        '$sumatoria_igv_18_2',
        '$sumatoria_igv_18_3',
        '$sumatoria_igv_18_4',
        '$sumatoria_igv_18_5',
        '$importe_total_23',
        '$codigo_leyenda_26_1',
        '$descripcion_leyenda_26_2',
        '$tipo_documento_25_1',
        '$guia_remision_25',
        '$version_ubl_37',
        '$version_estructura_38',
        '$tipo_moneda_24',
        '$tasa_igv',
        '5',
        '$tipodocuCliente',
        '$rucCliente',
        '$RazonSocial',
        '0.00',
        '$vendedorsitio',
        '$tiponota',
        '$adelanto',
        '$faltante',
        '$ncotizacion',
        '$ambtra',
        '$efectivo',
        '$visa',
        '$yape',
        '$plin',
        '$mastercard',
        '$deposito'
      )";
        //return ejecutarConsulta($sql);
        $idBoletaNew=ejecutarConsulta_retornarID($sql);

        $num_elementos=0;
        $sw=true;
        while ($num_elementos < count($idarticulo))
        {
            //Guardar en Detalle
        $sql_detalle = "insert into
        detalle_notapedido_producto(idboleta,
          idarticulo,
          numero_orden_item_29,
          cantidad_item_12,
          codigo_precio_14_1,
          precio_uni_item_14_2,
          afectacion_igv_item_monto_27_1,
          afectacion_igv_item_monto_27_2,
          afectacion_igv_3,
          afectacion_igv_4,
          afectacion_igv_5,
          afectacion_igv_6,
          igv_item,
          valor_uni_item_31,
          valor_venta_item_32,
          descdet,
          umedida
          )

            values

            (
            '$idBoletaNew',
            '$idarticulo[$num_elementos]',
            '$numero_orden_item_29[$num_elementos]',
            '$cantidad_item_12[$num_elementos]',
            '$codigo_precio_14_1',
            '$precio_unitario[$num_elementos]',
            '$igvBD[$num_elementos]',
            '$igvBD[$num_elementos]',
            '$afectacion_igv_3',
            '$afectacion_igv_4',
            '$afectacion_igv_5',
            '$afectacion_igv_6',
            '$igvBD2[$num_elementos]',
            '$vvu[$num_elementos]',
            '$subtotalBD[$num_elementos]',
            '$descdet[$num_elementos]',
            '$unidad_medida[$num_elementos]'
            )";

        //Guardar en Kardex
            $sql_kardex="insert into
            kardex
            (idcomprobante,
              idarticulo,
              transaccion,
              codigo,
              fecha,
              tipo_documento,
              numero_doc,
              cantidad,
              costo_1,
              unidad_medida,
              saldo_final,
              costo_2,
              valor_final,
              idempresa,
              tcambio,
              moneda )

              values

            ('$idBoletaNew',
            '$idarticulo[$num_elementos]',
            'VENTA',
            '$codigo[$num_elementos]',
            '$fecha_emision_01',
            '50' ,
            '$SerieReal-$numero_boleta',
            '$cantidadreal[$num_elementos]',
            '$precio_unitario[$num_elementos]',
            '$unidad_medida[$num_elementos]',
             '' ,
             '' ,
             '',
            '$idempresa',
            '',
            '$tipo_moneda_24')";

           ejecutarConsulta($sql_kardex) or $sw = false;
           ejecutarConsulta($sql_detalle) or $sw = false;



    // SI EL NUMERO DE COMPROBANTE YA EXISTE NO HARA LA OPERACION
    if ($idBoletaNew==""){
    $sw=false;
    }
    else
    {

     $sql_update_articulo="update
      articulo
      set
      saldo_finu=saldo_finu - '$cantidadreal[$num_elementos]',
      ventast=ventast + '$cantidadreal[$num_elementos]',
      valor_finu=(saldo_iniu+comprast-ventast) * precio_final_kardex,
      stock=saldo_finu,
      valor_fin_kardex=(select valor_final
        from
        kardex
        where
        idarticulo='$idarticulo[$num_elementos]' and transaccion='VENTA' order by idkardex desc limit 1)
        where
        idarticulo='$idarticulo[$num_elementos]'";

        ejecutarConsulta($sql_update_articulo) or $sw = false;


         //Para actualizar numeracion de las series de la factura
         $sql_update_numeracion="update
         numeracion
         set
         numero='$numero_boleta' where idnumeracion='$idserie'";
        ejecutarConsulta($sql_update_numeracion) or $sw = false;
         //Fin

    }
            $num_elementos=$num_elementos + 1;
        }


        if ($idnota!="") {

        $num_elementos=0;
        $sw=true;
        while ($num_elementos < count($idnota))
        {
          //Para actualizar numeracion de las series de la factura
         $sqlupdateestado="update
         notapedido
         set
         estado='5' where idboleta='$idnota[$num_elementos]'";
        ejecutarConsulta($sqlupdateestado) or $sw = false;
         //Fin
        $num_elementos=$num_elementos + 1;
        }
      }

      return $idBoletaNew;
    }



//Implementamos un método para anular la factura
public function anular($idboleta)
{

   $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

 $query="select idboleta, idarticulo  from detalle_notapedido_producto where idboleta='$idboleta'";
 $resultado = mysqli_query($connect,$query);


    $Idb=array();
    $Ida=array();
    $sw=true;

    while ($fila = mysqli_fetch_assoc($resultado)) {
    for($i=0; $i < count($resultado) ; $i++){
        $Idb[$i] = $fila["idboleta"];
        $Ida[$i] = $fila["idarticulo"];

    $sql_update_articulo="update
     detalle_notapedido_producto de inner join articulo a  on de.idarticulo=a.idarticulo set
       a.saldo_finu=a.saldo_finu + de.cantidad_item_12,
       a.stock=a.stock + de.cantidad_item_12,
       a.ventast=a.ventast - de.cantidad_item_12,
       a.valor_finu=(a.saldo_finu + a.comprast - a.ventast) * a.costo_compra
        where
        de.idboleta='$Idb[$i]' and de.idarticulo='$Ida[$i]'";

        //ACTUALIZAR TIPO TRANSACCION KARDEX
    //Guardar en Kardex
    $sql_kardex="insert into
    kardex
     (idcomprobante,
      idarticulo,
      transaccion,
      codigo,
      fecha,
      tipo_documento,
      numero_doc,
      cantidad,
      costo_1,
      unidad_medida,
      saldo_final,
      costo_2,valor_final)

            values

            ('$idboleta',

            (select a.idarticulo from articulo a inner join detalle_notapedido_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

            'ANULADO',

            (select a.codigo from articulo a inner join detalle_notapedido_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

             (select fecha_emision_01 from boleta where idboleta='$Idb[$i]'),
             '01',
             (select numeracion_07 from boleta where idboleta='$Idb[$i]'),

(select dtb.cantidad_item_12 from articulo a inner join detalle_notapedido_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

(select dtb.valor_uni_item_31 from articulo a inner join detalle_notapedido_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

(select a.unidad_medida from articulo a inner join detalle_notapedido_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

0, 0, 0)";

        $sqlestado="update
        notapedido
        set
        estado='0'
        where
        idboleta='$idboleta'";
        }

         ejecutarConsulta($sql_update_articulo) or $sw=false;
         ejecutarConsulta($sql_kardex) or $sw=false;
         ejecutarConsulta($sqlestado) or $sw=false;
        }

   return $sw;
}

public function baja($idnotap, $fecha_baja, $com, $hora)
{
   $sw=true;
   $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }
    $query="select idboleta, idarticulo  from detalle_notapedido_producto where idboleta='$idnotap'";
    $resultado = mysqli_query($connect,$query);
    $Idb=array();
    $Ida=array();
    while ($fila = mysqli_fetch_assoc($resultado)) {
    for($i=0; $i < count($resultado) ; $i++){
        $Idb[$i] = $fila["idboleta"];
        $Ida[$i] = $fila["idarticulo"];

    $sql_update_articulo="update
     detalle_notapedido_producto de inner join articulo a  on de.idarticulo=a.idarticulo set
       a.saldo_finu=a.saldo_finu + de.cantidad_item_12,
       a.stock=a.stock + de.cantidad_item_12,
       a.ventast=a.ventast - de.cantidad_item_12,
       a.valor_finu=(a.saldo_finu + a.comprast - a.ventast) * a.costo_compra
        where
        de.idboleta='$Idb[$i]' and de.idarticulo='$Ida[$i]'";

    //ACTUALIZAR TIPO TRANSACCION KARDEX
    //Guardar en Kardex
    $sql_kardex="insert into
    kardex
     (idcomprobante,
      idarticulo,
      transaccion,
      codigo,
      fecha,
      tipo_documento,
      numero_doc,
      cantidad,
      costo_1,
      unidad_medida,
      saldo_final,
      costo_2,valor_final)

            values

            ('$idnotap',

            (select a.idarticulo from articulo a inner join detalle_notapedido_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

            'ANULADO',

            (select a.codigo from articulo a inner join detalle_notapedido_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

             (select fecha_emision_01 from notapedido where idboleta='$Idb[$i]'),
             '50',
             (select numeracion_07 from notapedido where idboleta='$Idb[$i]'),

(select dtb.cantidad_item_12 from articulo a inner join detalle_notapedido_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

(select dtb.valor_uni_item_31 from articulo a inner join detalle_notapedido_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

(select a.unidad_medida from articulo a inner join detalle_notapedido_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

0, 0, 0)";

        }

         ejecutarConsulta($sql_update_articulo) or $sw=false;
         ejecutarConsulta($sql_kardex) or $sw=false;

        }

        $sqlestado="update
        notapedido
        set
        estado='3',
        fecha_baja='$fecha_baja $hora',
        comentario_baja='$com'
        where
        idboleta='$idnotap'";
        ejecutarConsulta($sqlestado) or $sw=false;



  return $sw;

}


    //Implementar un método para mostrar los datos de un registro a modificar
    public function mostrar($idboleta)
    {
        $sql="select
        b.idboleta,
        date(b.fecha_emision_01) as fecha,
        b.idcliente,p.razon_social as cliente,
        p.numero_documento,
        p.domicilio_fiscal,
        u.idusuario,
        u.nombre as usuario,
        b.tipo_documento_06,
        b.numeracion_07,
        b.importe_total_23,
        b.estado
        from
        notapedido b inner join persona p on b.idcliente=p.idpersona inner join usuario u on b.idusuario=u.idusuario WHERE b.idboleta='$idboleta'";
        return ejecutarConsultaSimpleFila($sql);
    }

    public function listarDetalle($idboleta)
    {
        $sql="select
        df.idboleta,
        df.idarticulo,
        a.nombre,
        df.cantidad_item_12,
        df.valor_uni_item_14,
        df.valor_venta_item_21,
        df.igv_item
        from
        detalle_fac_art df inner join articulo a on df.idarticulo=a.idarticulo where df.idboleta='$idboleta'";
        return ejecutarConsulta($sql);
    }

    //Implementar un método para listar los registros
    public function listar()
    {
        $sql="select
        b.idboleta,
        date_format(b.fecha_emision_01,'%d/%m/%y') as fecha,
        b.idcliente,
        left(p.razon_social,20) as cliente,
        b.vendedorsitio,
        u.nombre as usuario,
        b.tipo_documento_06,
        b.numeracion_07,
        b.monto_15_2,
        b.adelanto,
        b.faltante,
        format(b.importe_total_23,2) as importe_total_23,
        b.estado,
        p.nombres,
        p.apellidos,
        e.numero_ruc,
        p.email,
        p.idpersona
        from
        notapedido b inner join persona p on b.idcliente=p.idpersona
        inner join usuario u on b.idusuario=u.idusuario
        inner join empresa e on b.idempresa=e.idempresa
        order by b.idboleta desc";
        return ejecutarConsulta($sql);
    }


    public function ventacabecera($idboleta){
        $sql="select
        np.idboleta,
        np.idcliente,
        p.razon_social,
        p.nombres as cliente,
        p.domicilio_fiscal as direccion,
        p.tipo_documento,
        p.numero_documento,
        p.email,
        p.telefono1,
        np.idusuario,
        u.nombre as usuario,
        np.tipo_documento_06,
        np.numeracion_07,
        right(substring_index(np.numeracion_07,'-',1),4) as serie,
        np.numeracion_07 as numerofac,
        date_format(np.fecha_emision_01,'%d-%m-%Y') as fecha,
        date_format(np.fecha_emision_01,'%Y-%m-%d') as fecha2,
        np.importe_total_23 as totalLetras,
        np.importe_total_23 as itotal,
        np.estado,
        e.numero_ruc,
        np.tdescuento,
        np.guia_remision_25 as guia,
        np.vendedorsitio,
        np.sumatoria_igv_18_1,
        np.ncotizacion,
        np.ambtra,
        np.efectivo,
        np.visa,
        np.yape,
        np.plin,
        np.mastercard,
        np.deposito,
        np.adelanto,
        np.faltante,
        np.monto_15_2 as subtotal

        from
        notapedido np inner join persona p on np.idcliente=p.idpersona
        inner join usuario u on np.idusuario=u.idusuario
        inner join empresa e on np.idempresa=e.idempresa
         where np.idboleta='$idboleta'";
        return ejecutarConsulta($sql);
    }

     public function recibospendientes($idcliente){
        $sql="select  numeracion_07, importe_total_23 as total from notapedido np inner join persona p on np.idcliente=p.idpersona where p.idpersona='$idcliente' and np.estado='1'";
        return ejecutarConsulta($sql);
    }

    public function ventadetalle($idboleta){
        $sql="select
        a.nombre as articulo,
        a.codigo,
        format(db.cantidad_item_12,2) as cantidad_item_12,
        db.valor_uni_item_31,
        db.precio_uni_item_14_2,
        db.valor_venta_item_32,
        format(valor_venta_item_32,2) as subtotal,
        db.dcto_item,
        db.descdet,
        um.abre
        from
        detalle_notapedido_producto db inner join articulo a on db.idarticulo=a.idarticulo inner join umedida um on a.unidad_medida=um.idunidad
        where
        db.idboleta='$idboleta'";
        return ejecutarConsulta($sql);
    }

        public function listarD()
    {
        $sql="select
        documento
        from
        correlativo
        where
        documento='factura' or documento='boleta' or documento='nota de credito'or documento='nota de debito' group by documento";
        return ejecutarConsulta($sql);
    }

    public function datosemp()
    {

    $sql="select * from empresa where idempresa='1'";
    return ejecutarConsulta($sql);
    }

    //Implementamos un método para dar de baja a factura
public function ActualizarEstado($idboleta,$st)
{
        $sw=true;
        $sqlestado="update notapedido set estado='$st' where idboleta='$idboleta'";
        ejecutarConsulta($sqlestado) or $sw=false;
    return $sw;
}


 public function listarcomprobantes($dnicliente)
    {
        $sql="select
        n.idboleta,
        date_format(n.fecha_emision_01,'%d/%m/%y') as fecha,
        n.idcliente,
        left(p.razon_social,20) as cliente,
        u.nombre as usuario,
        n.tipo_documento_06,
        n.numeracion_07,
        format(n.importe_total_23,2) as total,
        n.estado,
        p.nombres,
        p.apellidos,
        e.numero_ruc,
        n.numeracion_07 as numeroserie
        from
        notapedido n inner join persona p on n.idcliente=p.idpersona
        inner join usuario u on n.idusuario=u.idusuario
        inner join empresa e on n.idempresa=e.idempresa
        where p.numero_documento='$dnicliente' and n.estado='1'
        order by n.idboleta desc";
        return ejecutarConsulta($sql);
    }

    public function listarcomprobantesCE()
    {
        $sql="select
        n.idboleta,
        date_format(n.fecha_emision_01,'%d/%m/%y') as fecha,
        n.idcliente,
        left(p.razon_social,20) as cliente,
        u.nombre as usuario,
        n.tipo_documento_06,
        n.numeracion_07,
        format(n.importe_total_23,2) as total,
        n.estado,
        p.nombres,
        p.apellidos,
        e.numero_ruc,
        n.numeracion_07 as numeroserie
        from
        notapedido n inner join persona p on n.idcliente=p.idpersona
        inner join usuario u on n.idusuario=u.idusuario
        inner join empresa e on n.idempresa=e.idempresa
        where n.estado='1'
        order by n.idboleta desc";
        return ejecutarConsulta($sql);
    }




    public function actualizarestados($idnota, $cestado)
    {
        $num_elementos=0;
        $sw=true;
         while ($num_elementos < count($idnota))
        {
     //Guardar en Detalle
     $sql = "update notapedido set estado='$cestado' where idboleta= '$idnota[$num_elementos]'";
     ejecutarConsulta($sql) or $sw = false;
     $num_elementos=$num_elementos + 1;
        }

      return $sw;
    }

        public function almacenlista()
    {

    $sql="select * from almacen where estado='1' order by idalmacen";
    return ejecutarConsulta($sql);
    }


    public function mostrarultimocomprobanteId($idempresa)
    {
    $sql="select np.idboleta, e.tipoimpresion from notapedido np inner join empresa e on np.idempresa=e.idempresa  where e.idempresa='$idempresa'  order by idboleta desc limit 1";
    return ejecutarConsultaSimpleFila($sql);
    }



}
?>
