<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";


Class Pedidorapido
{

    //Implementamos nuestro constructor
    public function __construct()
    {
 

    }


public function insertarTc($fechatc, $compra, $venta)
    {
        $sql="insert into tcambio (fecha, compra, venta)
        values ('$fechatc', '$compra', '$venta')";
        return ejecutarConsulta($sql);
    }

  //Implementamos un método para editar registros
  public function editarTc($id, $fechatc, $compra, $venta)
  {
        $sql="update tcambio  set fecha='$fechatc', compra='$compra', venta='$venta' where idtipocambio='$id' ";
        return ejecutarConsulta($sql);
  }

  public function mesalist()
  {
    $sql="select * from mesa";
    return  ejecutarConsulta($sql);
  }


  public function insertarCaja($fechacaja, $montoi, $montof, $idempresa)
    {
        $sql="insert into caja (fecha, montoi, montof, idempresa)
        values ('$fechacaja', '$montoi', '$montof', '$idempresa')";
        return ejecutarConsulta($sql);
    }


    public function registraringreso($idcaja, $concepto, $monto)
    {
      $sw=true;
        $sql="insert into ingresocaja (idcaja, concepto, monto)
        values ('$idcaja', '$concepto', '$monto')";
        ejecutarConsulta($sql) or $sw = false;

        $sqlupdate="update caja set montof = montof + '$monto' where idcaja='$idcaja' ";
        ejecutarConsulta($sqlupdate) or $sw = false;


        return $sw;
    }


    public function registrarsalida($idcaja, $concepto, $monto)
    {
      $sw=true;
        $sql="insert into salidacaja (idcaja, concepto, monto)
        values ('$idcaja', '$concepto', '$monto')";
        ejecutarConsulta($sql) or $sw = false;

        $sqlupdate="update caja set montof = montof - '$monto' where idcaja='$idcaja' ";
        ejecutarConsulta($sqlupdate) or $sw = false;


        return $sw;
    }

  //Implementamos un método para editar registros
  public function editarCaja($idcaja, $fecha, $montoi, $montof, $st, $idempresa)
  {
        $sql="update caja  set fecha='$fecha', montoi='$montoi', montof='$montof', estado='$st', idempresa='$idempresa' where idcaja='$idcaja' ";
        return ejecutarConsulta($sql);
  }

 
 
    //Implementamos un método para insertar registros para factura
    public function insertar($idusuario, $fecha_emision, $firma_digital, $idempresa, $tipo_documento, $numeracion, $idcliente, $total_operaciones_gravadas_codigo, $total_operaciones_gravadas_monto, $sumatoria_igv_1, $sumatoria_igv_2, $codigo_tributo_3, $nombre_tributo_4, $codigo_internacional_5, $importe_total_venta, $tipo_documento_guia, $guia_remision_29_2, $codigo_leyenda_1, $descripcion_leyenda_2, $version_ubl, $version_estructura, $tipo_moneda, $tasa_igv ,   $idarticulo,    $numero_orden_item, $cantidad, $codigo_precio, $pvt, $igvBD2, $igvBD3, $afectacion_igv_3, $afectacion_igv_4, $afectacion_igv_5, $afectacion_igv_6, $igvBD, $valor_unitario, $subtotalBD, $codigo, $unidad_medida, $idserie, $SerieReal, $numero_factura, $tipodocuCliente ,$rucCliente,  $RazonSocial, $hora, $sumadcto, $vendedorsitio, $email, $domicilio_fiscal2, $codigotributo, $tdescuento,$tcambio,$tipopago , $nroreferencia , $ipagado, $saldo, $descdet, $total_icbper, $tipofactura)
    {

        $st='1';
        if ($SerieReal=='0001' || $SerieReal=='0002') {
          $st='6';
        }

        $sql="insert into 
        factura
         (  
            
            idusuario,
            fecha_emision_01, 
            firmadigital_02, 
            idempresa, 
            tipo_documento_07, 
            numeracion_08, 
            idcliente, 
            total_operaciones_gravadas_codigo_18_1, 
            total_operaciones_gravadas_monto_18_2, 
            sumatoria_igv_22_1, 
            sumatoria_igv_22_2, 
            codigo_tributo_22_3, 
            nombre_tributo_22_4, 
            codigo_internacional_22_5, 
            importe_total_venta_27, 
            tipo_documento_29_1,
             guia_remision_29_2, 
             codigo_leyenda_31_1, 
             descripcion_leyenda_31_2, 
             version_ubl_36, 
             version_estructura_37, 
             tipo_moneda_28, 
             tasa_igv, 
             estado, 
             tipodocuCliente, 
             rucCliente, 
             RazonSocial,
             tdescuento,
             vendedorsitio,
             tcambio,
             tipopago,
             nroreferencia, 
             ipagado,
             saldo,
             DetalleSunat,
             icbper,
             tipofactura
          )
          values
          (
          
          '$idusuario',
          '$fecha_emision $hora',
          '$firma_digital',
          '$idempresa',
          '$tipo_documento',
          '$SerieReal-$numero_factura',
          '$idcliente',
          '$total_operaciones_gravadas_codigo',
          '$total_operaciones_gravadas_monto',
          '$sumatoria_igv_1',
          '$sumatoria_igv_2',
          (select codigo from catalogo5 where codigo='$codigo_tributo_3'),
          (select descripcion from catalogo5 where codigo='$codigo_tributo_3'),
          (select unece5153 from catalogo5 where codigo='$codigo_tributo_3'),
          '$importe_total_venta',
          '$tipo_documento_guia',
          '$guia_remision_29_2',
          '$codigo_leyenda_1',
          '$descripcion_leyenda_2',
          '$version_ubl',
          '$version_estructura',
          '$tipo_moneda',
          '$tasa_igv',
          '$st', 
          '$tipodocuCliente ',
          '$rucCliente',
          '$RazonSocial',
          '$tdescuento',
          '$vendedorsitio',
          '$tcambio',
          '$tipopago',
          '$nroreferencia',
          '$ipagado',
          '$saldo',
          'Emitido',
          '$total_icbper',
          '$tipofactura'
        )";
        //return ejecutarConsulta($sql);
        $idfacturanew=ejecutarConsulta_retornarID($sql);
 
        

        $num_elementos=0;
        $sw=true;

        while ($num_elementos < count($idarticulo))
        {



        //Guardar en Detalle
        $sql_detalle = "insert into 
        detalle_fac_art
        (
        idfactura, 
        idarticulo, 
        numero_orden_item_33, 
        cantidad_item_12, 
        codigo_precio_15_1, 
        precio_venta_item_15_2, 
        afectacion_igv_item_16_1, 
        afectacion_igv_item_16_2, 
        afectacion_igv_item_16_3, 
        afectacion_igv_item_16_4, 
        afectacion_igv_item_16_5, 
        afectacion_igv_item_16_6, 
        igv_item, 
        valor_uni_item_14, 
        valor_venta_item_21, 
        dcto_item,
        descdet
        ) 
          values 
          (
          '$idfacturanew', 
          '$idarticulo[$num_elementos]',
          '$numero_orden_item[$num_elementos]',
          '$cantidad[$num_elementos]',
          '$codigo_precio',
          '$valor_unitario[$num_elementos]',
          '$igvBD2[$num_elementos]',
          '$igvBD3[$num_elementos]',
          (select codigo from catalogo7 where codigo='$afectacion_igv_3[$num_elementos]'),
          (select codigo from catalogo5 where codigo='$afectacion_igv_4[$num_elementos]'),
          (select descripcion from catalogo5 where codigo='$afectacion_igv_4[$num_elementos]'),
          (select unece5153 from catalogo5 where codigo='$afectacion_igv_4[$num_elementos]'),
          '$igvBD[$num_elementos]',
          '$pvt[$num_elementos]',
          '$subtotalBD[$num_elementos]', 
          '$sumadcto[$num_elementos]',
          '$descdet[$num_elementos]'
        )";

        //Guardar en Kardex
            $sql_kardex="insert into kardex 
            (
            idcomprobante, 
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
            moneda
            ) 
            values
            (
            '$idfacturanew',
            '$idarticulo[$num_elementos]',
            'VENTA', 
            '$codigo[$num_elementos]', 
            '$fecha_emision' , 
            '01',
            '$SerieReal-$numero_factura', 
            '$cantidad[$num_elementos]', 
            '$pvt[$num_elementos]',
            '$unidad_medida[$num_elementos]',
            (select saldo_finu - '$cantidad[$num_elementos]' from articulo where idarticulo='$idarticulo[$num_elementos]') ,
            (select precio_final_kardex from articulo where idarticulo='$idarticulo[$num_elementos]'), saldo_final * costo_2,
            '$idempresa',
            '$tcambio',
            '$tipo_moneda'
          )";

          $sqlupdatecorreocliente="update persona set email='$email', domicilio_fiscal='$domicilio_fiscal2', razon_social='$RazonSocial', nombre_comercial='$RazonSocial'   where idpersona='$idcliente'";

            //return ejecutarConsulta($sql);
            ejecutarConsulta($sql_detalle) or $sw = false;
            ejecutarConsulta($sql_kardex) or $sw = false;
            ejecutarConsulta($sqlupdatecorreocliente) or $sw = false;

      // SI EL NUMERO DE COMPROBANTE YA EXISTE NO HARA LA OPERACIon
      if ($idfacturanew==""){
      $sw=false;
      }
      else
      {
          if ($idarticulo[$num_elementos]!='2') {
            //ACTUALIZA TABLA ARTICULOS SI ES SERVICIO
            $sql_update_articulo="update
            articulo set saldo_finu = saldo_finu - '$cantidad[$num_elementos]', 
            ventast = ventast + '$cantidad[$num_elementos]',
            valor_finu = (saldo_iniu + comprast - ventast) * precio_final_kardex, 
            stock = saldo_finu, 
            valor_fin_kardex=(select valor_final from kardex where idarticulo='$idarticulo[$num_elementos]' and transaccion='VENTA' order by idkardex desc limit 1)
             where
             idarticulo = '$idarticulo[$num_elementos]'";
             ejecutarConsulta($sql_update_articulo) or $sw = false;
          }
            

        }
            $num_elementos=$num_elementos + 1;
        } //Fin While

                //Para actualizar numeracion de las series de la factura
                 $sql_update_numeracion="update
                  numeracion 
                  set 
                  numero='$numero_factura' 
                  where 
                  idnumeracion='$idserie'";
                 ejecutarConsulta($sql_update_numeracion) or $sw = false;
                 //Fin



//================ EXPORTAR COMPROBANTES A TXT =============
      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp($idempresa);
    $datose = $datos->fetch_object();


    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutadata=$Prutas->rutadata; // ruta de la carpeta DATA
    $rutadatalt=$Prutas->rutadatalt; // ruta de la carpeta BAJA

    $query = "select
     date_format(f.fecha_emision_01, '%Y-%m-%d') as fecha, 
     right(substring_index(f.numeracion_08,'-',1),1) as serie,
     date_format(f.fecha_emision_01, '%H:%i:%s') as hora,
     p.tipo_documento as  tipodocuCliente, 
     p.numero_documento, 
     p.razon_social, 
     f.tipo_moneda_28, 
     f.total_operaciones_gravadas_monto_18_2 as subtotal, 
     f.sumatoria_igv_22_1 as igv, 
     f.importe_total_venta_27 as total, 
     f.tipo_documento_07 as tipocomp, 
     f.numeracion_08 as numerodoc, 
     f.estado, 
     f.tdescuento,
     f.codigo_tributo_22_3 as codigotrib,
     f.nombre_tributo_22_4  as nombretrib,
     f.codigo_internacional_22_5 as codigointtrib
     from 
     factura f inner join persona p on f.idcliente=p.idpersona where idfactura='$idfacturanew' and f.estado='1' order by numerodoc";

    $querydetfac = "select
       f.tipo_documento_07 as tipocomp, 
       f.numeracion_08 as numerodoc,  
       df.cantidad_item_12 as cantidad, 
       a.codigo, 
       a.nombre as descripcion, 
       a.unidad_medida as um,
       replace(format(df.valor_uni_item_14,5),',','') as vui, 
       df.igv_item as igvi, 
       df.precio_venta_item_15_2 as pvi, 
       df.valor_venta_item_21 as vvi,
       df.afectacion_igv_item_16_1 as sutribitem,

       df.afectacion_igv_item_16_3 as aigv,
       df.afectacion_igv_item_16_4 codtrib,
       df.afectacion_igv_item_16_5 as nomtrib,
       df.afectacion_igv_item_16_6 as coditrib,
       a.codigosunat,
       a.cicbper,
       a.nticbperi,
       a.ctticbperi,
       a.mticbperu

       from
       factura f inner join detalle_fac_art df on f.idfactura=df.idfactura inner join articulo a on df.idarticulo=a.idarticulo
          where f.idfactura='$idfacturanew' and f.estado='1' order by f.fecha_emision_01";


      $result = mysqli_query($connect, $query);  
      $resultf = mysqli_query($connect, $querydetfac); 


      //Parametros de salida
      $fecha=array();
      $serie=array();
      $tipodocu=array();
      $numdocu=array();
      $rasoc=array();
      $moneda=array();
      $codigotrib=array();
      $nombretrib=array();
      $codigointtrib=array();
      $subtotal=array();
      $igv=array();
      $total=array();
      $tdescu=array();
      //Parametros de salida
      

      $con=0; //COntador de variable
            
      while($row=mysqli_fetch_assoc($result)){
      for($i=0; $i <= count($result); $i++){
           $fecha[$i]=$row["fecha"]; //Fecha emision
           $serie[$i]=$row["serie"];
           $tipodocu[$i]=$row["tipodocuCliente"];
           $numdocu[$i]=$row["numero_documento"];
           $rasoc[$i]=$row["razon_social"];
           $moneda[$i]=$row["tipo_moneda_28"];
           $subtotal[$i]=$row["subtotal"];
           $igv[$i]=$row["igv"];
           $total[$i]=$row["total"];
           $tdescu[$i]=$row["tdescuento"];
           $hora=$row["hora"];
           $tipocomp=$row["tipocomp"];
           $numerodoc=$row["numerodoc"];
           $ruc=$datose->numero_ruc;
           $ubigueo=$datose->ubigueo;

           $codigotrib[$i]=$row["codigotrib"];//codigo de tributo de la tabla catalo 5
           $nombretrib[$i]=$row["nombretrib"];//NOmbre de tributo de la tabla catalo 5
           $codigointtrib[$i]=$row["codigointtrib"];//Codigo internacional de la tabla catalo 5


 //========================== SALIDA EN FORMATO DE ARCHIVOS INDEPENDIENTES ===============================
      // $path=$rutadata.$ruc."-".$tipocomp."-".$numerodoc.".cab";
      // $handle=fopen($path, "w");
      // fwrite($handle,"0101|".$fecha[$i]."|".$hora."|-|0000|".$tipodocu[$i]."|".$numdocu[$i]."|".$rasoc[$i]."|".$moneda[$i]."|".$igv[$i]."|".$subtotal[$i]."|".$total[$i]."|".$tdescu[$i]."|0|0|".$total[$i]."|2.1|2.0|"); 
      // fclose($handle);

      // $path=$rutadatalt.$ruc."-".$tipocomp."-".$numerodoc.".cab"; //RUTA ALTERNA DE DESCARGA
      // $handle=fopen($path, "w");
      // fwrite($handle,"0101|".$fecha[$i]."|".$hora."|-|0000|".$tipodocu[$i]."|".$numdocu[$i]."|".$rasoc[$i]."|".$moneda[$i]."|".$igv[$i]."|".$subtotal[$i]."|".$total[$i]."|".$tdescu[$i]."|0|0|".$total[$i]."|2.1|2.0|");  
      // fclose($handle);
  //========================== SALIDA EN FORMATO DE ARCHIVOS INDEPENDIENTES ===============================


       require_once "Letras.php";
       $V=new EnLetras(); 
       $con_letra=strtoupper($V->ValorEnLetras($total[$i],"NUEVOS SOLES"));

//========================== SALIDA EN FORMATO DE ARCHIVOS INDEPENDIENTES ===============================
      // $path=$rutadata.$ruc."-".$tipocomp."-".$numerodoc.".ley";
      // //$server_ley = $path; //Nombre archivo en FTP
      // $handle=fopen($path, "w");
      // fwrite($handle,"1000|".$con_letra."|"); 
      // fclose($handle);

      // $path=$rutadata.$ruc."-".$tipocomp."-".$numerodoc.".tri";
      // //$server_tri = $path; //Nombre archivo en FTP
      // $handle=fopen($path, "w");
      // //fwrite($handle,"1000|IGV|VAT|S|".$subtotal[$i]."|".$igv[$i]."|");  VERSION 1.1
      // fwrite($handle,"1000|IGV|VAT|".$subtotal[$i]."|".$igv[$i]."|"); 
      // fclose($handle);
//========================== SALIDA EN FORMATO DE ARCHIVOS INDEPENDIENTES ===============================

//======================================== FORMATO JSON ========================================================
 $json = array(
'cabecera' => array(
 'tipOperacion'=>'0101', 
  'fecEmision'=>$fecha[$i], 
  'horEmision'=>$hora, 
  'fecVencimiento'=>"-", 
  'codLocalEmisor'=>$ubigueo,
   'tipDocUsuario'=>$tipodocu[$i],
    'numDocUsuario'=>$numdocu[$i], 
    'rznSocialUsuario'=>$rasoc[$i],
     'tipMoneda'=>$moneda[$i],
      'sumTotTributos'=>number_format($igv[$i],2,'.',''), 
      'sumTotValVenta'=>number_format($subtotal[$i],2,'.',''),
       'sumPrecioVenta'=>number_format($total[$i],2,'.',''), 
       'sumDescTotal'=>number_format($tdescu[$i],2,'.',''),
        'sumOtrosCargos'=>"0.00", 
        'sumTotalAnticipos'=>"0.00",
         'sumImpVenta'=>number_format($total[$i],2,'.',''), 
         'ublVersionId'=>"2.1",
          'customizationId'=>"2.0"), 
            'detalle' => array(), 
              'leyendas' => array(),
               'tributos' => array());


//============================================== Leyenda JSON ================================================
  $json['leyendas'][] = array(
    'codLeyenda'=>"1000",
    'desLeyenda'=>$con_letra);

  $json['tributos'][] = array(
    'ideTributo'=>$codigotrib[$i], 
    'nomTributo'=>$nombretrib[$i], 
    'codTipTributo'=>$codigointtrib[$i], 
    'mtoBaseImponible'=>number_format($subtotal[$i],2,'.',''), 
    'mtoTributo'=>number_format($igv[$i],2,'.',''));
//============================================== Leyenda JSON ================================================


          }//For
           $i=$i+1;
           $con=$con+1;           
          }//While

          
      $codigo=array();
      $cantidad=array();
      $descripcion=array();
      $um=array();
      $vui=array();
      $igvi=array();
      $pvi=array();
      $vvi=array();
      $sutribitem=array();

      $aigv=array();
      $codtrib=array();
      $nomtrib=array();
      $coditrib=array();
      $codigosunat=array();

      //Nuevos codigos
      $cicbper=array();
      $nticbperi=array();
      $ctticbperi=array();
      $mticbperu=array();

      
      while($rowf=mysqli_fetch_assoc($resultf)){
      for($if=0; $if < count($resultf); $if++){
           $codigo[$if]=$rowf["codigo"];
           $cantidad[$if]=$rowf["cantidad"];
           $descripcion[$if]=$rowf["descripcion"];
           $vui[$if]=$rowf["vui"];
           $sutribitem[$if]=$rowf["sutribitem"];           
           $igvi[$if]=$rowf["igvi"];
           $pvi[$if]=$rowf["pvi"];
           $vvi[$if]=$rowf["vvi"];
           $um[$if]=$rowf["um"];
           $tipocompf=$rowf["tipocomp"];
           $numerodocf=$rowf["numerodoc"];
           $ruc=$datose->numero_ruc;

           $aigv[$if]=$rowf["aigv"];
           $codtrib[$if]=$rowf["codtrib"];
           $nomtrib[$if]=$rowf["nomtrib"];
           $coditrib[$if]=$rowf["coditrib"];
           $codigosunat[$if]=$rowf["codigosunat"];

           $cicbper[$if]=$rowf["cicbper"];
           $nticbperi[$if]=$rowf["nticbperi"];
           $ctticbperi[$if]=$rowf["ctticbperi"];
           $mticbperu[$if]=$rowf["mticbperu"];

          if ( $cicbper[$if]!='7152') {
              $cicbper[$if]=="-";
              //$vvi[$if]="0";
              //$cantidad[$if]="0";
              $nticbperi[$if]="";
              $ctticbperi[$if]="";
              $mticbperu[$if]="";
            }else{
              $vvi[$if]=$rowf["vvi"];
              $cantidad[$if]=$rowf["cantidad"];
          }

          
    // $pathf=$rutadata.$ruc."-".$tipocompf."-".$numerodocf.".det";
    // $handlef=fopen($pathf, "a");
    // fwrite($handlef, $um[$if]."|".$cantidad[$if]."|".$codigo[$if]."|-|".$descripcion[$if]."|".$vui[$if]."|".$sutribitem[$if]."|1000|".$sutribitem[$if]."|".$vvi[$if]."|IGV|VAT|10|18|-|||||||-||||||".$pvi[$if]."|".$vvi[$if]."|0|\r\n");  
    //        fclose($handlef);
 
    // $pathf=$rutadatalt.$ruc."-".$tipocompf."-".$numerodocf.".det";
    // $handlef=fopen($pathf, "a");
    // fwrite($handlef,$um[$if]."|".$cantidad[$if]."|".$codigo[$if]."|-|".$descripcion[$if]."|".$vui[$if]."|".$sutribitem[$if]."|1000|".$sutribitem[$if]."|".$vvi[$if]."|IGV|VAT|10|18|-|||||||-||||||".$pvi[$if]."|".$vvi[$if]."|0|\r\n");    
    //        fclose($handlef);

    //FORMATO JSON
    $json['detalle'][] = array(
      'codUnidadMedida'=>$um[$if], 
      'ctdUnidadItem'=>number_format($cantidad[$if],2,'.',''), 
      'codProducto'=>$codigo[$if], 
      'codProductoSUNAT'=>$codigosunat[$if], 
      'desItem'=>$descripcion[$if], 
      'mtoValorUnitario'=>number_format($vui[$if],5,'.',''), 
      'sumTotTributosItem'=>number_format($sutribitem[$if],2,'.',''), 
      'codTriIGV'=>$codtrib[$if], 
      'mtoIgvItem'=>number_format($sutribitem[$if],2,'.',''), 
      'mtoBaseIgvItem'=>number_format($vvi[$if],2,'.',''), 
      'nomTributoIgvItem'=>$nomtrib[$if], 
      'codTipTributoIgvItem'=>$coditrib[$if], 
      'tipAfeIGV'=>$aigv[$if], 
      'porIgvItem'=>"18.0", 
      'codTriISC'=>"-", 
      'mtoIscItem'=>"", 
      'mtoBaseIscItem'=>"", 
      'nomTributoIscItem'=>"", 
      'codTipTributoIscItem'=>"", 
      'tipSisISC'=>"", 
      'porIscItem'=>"", 
      'codTriOtroItem'=>"-", 
      'mtoTriOtroItem'=>"", 
      'mtoBaseTriOtroItem'=>"", 
      'nomTributoIOtroItem'=>"", 
      'codTipTributoIOtroItem'=>"", 
      'porTriOtroItem'=>"", 
      'codTriIcbper'=>$cicbper[$if], //=========== BOLSAS ======================== 
      'mtoTriIcbperItem'=>number_format($vvi[$if],2,'.',''), 
      'ctdBolsasTriIcbperItem'=>number_format($cantidad[$if],0,'',''), 
      'nomTributoIcbperItem'=>$nticbperi[$if], 
      'codTipTributoIcbperItem'=>$ctticbperi[$if], 
      'mtoTriIcbperUnidad'=>$mticbperu[$if], //=========== BOLSAS ========================
      'mtoPrecioVentaUnitario'=>number_format($pvi[$if],2,'.',''), 
      'mtoValorVentaItem'=>number_format($vvi[$if],2,'.',''), 
      'mtoValorReferencialUnitario'=>"0");
      }
      }


      $path=$rutadata.$ruc."-".$tipocomp."-".$numerodoc.".json";
      $jsonencoded = json_encode($json,JSON_UNESCAPED_UNICODE);
      $fh = fopen($path, 'w');
      fwrite($fh, $jsonencoded);
      fclose($fh);


      return $sw; //FIN DE LA FUNCION
//=======================================

} 
 



public function mostrarultimocomprobante($idempresa)
  {
    $sql="select f.estado, f.numeracion_08, p.email from factura f inner join empresa e on f.idempresa=e.idempresa inner join persona p on f.idcliente=p.idpersona  where e.idempresa='$idempresa'  order by idfactura desc limit 1";
    return ejecutarConsultaSimpleFila($sql);    
  }


  public function mostrarultimocomprobanteId($idempresa)
  {
    $sql="select idfactura from factura f inner join empresa e on f.idempresa=e.idempresa  where e.idempresa='$idempresa'  order by idfactura desc limit 1";
    return ejecutarConsultaSimpleFila($sql);    
  }


public function crearPDF($idfactura, $idempresa)
{
require('Factura.php');
//Obtenemos los datos de la cabecera de la venta actuall
require_once "../modelos/Factura.php";
$factura = new Factura();
$rsptav = $factura->ventacabecera($idfactura, $idempresa);
$datos = $factura->datosemp($idempresa);
//Recorremos todos los valores obtenidos
$regv = $rsptav->fetch_object();
$datose = $datos->fetch_object();
$logo = "../files/logo/".$datose->logo;
$ext_logo = substr($datose->logo, strpos($datose->logo,'.'),-4);
//Establecemos la configuración de la factura
$pdf = new PDF_Invoice( 'P', 'mm',  'A4' );
$pdf->AddPage();
#Establecemos los márgenes izquierda, arriba y derecha: 
$pdf->SetMargins(10, 10 , 10); 
#Establecemos el margen inferior: 
$pdf->SetAutoPageBreak(true,10); 
//Enviamos los datos de la empresa al método addSociete de la clase Factura
$pdf->addSociete(utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),utf8_decode("Dirección    : ").utf8_decode($datose->domicilio_fiscal)."\n".utf8_decode("Teléfono     : ").$datose->telefono1." - ".$datose->telefono2."\n" ."Email          : ".$datose->correo, $logo, $ext_logo);
$pdf->numFactura("$regv->numeracion_08" , "$datose->numero_ruc");
$pdf->RotatedText($regv->estado, 35,190,'ANULADO - DADO DE BAJA',45);
$pdf->temporaire( "" );
//Enviamos los datos del cliente al método addClientAdresse de la clase Factura
$pdf->addClientAdresse( $regv->fecha."   /  Hora: ".$regv->hora,    utf8_decode(htmlspecialchars_decode($regv->cliente)), $regv->numero_documento, utf8_decode($regv->direccion), $regv->estado, utf8_decode($regv->vendedorsitio), utf8_decode($regv->guia) );
if ($regv->nombretrib=="IGV") {
        $nombret="V.U.";
    }else{
        $nombret="PRECIO";
    }
//Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
$cols=array( "CODIGO"=>23,
             "DESCRIPCION"=>78,
             "CANTIDAD"=>22,
             $nombret=>25,
             "DSCTO"=>20,
             "SUBTOTAL"=>22);
$pdf->addCols( $cols);
$cols=array( "CODIGO"=>"L",
             "DESCRIPCION"=>"L",
             "CANTIDAD"=>"C",
             $nombret=>"R",
             "DSCTO" =>"R",
             "SUBTOTAL"=>"C");
$pdf->addLineFormat( $cols);
$pdf->addLineFormat($cols); 
//Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
$y= 62;
//Obtenemos todos los detalles de la venta actual
$rsptad = $factura->ventadetalle($idfactura);
 
while ($regd = $rsptad->fetch_object()) {
    if ($regd->nombretribu=="IGV") {
        $pv=$regd->valor_uni_item_14;
        $subt=$regd->subtotal;
    }else{
        $pv=$regd->precio;
        $subt=$regd->subtotal2;
    }
  $line = array( "CODIGO"=> "$regd->codigo",
                "DESCRIPCION"=>  utf8_decode(htmlspecialchars_decode("$regd->articulo"." - "."$regd->descdet")),
                "CANTIDAD"=> "$regd->cantidad_item_12"." "."$regd->unidad_medida",
                $nombret=> $pv,
                "DSCTO" => "$regd->descuento",
                "SUBTOTAL"=> $subt);
            $size = $pdf->addLine( $y, $line );
            $y   += $size + 2;
}
//======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($_SESSION['idempresa']);
    $Prutas = $Rrutas->fetch_object();
    $rutafirma=$Prutas->rutafirma; // ruta de la carpeta FIRMA
    $data[0] = "";

//===========PARA EXTRAER EL CODIGO HASH =============================
if ($regv->estado=='5') {
$facturaFirm=$regv->numero_ruc."-".$regv->tipo_documento_07."-".$regv->numeracion_08;
$sxe = new SimpleXMLElement($rutafirma.$facturaFirm.'.xml', null, true);
$urn = $sxe->getNamespaces(true);
$sxe->registerXPathNamespace('ds', $urn['ds']);
$data = $sxe->xpath('//ds:DigestValue');
}
else
{
     $data[0] = "";
}
//==================== PARA IMAGEN DEL CODIGO HASH ================================================
//set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'/generador-qr/temp'.DIRECTORY_SEPARATOR;
    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';
    include 'generador-qr/phpqrcode.php';    
    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR)){
        mkdir($PNG_TEMP_DIR);
    }
    $filename = $PNG_TEMP_DIR.'test.png';
    //processing form input
    //remember to sanitize user input in real-life solution !!!
$dataTxt=$regv->numero_ruc."|".$regv->tipo_documento_07."|".$regv->serie."|".$regv->numerofac."|".$regv->sumatoria_igv_22_1."|".$regv->importe_total_venta_27."|".$regv->fecha2."|".$regv->tipo_documento."|".$regv->numero_documento."|";
$errorCorrectionLevel = 'H';    
$matrixPointSize = '2';
    // user data
        $filename = $PNG_TEMP_DIR.'test'.md5($dataTxt.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($dataTxt, $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        //default data
        //QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
       //display generated file
        $PNG_WEB_DIR.basename($filename);
// //==================== PARA IMAGEN  ================================================
$logoQr = $filename;
//$logoQr = "../files/logo/".$datose->logo;
$ext_logoQr = substr($filename, strpos($filename,'.'),-4);
$pdf->ImgQr($logoQr, $ext_logoQr);
//======= PARA EXTRAER EL HASH DEL DOCUMENTO FIRMADO ========================================
//Convertimos el total en letras
require_once "Letras.php";
$V=new EnLetras(); 
$con_letra=strtoupper($V->ValorEnLetras($regv->importe_total_venta_27,"CON"));
$pdf->addCadreTVAs("".$con_letra);
$pdf->observSunat($regv->numeracion_08,$regv->estado, $data[0], $datose->webconsul,  $datose->nresolucion);
//Mostramos el impuesto
$pdf->addTVAs( $regv->sumatoria_igv_22_1 , $regv->importe_total_venta_27,"S/ ", $regv->tdescuento);
$pdf->addCadreEurosFrancs($regv->sumatoria_igv_22_1, $regv->nombretrib);
//===============SEGUNDA COPIA DE FACTURA=========================
//Enviamos los datos de la empresa al método addSociete de la clase Factura
$pdf->addSociete2(utf8_decode(htmlspecialchars_decode($datose->nombre_comercial)),utf8_decode("Dirección: ").utf8_decode($datose->domicilio_fiscal)."\n".utf8_decode("Teléfono: ").$datose->telefono1." - ".$datose->telefono2."\n" ."Email : ".$datose->correo, $logo, $ext_logo);
//Datos de la empresa
$pdf->numFactura2("$regv->numeracion_08" , "$datose->numero_ruc" );
$pdf->temporaire( "" );
////Enviamos los datos del cliente al método addClientAdresse de la clase Factura
$pdf->addClientAdresse2( $regv->fecha."   /  Hora: ".$regv->hora, utf8_decode($regv->cliente), $regv->numero_documento, utf8_decode($regv->direccion), $regv->estado, utf8_decode($regv->vendedorsitio), utf8_decode($regv->guia));
if ($regv->nombretrib=="IGV") {

        $nombret="V.U.";
    }else{
        $nombret="PRECIO";
    }

//Establecemos las columnas que va a tener la sección donde mostramos los detalles de la venta
$cols=array( "CODIGO"=>23,
             "DESCRIPCION"=>78,
             "CANTIDAD"=>22,
             $nombret=>25,
             "DSCTO"=>20,
             "SUBTOTAL"=>22);
$pdf->addCols2( $cols);
$cols=array( "CODIGO"=>"L",
             "DESCRIPCION"=>"L",
             "CANTIDAD"=>"C",
             $nombret=>"R",
             "DSCTO" =>"R",
             "SUBTOTAL"=>"C");
$pdf->addLineFormat2( $cols);
$pdf->addLineFormat2($cols);
//Actualizamos el valor de la coordenada "y", que será la ubicación desde donde empezaremos a mostrar los datos
$y2= 208; // para el tamaño del cuadro del segundo detalle
//Obtenemos todos los detalles de la venta actual
$rsptad = $factura->ventadetalle($idfactura);
while ($regd = $rsptad->fetch_object()) {
  if ($regd->nombretribu=="IGV") {
        $pv=$regd->valor_uni_item_14;
        $subt=$regd->subtotal;
    }else{
        $pv=$regd->precio;
        $subt=$regd->subtotal2;
    }
  $line = array( "CODIGO"=> "$regd->codigo",
                "DESCRIPCION"=>  utf8_decode(htmlspecialchars_decode("$regd->articulo"." - "."$regd->descdet")),
                "CANTIDAD"=> "$regd->cantidad_item_12"." "."$regd->unidad_medida",
                $nombret=> $pv,
                "DSCTO" => "$regd->descuento",
                "SUBTOTAL"=> $subt);
            $size2 = $pdf->addLine2( $y2, $line );
            $y2   += $size2 + 2;
}

$V=new EnLetras(); 
$con_letra=strtoupper($V->ValorEnLetras($regv->importe_total_venta_27,"CON"));
$pdf->addCadreTVAs2("".$con_letra);
$pdf->observSunat2($regv->numeracion_08,$regv->estado,$data[0], $datose->webconsul, $datose->nresolucion);
//Mostramos el impuesto
$pdf->addTVAs2( $regv->sumatoria_igv_22_1, $regv->importe_total_venta_27,"S/ ", $regv->tdescuento);
$pdf->addCadreEurosFrancs2($regv->sumatoria_igv_22_1, $regv->nombretrib);
//Linea para guardar la factura en la carpeta facturas PDF
//$Factura=$pdf->Output($regv->numeracion_08.'.pdf','I');
$Factura=$pdf->Output('../facturasPDF/'.$regv->numeracion_08.'.pdf','F');
}













//Implementamos un método para dar de baja a factura
public function baja($idfactura,$fecha_baja, $com, $hora)
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

    $query="select dt.idfactura, a.idarticulo, dt.cantidad_item_12,  dt.valor_uni_item_14, a.codigo, a.unidad_medida  from detalle_fac_art dt inner join articulo a on dt.idarticulo=a.idarticulo where idfactura = '$idfactura'";
    $resultado = mysqli_query($connect,$query);

    $Idf=array();
    $Ida=array();
    $Ct=array();
    $Cod=array();
    $Vu=array();
    $Um=array();
    $sw=true;

    while ($fila = mysqli_fetch_assoc($resultado)) {
    for($i=0; $i < count($resultado) ; $i++){
        $Idf[$i] = $fila["idfactura"];  
        $Ida[$i] = $fila["idarticulo"];  
        $Ct[$i] = $fila["cantidad_item_12"];  
        $Cod[$i] = $fila["codigo"];  
        $Vu[$i] = $fila["valor_uni_item_14"];  
        $Um[$i] = $fila["unidad_medida"];  

    $sql_update_articulo="update detalle_fac_art de inner join 
    articulo a on de.idarticulo=a.idarticulo 
    set 
     a.saldo_finu=a.saldo_finu + '$Ct[$i]', 
     a.stock=a.stock + '$Ct[$i]', 
     a.ventast=a.ventast - '$Ct[$i]' 
    where 
    de.idfactura='$Idf[$i]' and de.idarticulo='$Ida[$i]'";


    $sql_update_articulo_2="update detalle_fac_art de inner join articulo a on de.idarticulo=a.idarticulo 
    set 
    a.valor_finu=(a.saldo_iniu + a.comprast - ventast) * a.costo_compra
    where 
    de.idfactura='$Idf[$i]' and de.idarticulo='$Ida[$i]'";
        
        
    //ACTUALIZAR TIPO TRANSACCIon KARDEX
    //Guardar en Kardex
    $sql_kardex="insert into kardex (idcomprobante, idarticulo, transaccion, codigo, fecha, tipo_documento, numero_doc, cantidad, costo_1, unidad_medida, saldo_final, costo_2,valor_final) 

            values 

            ('$idfactura', '$Ida[$i]', 

            'ANULADO', 

            '$Cod[$i]',

             '$fecha_baja $hora', 
             '01',
             (select numeracion_08 from factura where idfactura='$Idf[$i]'), 

             '$Ct[$i]', 

             '$Vu[$i]',

             '$Um[$i]',

             0, 0, 0)";
        }
        //Fin de FOR
         ejecutarConsulta($sql_update_articulo) or $sw=false;
         ejecutarConsulta($sql_update_articulo_2) or $sw=false;
         ejecutarConsulta($sql_kardex) or $sw=false; 

         $sqlestado="update factura set estado='3', fecha_baja='$fecha_baja $hora', comentario_baja='$com' , 
          DetalleSunat='C/Baja',  CodigoRptaSunat='3' where idfactura='$idfactura'";
        ejecutarConsulta($sqlestado) or $sw=false;
        }
        //Fin de WHILE


        

   
//***************************************************************************
//     require_once "../modelos/Factura.php";
//     $factura = new Factura();
//     $datos = $factura->datosemp();
//     $datose = $datos->fetch_object();

//     //Inclusion de la tabla RUTAS
//     require_once "../modelos/Rutas.php";
//     $rutas = new Rutas();
//     $Rrutas = $rutas->mostrar2();
//     $Prutas = $Rrutas->fetch_object();
//     $rutadata=$Prutas->rutadata; // ruta de la carpeta DATA
//     $rutabaja=$Prutas->rutabaja; // ruta de la carpeta BAJA
//     $rutadatalt=$Prutas->rutadatalt; // ruta de la carpeta BAJA


// $query = "select date_format(fecha_emision_01, '%Y-%m-%d') as fecha, 
// date_format(curdate(), '%Y%m%d') as fechabaja2, 
// date_format(fecha_baja, '%Y-%m-%d') as fechabaja, 
// right(substring_index(numeracion_08,'-',1),3) as serie,
// tipodocuCliente, 
// rucCliente, 
// RazonSocial, 
// tipo_moneda_28, 
// total_operaciones_gravadas_monto_18_2 as subtotal, 
// sumatoria_igv_22_1 as igv, 
// importe_total_venta_27 as total, 
// tipo_documento_07 as tipocomp, 
// numeracion_08 as numerodoc, 
// f.estado, 
// comentario_baja  
//   from 
//   factura f inner join persona p on f.idcliente=p.idpersona where f.idfactura='$idfactura'";  



//       //==================================================
//       $result = mysqli_query($connect, $query);  
//       //==================================================


// //==================FACTURA & BOLETAS================================

//        //$mask = $rutabaja.'*';
//        //array_map( "unlink", glob( $mask ) );


//       $fecha=array();
//       $tipocomp=array();
//       $numdocu=array();
//       $rasoc=array();
//       $fechabaja=array();
//       $numeroc=array();
//       $comen=array();
            
//       $con=0;
//       $fecdeldia=date ("Ymd");  
            
//       while($row=mysqli_fetch_assoc($result)){
//       for($i=0; $i <= count($result); $i++){
//            $fecha[$i]=$row["fecha"];
//            $fechabaja[$i]=$row["fechabaja"];
//            $tipocomp[$i]=$row["tipocomp"];
//            $numeroc[$i]=$row["numerodoc"];
//            $comen[$i]=$row["comentario_baja"];
//            $ruc=$datose->numero_ruc;
//            $fbaja2=$row["fechabaja2"];

//            $path=$rutadata.$ruc."-RA-".$fbaja2."-011.cba";
//             $handle=fopen($path, "a");
//            fwrite($handle, $fecha[$i]."|".$fechabaja[$i]."|".$tipocomp[$i]."|".$numeroc[$i]."|".$comen[$i]."|\r\n"); 
//            fclose($handle);

//            $path=$rutadatalt.$ruc."-RA-".$fbaja2."-011.cba";
//             $handle=fopen($path, "a");
//            fwrite($handle, $fecha[$i]."|".$fechabaja[$i]."|".$tipocomp[$i]."|".$numeroc[$i]."|".$comen[$i]."|\r\n"); 
//            fclose($handle);
           
//            $i=$i+1;
//            $con=$con+1;           
//       }
//     }
//**************************************************************************

    return $sw;    

}




//Implementamos un método para dar de baja a factura
public function ActualizarEstado($idfactura,$st)
{
        $sw=true;
        $sqlestado="update factura set estado='$st' where idfactura='$idfactura'";
        ejecutarConsulta($sqlestado) or $sw=false; 
    return $sw;    
}



//Implementamos un método para anular la factura
public function anular($idfactura)
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

    while ($fila = mysqli_fetch_assoc($resultado)) {
    for($i=0; $i < count($resultado) ; $i++){
        $Idf[$i] = $fila["idfactura"];  
        $Ida[$i] = $fila["idarticulo"];  

    $sql_update_articulo="update detalle_fac_art de 
    inner join 
    articulo a  
    on de.idarticulo=a.idarticulo 
    set 
     a.saldo_finu=a.saldo_finu + de.cantidad_item_12, a.stock=a.stock + de.cantidad_item_12, a.ventast=a.ventast - de.cantidad_item_12, a.valor_finu=(a.saldo_finu + a.comprast - a.ventast) * a.costo_compra
    where 
    de.idfactura='$Idf[$i]' and de.idarticulo='$Ida[$i]'";
        
        
    //ACTUALIZAR TIPO TRANSACCIon KARDEX
    //Guardar en Kardex
    $sql_kardex="insert into kardex (idcomprobante, idarticulo, transaccion, codigo, fecha, tipo_documento, numero_doc, cantidad, costo_1, unidad_medida, saldo_final, costo_2,valor_final) 

            values 

            ('$idfactura', (select a.idarticulo from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 

            'ANULADO', 

            (select a.codigo from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

             (select fecha_emision_01 from factura where idfactura='$Idf[$i]'), 
             '01',
             (select numeracion_08 from factura where idfactura='$Idf[$i]'), 

             (select dtf.cantidad_item_12 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 

             (select dtf.valor_uni_item_14 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

             (select a.unidad_medida from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

             0, 0, 0)";
        }

        $sqlestado="update factura  set estado='0' where idfactura='$idfactura'";

        //Fin de FOR
         ejecutarConsulta($sql_update_articulo) or $sw=false;
         ejecutarConsulta($sql_kardex) or $sw=false; 
         ejecutarConsulta($sqlestado) or $sw=false; 
        }
        //Fin de WHILE
    return $sw;    
}

 
    //Implementar un método para mostrar los datos de un registro a modificar
    public function mostrar($idfactura)
    {
        $sql="select 
        f.idfactura,
        date(f.fecha_emision_01) as fecha,
        f.idcliente,
        p.razon_social as cliente,
        p.numero_documento,
        p.domicilio_fiscal,
        u.idusuario,
        u.nombre as usuario,
        f.tipo_documento_07,
        f.numeracion_08, 
        f.total_operaciones_gravadas_monto_18_2, 
        f.sumatoria_igv_22_1, 
        f.importe_total_venta_27, 
        f.estado 
        from 
        factura f inner join persona p on f.idcliente=p.idpersona inner join usuario u on f.idusuario=u.idusuario where f.idfactura='$idfactura'";
        return ejecutarConsultaSimpleFila($sql);
    }

    public function mostrarxml($idfactura, $idempresa)
    {
      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp($idempresa);
    $datose = $datos->fetch_object();

    $nombrecomercial=$datose->nombre_comercial;

    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutadata=$Prutas->rutadata; // ruta de la carpeta DATA
    $rutafirma=$Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutadatalt=$Prutas->rutadatalt; // ruta de la carpeta DATAALTERNA
    $rutaenvio=$Prutas->rutaenvio; // ruta de la carpeta rutaenvio
    $rutaunzipxml=$Prutas->unziprpta; // ruta de la carpeta ruta unziprpta

     $query = "select
     f.tipo_documento_07 as tipocomp, 
     f.numeracion_08 as numerodoc 
     from 
     factura f inner join persona p on f.idcliente=p.idpersona inner join empresa e on f.idempresa=e.idempresa where idfactura='$idfactura' and f.estado in('1','4','5') order by numerodoc";

     $result = mysqli_query($connect, $query);  


     if ($result) {
      while($row=mysqli_fetch_assoc($result)){
      for($i=0; $i <= count($result); $i++){
           $tipocomp=$row["tipocomp"];
           $numerodoc=$row["numerodoc"];
           $ruc=$datose->numero_ruc;
         }
       }
    $cabextxml=$rutafirma.$ruc."-".$tipocomp."-".$numerodoc.".xml";
    $rpta = array ('rutafirma'=>$cabextxml);

     }else{

      $rpta = array ('rutafirma'=>'Aún no se ha creado el archivo XML.');
     }
      

  return $rpta;
    }





    public function mostrarrpta($idfactura, $idempresa)
    {
      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp($idempresa);
    $datose = $datos->fetch_object();

    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutarpta=$Prutas->rutarpta; // ruta de la carpeta DATA
    $rutaunzipxml=$Prutas->unziprpta; // ruta de la carpeta ruta unziprpta
    

     $query = "select
     f.tipo_documento_07 as tipocomp, 
     f.numeracion_08 as numerodoc 
     from 
     factura f inner join persona p on f.idcliente=p.idpersona inner join empresa e on f.idempresa=e.idempresa where idfactura='$idfactura' and f.estado in('5','4') order by numerodoc";

     $result = mysqli_query($connect, $query);  

      $con=0; //COntador de variable
            
      while($row=mysqli_fetch_assoc($result)){
      for($i=0; $i <= count($result); $i++){
           $tipocomp=$row["tipocomp"];
           $numerodoc=$row["numerodoc"];
           $ruc=$datose->numero_ruc;
         }
       }

  $rutarptazip=$rutarpta.'R'.$ruc."-".$tipocomp."-".$numerodoc.".zip";
  // $zip = new ZipArchive;
  // //en la función open se le pasa la ruta de nuestro archivo (alojada en carpeta temporal)
  // if ($zip->open($rutarptazip) === TRUE) 
  // {
  //   //función para extraer el ZIP, le pasamos la ruta donde queremos que nos descomprima
  //   $zip->extractTo($rutaunzipxml);
  //   $zip->close();
  // }
   $rutaxmlrpta=$rutaunzipxml.'R-'.$ruc."-".$tipocomp."-".$numerodoc.".xml";
   $rpta = array ('rpta'=>$rutarptazip, 'rutaxmlr'=> $rutaxmlrpta);
   return $rpta;
  }





    public function generarxml($idfactura, $idempresa)
    {
      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp($idempresa);
    $datose = $datos->fetch_object();
    $configuraciones = $factura->configuraciones($idempresa);
    $configE=$configuraciones->fetch_object();

    

    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutadata=$Prutas->rutadata; // ruta de la carpeta DATA
    $rutafirma=$Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutadatalt=$Prutas->rutadatalt; // ruta de la carpeta DATAALTERNA
    $rutaenvio=$Prutas->rutaenvio; // ruta de la carpeta DATAALTERNA

    $query = "select
     date_format(f.fecha_emision_01, '%Y-%m-%d') as fecha, 
     right(substring_index(f.numeracion_08,'-',1),1) as serie,
     date_format(f.fecha_emision_01, '%H:%i:%s') as hora,
     p.tipo_documento as  tipodocuCliente, 
     p.numero_documento, 
     p.razon_social, 
     f.tipo_moneda_28, 
     f.total_operaciones_gravadas_monto_18_2 as subtotal, 
     f.sumatoria_igv_22_1 as igv, 
     f.importe_total_venta_27 as total, 
     f.tipo_documento_07 as tipocomp, 
     f.numeracion_08 as numerodoc, 
     f.estado, 
     f.tdescuento,
     f.codigo_tributo_22_3 as codigotrib,
     f.nombre_tributo_22_4  as nombretrib,
     f.codigo_internacional_22_5 as codigointtrib,
     f.total_operaciones_gravadas_codigo_18_1 as opera,
     e.ubigueo,
     f.icbper
     from 
     factura f inner join persona p on f.idcliente=p.idpersona inner join empresa e on f.idempresa=e.idempresa where idfactura='$idfactura' and f.estado in('1','4') order by numerodoc";

    $querydetfac = "select
       f.tipo_documento_07 as tipocomp, 
       f.numeracion_08 as numerodoc,  
       df.cantidad_item_12 as cantidad, 
       a.codigo, 
       a.nombre as descripcion, 
       a.unidad_medida as um,
       replace(format(df.valor_uni_item_14,5),',','') as vui, 
       df.igv_item as igvi, 
       df.precio_venta_item_15_2 as pvi, 
       df.valor_venta_item_21 as vvi,
       df.afectacion_igv_item_16_1 as sutribitem,
       df.numero_orden_item_33 as numorden,

       df.afectacion_igv_item_16_3 as aigv,
       df.afectacion_igv_item_16_4 codtrib,
       df.afectacion_igv_item_16_5 as nomtrib,
       df.afectacion_igv_item_16_6 as coditrib,
       a.codigosunat,
       f.tipo_moneda_28 as moneda,
       a.mticbperu,
       f.icbper

       from
       factura f inner join detalle_fac_art df on f.idfactura=df.idfactura inner join articulo a on df.idarticulo=a.idarticulo
          where f.idfactura='$idfactura' and f.estado in ('1','4') order by f.fecha_emision_01";


      $result = mysqli_query($connect, $query);  
      $resultf = mysqli_query($connect, $querydetfac); 

    $nombrecomercial=$datose->nombre_comercial;
    $domiciliofiscal=$datose->domicilio_fiscal;
    $codestablecimiento=$datose->ubigueo;
    $codubigueo=$datose->codubigueo;
    $ciudad=$datose->ciudad;
    $distrito=$datose->distrito;
    $interior=$datose->interior;
    $codigopais=$datose->codigopais;




      //Parametros de salida
      $fecha=array();
      $hora=array();
      $serie=array();
      $tipodocu=array();
      $numdocu=array();
      $rasoc=array();
      $moneda=array();
      $codigotrib=array();
      $nombretrib=array();
      $codigointtrib=array();
      $subtotal=array();
      $igv=array();
      $total=array();
      $tdescu=array();
      $opera=array();
      $ubigueo=array();
     

      $con=0; //COntador de variable
      $icbper="";
            
      while($row=mysqli_fetch_assoc($result)){
      for($i=0; $i <= count($result); $i++){
           $fecha[$i]=$row["fecha"]; //Fecha emision
           $serie[$i]=$row["serie"];
           $tipodocu[$i]=$row["tipodocuCliente"]; //Tipo de documento de cliente ruc o dni
           $numdocu[$i]=$row["numero_documento"]; //NUmero de docuemnto de cliente
           $rasoc[$i]=$row["razon_social"]; //Nombre de cliente
           $moneda[$i]=$row["tipo_moneda_28"];
           $subtotal[$i]=$row["subtotal"];
           $igv[$i]=$row["igv"];
           $total[$i]=$row["total"];
           $tdescu[$i]=$row["tdescuento"];
           $hora[$i]=$row["hora"];
           $tipocomp=$row["tipocomp"];
           $numerodoc=$row["numerodoc"];
           $ruc=$datose->numero_ruc;
           $ubigueo=$datose->ubigueo;
           $opera[$i]=$row["opera"];


           $codigotrib[$i]=$row["codigotrib"];//codigo de tributo de la tabla catalo 5
           $nombretrib[$i]=$row["nombretrib"];//NOmbre de tributo de la tabla catalo 5
           $codigointtrib[$i]=$row["codigointtrib"];//Codigo internacional de la tabla catalo 5

             $Lmoneda="NUEVOS SOLES";
      if ($moneda[$i]=='USD') {
             $Lmoneda="DOLARES AMERICANOS";
           }

           $icbper=$row["icbper"];


       require_once "Letras.php";
       $V=new EnLetras(); 
       $con_letra=strtoupper($V->ValorEnLetras($total[$i], $Lmoneda));


//======================================== FORMATO XML ========================================================
 $domiciliofiscal=$datose->domicilio_fiscal;
//Primera parte
$facturaXML ='<?xml version="1.0" encoding="utf-8"?>
            <Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
                     xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                     xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
                     xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                     xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
                <ext:UBLExtensions>
                    <ext:UBLExtension>
                        <ext:ExtensionContent/>
                    </ext:UBLExtension>
                </ext:UBLExtensions>
                <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
                <cbc:CustomizationID>2.0</cbc:CustomizationID>
                <cbc:ID>'.$numerodoc.'</cbc:ID>
                <cbc:IssueDate>'.$fecha[$i].'</cbc:IssueDate>
                <cbc:IssueTime>'.$hora[$i].'</cbc:IssueTime>

                <cbc:InvoiceTypeCode listID="0101">'.$tipocomp.'</cbc:InvoiceTypeCode>

                <cbc:Note languageLocaleID="1000">'.$con_letra.'</cbc:Note>

              <cbc:Note languageLocaleID="2006">Leyenda: Operación sujeta a detracción</cbc:Note>
              <cbc:DocumentCurrencyCode>'.$moneda[$i].'</cbc:DocumentCurrencyCode>

                <cac:Signature>
                    <cbc:ID>'.$ruc.'</cbc:ID>
                    <cbc:Note>SENCON</cbc:Note>
                    <cac:SignatoryParty>
                        <cac:PartyIdentification>
                            <cbc:ID>'.$ruc.'</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyName>
                            <cbc:Name><![CDATA['.$nombrecomercial.']]></cbc:Name>
                        </cac:PartyName>
                    </cac:SignatoryParty>
                    <cac:DigitalSignatureAttachment>
                        <cac:ExternalReference>
                            <cbc:URI>#SIGN-SENCON</cbc:URI>
                        </cac:ExternalReference>
                    </cac:DigitalSignatureAttachment>
                </cac:Signature>

                <cac:AccountingSupplierParty>
                    <cac:Party>
                        <cac:PartyIdentification>
                            <cbc:ID schemeID="6">'.$ruc.'</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyName>
                            <cbc:Name><![CDATA['.$nombrecomercial.']]></cbc:Name>
                        </cac:PartyName>
                        <cac:PartyLegalEntity>
                            <cbc:RegistrationName><![CDATA['.$nombrecomercial.']]></cbc:RegistrationName>
                        <cac:RegistrationAddress>
                             <cbc:AddressTypeCode>'.$codestablecimiento.'</cbc:AddressTypeCode>
                               <cbc:CitySubdivisionName>'.$interior.'</cbc:CitySubdivisionName>
                                <cbc:CityName>'.$ciudad.'</cbc:CityName>
                                  <cbc:CountrySubentity>'.$ciudad.'</cbc:CountrySubentity>
                                    <cbc:CountrySubentityCode>'.$codubigueo.'</cbc:CountrySubentityCode>
                                      <cbc:District>'.$distrito.'</cbc:District> 
                                      <cac:AddressLine>
                                        <cbc:Line><![CDATA['.$domiciliofiscal.']]></cbc:Line>
                                          </cac:AddressLine>    
                                            <cac:Country>
                                              <cbc:IdentificationCode>PE</cbc:IdentificationCode>
                                                </cac:Country>
                            </cac:RegistrationAddress>
                        </cac:PartyLegalEntity>
                    </cac:Party>
                </cac:AccountingSupplierParty>

                <cac:AccountingCustomerParty>
                    <cac:Party>
                        <cac:PartyIdentification>
                            <cbc:ID schemeID="'.$tipodocu[$i].'">'.$numdocu[$i].'</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyLegalEntity>
                            <cbc:RegistrationName><![CDATA['.$rasoc[$i].']]></cbc:RegistrationName>
                        </cac:PartyLegalEntity>
                    </cac:Party>
                </cac:AccountingCustomerParty>';

               /*  $facturaXML.='<cac:PaymentMeans>
                    <cbc:PaymentMeansCode>0</cbc:PaymentMeansCode>
                    <cac:PayeeFinancialAccount>
                        <cbc:ID>-</cbc:ID>
                    </cac:PayeeFinancialAccount>
                </cac:PaymentMeans>

                <cac:PaymentTerms>
                    <cbc:PaymentMeansID>000</cbc:PaymentMeansID>
                    <cbc:PaymentPercent>0.00</cbc:PaymentPercent>
                    <cbc:Amount currencyID="PEN">0.00</cbc:Amount>
                </cac:PaymentTerms>'; */


                $facturaXML.='
                <!-- Inicio Tributos cabecera-->  
                <cac:TaxTotal>
                    <cbc:TaxAmount currencyID="'.$moneda[$i].'">'.$igv[$i].'</cbc:TaxAmount>
                        <cac:TaxSubtotal>
                        <cbc:TaxableAmount currencyID="'.$moneda[$i].'">'.$subtotal[$i].'</cbc:TaxableAmount>
                        <cbc:TaxAmount currencyID="'.$moneda[$i].'">'.$igv[$i].'</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cac:TaxScheme>
                                <cbc:ID>'.$codigotrib[$i].'</cbc:ID>
                                <cbc:Name>'.$nombretrib[$i].'</cbc:Name>
                                <cbc:TaxTypeCode>'.$codigointtrib[$i].'</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>';


            if ($icbper>0) {
             $facturaXML.='
                <cac:TaxSubtotal>
                  <cbc:TaxAmount currencyID="'.$moneda[$i].'">'.$icbper.'</cbc:TaxAmount>
                         <cac:TaxCategory>
                            <cac:TaxScheme>
                               <cbc:ID>7152</cbc:ID>
                               <cbc:Name>ICBPER</cbc:Name>
                               <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                         </cac:TaxCategory>
                      </cac:TaxSubtotal>';
                              }

              $facturaXML.='
              </cac:TaxTotal>
              <!-- Fin Tributos  Cabecera-->

                <cac:LegalMonetaryTotal>
                    <cbc:LineExtensionAmount currencyID="'.$moneda[$i].'">'.$subtotal[$i].'</cbc:LineExtensionAmount>
                    <cbc:TaxInclusiveAmount currencyID="'.$moneda[$i].'">'.$total[$i].'</cbc:TaxInclusiveAmount>
                    <cbc:AllowanceTotalAmount currencyID="'.$moneda[$i].'">0.00</cbc:AllowanceTotalAmount>
                    <cbc:ChargeTotalAmount currencyID="'.$moneda[$i].'">0.00</cbc:ChargeTotalAmount>  
                    <cbc:PrepaidAmount currencyID="'.$moneda[$i].'">0.00</cbc:PrepaidAmount>  
                    <cbc:PayableAmount currencyID="'.$moneda[$i].'">'.$total[$i].'</cbc:PayableAmount>
                </cac:LegalMonetaryTotal>';
                        }//For cabecera
                        $i=$i+1;
                        $con=$con+1;           
                        }//While cabecera

      $codigo=array();  
      $cantidad=array(); 
      $descripcion=array();  
      $um=array();  
      $vui=array();
      $igvi=array();  
      $pvi=array(); 
      $vvi=array(); 
      $sutribitem=array();  
      $aigv=array(); 
      $codtrib=array();
      $nomtrib=array(); 
      $coditrib=array(); 
      $codigosunat=array(); 
      $numorden=array();  
      $monedaD=array();
      $mticbperu=array();
      $igv_="";
      $igv_=$configE->igv;

  while($rowf=mysqli_fetch_assoc($resultf)){
      for($if=0; $if < count($resultf); $if++){
           $codigo[$if]=$rowf["codigo"];
           $cantidad[$if]=$rowf["cantidad"];
           $descripcion[$if]=$rowf["descripcion"];
           $vui[$if]=$rowf["vui"];
           $sutribitem[$if]=$rowf["sutribitem"];           
           $igvi[$if]=$rowf["igvi"];
           $pvi[$if]=$rowf["pvi"];
           $vvi[$if]=$rowf["vvi"];
           $um[$if]=$rowf["um"];
           $tipocompf=$rowf["tipocomp"];
           $numerodocf=$rowf["numerodoc"];
           $ruc=$datose->numero_ruc;
           $aigv[$if]=$rowf["aigv"];
           $codtrib[$if]=$rowf["codtrib"];
           $nomtrib[$if]=$rowf["nomtrib"];
           $coditrib[$if]=$rowf["coditrib"];
           $codigosunat[$if]=$rowf["codigosunat"];
           $numorden[$if]=$rowf["numorden"];
           $monedaD[$if]=$rowf["moneda"];

           $mticbperu[$if]=$rowf["mticbperu"] ;           
           $icbperD=$rowf["icbper"];

               /* Número de orden del Ítem
                  Cantidad y Unidad de medida por ítem
                  Valor de venta del ítem  */

                $facturaXML.='
                <cac:InvoiceLine>
                    <cbc:ID>'. $numorden[$if] .'</cbc:ID>
                    <cbc:InvoicedQuantity unitCode="'. $um[$if] .'">'.number_format($cantidad[$if],2,'.','').'</cbc:InvoicedQuantity>
                    <cbc:LineExtensionAmount currencyID="'.$monedaD[$if].'">'.number_format($vvi[$if],2,'.','').'</cbc:LineExtensionAmount>
                    
                    <cac:PricingReference>
                        <cac:AlternativeConditionPrice>
                            <cbc:PriceAmount currencyID="'.$monedaD[$if].'">'.number_format($pvi[$if],2,'.','').'</cbc:PriceAmount>
                            <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
                        </cac:AlternativeConditionPrice>
                    </cac:PricingReference>

                    <!-- Inicio Tributos --> 
                    <cac:TaxTotal>
                        <cbc:TaxAmount currencyID="'.$monedaD[$if].'">'.number_format($sutribitem[$if],2,'.','').'</cbc:TaxAmount>                        
                        <cac:TaxSubtotal>
                            <cbc:TaxableAmount currencyID="'.$monedaD[$if].'">'.number_format($vvi[$if],2,'.','').'</cbc:TaxableAmount>
                            <cbc:TaxAmount currencyID="'.$monedaD[$if].'">'.number_format($sutribitem[$if],2,'.','').'</cbc:TaxAmount>
                            <cac:TaxCategory>
                                <cbc:Percent>'.$igv_.'</cbc:Percent>
                                <cbc:TaxExemptionReasonCode>'.$aigv[$if].'</cbc:TaxExemptionReasonCode>
                                <cac:TaxScheme>
                                    <cbc:ID>'.$codtrib[$if].'</cbc:ID>
                                    <cbc:Name>'.$nomtrib[$if].'</cbc:Name>
                                    <cbc:TaxTypeCode>'.$coditrib[$if].'</cbc:TaxTypeCode>
                                </cac:TaxScheme>
                            </cac:TaxCategory>
                        </cac:TaxSubtotal>';


        if ($codigo[$if]=="ICBPER")
                         {
                        
                $facturaXML.='
                <cac:TaxSubtotal>
                <cbc:TaxAmount currencyID="'. $monedaD[$if] .'">'.$icbperD.'</cbc:TaxAmount>
                    <cbc:BaseUnitMeasure unitCode="'.$um[$if].'">'.number_format($cantidad[$if],0,'.','').'</cbc:BaseUnitMeasure>
                    <cac:TaxCategory>
                    <cbc:PerUnitAmount currencyID="'. $monedaD[$if] .'">'.number_format($mticbperu[$if],2,'.','').'</cbc:PerUnitAmount>
                       <cac:TaxScheme>
                          <cbc:ID>7152</cbc:ID>
                          <cbc:Name>ICBPER</cbc:Name>
                          <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                       </cac:TaxScheme>
                    </cac:TaxCategory>
                 </cac:TaxSubtotal>';
                      };


                     $facturaXML.='
                     </cac:TaxTotal>

                    <cac:Item>
                        <cbc:Description><![CDATA['.$descripcion[$if].']]></cbc:Description>
                         <cac:SellersItemIdentification>
                            <cbc:ID>'.$codigo[$if].'</cbc:ID>
                        </cac:SellersItemIdentification>
                    </cac:Item>

                    <cac:Price>
                        <cbc:PriceAmount currencyID="'.$monedaD[$if].'">'.number_format($vui[$if],5,'.','').'</cbc:PriceAmount>
                    </cac:Price>
                </cac:InvoiceLine>';
  
     }//Fin for
     }//Find e while 
   $facturaXML.= '</Invoice>';
//FIN DE CABECERA ===================================================================


// Nos aseguramos de que la cadena que contiene el XML esté en UTF-8
  $facturaXML = mb_convert_encoding($facturaXML, "UTF-8");
  // Grabamos el XML en el servidor como un fichero plano, para
  // poder ser leido por otra aplicación.
  $gestor = fopen($rutafirma.$ruc."-".$tipocomp."-".$numerodoc.".xml", 'w');
  fwrite($gestor, $facturaXML);
  fclose($gestor);

  $cabextxml=$rutafirma.$ruc."-".$tipocomp."-".$numerodoc.".xml";
  $cabxml=$ruc."-".$tipocomp."-".$numerodoc.".xml";
  $nomxml=$ruc."-".$tipocomp."-".$numerodoc;
  $nomxmlruta=$rutafirma.$ruc."-".$tipocomp."-".$numerodoc;

              require_once ("../greemter/Greenter.php");
              $invo = new Greenter();
              $out=$invo->getDatFac($cabextxml);

              $filenaz = $nomxml.".zip";
              $zip = new ZipArchive();
              if($zip->open($filenaz,ZIPARCHIVE::CREATE)===true) {
                //$zip->addEmptyDir("dummy");
                $zip->addFile($cabextxml,$cabxml);
                $zip->close();

                //if(!file_exists($rutaz)){mkdir($rutaz);}
                $imagen = file_get_contents($filenaz);
                $imageData = base64_encode($imagen);
                rename($cabextxml, $rutafirma.$cabxml);
                rename($filenaz, $rutaenvio.$filenaz);
              }
              else
              {
                $out="Error al comprimir archivo";
              }
              
            $rpta = array ('cabextxml'=>$cabextxml,'cabxml'=>$cabxml, 'rutafirma'=>$cabextxml);
            $sqlDetalle="update factura set DetalleSunat='XML firmado' where idfactura='$idfactura'";
            ejecutarConsulta($sqlDetalle);

  return $rpta;

  } //Fin de funcion



  public function enviarxmlSUNAT($idfactura, $idempresa)
  {
    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->correo();
    $correo = $datos->fetch_object();


    require_once "../modelos/Consultas.php";  
    $consultas = new consultas();
    $paramcerti = $consultas->paramscerti();
    $datosc = $paramcerti->fetch_object();

     //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutafirma=$Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutaenvio=$Prutas->rutaenvio; // ruta de la carpeta FIRMA
    $rutarpta=$Prutas->rutarpta; // ruta de la carpeta FIRMA
    $rutaunzip=$Prutas->unziprpta; // ruta de la carpeta rpta xml

      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

        $sqlsendmail="select 
        f.idfactura, 
        p.email,  
        p.nombres, 
        p.apellidos, 
        p.nombre_comercial, 
        e.numero_ruc,
        f.tipo_documento_07,
        f.numeracion_08 
        from 
        factura f inner join persona p on 
        f.idcliente=p.idpersona inner join empresa e on 
        f.idempresa=e.idempresa 
        where 
        f.idfactura='$idfactura' and e.idempresa='$idempresa' ";

        $result = mysqli_query($connect, $sqlsendmail); 

      $con=0;
      while($row=mysqli_fetch_assoc($result)){
      for($i=0; $i <= count($result); $i++){
           $correocliente=$row["email"];
          }

  //Agregar=====================================================
  // Ruta del directorio donde están los archivos
        $path  = $rutafirma; 
        $files = array_diff(scandir($path), array('.', '..')); 
  //=============================================================
  $factura=$row['numero_ruc']."-".$row['tipo_documento_07']."-".$row['numeracion_08'];

    //Validar si existe el archivo firmado
    foreach($files as $file){
    // Divides en dos el nombre de tu archivo utilizando el . 
    $dataSt = explode(".", $file);
    // Nombre del archivo
    $fileName = $dataSt[0];
    $st="1";
    // Extensión del archivo 
    $fileExtension = $dataSt[1];
    if($factura == $fileName){
        $archivoFactura=$fileName;
        // Realizamos un break para que el ciclo se interrumpa
         break;
      }
    }
    //$url=$rutafirma.$archivoFactura.'.xml';
    $ZipFactura=$rutaenvio.$archivoFactura.'.zip';
    copy($ZipFactura, $archivoFactura.'.zip');
    $ZipFinal=$factura.'.zip';
    //echo $ZipFactura;

    $webservice=$datosc->rutaserviciosunat;
    $usuarioSol=$datosc->usuarioSol;
    $claveSol=$datosc->claveSol;
    $nruc=$datosc->numeroruc;

  //Llamada al WebService=======================================================================
  $service = $webservice; 
  $headers = new CustomHeaders($nruc.$usuarioSol, $claveSol); 
  $client = new SoapClient($service, [ 
    'cache_wsdl' => WSDL_CACHE_NONE, 
    'trace' => TRUE , 
    'soap_version' => SOAP_1_1 ] 
  ); 
  
   try{
   $client->__setSoapHeaders([$headers]); 
   $fcs = $client->__getFunctions();
   $params = array('fileName' => $ZipFinal, 'contentFile' => file_get_contents($ZipFinal) ); 

    //Llamada al WebService=======================================================================
   $status = $client->sendBill($params); // Comando para enviar xml a SUNAT
   $conte  =  $client->__getLastResponse();
   $texto=trim(strip_tags($conte));


   $zip = new ZipArchive();
   if($zip->open("R".$ZipFinal,ZIPARCHIVE::CREATE)===true) {
   $zip->addEmptyDir("dummy");
   $zip->close();}


     $rpt = fopen("R".$ZipFinal, 'w') or die("no se pudo crear archivo");
     fwrite($rpt, base64_decode($texto));
     fclose($rpt);
     rename("R".$ZipFinal, $rutarpta."R".$ZipFinal);
     unlink($ZipFinal);

     $rutarptazip= $rutarpta."R".$ZipFinal;
  $zip = new ZipArchive;
  if ($zip->open($rutarptazip) === TRUE) 
  {
    $zip->extractTo($rutaunzip);
    $zip->close();
  }
   $xmlFinal=$rutaunzip.'R-'.$factura.'.xml';
   $data[0] = "";
   $rpta[0]="";
      $sxe = new SimpleXMLElement($xmlFinal, null, true);
      $urn = $sxe->getNamespaces(true);
      $sxe->registerXPathNamespace('cac', $urn['cbc']);
      $data = $sxe->xpath('//cbc:Description');
      $rpta = $sxe->xpath('//cbc:ResponseCode');
      
      if ($rpta[0]=='0') {
          $msg="Aceptada por SUNAT";
          $sqlCodigo="update factura set CodigoRptaSunat='$rpta[0]', DetalleSunat='$data[0]' where idfactura='$idfactura'";
        }else{
          $sqlCodigo="update factura set CodigoRptaSunat='$rpta[0]', DetalleSunat='$data[0]' where idfactura='$idfactura'";    
      }

      ejecutarConsulta($sqlCodigo);

  return $data[0];


// Llamada al WebService=======================================================================
   }catch (SoapFault $exception){
   $exception=print_r($client->__getLastResponse());

   
   }

  }//Fin While

  
  //return $exception;

  }

    
    public function enviarcorreo($idfactura, $idempresa)
    {

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->correo();
    $correo = $datos->fetch_object();

     //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutafirma=$Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutarpta=$Prutas->rutarpta; // ruta de la carpeta FIRMA

      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

        $sqlsendmail="select 
        f.idfactura, 
        p.email,  
        p.nombres, 
        p.apellidos, 
        p.nombre_comercial, 
        e.numero_ruc,
        f.tipo_documento_07,
        f.numeracion_08,
        p.email 
        from 
        factura f inner join persona p on 
        f.idcliente=p.idpersona inner join empresa e on 
        f.idempresa=e.idempresa 
        where 
        f.idfactura='$idfactura' and e.idempresa='$idempresa' ";

        $result = mysqli_query($connect, $sqlsendmail); 

      $con=0;
            
      while($row=mysqli_fetch_assoc($result)){
      for($i=0; $i <= count($result); $i++){
           $correocliente=$row["email"];
          }

  //Agregar=====================================================
  // Ruta del directorio donde están los archivos
        $path  = $rutafirma; 
        $pathRpta  = $rutarpta; 
        $pathFactura  = '../facturasPDF/'; 
        // Arreglo con todos los nombres de los archivos
        $files = array_diff(scandir($path), array('.', '..')); 
        $filesFactura = array_diff(scandir($pathFactura), array('.', '..')); 
        $filesrpta = array_diff(scandir($pathRpta), array('.', '..')); 
  //=============================================================
        $factura=$row['numero_ruc']."-".$row['tipo_documento_07']."-".$row['numeracion_08'];
        $facturaRpta='R'.$row['numero_ruc']."-".$row['tipo_documento_07']."-".$row['numeracion_08'];

    //Validar si existe el archivo firmado
    foreach($files as $file){
    // Divides en dos el nombre de tu archivo utilizando el . 
    
    $dataSt = explode(".", $file);
    // Nombre del archivo
    $fileName = $dataSt[0];
    $st="1";
    // Extensión del archivo 
    $fileExtension = $dataSt[1];

    if($factura == $fileName){
        $archivoFactura=$fileName;
        
        // Realizamos un break para que el ciclo se interrumpa
         break;
      }
    }
//=========================================================================

    //Validar si existe el archivo firmado
    foreach($filesFactura as $fileFactura){
    // Divides en dos el nombre de tu archivo utilizando el . 
    $dataStF = explode(".", $fileFactura);
    // Nombre del archivo
    $fileNameF = $dataStF[0];
    // Extensión del archivo 
    $fileExtensionF = $dataStF[1];

    if($row['numeracion_08'] == $fileNameF){
        $archivoFacturaPDF=$fileNameF;
        // Realizamos un break para que el ciclo se interrumpa
         break;
      }
    }


    //Para la busqueda de archivo rpta
    foreach($filesrpta as $fileRpta){
    // Divides en dos el nombre de tu archivo utilizando el . 
    $dataStR = explode(".", $fileRpta);
    // Nombre del archivo
    $fileNameR = $dataStR[0];
    // Extensión del archivo 
    $fileExtensionR = $dataStR[1];

    if($facturaRpta == $fileNameR){
        $archivoFacturaRPTA=$fileNameR;
        // Realizamos un break para que el ciclo se interrumpa
         break;
      }
    }
    

    $url=$rutafirma.$archivoFactura.'.xml';
    $fichero = file_get_contents($url);

    $urlFac='../facturasPDF/'.$archivoFacturaPDF.'.pdf';
    $ficheroFact = file_get_contents($urlFac);

    $urlRpta=$rutarpta.$archivoFacturaRPTA.'.zip';
    $ficheroRpta = file_get_contents($urlRpta);



// FUNCION PARA ENVIO DE CORREO CON LA FACTURA AL CLIENTE .
  require '../correo/PHPMailer/class.phpmailer.php';
  require '../correo/PHPMailer/class.smtp.php';
  $mail = new PHPMailer;
  $mail->isSMTP();  
  //$mail -> SMTPDebug  =  2 ;                       // Establecer el correo electrónico para utilizar SMTP
  $mail->Host = $correo->host;             // Especificar el servidor de correo a utilizar 
  $mail->SMTPAuth = true;                  // Habilitar la autenticacion con SMTP
  $mail->Username = $correo->username ;    // Correo electronico saliente ejemplo: tucorreo@gmail.com
  //$clavehash=hash("SHA256",$correo->password);
  $mail->Password = $correo->password;     // Tu contraseña de gmail
  $mail->SMTPSecure = $correo->smtpsecure;                  // Habilitar encriptacion, `ssl` es aceptada
  $mail->Port = $correo->port;                          // Puerto TCP  para conectarse 
  $mail->setFrom($correo->username, utf8_decode($correo->nombre));//Introduzca la dirección de la que debe aparecer el correo electrónico. Puede utilizar cualquier dirección que el servidor SMTP acepte como válida. El segundo parámetro opcional para esta función es el nombre que se mostrará como el remitente en lugar de la dirección de correo electrónico en sí.
  $mail->addReplyTo($correo->username, utf8_decode($correo->nombre));//Introduzca la dirección de la que debe responder. El segundo parámetro opcional para esta función es el nombre que se mostrará para responder
  $mail->addStringAttachment($fichero, $archivoFactura.'.xml');
  $mail->addStringAttachment($ficheroFact, $archivoFacturaPDF.'.pdf');
  $mail->addStringAttachment($ficheroRpta, $archivoFacturaRPTA.'.zip');
  $mail->addAddress($correocliente);   // Agregar quien recibe el e-mail enviado
  //$mail->addAttachment();
  $message = file_get_contents('../correo/email_template.html');
  $message = str_replace('{{first_name}}', utf8_decode($correo->nombre),utf8_decode($correo->mensaje));
  $message = str_replace('{{message}}', utf8_decode($correo->mensaje), utf8_decode($correo->mensaje));
  $message = str_replace('{{customer_email}}', $correo->username, utf8_decode($correo->mensaje));
  $mail->isHTML(true);  // Establecer el formato de correo electrónico en HTML
  
  $mail->Subject = $correo->username;
  $mail->msgHTML($message);
  //$mail->send();

  if(!$mail->send()) {
    //echo '<p style="color:red">No se pudo enviar el mensaje..';
    echo $mail->ErrorInfo;
    //echo "</p>";
  } else {
    echo 'Se enviaron los comprobantes al correo '.'<h3 style=color:green;>'. $correocliente.'</h3>';
  }
  // FUNCION PARA ENVIO DE CORREO CON LA FACTURA AL CLIENTE .


           $i=$i+1;
           $con=$con+1;           
          }


//Guardar en tabla envicorreo =========================================
$sql="insert into 
        enviocorreo
         (  
            numero_documento,
            cliente, 
            correo, 
            comprobante, 
            fecha_envio
          )
          values
          (
          
          (select numero_documento from factura f inner join persona p on f.idcliente=p.idpersona where f.idfactura='$idfactura'),
          (select razon_social from factura f inner join persona p on f.idcliente=p.idpersona where f.idfactura='$idfactura'),
          (select email from factura f inner join persona p on f.idcliente=p.idpersona where f.idfactura='$idfactura'),
          (select numeracion_08 from factura f inner join persona p on f.idcliente=p.idpersona where f.idfactura='$idfactura'),
          now()
        )";
        //return ejecutarConsulta($sql);
        $enviarcorreo=ejecutarConsulta($sql);
//Guardar en tabla envicorreo =========================================
}



public function enviarUltimoComprobantecorreo($idempresa)
    {

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->correo();
    $correo = $datos->fetch_object();

     //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutafirma=$Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutarpta=$Prutas->rutarpta; // ruta de la carpeta FIRMA

      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

        $sqlsendmail="select 
        f.idfactura, 
        p.email,  
        p.nombres, 
        p.apellidos, 
        p.nombre_comercial, 
        e.numero_ruc,
        f.tipo_documento_07,
        f.numeracion_08 
        from 
        factura f inner join persona p on 
        f.idcliente=p.idpersona inner join empresa e on 
        f.idempresa=e.idempresa 
        where e.idempresa='$idempresa' order by f.idfactura desc limit 1";

        $result = mysqli_query($connect, $sqlsendmail); 

      $con=0;
            
      while($row=mysqli_fetch_assoc($result)){
      for($i=0; $i <= count($result); $i++){
           $correocliente=$row["email"];
          }

  //Agregar=====================================================
  // Ruta del directorio donde están los archivos
        $path  = $rutafirma; 
        $pathRpta  = $rutarpta; 
        $pathFactura  = '../facturasPDF/'; 
        // Arreglo con todos los nombres de los archivos
        $files = array_diff(scandir($path), array('.', '..')); 
        $filesFactura = array_diff(scandir($pathFactura), array('.', '..')); 
        $filesrpta = array_diff(scandir($pathRpta), array('.', '..')); 
  //=============================================================
        $factura=$row['numero_ruc']."-".$row['tipo_documento_07']."-".$row['numeracion_08'];
        //$facturaRpta='R'.$row['numero_ruc']."-".$row['tipo_documento_07']."-".$row['numeracion_08'];

    //Validar si existe el archivo firmado
    foreach($files as $file){
    // Divides en dos el nombre de tu archivo utilizando el . 
    
    $dataSt = explode(".", $file);
    // Nombre del archivo
    $fileName = $dataSt[0];
    $st="1";
    // Extensión del archivo 
    $fileExtension = $dataSt[1];

    if($factura == $fileName){
        $archivoFactura=$fileName;
        
        // Realizamos un break para que el ciclo se interrumpa
         break;
      }
    }
//=========================================================================

    //Validar si existe el archivo firmado
    foreach($filesFactura as $fileFactura){
    // Divides en dos el nombre de tu archivo utilizando el . 
    $dataStF = explode(".", $fileFactura);
    // Nombre del archivo
    $fileNameF = $dataStF[0];
    // Extensión del archivo 
    $fileExtensionF = $dataStF[1];

    if($row['numeracion_08'] == $fileNameF){
        $archivoFacturaPDF=$fileNameF;
        // Realizamos un break para que el ciclo se interrumpa
         break;
      }
    }


    //Para la busqueda de archivo rpta
    foreach($filesrpta as $fileRpta){
    // Divides en dos el nombre de tu archivo utilizando el . 
    $dataStR = explode(".", $fileRpta);
    // Nombre del archivo
    $fileNameR = $dataStR[0];
    // Extensión del archivo 
    $fileExtensionR = $dataStR[1];

    if('R'.$factura == $fileNameR){
        $archivoFacturaRPTA=$fileNameR;
        // Realizamos un break para que el ciclo se interrumpa
         break;
      }
    }
    

    $url=$rutafirma.$archivoFactura.'.xml';
    $fichero = file_get_contents($url);

    $urlFac='../facturasPDF/'.$archivoFacturaPDF.'.pdf';
    $ficheroFact = file_get_contents($urlFac);

    $urlRpta=$rutarpta.$archivoFacturaRPTA.'.zip';
    $ficheroRpta = file_get_contents($urlRpta);



// FUNCION PARA ENVIO DE CORREO CON LA FACTURA AL CLIENTE .
  require '../correo/PHPMailer/class.phpmailer.php';
  require '../correo/PHPMailer/class.smtp.php';
  $mail = new PHPMailer;
  $mail->isSMTP();  
  //$mail -> SMTPDebug  =  2 ;                       // Establecer el correo electrónico para utilizar SMTP
  $mail->Host = $correo->host;             // Especificar el servidor de correo a utilizar 
  $mail->SMTPAuth = true;                  // Habilitar la autenticacion con SMTP
  $mail->Username = $correo->username ;    // Correo electronico saliente ejemplo: tucorreo@gmail.com
  //$clavehash=hash("SHA256",$correo->password);
  $mail->Password = $correo->password;     // Tu contraseña de gmail
  $mail->SMTPSecure = $correo->smtpsecure;                  // Habilitar encriptacion, `ssl` es aceptada
  $mail->Port = $correo->port;                          // Puerto TCP  para conectarse 
  $mail->setFrom($correo->username, utf8_decode($correo->nombre));//Introduzca la dirección de la que debe aparecer el correo electrónico. Puede utilizar cualquier dirección que el servidor SMTP acepte como válida. El segundo parámetro opcional para esta función es el nombre que se mostrará como el remitente en lugar de la dirección de correo electrónico en sí.
  $mail->addReplyTo($correo->username, utf8_decode($correo->nombre));//Introduzca la dirección de la que debe responder. El segundo parámetro opcional para esta función es el nombre que se mostrará para responder
  $mail->addStringAttachment($fichero, $archivoFactura.'.xml');
  $mail->addStringAttachment($ficheroFact, $archivoFacturaPDF.'.pdf');
  $mail->addStringAttachment($ficheroRpta, $archivoFacturaRPTA.'.zip');
  $mail->addAddress($correocliente);   // Agregar quien recibe el e-mail enviado
  //$mail->addAttachment();
  $message = file_get_contents('../correo/email_template.html');
  $message = str_replace('{{first_name}}', utf8_decode($correo->nombre),utf8_decode($correo->mensaje));
  $message = str_replace('{{message}}', utf8_decode($correo->mensaje), utf8_decode($correo->mensaje));
  $message = str_replace('{{customer_email}}', $correo->username, utf8_decode($correo->mensaje));
  $mail->isHTML(true);  // Establecer el formato de correo electrónico en HTML
  
  $mail->Subject = $correo->username;
  $mail->msgHTML($message);
  //$mail->send();

  if(!$mail->send()) {
    //echo '<p style="color:red">No se pudo enviar el mensaje..';
    echo $mail->ErrorInfo;
    //echo "</p>";
  } else {
    echo 'Tu mensaje ha sido enviado';
  }
  // FUNCION PARA ENVIO DE CORREO CON LA FACTURA AL CLIENTE .


           $i=$i+1;
           $con=$con+1;           
          }


//Guardar en tabla envicorreo =========================================
$sql="insert into 
        enviocorreo
         (  
            numero_documento,
            cliente, 
            correo, 
            comprobante, 
            fecha_envio
          )
          values
          (
          
          (select numero_documento from factura f inner join persona p on f.idcliente=p.idpersona where f.idfactura='$idfactura'),
          (select razon_social from factura f inner join persona p on f.idcliente=p.idpersona where f.idfactura='$idfactura'),
          (select email from factura f inner join persona p on f.idcliente=p.idpersona where f.idfactura='$idfactura'),
          (select numeracion_08 from factura f inner join persona p on f.idcliente=p.idpersona where f.idfactura='$idfactura'),
          now()
        )";
        //return ejecutarConsulta($sql);
        $enviarcorreo=ejecutarConsulta($sql);
//Guardar en tabla envicorreo =========================================
}


    //Implementar un método para mostrar los datos de un registro a modificar
    public function mostrarCabFac()
    {
        $sql="select
        f.idfactura,
     e.numero_ruc as ruc,
     f.tipo_documento_07 as tipodoc,
     f.numeracion_08 as numerodoc
     from 
     factura f inner join persona p on f.idcliente=p.idpersona
     inner join empresa e on f.idempresa=f.idempresa
     ";
        return ejecutarConsulta($sql);
    }
 
    public function listarDetalle($idfactura)
    {
        $sql="select df.idfactura,df.idarticulo,a.nombre,df.cantidad_item_12, df.valor_uni_item_14, df.valor_venta_item_21, df.igv_item from detalle_fac_art df inner join articulo a on df.idarticulo=a.idarticulo where df.idfactura='$idfactura'";
        return ejecutarConsulta($sql);
    }
 
    //Implementar un método para listar los registros
    public function listarplatos()
    {
        $sql="select 
       p.idplato,
       c.idcategoria,
       p.codigo,
       p.nombre,
       p.precio,
       p.imagen,
       p.estado,
       c.nombreCategoria
        from 
        platos p inner join categoria_plato c on p.idcategoria=c.idcategoria where c.idcategoria='1' order by rand()";
        return ejecutarConsulta($sql);  

    }


    //Implementar un método para listar los registros
    public function listarplatos2()
    {
        $sql="select 
       p.idplato,
       c.idcategoria,
       p.codigo,
       p.nombre,
       p.precio,
       p.imagen,
       p.estado,
       c.nombreCategoria
        from 
        platos p inner join categoria_plato c on p.idcategoria=c.idcategoria  where c.idcategoria='2' order by rand()";
        return ejecutarConsulta($sql);  

    }


    //Implementar un método para listar los registros
    public function listarplatos3()
    {
        $sql="select 
       p.idplato,
       c.idcategoria,
       p.codigo,
       p.nombre,
       p.precio,
       p.imagen,
       p.estado,
       c.nombreCategoria
        from 
        platos p inner join categoria_plato c on p.idcategoria=c.idcategoria where c.idcategoria='3' order by rand()";
        return ejecutarConsulta($sql);  

    }


      public function listarValidar($ano, $mes, $dia, $idempresa)
    {
        $sql="select 
        f.idfactura,
        date_format(f.fecha_emision_01,'%d/%m/%y') as fecha,
        date_format(curdate(),'%Y%m%d') as fechabaja,
        f.idcliente,
        left(p.razon_social,20) as cliente,
        f.vendedorsitio,
        u.nombre as usuario,
        f.tipo_documento_07,
        f.numeracion_08,
        format(f.importe_total_venta_27,2)as importe_total_venta_27 ,
        f.sumatoria_igv_22_1,f.estado,
        e.numero_ruc,
        p.email ,
        TIMESTAMPDIFF(DAY,f.fecha_emision_01 ,  curdate()) AS diast,
        f.DetalleSunat
        from 
        factura f inner join persona p on f.idcliente=p.idpersona 
        inner join usuario u on f.idusuario=u.idusuario 
        inner join empresa e on f.idempresa=e.idempresa where
        year(fecha_emision_01)='$ano' and month(fecha_emision_01)='$mes' and day(fecha_emision_01)='$dia' and e.idempresa='$idempresa' order by idfactura desc";
        return ejecutarConsulta($sql);  

    }

    public function listarValidarcaja($ano, $mes, $dia, $idempresa)
    {
        $sql="select 
        fecha, 
        monto, 
        concepto, 
        tipo,
        idempresa 
        from 
(select c.fecha, ic.monto, ic.concepto, ic.tipo, c.idempresa from
caja c inner join ingresocaja ic on c.idcaja=ic.idcaja inner join empresa e on c.idempresa=e.idempresa
 union all 
 select c.fecha, sc.monto, sc.concepto, sc.tipo, c.idempresa from
caja c inner join salidacaja sc on c.idcaja=sc.idcaja inner join empresa e on c.idempresa=e.idempresa)
as tabla where year(fecha)='$ano' and month(fecha)='$mes' and day(fecha)='$dia' and idempresa='$idempresa'";
        return ejecutarConsulta($sql);  

    }

     public function listarDR($ano, $mes, $idempresa)
    {
        $sql="select 
        idfactura,
        idcliente,
        numeracion_08 as numerofactura,
        date_format(fecha_emision_01,'%d/%m/%y') as fecha,
        date_format(fecha_baja,'%d/%m/%y') as fechabaja,
        left(razon_social,20) as cliente,
        numero_documento as ruccliente,
        total_operaciones_gravadas_monto_18_2 as opgravada,        
        sumatoria_igv_22_1 as igv,
        format(importe_total_venta_27,2) as total,
        vendedorsitio,
        estado 
        from 
        (select 
        f.idfactura,
        f.idcliente,
        f.numeracion_08,
        f.fecha_emision_01,
        fecha_baja,
        p.razon_social,
        p.numero_documento,
        f.total_operaciones_gravadas_monto_18_2,        
        f.sumatoria_igv_22_1,
        f.importe_total_venta_27,
        f.vendedorsitio,
        f.estado from
        factura f inner join persona p on f.idcliente=p.idpersona 
        inner join usuario u on f.idusuario=u.idusuario 
        inner join empresa e on f.idempresa=e.idempresa where  year(f.fecha_emision_01)='$ano' and month(f.fecha_emision_01)='$mes' and f.estado in ('0','3') and e.idempresa='$idempresa' 
        union all 
        select 
        f.idfactura,
        f.idcliente,
        f.numeracion_08,
        f.fecha_emision_01,
        fecha_baja,
        p.razon_social,
        p.numero_documento,
        f.total_operaciones_gravadas_monto_18_2,        
        f.sumatoria_igv_22_1,
        f.importe_total_venta_27,
        f.vendedorsitio,
        f.estado from
        facturaservicio f inner join persona p on f.idcliente=p.idpersona 
        inner join usuario u on f.idusuario=u.idusuario 
        inner join empresa e on f.idempresa=e.idempresa where  year(f.fecha_emision_01)='$ano' and month(f.fecha_emision_01)='$mes' and f.estado in ('0','3') and e.idempresa='$idempresa' ) as tabla
        order by idfactura desc";
        return ejecutarConsulta($sql);  
    }

     public function listarDRdetallado($idcomp, $idempresa)
    {
        $sql="select 
        ncd.codigo_nota,
        ncd.numeroserienota as numero,
        f.numeracion_08,
        date_format(ncd.fecha,'%d/%m/%y') as fecha,
        ncd.desc_motivo as motivo,
        ncd.total_val_venta_og as subtotal,
        ncd.sum_igv as igv,
        ncd.importe_total as total
        from 
        factura f inner join persona p on f.idcliente=p.idpersona 
        inner join usuario u on f.idusuario=u.idusuario 
        inner join empresa e on f.idempresa=e.idempresa inner join notacd ncd on f.idfactura=ncd.idcomprobante
        where f.idfactura='$idcomp'  and e.idempresa='$idempresa'";
        return ejecutarConsulta($sql);  

    }


    public function ventacabecera($idfactura, $idempresa){
        $sql="select 
        f.idfactura, 
        f.idcliente, 
        p.razon_social as cliente, 
        p.domicilio_fiscal as direccion, 
        p.tipo_documento, 
        p.numero_documento, 
        p.email, 
        p.telefono1, 
        p.nombre_comercial, 
        f.idusuario, 
        concat(u.nombre,' ',u.apellidos) as usuario, 
        f.tipo_documento_07,
        right(substring_index(f.numeracion_08,'-',1),4) as serie, 
        right(substring_index(f.numeracion_08,'-',-1),10) as numerofac, 
        f.numeracion_08, 
        date_format(f.fecha_emision_01,'%d-%m-%Y') as fecha, 
        date_format(f.fecha_emision_01,'%Y-%m-%d') as fecha2,
        date_format(f.fecha_emision_01, '%H:%i:%s') as hora, 
        f.sumatoria_igv_22_1, 
        f.importe_total_venta_27, 
        f.tasa_igv, 
        f.guia_remision_29_2 as guia, 
        f.estado,
        e.numero_ruc, 
        f.tdescuento,
        f.total_operaciones_gravadas_monto_18_2 as subtotal,
        f.vendedorsitio,
        f.nombre_tributo_22_4 as nombretrib,
        f.ipagado,
        f.saldo,
        f.tipopago,
        f.nroreferencia,
        f.icbper,
        f.tipo_moneda_28 as moneda
          from
          factura f inner join persona p on f.idcliente=p.idpersona inner join empresa e 
          on e.idempresa=f.idempresa inner join  usuario u on f.idusuario=u.idusuario 
          where f.idfactura='$idfactura' and e.idempresa='$idempresa'";
        return ejecutarConsulta($sql);
    }

    

    public function ventadetalle($idfactura){
        $sql="select  
        a.nombre as articulo, 
        a.codigo, 
        format(dfa.cantidad_item_12,2) as cantidad_item_12, 
        dfa.valor_uni_item_14, 
        format((dfa.cantidad_item_12 * dfa.valor_uni_item_14),2) as subtotal, 
        dfa.precio_venta_item_15_2, 
        dfa.valor_venta_item_21,
        dfa.dcto_item as descuento,
        dfa.descdet,
        a.unidad_medida,
        afectacion_igv_item_16_5 as nombretribu,
        dfa.precio_venta_item_15_2 as precio,
        format(dfa.valor_venta_item_21,2) as subtotal2,
        dfa.numero_orden_item_33 as norden
        
        from 
        detalle_fac_art dfa inner join articulo a on dfa.idarticulo=a.idarticulo where dfa.idfactura='$idfactura'";
        return ejecutarConsulta($sql);
    }

        public function listarD()
    {
        $sql="select documento from correlativo where documento='factura' or documento='boleta' or documento='nota de credito'or documento='nota de debito' group by documento";
        return ejecutarConsulta($sql);      
    }


     public function listarS($serie)
    {
        $sql="select serie from correlativo where documento='$serie'"; 
        return ejecutarConsulta($sql);      
    }

    public function sumarC($tipo_comprobante, $serie_comprobante){

        $sql="select (numero + 1) as addnumero from `correlativo` where documento='$tipo_comprobante' and serie='$serie_comprobante' order by numero desc limit 1";
        return ejecutarConsulta($sql);      
    }

    public function autogenerarN(){

    $sql="select (idfactura + 1) as Nnum from factura order by idfactura desc limit 1";
    return ejecutarConsulta($sql);      

    }

    public function datosemp($idempresa)
    {

    $sql="select * from empresa where idempresa='$idempresa'";
    return ejecutarConsulta($sql);      
    }


    public function configuraciones($idempresa)
    {

    $sql="select * from configuraciones where idempresa='$idempresa'";
    return ejecutarConsulta($sql);      
    }


     public function tributo()
    {

    $sql="select * from catalogo5 where estado='1'";
    return ejecutarConsulta($sql);      
    }

    public function afectacionigv()
    {

    $sql="select * from catalogo7";
    return ejecutarConsulta($sql);      
    }

    public function correo()
    {

    $sql="select * from correo";
    return ejecutarConsulta($sql);      
    }


public function downftp($idfactura, $idempresa){    

    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutadata=$Prutas->rutadata; // ruta de la carpeta data

      $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

        $sql="select 
        f.idfactura, 
        p.email,  
        p.nombres, 
        p.apellidos, 
        p.nombre_comercial, 
        e.numero_ruc,
        f.tipo_documento_07,
        f.numeracion_08 
        from 
        factura f inner join persona p on 
        f.idcliente=p.idpersona inner join empresa e on 
        f.idempresa=e.idempresa 
        where 
        f.idfactura='$idfactura' and e.idempresa='$idempresa' ";
        $result = mysqli_query($connect, $sql); 
        $con=0;
      while($row=mysqli_fetch_assoc($result)){
      for($i=0; $i <= count($result); $i++){
           $correocliente=$row["email"];
          }
  //Agregar=====================================================
  // Ruta del directorio donde están los archivos
        $path  = $rutadata; 
        $files = array_diff(scandir($path), array('.', '..')); 
  //=============================================================
        $facturaData=$row['numero_ruc']."-".$row['tipo_documento_07']."-".$row['numeracion_08'];
    //Validar si existe el archivo firmado
    foreach($files as $file){
    // Divides en dos el nombre de tu archivo utilizando el . 
    $dataSt = explode(".", $file);
    // Nombre del archivo
    $fileName = $dataSt[0];
    $st="1";
    // Extensión del archivo 
    $fileExtension = $dataSt[1];
    if($facturaData == $fileName){
        $archivoFacturaData=$fileName;
        // Realizamos un break para que el ciclo se interrumpa
         break;
      }
    }

    $cabext=$rutadata.$archivoFacturaData.'.json';
    $cab=$archivoFacturaData.'.json';

    $rpta = array ('cabext'=>$cabext,'cab'=>$cab);

    return $rpta;

           $i=$i+1;
           $con=$con+1;           
          } //Fin while
}







public function AutocompletarRuc($buscar){

  $connect = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
      mysqli_query( $connect, 'SET NAMES "'.DB_ENCODE.'"');
      //Si tenemos un posible error en la conexión lo mostramos
      if (mysqli_connect_errno())
      {
            printf("Falló conexión a la base de datos: %s\n",mysqli_connect_error());
            exit();
      }

        $sql="select numero_documento, razon_social, domicilio_fiscal from persona where numero_documento like '%$buscar' and estado='1' and tipo_persona='cliente'";

        $Result=mysqli_query($connect, $sql);

        if ($Result->num_rows > 0)
        {
          while($fila=$result->fecth_array())
          {
            $datos[]=$fila['numero_documento'];
          }
          echo json_encode($datos);
        }

      }


      public function listarcaja($idempresa)
    {
        $sql="select idcaja, date_format(fecha,'%d-%m-%Y' ) as fecha, format(montoi,2) as montoi, format(montof,2) as montof from caja c inner join empresa e on c.idempresa=e.idempresa where c.idempresa='$idempresa' order  by  idcaja desc ";
        return ejecutarConsulta($sql);      
    }



    public function consultatemporizador(){
      $sql="select id as idtempo, tiempo, estado from temporizador where id='1' ";
      return ejecutarConsultaSimpleFila($sql);  
    }


     

    //Implementamos un método para dar de baja a factura
public function ActualizarEstadoBaja($idfactura,$st)
{
        $sw=true;
        $sqlestado="update factura set estado='$st', DetalleSunat='Con nota de credito', CodigoRptaSunat='3' where idfactura='$idfactura'";
        ejecutarConsulta($sqlestado) or $sw=false; 
    return $sw;    
}

       
}
?>