<?php

//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Notacd
{
  //Implementamos nuestro constructor
  public function __construct()
  {

  }

  //Implementar un método para listar los registros y mostrar en el select
  public function selectD()
  {
    $sql = "select codigo, descripcion from catalogo9 where codigo not in('05','08','09','10')";
    return ejecutarConsulta($sql);
  }

  public function selectDebito()
  {
    $sql = "select codigo, descripcion from catalogo10";
    return ejecutarConsulta($sql);
  }

  public function insertarNCxDescuentoGlobal($idnota, $nombre, $serie, $numero_nc, $fecha, $codigo_nota, $codtiponota, $desc_motivo, $tipo_doc_mod, $numero_comprobante, $tipo_doc_ide, $numero_documento, $razon_social, $tipo_moneda, $sum_ot_car, $subtotaldesc, $total_val_venta_oi, $total_val_venta_oe, $igvdescu, $sum_isc, $sum_ot, $totaldescu, $idserie, $idcomprobante, $fecha_comprobante, $hora, $tiponotaC, $vendedorsitio, $idempresa, $tipodoc_mod, $descripitem)
  {
    $sw = true;
    //Guardar en la table nota de credito
    $sql = "insert into notacd 
        (
        idnota, 
        nombre, 
        numeroserienota, 
        fecha, 
        codigo_nota, 
        codtiponota,  
        desc_motivo, 
        tipo_doc_mod, 
        serie_numero, 
        tipo_doc_ide, 
        numero_doc_ide, 
        razon_social, 
        tipo_moneda, 
        sum_ot_car, 
        total_val_venta_og, 
        total_val_venta_oi, 
        total_val_venta_oe, 
        sum_igv, 
        sum_isc, 
        sum_ot, 
        importe_total, 
        estado, 
        idcomprobante,
        fechacomprobante, 
        idempresa,
        vendedorsitio,
        difComprobante,
        DetalleSunat,
        motivonota,
        descripitem
        )
        values
        (
         '$idnota',
         '$nombre',
         '$serie-$numero_nc',
         '$fecha $hora',
         '$codigo_nota',
         '$codtiponota',
         '$desc_motivo',
         '$tipo_doc_mod',
         '$numero_comprobante',
         '$tipo_doc_ide',
         '$numero_documento',
         '$razon_social',
         '$tipo_moneda',
         '$sum_ot_car',
         '$subtotaldesc',
         '$total_val_venta_oi',
         '$total_val_venta_oe',
         '$igvdescu',
         '$sum_isc',
         '0',
         '$totaldescu', 
         '1',
         '$idcomprobante',
         '$fecha_comprobante',
          '$idempresa',
          '$vendedorsitio',
          '$tipodoc_mod',
          'EMITIDO',
          '$motivonota',
          '$descripitem'
        )";
    $idnotanew = ejecutarConsulta_retornarID($sql);

    //Guardar registro en Kardex
    $sql_kardex = "insert into 
        kardex 
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
          valor_final, idempresa) 

          values 

          (
          '$idnotanew', 
          (select idarticulo 
          from 
          articulo where codigo='1000ncdg'), 
          'NOTAC', 
          '1000ncdg',
          '$fecha', 
          '07', 
          '$serie-$numero_nc', 
             '1', 
             '',
             '',
             '',
             '',
             '', '$idempresa')";
    ejecutarConsulta($sql_kardex) or $sw = false; //Guarda KARDEX

    //Guardar en el detalle de nota de crédito
    $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta
          ) 
          values 
          (
          '$idnotanew', 
          (select idarticulo 
          from 
          articulo where codigo='1000ncdg'), 
          '1', 
          '1', 
          '$totaldescu',
          '$igvdescu',
          '$subtotaldesc',
          '$subtotaldesc'
        )";

    ejecutarConsulta($sql_det_notacd) or $sw = false;
    //Para actualizar numeracion de las series de la factura
    $sql_update_numeracion = "update numeracion set numero='$numero_nc' where idnumeracion='$idserie'";
    ejecutarConsulta($sql_update_numeracion) or $sw = false;
    //Para actualizar numeracion de las series de la factura

    //===================== EXPORTAR A TX COMPROBANTE ===========================================
    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
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
    $rutadata = $Prutas->rutadata; // ruta de la carpeta DATA
    $rutadatalt = $Prutas->rutadatalt; // ruta de la carpeta DATA

    $querynotacd = "select
       n.codigo_nota, 
       n.numeroserienota, 
       date_format(n.fecha,'%Y-%m-%d') as fecha, 
       date_format(n.fecha, '%H:%i:%s') as hora,
       n.codtiponota, 
       c.descripcion, 
       n.tipo_doc_mod, 
       n.serie_numero, 
       n.tipo_doc_ide, 
       n.numero_doc_ide, 
       n.razon_social, 
       n.tipo_moneda, 
       n.sum_ot, 
       n.total_val_venta_og, 
       n.total_val_venta_oi, 
       n.total_val_venta_oe, 
       n.sum_igv, n.sum_isc, 
       n.sum_ot, 
       n.importe_total as total
        from
         notacd n inner join catalogo9 c on n.codtiponota=c.codigo where n.idnota='$idnotanew' ";

    $querydetfacncd = "select f.tipo_documento_07 as tipocomp,  f.numeracion_08 as numerodoc, dncd.cantidad, a.codigo, a.nombre as descripcion, format(dncd.valor_unitario,2) as vui, dncd.igv as igvi, dncd.precio_venta as pvi, dncd.valor_venta as vvi, ncd.codigo_nota, ncd.numeroserienota, a.unidad_medida as um, ncd.sum_igv 
      from 
      factura f inner join  notacd ncd on f.idfactura=ncd.idcomprobante inner join detalle_notacd_art dncd on ncd.idnota=dncd.idnotacd  inner join articulo a on dncd.idarticulo=a.idarticulo where ncd.idnota='$idnotanew' order by ncd.numeroserienota";


    $querydetbolncd = "select b.tipo_documento_06 as tipocomp , b.numeracion_07 as numerodoc,  dncd.cantidad, a.codigo, a.nombre as descripcion, format(dncd.valor_unitario,2) as vui, dncd.igv as igvi, dncd.precio_venta as pvi, dncd.valor_venta as vvi, ncd.codigo_nota, ncd.numeroserienota, a.unidad_medida as um, ncd.sum_igv   
      from 
      boleta b inner join notacd ncd on b.idboleta=ncd.idcomprobante inner join detalle_notacd_art dncd on ncd.idnota=dncd.idnotacd  inner join articulo a on dncd.idarticulo=a.idarticulo where ncd.idnota='$idnotanew' order by ncd.numeroserienota";



    $resultnc = mysqli_query($connect, $querynotacd);




    if ($tipo_doc_mod == '01') {

      $resultdfnc = mysqli_query($connect, $querydetfacncd);

      $fecha = array();
      $codtiponotanc = array();
      $descrip = array();
      $tipodocmodnc = array();
      $serienumeronc = array();
      $tdoccliente = array();
      $ndocucliente = array();
      $rsocialcliente = array();
      $tipomone = array();
      $sumot = array();
      $totvalvenog = array();
      $totvalvenoi = array();
      $totvalvenoe = array();
      $sumigv = array();
      $sumisc = array();
      $sumot = array();
      $imptotal = array();

      $codigo_nota = "";
      $numeroserienota = "";

      $connc = 0;

      while ($rownc = mysqli_fetch_assoc($resultnc)) {
        for ($i = 0; $i <= count($resultnc); $i++) {
          $codigo_nota = $rownc["codigo_nota"];
          $numeroserienota = $rownc["numeroserienota"];
          $fecha[$i] = $rownc["fecha"];
          $codtiponotanc[$i] = $rownc["codtiponota"];
          $descrip[$i] = $rownc["descripcion"];
          $tipodocmodnc[$i] = $rownc["tipo_doc_mod"];
          $serienumeronc[$i] = $rownc["serie_numero"];
          $tdoccliente[$i] = $rownc["tipo_doc_ide"];
          $rsocialcliente[$i] = $rownc["razon_social"];
          $ndocucliente[$i] = $rownc["numero_doc_ide"];
          $tipomone[$i] = $rownc["tipo_moneda"];
          $sumot[$i] = $rownc["sum_ot"];
          $totvalvenog[$i] = $rownc["total_val_venta_og"];
          $totvalvenoi[$i] = $rownc["total_val_venta_oi"];
          $totvalvenoe[$i] = $rownc["total_val_venta_oe"];
          $sumigv[$i] = $rownc["sum_igv"];
          $sumisc[$i] = $rownc["sum_isc"];
          $sumot[$i] = $rownc["sum_ot"];
          $hora = $rownc["hora"];
          $imptotal[$i] = $rownc["total"];
          $ruc = $datose->numero_ruc;

          // $path=$rutadata.$ruc."-".$codigo_nota."-".$numeroserienota.".not";
          // $handlenc=fopen($path, "w");
          // fwrite($handlenc, "0101|".$fecha[$i]."|".$hora."|0000|".$tdoccliente[$i]."|".$ndocucliente[$i]."|".$rsocialcliente[$i]."|".$tipomone[$i]."|".$codtiponotanc[$i]."|".$descrip[$i]."|".$tipodocmodnc[$i]."|".$serienumeronc[$i]."|".$sumigv[$i]."|".$totvalvenog[$i]."|".$imptotal[$i]."|0|0|0|".$imptotal[$i]."|2.1|2.0|"); 
          // fclose($handlenc);

          require_once "Letras.php";
          $V = new EnLetras();
          $con_letra = strtoupper($V->ValorEnLetras($imptotal[$i], "NUEVOS SOLES"));
          // $path=$rutadata.$ruc."-".$codigo_nota."-".$numeroserienota.".ley";
          // $handle=fopen($path, "w");
          // fwrite($handle,"1000|".$con_letra."|"); 
          // fclose($handle);

          // $path=$rutadata.$ruc."-".$codigo_nota."-".$numeroserienota.".tri";
          // $handle=fopen($path, "w");
          // //fwrite($handle,"1000|IGV|VAT|S|".$totvalvenog[$i]."|".$sumigv[$i]."|"); VERSION 1.1
          // fwrite($handle,"1000|IGV|VAT|".$totvalvenog[$i]."|".$sumigv[$i]."|"); 
          // fclose($handle);


          $json = array('cabecera' => array('tipOperacion' => '0101', 'fecEmision' => $fecha[$i], 'horEmision' => $hora, 'codLocalEmisor' => "0000", 'tipDocUsuario' => $tdoccliente[$i], 'numDocUsuario' => $ndocucliente[$i], 'rznSocialUsuario' => $rsocialcliente[$i], 'tipMoneda' => $tipomone[$i], 'codMotivo' => $codtiponotanc[$i], 'desMotivo' => $descrip[$i], 'tipDocAfectado' => $tipodocmodnc[$i], 'numDocAfectado' => $serienumeronc[$i], 'sumTotTributos' => $sumigv[$i], 'sumTotValVenta' => $totvalvenog[$i], 'sumPrecioVenta' => $imptotal[$i], 'sumDescTotal' => '0.00', 'sumOtrosCargos' => '0.00', 'sumTotalAnticipos' => '0.00', 'sumImpVenta' => $imptotal[$i], 'ublVersionId' => "2.1", 'customizationId' => "2.0"), 'detalle' => array(), 'leyendas' => array(), 'tributos' => array());


          //Leyenda JSON
          $json['leyendas'][] = array('codLeyenda' => "1000", 'desLeyenda' => $con_letra);
          $json['tributos'][] = array('ideTributo' => "1000", 'nomTributo' => "IGV", 'codTipTributo' => "VAT", 'mtoBaseImponible' => number_format($totvalvenog[$i], 2, '.', ''), 'mtoTributo' => number_format($sumigv[$i], 2, '.', ''));

        }
        $i = $i + 1;
        $connc = $connc + 1;
      }

      //++++++++++++++++++++++++++++++++++++++++++++++
      //detalle factura Nota de Credito
      $codigo = array();
      $cantidad = array();
      $descripcion = array();
      $vui = array();
      $igvi = array();
      $pvi = array();
      $vvi = array();
      $um = array();
      $sum_igv = array();

      while ($rowdfncd = mysqli_fetch_assoc($resultdfnc)) {
        for ($ifnc = 0; $ifnc < count($resultdfnc); $ifnc++) {
          $codigo[$ifnc] = $rowdfncd["codigo"];
          $cantidad[$ifnc] = $rowdfncd["cantidad"];
          $descripcion[$ifnc] = $rowdfncd["descripcion"];
          $vui[$ifnc] = $rowdfncd["vui"];
          $igvi[$ifnc] = $rowdfncd["igvi"];
          $pvi[$ifnc] = $rowdfncd["pvi"];
          $vvi[$ifnc] = $rowdfncd["vvi"];
          $um[$ifnc] = $rowdfncd["um"];
          $sum_igv[$ifnc] = $rowdfncd["sum_igv"];

          $tipocompf = $rowdfncd["codigo_nota"];
          $numerodocf = $rowdfncd["numeroserienota"];
          $ruc = $datose->numero_ruc;

          // $pathdfnc=$rutadata.$ruc."-".$tipocompf."-".$numerodocf.".det";
          // $handledfnc=fopen($pathdfnc, "a");
          // fwrite($handledfnc, $um[$ifnc]."|".$cantidad[$ifnc]."|".$codigo[$ifnc]."|-|".$descripcion[$ifnc]."|".$vui[$ifnc]."|".$sum_igv[$ifnc]."|1000|".$sum_igv[$ifnc]."|".$vvi[$ifnc]."|IGV|VAT|10|18|-|||||||-||||||".$pvi[$ifnc]."|".$vvi[$ifnc]."|0|\r\n"); 
          // fclose($handledfnc);

          $json['detalle'][] = array('codUnidadMedida' => $um[$ifnc], 'ctdUnidadItem' => number_format($cantidad[$ifnc], 2, '.', ''), 'codProducto' => $codigo[$ifnc], 'codProductoSUNAT' => "-", 'desItem' => $descripcion[$ifnc], 'mtoValorUnitario' => number_format($vui[$ifnc], 2, '.', ''), 'sumTotTributosItem' => number_format($igvi[$ifnc], 2, '.', ''), 'codTriIGV' => "1000", 'mtoIgvItem' => number_format($igvi[$ifnc], 2, '.', ''), 'mtoBaseIgvItem' => number_format($vvi[$ifnc], 2, '.', ''), 'nomTributoIgvItem' => "IGV", 'codTipTributoIgvItem' => "VAT", 'tipAfeIGV' => "10", 'porIgvItem' => "18.0", 'codTriISC' => "-", 'mtoIscItem' => "", 'mtoBaseIscItem' => "", 'nomTributoIscItem' => "", 'codTipTributoIscItem' => "", 'tipSisISC' => "", 'porIscItem' => "", 'codTriOtroItem' => "-", 'mtoTriOtroItem' => "", 'mtoBaseTriOtroItem' => "", 'nomTributoIOtroItem' => "", 'codTipTributoIOtroItem' => "", 'porTriOtroItem' => "", 'mtoPrecioVentaUnitario' => number_format($pvi[$ifnc], 2, '.', ''), 'mtoValorVentaItem' => number_format($vvi[$ifnc], 2, '.', ''), 'mtoValorReferencialUnitario' => "0");
        }
      }


      $path = $rutadata . $ruc . "-" . $codigo_nota . "-" . $numeroserienota . ".json";
      $jsonencoded = json_encode($json, JSON_UNESCAPED_UNICODE);
      $fh = fopen($path, 'w');
      fwrite($fh, $jsonencoded);
      fclose($fh);

    } else { // ELSE BOLETA

      $resultdbnc = mysqli_query($connect, $querydetbolncd);

      $fecha = array();
      $codtiponotanc = array();
      $descrip = array();
      $tipodocmodnc = array();
      $serienumeronc = array();
      $tdoccliente = array();
      $ndocucliente = array();
      $rsocialcliente = array();
      $tipomone = array();
      $sumot = array();
      $totvalvenog = array();
      $totvalvenoi = array();
      $totvalvenoe = array();
      $sumigv = array();
      $sumisc = array();
      $sumot = array();
      $imptotal = array();

      $codigo_nota = "";
      $numeroserienota = "";

      $connc = 0;

      while ($rownc = mysqli_fetch_assoc($resultnc)) {
        for ($i = 0; $i <= count($resultnc); $i++) {
          $codigo_nota = $rownc["codigo_nota"];
          $numeroserienota = $rownc["numeroserienota"];

          $fecha[$i] = $rownc["fecha"];
          $codtiponotanc[$i] = $rownc["codtiponota"];
          $descrip[$i] = $rownc["descripcion"];
          $tipodocmodnc[$i] = $rownc["tipo_doc_mod"];
          $serienumeronc[$i] = $rownc["serie_numero"];
          $tdoccliente[$i] = $rownc["tipo_doc_ide"];
          $rsocialcliente[$i] = $rownc["razon_social"];
          $ndocucliente[$i] = $rownc["numero_doc_ide"];
          $tipomone[$i] = $rownc["tipo_moneda"];
          $sumot[$i] = $rownc["sum_ot"];
          $totvalvenog[$i] = $rownc["total_val_venta_og"];
          $totvalvenoi[$i] = $rownc["total_val_venta_oi"];
          $totvalvenoe[$i] = $rownc["total_val_venta_oe"];
          $sumigv[$i] = $rownc["sum_igv"];
          $sumisc[$i] = $rownc["sum_isc"];
          $sumot[$i] = $rownc["sum_ot"];
          $hora = $rownc["hora"];
          $imptotal[$i] = $rownc["total"];
          $ruc = $datose->numero_ruc;
          $path = $rutadata . $ruc . "-" . $codigo_nota . "-" . $numeroserienota . ".not";

          // $handlenc=fopen($path, "w");
          // fwrite($handlenc, "0101|".$fecha[$i]."|".$hora."|0000|".$tdoccliente[$i]."|".$ndocucliente[$i]."|".$rsocialcliente[$i]."|".$tipomone[$i]."|".$codtiponotanc[$i]."|".$descrip[$i]."|".$tipodocmodnc[$i]."|".$serienumeronc[$i]."|".$sumigv[$i]."|".$totvalvenog[$i]."|".$imptotal[$i]."|0|0|0|".$imptotal[$i]."|2.1|2.0|"); 
          // fclose($handlenc);

          require_once "Letras.php";
          $V = new EnLetras();
          $con_letra = strtoupper($V->ValorEnLetras($imptotal[$i], "NUEVOS SOLES"));
          // $path=$rutadata.$ruc."-".$codigo_nota."-".$numeroserienota.".ley";
          // $handle=fopen($path, "w");
          // fwrite($handle,"1000|".$con_letra."|"); 
          // fclose($handle);

          // $path=$rutadata.$ruc."-".$codigo_nota."-".$numeroserienota.".tri";
          // $handle=fopen($path, "w");
          // //fwrite($handle,"1000|IGV|VAT|S|".$totvalvenog[$i]."|".$sumigv[$i]."|"); VERSION 1.1
          // fwrite($handle,"1000|IGV|VAT|".$totvalvenog[$i]."|".$sumigv[$i]."|"); 
          // fclose($handle);

          $json = array('cabecera' => array('tipOperacion' => '0101', 'fecEmision' => $fecha[$i], 'horEmision' => $hora, 'codLocalEmisor' => "0000", 'tipDocUsuario' => $tdoccliente[$i], 'numDocUsuario' => $ndocucliente[$i], 'rznSocialUsuario' => $rsocialcliente[$i], 'tipMoneda' => $tipomone[$i], 'codMotivo' => $codtiponotanc[$i], 'desMotivo' => $descrip[$i], 'tipDocAfectado' => $tipodocmodnc[$i], 'numDocAfectado' => $serienumeronc[$i], 'sumTotTributos' => $sumigv[$i], 'sumTotValVenta' => $totvalvenog[$i], 'sumPrecioVenta' => $imptotal[$i], 'sumDescTotal' => '0.00', 'sumOtrosCargos' => '0.00', 'sumTotalAnticipos' => '0.00', 'sumImpVenta' => $imptotal[$i], 'ublVersionId' => "2.1", 'customizationId' => "2.0"), 'detalle' => array(), 'leyendas' => array(), 'tributos' => array());


          //Leyenda JSON
          $json['leyendas'][] = array('codLeyenda' => "1000", 'desLeyenda' => $con_letra);
          $json['tributos'][] = array('ideTributo' => "1000", 'nomTributo' => "IGV", 'codTipTributo' => "VAT", 'mtoBaseImponible' => number_format($totvalvenog[$i], 2, '.', ''), 'mtoTributo' => number_format($sumigv[$i], 2, '.', ''));
          //Leyenda JSON
        }
        $i = $i + 1;
        $connc = $connc + 1;
      }


      //---------------------------DETALLE PARA BOLETA------------------------------------------
      $codigo = array();
      $cantidad = array();
      $descripcion = array();
      $vui = array();
      $igvi = array();
      $pvi = array();
      $vvi = array();
      $um = array();
      $sum_igv = array();

      while ($rowdbncd = mysqli_fetch_assoc($resultdbnc)) {
        for ($i = 0; $i < count($resultdbnc); $i++) {
          $codigo[$i] = $rowdbncd["codigo"];
          $cantidad[$i] = $rowdbncd["cantidad"];
          $descripcion[$i] = $rowdbncd["descripcion"];
          $vui[$i] = $rowdbncd["vui"];
          $igvi[$i] = $rowdbncd["igvi"];
          $pvi[$i] = $rowdbncd["pvi"];
          $vvi[$i] = $rowdbncd["vvi"];
          $tipocompb = $rowdbncd["codigo_nota"];
          $numerodocb = $rowdbncd["numeroserienota"];
          $ruc = $datose->numero_ruc;
          $um[$i] = $rowdbncd["um"];

          // $pathdbnc=$rutadata.$ruc."-".$tipocompb."-".$numerodocb.".det";
          // $handledbnc=fopen($pathdbnc, "a");
          // fwrite($handledbnc, $um[$i]."|".$cantidad[$i]."|".$codigo[$i]."|-|".$descripcion[$i]."|".$vui[$i]."|".$igvi[$i]."|1000|".$igvi[$i]."|".$vvi[$i]."|IGV|VAT|10|18|-|||||||-||||||".$pvi[$i]."|".$vvi[$i]."|0|\r\n"); 
          // fclose($handledbnc);


          $json['detalle'][] = array('codUnidadMedida' => $um[$i], 'ctdUnidadItem' => number_format($cantidad[$i], 2, '.', ''), 'codProducto' => $codigo[$i], 'codProductoSUNAT' => "-", 'desItem' => $descripcion[$i], 'mtoValorUnitario' => number_format($vui[$i], 2, '.', ''), 'sumTotTributosItem' => number_format($igvi[$i], 2, '.', ''), 'codTriIGV' => "1000", 'mtoIgvItem' => number_format($igvi[$i], 2, '.', ''), 'mtoBaseIgvItem' => number_format($vvi[$i], 2, '.', ''), 'nomTributoIgvItem' => "IGV", 'codTipTributoIgvItem' => "VAT", 'tipAfeIGV' => "10", 'porIgvItem' => "18.0", 'codTriISC' => "-", 'mtoIscItem' => "", 'mtoBaseIscItem' => "", 'nomTributoIscItem' => "", 'codTipTributoIscItem' => "", 'tipSisISC' => "", 'porIscItem' => "", 'codTriOtroItem' => "-", 'mtoTriOtroItem' => "", 'mtoBaseTriOtroItem' => "", 'nomTributoIOtroItem' => "", 'codTipTributoIOtroItem' => "", 'porTriOtroItem' => "", 'mtoPrecioVentaUnitario' => number_format($pvi[$i], 2, '.', ''), 'mtoValorVentaItem' => number_format($vvi[$i], 2, '.', ''), 'mtoValorReferencialUnitario' => "0");
        }
      }


      $path = $rutadata . $ruc . "-" . $codigo_nota . "-" . $numeroserienota . ".json";
      $jsonencoded = json_encode($json, JSON_UNESCAPED_UNICODE);
      $fh = fopen($path, 'w');
      fwrite($fh, $jsonencoded);
      fclose($fh);
      //===================== EXPORTAR A TX COMPROBANTE ===================================

    }
    // Retorna el ID de la nota creada si fue exitoso, o false si hubo error
    return $sw ? $idnotanew : false;
  }







  //Implementamos un método para insertar registros para factura
  public function insertarNC(
    $idnota,
    $nombre,
    $serie,
    $numero_nc,
    $fecha,
    $codigo_nota,
    $codtiponota,
    $desc_motivo,
    $tipo_doc_mod,
    $numero_comprobante,
    $tipo_doc_ide,
    $numero_documento,
    $razon_social,
    $tipo_moneda,
    $sum_ot_car,
    $subtotal,
    $total_val_venta_oi,
    $total_val_venta_oe,
    $igv_,
    $sum_isc,
    $total_finalNC,
    $total,
    $idserie,
    $idcomprobante,
    $fecha_comprobante,
    $hora,
    $tiponotaC,
    $idarticulo,
    $codigo,
    $cantidad,
    $pvt,
    $unidad_medida,
    $igvBD,
    $valor_unitario,
    $subtotalBD,
    $vendedorsitio,
    $idempresa,
    $tipodoc_mod,
    $motivonota,
    $aigv,
    $codtrib,
    $nomtrib,
    $coditrib,
    $numorden,
    $descarti
  ) {
    $sw = true;
    //Guardar en la table nota de credito
    $sql = "insert into notacd 
        (
         
        nombre, 
        numeroserienota, 
        fecha, 
        codigo_nota, 
        codtiponota,  
        desc_motivo, 
        tipo_doc_mod, 
        serie_numero, 
        tipo_doc_ide, 
        numero_doc_ide, 
        razon_social, 
        tipo_moneda, 
        sum_ot_car, 
        total_val_venta_og, 
        total_val_venta_oi, 
        total_val_venta_oe, 
        sum_igv, 
        sum_isc, 
        sum_ot, 
        importe_total, 
        estado, 
        idcomprobante,
        fechacomprobante, 
        idempresa,
        vendedorsitio,
        difComprobante,
        DetalleSunat,
        motivonota
        )
        values
        (
        
         '$nombre',
         '$serie-$numero_nc',
         '$fecha $hora',
         '$codigo_nota',
         '$codtiponota',
         '$desc_motivo',
         '$tipo_doc_mod',
         '$numero_comprobante',
         '$tipo_doc_ide',
         '$numero_documento',
         '$razon_social',
         '$tipo_moneda',
         '$sum_ot_car',
         '$subtotal',
         '$total_val_venta_oi',
         '$total_val_venta_oe',
         '$igv_',
         '$sum_isc',
         '0',
         '$total', 
         '1',
         '$idcomprobante',
         '$fecha_comprobante',
          '$idempresa',
          '$vendedorsitio',
          '$tipodoc_mod',
           'EMITIDO',
          '$motivonota'
        )";

    $idnotanew = ejecutarConsulta_retornarID($sql);

    //Para actualizar numeracion de las series de la factura
    $sql_update_numeracion = "update numeracion set numero='$numero_nc' where idnumeracion='$idserie'";
    ejecutarConsulta($sql_update_numeracion) or $sw = false;
    //Para actualizar numeracion de las series de la factura


    //===============================================================================================
    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
      exit();
    }

    if ($tipodoc_mod == '01') { //SI ES FACTURA
      if ($idarticulo == '') { // si es por todo el comprobante

        $query = "select 
    idfactura, 
    idarticulo  
    from 
    detalle_fac_art 
    where idfactura = '$idcomprobante'";
        $resultado = mysqli_query($connect, $query);

        $Idf = array();
        $Ida = array();

        while ($fila = mysqli_fetch_assoc($resultado)) {
          for ($i = 0; $i < count($resultado); $i++) {
            $Idf[$i] = $fila["idfactura"];
            $Ida[$i] = $fila["idarticulo"];

            //Guardar registro en Kardex
            $sql_kardex = "insert into 
        kardex 
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
          idempresa) 

          values 

          (
          '$idnotanew', 
          (select a.idarticulo 
            from 
            articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 

            'NOTAC', 

            (select a.codigo from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

             '$fecha', 
             '07', 
             '$serie-$numero_nc', 

             (select dtf.cantidad_item_12 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 

             (select dtf.valor_uni_item_14 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

             (select a.unidad_medida from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

             0, 0, 0, '$idempresa')";

            //Guardar en el detalle de nota de crédito
            $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta,
          aigv, 
          codtrib, 
          nomtrib, 
          coditrib,
          descripitem 
          ) 
          values 
          (
          '$idnotanew', 

    (select a.idarticulo from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 

    (select dtf.numero_orden_item_33 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 

          (select dtf.cantidad_item_12 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 

          (select dtf.precio_venta_item_15_2 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

          (select dtf.afectacion_igv_item_16_2 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

          (select dtf.valor_uni_item_14 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

          (select dtf.valor_venta_item_21 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

           (select dtf.afectacion_igv_item_16_3 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

           (select dtf.afectacion_igv_item_16_4 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

           (select dtf.afectacion_igv_item_16_5 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

           (select dtf.afectacion_igv_item_16_6 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),
           ''

           
        )";


          }

          ejecutarConsulta($sql_kardex) or $sw = false; //Guarda KARDEX
          ejecutarConsulta($sql_det_notacd) or $sw = false;

        }

      } else // else de la nota de credito cuando es por item//===========================
      {

        // si solo es por algunos items
        $num_elementos = 0;
        while ($num_elementos < count($idarticulo)) { // While1
          //Guardar en Kardex
          $sum = $num_elementos + 1;
          $sql_kardex = "insert into 
        kardex 
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
          valor_final
          ) 
          values 
          (
            '$idnotanew', 
            '$idarticulo[$num_elementos]',
            'NOTAC', 
            '$codigo[$num_elementos]', 
            '$fecha' , 
            '07',
            '$serie-$numero_nc', 
            '$cantidad[$num_elementos]', 
            '$pvt[$num_elementos]',
            '$unidad_medida[$num_elementos]',
            (select saldo_finu - '$cantidad[$num_elementos]' from articulo where idarticulo='$idarticulo[$num_elementos]') ,
            (select precio_final_kardex from articulo where idarticulo='$idarticulo[$num_elementos]'), saldo_final * costo_2
          )";

          //Guardar en el detalle de nota de crédito
          $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta,
           aigv, codtrib, nomtrib, coditrib , numorden,
           descripitem
          ) 
          values 
          (
          '$idnotanew', 
          '$idarticulo[$num_elementos]', 
          '$sum', 
          '$cantidad[$num_elementos]', 
          '$valor_unitario[$num_elementos]',
          '$igvBD[$num_elementos]',
          '$pvt[$num_elementos]',
          '$subtotalBD[$num_elementos]',

          '$aigv[$num_elementos]',
          '$codtrib[$num_elementos]',
          '$nomtrib[$num_elementos]',
          '$coditrib[$num_elementos]',
          '$numorden[$num_elementos]',
          '$descarti[$num_elementos]'
        )";

          ejecutarConsulta($sql_kardex) or $sw = false;
          ejecutarConsulta($sql_det_notacd) or $sw = false;

          $num_elementos = $num_elementos + 1;
        } //Fin while 1
      } //Fin else de nota de credito por Item


    } else if ($tipodoc_mod == '04') { //FACTURA DE SERVICIO
      if ($idarticulo == '') { // si es por todo el comprobante

        $query = "select 
    idfactura, 
    idarticulo  
    from 
    detalle_fac_art_ser 
    where idfactura = '$idcomprobante'";
        $resultado = mysqli_query($connect, $query);

        $Idf = array();
        $Ida = array();

        while ($fila = mysqli_fetch_assoc($resultado)) {
          for ($i2 = 0; $i2 < count($resultado); $i2++) {
            $Idf[$i2] = $fila["idfactura"];
            $Ida[$i2] = $fila["idarticulo"];


            //Guardar en el detalle de nota de crédito
            $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta
          ) 
          values 
          (
          '$idnotanew', 

  (select a.id from servicios_inmuebles a inner join detalle_fac_art_ser dtf on a.id=dtf.idarticulo where a.id='$Ida[$i2]' and dtf.idfactura = '$Idf[$i2]'), 

          '$i2', 

          '1', 

          (select dtf.precio_venta_item_15_2 from servicios_inmuebles a inner join detalle_fac_art_ser dtf on a.id=dtf.idarticulo where a.id='$Ida[$i2]' and dtf.idfactura = '$Idf[$i2]'),

          (select dtf.afectacion_igv_item_16_2 from servicios_inmuebles a inner join detalle_fac_art_ser dtf on a.id=dtf.idarticulo where a.id='$Ida[$i2]' and dtf.idfactura = '$Idf[$i2]'),

          (select dtf.valor_uni_item_14 from servicios_inmuebles a inner join detalle_fac_art_ser dtf on a.id=dtf.idarticulo where a.id='$Ida[$i2]' and dtf.idfactura = '$Idf[$i2]'),

          (select dtf.valor_venta_item_21 from servicios_inmuebles a inner join detalle_fac_art_ser dtf on a.id=dtf.idarticulo where a.id='$Ida[$i2]' and dtf.idfactura = '$Idf[$i2]')
        )";


          }

          ejecutarConsulta($sql_det_notacd) or $sw = false;

        }


        //  }
        //     else // else de la nota de credito cuando es por item//===========================
        //     {

        //  // si solo es por algunos items
        // $num_elementos=0;    
        // while ($num_elementos < count($idarticulo)){ // While1
        //     //Guardar en Kardex
        //       //Guardar en el detalle de nota de crédito
        //       $sql_det_notacd="insert into 
        //     detalle_notacd_art 
        //     (
        //       idnotacd,
        //       idarticulo, 
        //       nro_orden, 
        //       cantidad, 
        //       precio_venta, 
        //       igv, 
        //       valor_unitario, 
        //       valor_venta
        //       ) 
        //       values 
        //       (
        //       '$idnotanew', 
        //       '$idarticulo[$num_elementos]', 
        //       '$num_elementos', 
        //       '1', 
        //       '$valor_unitario[$num_elementos]',
        //       '$igvBD[$num_elementos]',
        //       '$pvt[$num_elementos]',
        //       '$subtotalBD[$num_elementos]'
        //     )";

        //       ejecutarConsulta($sql_det_notacd) or $sw=false;

        //       $num_elementos=$num_elementos + 1;
        //     } //Fin while 1
      } //Fin else de nota de credito por Item



    } else if ($tipodoc_mod == '03') // Else SI ES BOLETA 
    {
      if ($idarticulo == '') { //SI ES POR TODO EL COMPROBANTE

        $query = "select 
    idboleta, 
    idarticulo  
    from 
    detalle_boleta_producto 
    where idboleta = '$idcomprobante'";
        $resultado = mysqli_query($connect, $query);

        $Idb = array();
        $Ida = array();

        while ($fila = mysqli_fetch_assoc($resultado)) {
          for ($i = 0; $i < count($resultado); $i++) {
            $Idb[$i] = $fila["idboleta"];
            $Ida[$i] = $fila["idarticulo"];
            //Guardar en Kardex
            $sql_kardex = "insert into 
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
          valor_final, idempresa) 

          values 

          ('$idnotanew', 
          (select 
            a.idarticulo 
            from 
            articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'), 

            'NOTAC', 

            (select a.codigo from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

             '$fecha','07', '$serie-$numero_nc', 

             (select dtb.cantidad_item_12 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'), 

             (select dtb.valor_uni_item_31 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

             (select a.unidad_medida from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

             0, 0, 0,'$idempresa')";

            //Guardar en el detalle de nota de crédito
            $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta,
          aigv, codtrib, nomtrib, coditrib , numorden
          ) 
          values 
          (
          '$idnotanew', 
          (select a.idarticulo 
          from 
          articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'), 
           (select dtb.numero_orden_item_29 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'), 
          (select dtb.cantidad_item_12 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'), 
          (select dtb.precio_uni_item_14_2 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),
          (select dtb.afectacion_igv_item_monto_27_1 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),
          (select dtb.valor_uni_item_31 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),
          (select dtb.valor_venta_item_32 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

          (select dtb.afectacion_igv_3 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

           (select dtb.afectacion_igv_4 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

           (select dtb.afectacion_igv_5 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

           (select dtb.afectacion_igv_6 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

           (select dtb.numero_orden_item_29 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]')

        )";
          }

          ejecutarConsulta($sql_kardex) or $sw = false; //Guarda KARDEX
          ejecutarConsulta($sql_det_notacd) or $sw = false;

        }
      } else // else de la nota de credito cuando es por item//==========BOLETA
      {

        // si solo es por algunos items
        $num_elementos = 0;
        while ($num_elementos < count($idarticulo)) { // While1 //BOLETA 02-10
          //Guardar en Kardex
          $sql_kardex = "insert into 
        kardex 
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
          valor_final, idempresa
          ) 
          values 
          (
          '$idnotanew', 
           '$idarticulo[$num_elementos]',
            'NOTA DE CREDITO', 
            '$codigo[$num_elementos]', 
            '$fecha' , 
            '07',
            '$serie-$numero_nc', 
            '$cantidad[$num_elementos]', 
            '$pvt[$num_elementos]',
            '$unidad_medida[$num_elementos]',
            (select saldo_finu - '$cantidad[$num_elementos]' from articulo where idarticulo='$idarticulo[$num_elementos]') ,
            (select precio_final_kardex from articulo where idarticulo='$idarticulo[$num_elementos]'), saldo_final * costo_2, '$idempresa'
          )";

          //Guardar en el detalle de nota de crédito
          $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta,
          aigv, codtrib, nomtrib, coditrib , numorden,
          descripitem
          ) 
          values 
          (
          '$idnotanew', 
          '$idarticulo[$num_elementos]', 
          '$num_elementos', 
          '$cantidad[$num_elementos]', 
          '$valor_unitario[$num_elementos]',
          '$igvBD[$num_elementos]',
          '$pvt[$num_elementos]',
          '$subtotalBD[$num_elementos]',

          '$aigv[$num_elementos]',
          '$codtrib[$num_elementos]',
          '$nomtrib[$num_elementos]',
          '$coditrib[$num_elementos]',
          '$numorden[$num_elementos]',
          '$descarti[$num_elementos]'

        )";

          ejecutarConsulta($sql_kardex) or $sw = false;
          ejecutarConsulta($sql_det_notacd) or $sw = false;

          $num_elementos = $num_elementos + 1;
        } //Fin while 1
      } //Fin else de nota de credito por Item BOLETA



    } else { //BOLETA DE SERVICIO



      if ($idarticulo == '') { //SI ES POR TODO EL COMPROBANTE
        $query = "select 
    idboleta, 
    idarticulo  
    from 
    detalle_boleta_producto_ser 
    where idboleta = '$idcomprobante'";
        $resultado = mysqli_query($connect, $query);

        $Idb = array();
        $Ida = array();

        while ($fila = mysqli_fetch_assoc($resultado)) {
          for ($i = 0; $i < count($resultado); $i++) {
            $Idb[$i] = $fila["idboleta"];
            $Ida[$i] = $fila["idarticulo"];
            //Guardar en Kardex
            //Guardar en el detalle de nota de crédito
            $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta
          ) 
         values 
          (
          '$idnotanew', 

          (select a.id 
          from 
          servicios_inmuebles a inner join detalle_boleta_producto_ser dtb on a.id=dtb.idarticulo where a.id='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'), 

          '$i', 

          '1', 

          (select dtb.precio_uni_item_14_2 from servicios_inmuebles a inner join detalle_boleta_producto_ser dtb on a.id=dtb.idarticulo where a.id='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

          (select dtb.afectacion_igv_item_monto_27_1 from servicios_inmuebles a inner join detalle_boleta_producto_ser dtb on a.id=dtb.idarticulo where a.id='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

          (select dtb.valor_uni_item_31 from servicios_inmuebles a inner join detalle_boleta_producto_ser dtb on a.id=dtb.idarticulo where a.id='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

          (select dtb.valor_venta_item_32 from servicios_inmuebles a inner join detalle_boleta_producto_ser dtb on a.id=dtb.idarticulo where a.id='$Ida[$i]' and dtb.idboleta = '$Idb[$i]')
        )";
          }
        }

        ejecutarConsulta($sql_det_notacd) or $sw = false;


      } else // else de la nota de credito cuando es por item//==========BOLETA
      {
        // si solo es por algunos items
        $num_elementos = 0;
        while ($num_elementos < count($idarticulo)) { // While1 //BOLETA 02-10
          //Guardar en Kardex
          //Guardar en el detalle de nota de crédito
          $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta
          ) 
          values 
          (
          '$idnotanew', 
          '$idarticulo[$num_elementos]', 
          '$num_elementos', 
          '$cantidad[$num_elementos]', 
          '$valor_unitario[$num_elementos]',
          '$igvBD[$num_elementos]',
          '$pvt[$num_elementos]',
          '$subtotalBD[$num_elementos]'
        )";

          ejecutarConsulta($sql_det_notacd) or $sw = false;
          $num_elementos = $num_elementos + 1;
        } //Fin while 1
      } //Fin else de nota de credito por Item BOLETA

    }
    //=========================================================================




    //===================== EXPORTAR A TX COMPROBANTE ===================================
    // Retorna el ID de la nota creada si fue exitoso, o false si hubo error
    return $sw ? $idnotanew : false;
  }

































  //Implementamos un método para insertar registros para factura
  public function insertarND($idnota, $nombre, $serie, $numero_nc, $fecha, $codigo_nota, $codtiponota, $desc_motivo, $tipo_doc_mod, $numero_comprobante, $tipo_doc_ide, $numero_documento, $razon_social, $tipo_moneda, $sum_ot_car, $subtotal, $total_val_venta_oi, $total_val_venta_oe, $igv_, $sum_isc, $sum_ot, $total, $idserie, $idcomprobante, $fecha_comprobante, $hora2, $totalnd, $vendedorsitio, $idempresa, $tipodoc_mod, $motivonota)
  {
    $sw = true;

    $sql = "insert into notacd
         (
         idnota, 
          nombre, 
          numeroserienota, 
          fecha, 
          codigo_nota, 
          codtiponota, 
          desc_motivo, 
          tipo_doc_mod, 
          serie_numero, 
          tipo_doc_ide, 
          numero_doc_ide, 
          razon_social, 
          tipo_moneda, 
          sum_ot_car, 
          total_val_venta_og, 
          total_val_venta_oi, 
          total_val_venta_oe, 
          sum_igv, 
          sum_isc, 
          sum_ot, 
          importe_total, 
          estado, 
          idcomprobante, 
          fechacomprobante,
          adicional,
          idempresa,
          vendedorsitio,
          difComprobante,
           DetalleSunat,
          motivonota
        )
        values 
        (
        ' $idnota',
         '$nombre',
         '$serie-$numero_nc',
         '$fecha $hora2',
         '$codigo_nota',
         '$codtiponota',
         '$desc_motivo',
         '$tipo_doc_mod',
         '$numero_comprobante',
         '$tipo_doc_ide',
         '$numero_documento',
         '$razon_social',
         '$tipo_moneda',
         '$sum_ot_car',
         '$subtotal',
         '$total_val_venta_oi',
         '$total_val_venta_oe',
         '$igv_',
         '$sum_isc',
         '0',
         '$total', 
         '1',
         '$idcomprobante',
         '$fecha_comprobante',
         '$totalnd',
         '$idempresa',
         '$vendedorsitio',
         '$tipodoc_mod',
         'EMITIDO',
         '$motivonota'
      )";
    $idnotanew = ejecutarConsulta_retornarID($sql);
    //&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&&


    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
      exit();
    }



    if ($tipodoc_mod == '01') { //SI ES FACTURa

      $query = "select 
    idfactura, 
    idarticulo  
    from 
    detalle_fac_art 
    where idfactura = '$idcomprobante'";
      $resultado = mysqli_query($connect, $query);

      $Idf = array();
      $Ida = array();

      while ($fila = mysqli_fetch_assoc($resultado)) {
        for ($i = 0; $i < count($resultado); $i++) {
          $Idf[$i] = $fila["idfactura"];
          $Ida[$i] = $fila["idarticulo"];

          //Guardar registro en Kardex
          $sql_kardex = "insert into 
        kardex 
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
          valor_final, idempresa) 

          values 

          (
          '$idnotanew', 
          (select a.idarticulo from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 
            'NOTAD', 
            (select a.codigo from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),
             '$fecha', 
             '08', 
             '$serie-$numero_nc', 
             (select dtf.cantidad_item_12 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 
             (select dtf.valor_uni_item_14 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),
             (select a.unidad_medida from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),
             0, 0, 0, '$idempresa')";

          //Guardar en el detalle de nota de crédito
          $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta
          ) 
          values 
          (
          '$idnotanew', 
          (select a.idarticulo 
          from 
          articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 
          (select dtf.numero_orden_item_33 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 
          (select dtf.cantidad_item_12 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 
          (select dtf.precio_venta_item_15_2 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),
          (select dtf.afectacion_igv_item_16_2 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),
          (select dtf.valor_uni_item_14 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),
          (select dtf.valor_venta_item_21 from articulo a inner join detalle_fac_art dtf on a.idarticulo=dtf.idarticulo where a.idarticulo='$Ida[$i]' and dtf.idfactura = '$Idf[$i]')
        )";


        }

        ejecutarConsulta($sql_kardex) or $sw = false; //Guarda KARDEX
        ejecutarConsulta($sql_det_notacd) or $sw = false;

      }



    } else if ($tipodoc_mod == '04') { //Factura servicio


      $query = "select 
    idfactura, 
    idarticulo  
    from 
    detalle_fac_art_ser 
    where idfactura = '$idcomprobante'";
      $resultado = mysqli_query($connect, $query);

      $Idf = array();
      $Ida = array();

      while ($fila = mysqli_fetch_assoc($resultado)) {
        for ($i = 0; $i < count($resultado); $i++) {
          $Idf[$i] = $fila["idfactura"];
          $Ida[$i] = $fila["idarticulo"];

          //Guardar en el detalle de nota de crédito
          $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta
          ) 
          values 
          (
          '$idnotanew', 

          (select a.id 
          from 
          servicios_inmuebles a inner join detalle_fac_art_ser dtf on a.id=dtf.idarticulo where a.id='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'), 

          '$i', 

          '1', 

          (select dtf.precio_venta_item_15_2 from servicios_inmuebles a inner join detalle_fac_art_ser dtf on a.id=dtf.idarticulo where a.id='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

          (select dtf.afectacion_igv_item_16_2 from servicios_inmuebles a inner join detalle_fac_art_ser dtf on a.id=dtf.idarticulo where a.id='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

          (select dtf.valor_uni_item_14 from servicios_inmuebles a inner join detalle_fac_art_ser dtf on a.id=dtf.idarticulo where a.id='$Ida[$i]' and dtf.idfactura = '$Idf[$i]'),

          (select dtf.valor_venta_item_21 from servicios_inmuebles a inner join detalle_fac_art_ser dtf on a.id=dtf.idarticulo where a.id='$Ida[$i]' and dtf.idfactura = '$Idf[$i]')
        )";
        }
        ejecutarConsulta($sql_det_notacd) or $sw = false;

      }


    } else if ($tipodoc_mod == '03') // Else SI ES BOLETA 
    {

      $query = "select 
    idboleta, 
    idarticulo  
    from 
    detalle_boleta_producto 
    where idboleta = '$idcomprobante'";
      $resultado = mysqli_query($connect, $query);

      $Idb = array();
      $Ida = array();

      while ($fila = mysqli_fetch_assoc($resultado)) {
        for ($i = 0; $i < count($resultado); $i++) {
          $Idb[$i] = $fila["idboleta"];
          $Ida[$i] = $fila["idarticulo"];
          //Guardar en Kardex
          $sql_kardex = "insert into 
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
          valor_final, idempresa) 

          values 

          ('$idnotanew', 
          (select 
            a.idarticulo 
            from 
            articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'), 

            'NOTAD', 

            (select a.codigo from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

             '$fecha','08', '$serie-$numero_nc', 

             (select dtb.cantidad_item_12 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'), 

             (select dtb.valor_uni_item_31 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

             (select a.unidad_medida from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

             0, 0, 0,'$idempresa')";

          //Guardar en el detalle de nota de crédito
          $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta
          ) 
          values 
          (
          '$idnotanew', 
          (select a.idarticulo 
          from 
          articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'), 
          '$i', 
          (select dtb.cantidad_item_12 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'), 
          (select dtb.precio_uni_item_14_2 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),
          (select dtb.afectacion_igv_item_monto_27_1 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),
          (select dtb.valor_uni_item_31 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),
          (select dtb.valor_venta_item_32 from articulo a inner join detalle_boleta_producto dtb on a.idarticulo=dtb.idarticulo where a.idarticulo='$Ida[$i]' and dtb.idboleta = '$Idb[$i]')
        )";
        }

        ejecutarConsulta($sql_kardex) or $sw = false; //Guarda KARDEX
        ejecutarConsulta($sql_det_notacd) or $sw = false;

      }



    } else { //BOLETA DE SERVICIO


      $query = "select 
    idboleta, 
    idarticulo  
    from 
    detalle_boleta_producto_ser 
    where idboleta = '$idcomprobante'";
      $resultado = mysqli_query($connect, $query);

      $Idb = array();
      $Ida = array();

      while ($fila = mysqli_fetch_assoc($resultado)) {
        for ($i = 0; $i < count($resultado); $i++) {
          $Idb[$i] = $fila["idboleta"];
          $Ida[$i] = $fila["idarticulo"];
          //Guardar en Kardex
          //Guardar en el detalle de nota de crédito
          $sql_det_notacd = "insert into 
        detalle_notacd_art 
        (
          idnotacd,
          idarticulo, 
          nro_orden, 
          cantidad, 
          precio_venta, 
          igv, 
          valor_unitario, 
          valor_venta
          ) 
         values 
          (
          '$idnotanew', 

          (select a.id 
          from 
          servicios_inmuebles a inner join detalle_boleta_producto_ser dtb on a.id=dtb.idarticulo where a.id='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'), 

          '$i', 

          '1', 

          (select dtb.precio_uni_item_14_2 from servicios_inmuebles a inner join detalle_boleta_producto_ser dtb on a.id=dtb.idarticulo where a.id='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

          (select dtb.afectacion_igv_item_monto_27_1 from servicios_inmuebles a inner join detalle_boleta_producto_ser dtb on a.id=dtb.idarticulo where a.id='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

          (select dtb.valor_uni_item_31 from servicios_inmuebles a inner join detalle_boleta_producto_ser dtb on a.id=dtb.idarticulo where a.id='$Ida[$i]' and dtb.idboleta = '$Idb[$i]'),

          (select dtb.valor_venta_item_32 from servicios_inmuebles a inner join detalle_boleta_producto_ser dtb on a.id=dtb.idarticulo where a.id='$Ida[$i]' and dtb.idboleta = '$Idb[$i]')
        )";
        }
      }

      ejecutarConsulta($sql_det_notacd) or $sw = false;


    }












    //==============================================================================================
//Guardar en el detalle de nota de crédito
    // $sql_det_notacd="insert into 
    //     detalle_notacd_art 
    //     (
    //       idnotacd,
    //       idarticulo, 
    //       nro_orden, 
    //       cantidad, 
    //       precio_venta, 
    //       igv, 
    //       valor_unitario, 
    //       valor_venta
    //       ) 
    //       values 
    //       (
    //       '$idnotanew', 
    //       (select idarticulo 
    //       from 
    //       articulo where codigo='1000ncdg'), 
    //       '1', 
    //       '1', 
    //       '$total',
    //       '$igv_',
    //       '$subtotal',
    //       '$subtotal'
    //     )";

    //       ejecutarConsulta($sql_det_notacd) or $sw=false; 

    //   //Guardar registro en Kardex
    //     $sql_kardex="insert into 
    //     kardex 
    //     (
    //       idcomprobante,
    //       idarticulo, 
    //       transaccion, 
    //       codigo, 
    //       fecha, 
    //       tipo_documento, 
    //       numero_doc, 
    //       cantidad, 
    //       costo_1, 
    //       unidad_medida, 
    //       saldo_final, 
    //       costo_2, 
    //       valor_final, idempresa) 
    //       values 
    //       (
    //       '$idnotanew', 
    //       (select idarticulo 
    //       from 
    //       articulo where codigo='1000ncdg'), 
    //       'NOTAD', 
    //       '1000ncdg',
    //       '$fecha_comprobante', 
    //       '08', 
    //       '$serie-$numero_nc', 
    //          '1', 
    //          '',
    //          '',
    //          '',
    //          '',
    //          '', '$idempresa'
    //        )";
    //   ejecutarConsulta($sql_kardex) or $sw=false; //Guarda KARDEX
//==============================================================================================



















    //Para actualizar numeracion de las series de la factura
    $sql_update_numeracion = "update numeracion set numero='$numero_nc' where idnumeracion='$idserie'";
    ejecutarConsulta($sql_update_numeracion) or $sw = false;
    //Fin
    //Para actualizar numeracion de las series de la factura
//=======================================================================================

    //===================== EXPORTAR A TX COMPROBANTE ===========================================
    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
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
    $rutadata = $Prutas->rutadata; // ruta de la carpeta DATA
    $rutadatalt = $Prutas->rutadatalt; // ruta de la carpeta DATA

    $querynotacd = "select
       n.codigo_nota,
       n.numeroserienota, 
       date_format(n.fecha,'%Y-%m-%d') as fecha, 
       date_format(n.fecha, '%H:%i:%s') as hora,
       n.codtiponota, 
       c.descripcion, 
       n.tipo_doc_mod, 
       n.serie_numero, 
       n.tipo_doc_ide, 
       n.razon_social, 
       n.numero_doc_ide, 
       n.tipo_moneda, 
       n.sum_ot, 
       n.total_val_venta_og, 
       n.total_val_venta_oi, 
       n.total_val_venta_oe, 
       n.sum_igv, n.sum_isc, 
       n.sum_ot, 
       n.importe_total as total,
       n.adicional
      from
      notacd n inner join catalogo10 c on n.codtiponota=c.codigo where n.idnota='$idnotanew' ";

    $querydetfacncd =
      "select 
      tipocomp,  
      numerodoc, 
      cantidad,
      codigo, 
      descripcion, 
      vui, 
      igvi, 
      pvi, 
      vvi, 
      codigo_nota, 
      numeroserienota, 
      um, 
      sum_igv 
      from 
      (
      select 
      f.tipo_documento_07 as tipocomp, 
      f.numeracion_08 as numerodoc, 
      dncd.cantidad, 
      a.codigo, 
      a.nombre as descripcion, 
      format(dncd.valor_unitario,2) as vui, 
      dncd.igv as igvi, 
      dncd.precio_venta as pvi, 
      dncd.valor_venta as vvi, 
      ncd.codigo_nota, 
      ncd.numeroserienota, 
      a.unidad_medida as um, 
      ncd.sum_igv
      from
      factura f inner join  notacd ncd on f.idfactura=ncd.idcomprobante inner join detalle_notacd_art dncd on ncd.idnota=dncd.idnotacd  inner join articulo a on dncd.idarticulo=a.idarticulo where ncd.idnota='$idnotanew' and ncd.difComprobante='01'
      union all
      
      select 
      f.tipo_documento_07 as tipocomp,  
      f.numeracion_08 as numerodoc, 
      dncd.cantidad, 
      a.codigo, 
      a.descripcion, 
      format(dncd.valor_unitario,2) as vui, 
      dncd.igv as igvi, 
      dncd.precio_venta as pvi, 
      dncd.valor_venta as vvi, 
      ncd.codigo_nota, 
      ncd.numeroserienota, 
      a.codigo as um, 
      ncd.sum_igv
      from
      facturaservicio f inner join  notacd ncd on f.idfactura=ncd.idcomprobante inner join detalle_notacd_art dncd on ncd.idnota=dncd.idnotacd  inner join servicios_inmuebles a on dncd.idarticulo=a.id where ncd.idnota='$idnotanew' and ncd.difComprobante='04') as tabla
       order by numeroserienota";


    $querydetbolncd =
      "select 
    tipocomp, 
    numerodoc,  
    cantidad, 
    codigo, 
    descripcion, 
    vui, 
    igvi, 
    pvi, 
    vvi, 
    codigo_nota, 
    numeroserienota, 
    um, 
    sum_igv   
      from 
      (
    select 
    b.tipo_documento_06 as tipocomp, 
    b.numeracion_07 as numerodoc,  
    dncd.cantidad, 
    a.codigo, 
    a.nombre as descripcion, 
    format(dncd.valor_unitario,2) as vui, 
    dncd.igv as igvi, 
    dncd.precio_venta as pvi, 
    dncd.valor_venta as vvi, 
    ncd.codigo_nota, 
    ncd.numeroserienota, 
    a.unidad_medida as um, 
    ncd.sum_igv   
    from
      boleta b inner join notacd ncd on b.idboleta=ncd.idcomprobante inner join detalle_notacd_art dncd on ncd.idnota=dncd.idnotacd  inner join articulo a on dncd.idarticulo=a.idarticulo where ncd.idnota='$idnotanew' and ncd.difComprobante='03' 
      union all
      select 
    b.tipo_documento_06 as tipocomp, 
    b.numeracion_07 as numerodoc,  
    dncd.cantidad, 
    a.codigo, 
    a.descripcion, 
    format(dncd.valor_unitario,2) as vui, 
    dncd.igv as igvi, 
    dncd.precio_venta as pvi, 
    dncd.valor_venta as vvi, 
    ncd.codigo_nota, 
    ncd.numeroserienota, 
    a.codigo as um, 
    ncd.sum_igv   
    from
      boletaservicio b inner join notacd ncd on b.idboleta=ncd.idcomprobante inner join detalle_notacd_art dncd on ncd.idnota=dncd.idnotacd  inner join servicios_inmuebles a on dncd.idarticulo=a.id where ncd.idnota='$idnotanew' and ncd.difComprobante='05') 
      as tabla  order by numeroserienota";

    $resultnc = mysqli_query($connect, $querynotacd);

    if ($tipo_doc_mod == '01') {

      $resultdfnc = mysqli_query($connect, $querydetfacncd);

      $fecha = array();
      $codtiponotanc = array();
      $descrip = array();
      $tipodocmodnc = array();
      $serienumeronc = array();
      $tdoccliente = array();
      $ndocucliente = array();
      $rsocialcliente = array();
      $tipomone = array();
      $sumot = array();
      $totvalvenog = array();
      $totvalvenoi = array();
      $totvalvenoe = array();
      $sumigv = array();
      $sumisc = array();
      $sumot = array();
      $imptotal = array();

      $codigo_nota = "";
      $numeroserienota = "";

      $connc = 0;

      while ($rownc = mysqli_fetch_assoc($resultnc)) {
        for ($i = 0; $i <= count($resultnc); $i++) {
          $codigo_nota = $rownc["codigo_nota"];
          $numeroserienota = $rownc["numeroserienota"];

          $fecha[$i] = $rownc["fecha"];
          $codtiponotand[$i] = $rownc["codtiponota"];
          $descrip[$i] = $rownc["descripcion"];
          $tipodocmodnc[$i] = $rownc["tipo_doc_mod"];
          $serienumeronc[$i] = $rownc["serie_numero"];
          $tdoccliente[$i] = $rownc["tipo_doc_ide"];
          $rsocialcliente[$i] = $rownc["razon_social"];
          $ndocucliente[$i] = $rownc["numero_doc_ide"];
          $tipomone[$i] = $rownc["tipo_moneda"];
          $sumot[$i] = $rownc["sum_ot"];
          $totvalvenog[$i] = $rownc["total_val_venta_og"];
          $totvalvenoi[$i] = $rownc["total_val_venta_oi"];
          $totvalvenoe[$i] = $rownc["total_val_venta_oe"];
          $sumigv[$i] = $rownc["sum_igv"];
          $sumisc[$i] = $rownc["sum_isc"];
          $sumot[$i] = $rownc["sum_ot"];
          $imptotal[$i] = $rownc["total"];
          $hora = $rownc["hora"];
          $adicional = $rownc["adicional"];
          $ruc = $datose->numero_ruc;

          //       $path=$rutadata.$ruc."-".$codigo_nota."-".$numeroserienota.".not";
          //       $handlenc=fopen($path, "w");

          // fwrite($handlenc, "0101|".$fecha[$i]."|".$hora."|0000|".$tdoccliente[$i]."|".$ndocucliente[$i]."|".$rsocialcliente[$i]."|".$tipomone[$i]."|".$codtiponotand[$i]."|".$descrip[$i]."|".$tipodocmodnc[$i]."|".$serienumeronc[$i]."|".$sumigv[$i]."|".$totvalvenog[$i]."|".$imptotal[$i]."|0|".$adicional."|0|".$imptotal[$i]."|2.1|2.0|"); 
          //  fclose($handlenc);


          require_once "Letras.php";
          $V = new EnLetras();
          $con_letra = strtoupper($V->ValorEnLetras($imptotal[$i], "NUEVOS SOLES"));
          // $path=$rutadata.$ruc."-".$codigo_nota."-".$numeroserienota.".ley";
          // $handle=fopen($path, "w");
          // fwrite($handle,"1000|".$con_letra."|"); 
          // fclose($handle);

          // $path=$rutadata.$ruc."-".$codigo_nota."-".$numeroserienota.".tri";
          // $handle=fopen($path, "w");
          // fwrite($handle,"1000|IGV|VAT|".$totvalvenog[$i]."|".$sumigv[$i]."|"); 
          // fclose($handle);

          $json = array('cabecera' => array('tipOperacion' => '0101', 'fecEmision' => $fecha[$i], 'horEmision' => $hora, 'codLocalEmisor' => "0000", 'tipDocUsuario' => $tdoccliente[$i], 'numDocUsuario' => $ndocucliente[$i], 'rznSocialUsuario' => $rsocialcliente[$i], 'tipMoneda' => $tipomone[$i], 'codMotivo' => $codtiponotand[$i], 'desMotivo' => $descrip[$i], 'tipDocAfectado' => $tipodocmodnc[$i], 'numDocAfectado' => $serienumeronc[$i], 'sumTotTributos' => $sumigv[$i], 'sumTotValVenta' => $totvalvenog[$i], 'sumPrecioVenta' => $imptotal[$i], 'sumDescTotal' => '0.00', 'sumOtrosCargos' => '0.00', 'sumTotalAnticipos' => '0.00', 'sumImpVenta' => $imptotal[$i], 'ublVersionId' => "2.1", 'customizationId' => "2.0"), 'detalle' => array(), 'leyendas' => array(), 'tributos' => array());


          //Leyenda JSON
          $json['leyendas'][] = array('codLeyenda' => "1000", 'desLeyenda' => $con_letra);
          $json['tributos'][] = array('ideTributo' => "1000", 'nomTributo' => "IGV", 'codTipTributo' => "VAT", 'mtoBaseImponible' => number_format($totvalvenog[$i], 2, '.', ''), 'mtoTributo' => number_format($sumigv[$i], 2, '.', ''));
          //Leyenda JSON

        }
        $i = $i + 1;
        $connc = $connc + 1;
      }

      //++++++++++++++++++++++++++++++++++++++++++++++
      // detalle factura Nota de DEBITO
      $codigo = array();
      $cantidad = array();
      $descripcion = array();
      $vui = array();
      $igvi = array();
      $pvi = array();
      $vvi = array();
      $um = array();

      while ($rowdfncd = mysqli_fetch_assoc($resultdfnc)) {
        for ($ifnc = 0; $ifnc < count($resultdfnc); $ifnc++) {
          $codigo[$ifnc] = $rowdfncd["codigo"];
          $cantidad[$ifnc] = $rowdfncd["cantidad"];
          $descripcion[$ifnc] = $rowdfncd["descripcion"];
          $vui[$ifnc] = $rowdfncd["vui"];
          $igvi[$ifnc] = $rowdfncd["igvi"];
          $pvi[$ifnc] = $rowdfncd["pvi"];
          $vvi[$ifnc] = $rowdfncd["vvi"];
          $um[$ifnc] = $rowdfncd["um"];

          $tipocompf = $rowdfncd["codigo_nota"];
          $numerodocf = $rowdfncd["numeroserienota"];
          $ruc = $datose->numero_ruc;

          //        $pathdfnc=$rutadata.$ruc."-".$tipocompf."-".$numerodocf.".det";
          //        $handledfnc=fopen($pathdfnc, "a");

          // fwrite($handledfnc, $um[$ifnc]."|".$cantidad[$ifnc]."|".$codigo[$ifnc]."|-|".$descripcion[$ifnc]."|".$vui[$ifnc]."|".$igvi[$ifnc]."|1000|".$igvi[$ifnc]."|".$vvi[$ifnc]."|IGV|VAT|10|18|-|||||||-||||||".$pvi[$ifnc]."|".$vvi[$ifnc]."|0|\r\n");  
          //  fclose($handledfnc);

          $json['detalle'][] = array('codUnidadMedida' => $um[$ifnc], 'ctdUnidadItem' => number_format($cantidad[$ifnc], 2, '.', ''), 'codProducto' => $codigo[$ifnc], 'codProductoSUNAT' => "-", 'desItem' => $descripcion[$ifnc], 'mtoValorUnitario' => number_format($vui[$ifnc], 2, '.', ''), 'sumTotTributosItem' => number_format($igvi[$ifnc], 2, '.', ''), 'codTriIGV' => "1000", 'mtoIgvItem' => number_format($igvi[$ifnc], 2, '.', ''), 'mtoBaseIgvItem' => number_format($vvi[$ifnc], 2, '.', ''), 'nomTributoIgvItem' => "IGV", 'codTipTributoIgvItem' => "VAT", 'tipAfeIGV' => "10", 'porIgvItem' => "18.0", 'codTriISC' => "-", 'mtoIscItem' => "", 'mtoBaseIscItem' => "", 'nomTributoIscItem' => "", 'codTipTributoIscItem' => "", 'tipSisISC' => "", 'porIscItem' => "", 'codTriOtroItem' => "-", 'mtoTriOtroItem' => "", 'mtoBaseTriOtroItem' => "", 'nomTributoIOtroItem' => "", 'codTipTributoIOtroItem' => "", 'porTriOtroItem' => "", 'mtoPrecioVentaUnitario' => number_format($pvi[$ifnc], 2, '.', ''), 'mtoValorVentaItem' => number_format($vvi[$ifnc], 2, '.', ''), 'mtoValorReferencialUnitario' => "0");
        }
      }


      $path = $rutadata . $ruc . "-" . $codigo_nota . "-" . $numeroserienota . ".json";
      $jsonencoded = json_encode($json, JSON_UNESCAPED_UNICODE);
      $fh = fopen($path, 'w');
      fwrite($fh, $jsonencoded);
      fclose($fh);

    } else {

      $resultdbnc = mysqli_query($connect, $querydetbolncd);

      $fecha = array();
      $codtiponotanc = array();
      $descrip = array();
      $tipodocmodnc = array();
      $serienumeronc = array();
      $tdoccliente = array();
      $ndocucliente = array();
      $rsocialcliente = array();
      $tipomone = array();
      $sumot = array();
      $totvalvenog = array();
      $totvalvenoi = array();
      $totvalvenoe = array();
      $sumigv = array();
      $sumisc = array();
      $sumot = array();
      $imptotal = array();

      $codigo_nota = "";
      $numeroserienota = "";

      $connc = 0;

      while ($rownc = mysqli_fetch_assoc($resultnc)) {
        for ($i = 0; $i <= count($resultnc); $i++) {
          $codigo_nota = $rownc["codigo_nota"];
          $numeroserienota = $rownc["numeroserienota"];

          $fecha[$i] = $rownc["fecha"];
          $codtiponotand[$i] = $rownc["codtiponota"];
          $descrip[$i] = $rownc["descripcion"];
          $tipodocmodnc[$i] = $rownc["tipo_doc_mod"];
          $serienumeronc[$i] = $rownc["serie_numero"];
          $tdoccliente[$i] = $rownc["tipo_doc_ide"];
          $rsocialcliente[$i] = $rownc["razon_social"];
          $ndocucliente[$i] = $rownc["numero_doc_ide"];
          $tipomone[$i] = $rownc["tipo_moneda"];
          $sumot[$i] = $rownc["sum_ot"];
          $totvalvenog[$i] = $rownc["total_val_venta_og"];
          $totvalvenoi[$i] = $rownc["total_val_venta_oi"];
          $totvalvenoe[$i] = $rownc["total_val_venta_oe"];
          $sumigv[$i] = $rownc["sum_igv"];
          $sumisc[$i] = $rownc["sum_isc"];
          $sumot[$i] = $rownc["sum_ot"];
          $imptotal[$i] = $rownc["total"];
          $hora = $rownc["hora"];
          $adicional = $rownc["adicional"];
          $ruc = $datose->numero_ruc;

          //       $path=$rutadata.$ruc."-".$codigo_nota."-".$numeroserienota.".not";
          //       $handlenc=fopen($path, "w");

          // fwrite($handlenc, "0101|".$fecha[$i]."|".$hora."|0000|".$tdoccliente[$i]."|".$ndocucliente[$i]."|".$rsocialcliente[$i]."|".$tipomone[$i]."|".$codtiponotand[$i]."|".$descrip[$i]."|".$tipodocmodnc[$i]."|".$serienumeronc[$i]."|".$sumigv[$i]."|".$totvalvenog[$i]."|".$imptotal[$i]."|0|".$adicional."|0|".$imptotal[$i]."|2.1|2.0|"); 
          //       fclose($handlenc);


          require_once "Letras.php";
          $V = new EnLetras();
          $con_letra = strtoupper($V->ValorEnLetras($imptotal[$i], "NUEVOS SOLES"));
          // $path=$rutadata.$ruc."-".$codigo_nota."-".$numeroserienota.".ley";
          // $handle=fopen($path, "w");
          // fwrite($handle,"1000|".$con_letra."|"); 
          // fclose($handle);

          // $path=$rutadata.$ruc."-".$codigo_nota."-".$numeroserienota.".tri";
          // $handle=fopen($path, "w");
          // fwrite($handle,"1000|IGV|VAT|".$totvalvenog[$i]."|".$sumigv[$i]."|"); 
          // fclose($handle);

          $json = array('cabecera' => array('tipOperacion' => '0101', 'fecEmision' => $fecha[$i], 'horEmision' => $hora, 'codLocalEmisor' => "0000", 'tipDocUsuario' => $tdoccliente[$i], 'numDocUsuario' => $ndocucliente[$i], 'rznSocialUsuario' => $rsocialcliente[$i], 'tipMoneda' => $tipomone[$i], 'codMotivo' => $codtiponotand[$i], 'desMotivo' => $descrip[$i], 'tipDocAfectado' => $tipodocmodnc[$i], 'numDocAfectado' => $serienumeronc[$i], 'sumTotTributos' => $sumigv[$i], 'sumTotValVenta' => $totvalvenog[$i], 'sumPrecioVenta' => $imptotal[$i], 'sumDescTotal' => '0.00', 'sumOtrosCargos' => '0.00', 'sumTotalAnticipos' => '0.00', 'sumImpVenta' => $imptotal[$i], 'ublVersionId' => "2.1", 'customizationId' => "2.0"), 'detalle' => array(), 'leyendas' => array(), 'tributos' => array());


          //Leyenda JSON
          $json['leyendas'][] = array('codLeyenda' => "1000", 'desLeyenda' => $con_letra);
          $json['tributos'][] = array('ideTributo' => "1000", 'nomTributo' => "IGV", 'codTipTributo' => "VAT", 'mtoBaseImponible' => number_format($totvalvenog[$i], 2, '.', ''), 'mtoTributo' => number_format($sumigv[$i], 2, '.', ''));
          //Leyenda JSON

        }
        $i = $i + 1;
        $connc = $connc + 1;
      }

      //++++++++++++++++++++++++++++++++++++++++++++++
      // detalle boleta Nota de DEBITO
      $codigo = array();
      $cantidad = array();
      $descripcion = array();
      $vui = array();
      $igvi = array();
      $pvi = array();
      $vvi = array();
      $um = array();

      while ($rowdfncd = mysqli_fetch_assoc($resultdbnc)) {
        for ($ifnc = 0; $ifnc < count($resultdbnc); $ifnc++) {
          $codigo[$ifnc] = $rowdfncd["codigo"];
          $cantidad[$ifnc] = $rowdfncd["cantidad"];
          $descripcion[$ifnc] = $rowdfncd["descripcion"];
          $vui[$ifnc] = $rowdfncd["vui"];
          $igvi[$ifnc] = $rowdfncd["igvi"];
          $pvi[$ifnc] = $rowdfncd["pvi"];
          $vvi[$ifnc] = $rowdfncd["vvi"];
          $um[$ifnc] = $rowdfncd["um"];

          $tipocompf = $rowdfncd["codigo_nota"];
          $numerodocf = $rowdfncd["numeroserienota"];
          $ruc = $datose->numero_ruc;

          //        $pathdfnc=$rutadata.$ruc."-".$tipocompf."-".$numerodocf.".det";
          //        $handledfnc=fopen($pathdfnc, "a");

          // fwrite($handledfnc, $um[$ifnc]."|".$cantidad[$ifnc]."|".$codigo[$ifnc]."|-|".$descripcion[$ifnc]."|".$vui[$ifnc]."|".$igvi[$ifnc]."|1000|".$igvi[$ifnc]."|".$vvi[$ifnc]."|IGV|VAT|10|18|-|||||||-||||||".$pvi[$ifnc]."|".$vvi[$ifnc]."|0|\r\n");  
          //        fclose($handledfnc);
          $json['detalle'][] = array('codUnidadMedida' => $um[$ifnc], 'ctdUnidadItem' => number_format($cantidad[$ifnc], 2, '.', ''), 'codProducto' => $codigo[$ifnc], 'codProductoSUNAT' => "-", 'desItem' => $descripcion[$ifnc], 'mtoValorUnitario' => number_format($vui[$ifnc], 2, '.', ''), 'sumTotTributosItem' => number_format($igvi[$ifnc], 2, '.', ''), 'codTriIGV' => "1000", 'mtoIgvItem' => number_format($igvi[$ifnc], 2, '.', ''), 'mtoBaseIgvItem' => number_format($vvi[$ifnc], 2, '.', ''), 'nomTributoIgvItem' => "IGV", 'codTipTributoIgvItem' => "VAT", 'tipAfeIGV' => "10", 'porIgvItem' => "18.0", 'codTriISC' => "-", 'mtoIscItem' => "", 'mtoBaseIscItem' => "", 'nomTributoIscItem' => "", 'codTipTributoIscItem' => "", 'tipSisISC' => "", 'porIscItem' => "", 'codTriOtroItem' => "-", 'mtoTriOtroItem' => "", 'mtoBaseTriOtroItem' => "", 'nomTributoIOtroItem' => "", 'codTipTributoIOtroItem' => "", 'porTriOtroItem' => "", 'mtoPrecioVentaUnitario' => number_format($pvi[$ifnc], 2, '.', ''), 'mtoValorVentaItem' => number_format($vvi[$ifnc], 2, '.', ''), 'mtoValorReferencialUnitario' => "0");
        }
      }


      $path = $rutadata . $ruc . "-" . $codigo_nota . "-" . $numeroserienota . ".json";
      $jsonencoded = json_encode($json, JSON_UNESCAPED_UNICODE);
      $fh = fopen($path, 'w');
      fwrite($fh, $jsonencoded);
      fclose($fh);

    }

    return $sw;
  }


  //Implementar un método para listar los registros
  public function listarNC($idempresa)
  {
    $sql = "select 
        idnota, 
        nombre, 
        numeroserienota, 
        fechae as fecha, 
        descripcion, 
        serie_numero, 
        left(razon_social, 15) as razon_social , 
        numero_doc_ide, 
        tipo_moneda,  
        total_val_venta_og, 
        sum_igv, 
        importe_total, 
        estado, 
        codigo_nota,
        observacion,
        tipo_doc_mod,
        numero_ruc,
        email,
        idcomprobante,
         DetalleSunat
        from
        (
        select  
        n.idnota, 
        n.nombre, 
        n.numeroserienota, 
        date_format(n.fecha,'%d-%m-%Y') as fechae, 
        c.descripcion, 
        n.serie_numero, 
        left(n.razon_social, 15) as razon_social , 
        n.numero_doc_ide, 
        n.tipo_moneda,  
        n.total_val_venta_og, 
        n.sum_igv, 
        n.importe_total, 
        n.estado, 
        n.codigo_nota,
        n.desc_motivo as observacion,
        n.tipo_doc_mod,
        e.numero_ruc,
        p.email,
        f.idfactura as idcomprobante,
        n.DetalleSunat
        from 
        notacd n inner join catalogo9 c on n.codtiponota=c.codigo inner join empresa e on 
       e.idempresa=n.idempresa inner join factura f on n.idcomprobante=f.idfactura inner join persona p on f.idcliente=p.idpersona
    where n.codigo_nota='07' and e.idempresa='$idempresa' 
      union all 
       select  
        n.idnota, 
        n.nombre, 
        n.numeroserienota, 
        date_format(n.fecha,'%d-%m-%Y') as fechae, 
        c.descripcion, 
        n.serie_numero, 
        left(n.razon_social, 15) as razon_social , 
        n.numero_doc_ide, 
        n.tipo_moneda,  
        n.total_val_venta_og, 
        n.sum_igv, 
        n.importe_total, 
        n.estado, 
        n.codigo_nota,
        n.desc_motivo as observacion,
        n.tipo_doc_mod,
        e.numero_ruc,
        p.email,
        b.idboleta as idcomprobante,
         n.DetalleSunat  
        from 
        notacd n inner join catalogo9 c on n.codtiponota=c.codigo inner join empresa e on 
       e.idempresa=n.idempresa inner join boleta b on n.idcomprobante=b.idboleta inner join persona p on  b.idcliente=p.idpersona
    where n.codigo_nota='07' and e.idempresa='$idempresa') as tbncredito group by idnota  order by idnota desc  ";
    return ejecutarConsulta($sql);
  }


  //Implementar un método para listar los registros
  public function listarNCDia($idempresa)
  {
    $sql = "select 
        idnota, 
        nombre, 
        numeroserienota, 
        fechae as fecha, 
        descripcion, 
        serie_numero, 
        left(razon_social, 15) as razon_social , 
        numero_doc_ide, 
        tipo_moneda,  
        total_val_venta_og, 
        sum_igv, 
        importe_total, 
        estado, 
        codigo_nota,
        observacion,
        tipo_doc_mod,
        numero_ruc,
        email,
        idcomprobante,
        DetalleSunat  
        from
        (
        select  
        n.idnota, 
        n.nombre, 
        n.numeroserienota, 
        date_format(n.fecha,'%d-%m-%Y') as fechae, 
        c.descripcion, 
        n.serie_numero, 
        left(n.razon_social, 15) as razon_social , 
        n.numero_doc_ide, 
        n.tipo_moneda,  
        n.total_val_venta_og, 
        n.sum_igv, 
        n.importe_total, 
        n.estado, 
        n.codigo_nota,
        n.desc_motivo as observacion,
        n.tipo_doc_mod,
        e.numero_ruc,
        p.email,
        f.idfactura as idcomprobante,
        n.DetalleSunat   
        from 
        notacd n inner join catalogo9 c on n.codtiponota=c.codigo inner join empresa e on 
       e.idempresa=n.idempresa inner join factura f on n.idcomprobante=f.idfactura inner join persona p on f.idcliente=p.idpersona
    where n.codigo_nota='07' and e.idempresa='$idempresa'  and date(n.fecha)=current_date
      union all 
       select  
        n.idnota, 
        n.nombre, 
        n.numeroserienota, 
        date_format(n.fecha,'%d-%m-%Y') as fechae, 
        c.descripcion, 
        n.serie_numero, 
        left(n.razon_social, 15) as razon_social , 
        n.numero_doc_ide, 
        n.tipo_moneda,  
        n.total_val_venta_og, 
        n.sum_igv, 
        n.importe_total, 
        n.estado, 
        n.codigo_nota,
        n.desc_motivo as observacion,
        n.tipo_doc_mod,
        e.numero_ruc,
        p.email,
        b.idboleta as idcomprobante,
        n.DetalleSunat  
        from 
        notacd n inner join catalogo9 c on n.codtiponota=c.codigo inner join empresa e on 
       e.idempresa=n.idempresa inner join boleta b on n.idcomprobante=b.idboleta inner join persona p on  b.idcliente=p.idpersona
    where n.codigo_nota='07' and e.idempresa='$idempresa' and date(n.fecha)=current_date)
     as tbncredito group by idnota  order by idnota desc  ";
    return ejecutarConsulta($sql);
  }


  //Implementar un método para listar los registros
  public function listarND($idempresa)
  {
    $sql = "select 
        idnota, 
        nombre, 
        numeroserienota, 
        fechae as fecha, 
        descripcion, 
        serie_numero, 
        left(razon_social, 15) as razon_social , 
        numero_doc_ide, 
        tipo_moneda,  
        total_val_venta_og, 
        sum_igv, 
        importe_total, 
        estado, 
        codigo_nota,
        observacion,
        tipo_doc_mod,
        numero_ruc,
        email,
        idcomprobante,
          DetalleSunat
        from
        (
        select  
        n.idnota, 
        n.nombre, 
        n.numeroserienota, 
        date_format(n.fecha,'%d-%m-%Y') as fechae, 
        c.descripcion, 
        n.serie_numero, 
        left(n.razon_social, 15) as razon_social , 
        n.numero_doc_ide, 
        n.tipo_moneda,  
        n.total_val_venta_og, 
        n.sum_igv, 
        n.importe_total, 
        n.estado, 
        n.codigo_nota,
        n.desc_motivo as observacion,
        n.tipo_doc_mod,
        e.numero_ruc,
        p.email,
        f.idfactura as idcomprobante,
         n.DetalleSunat
        from 
        notacd n inner join catalogo9 c on n.codtiponota=c.codigo inner join empresa e on 
       e.idempresa=n.idempresa inner join factura f on n.idcomprobante=f.idfactura inner join persona p on f.idcliente=p.idpersona
    where n.codigo_nota='08' 
      union all 
       select  
        n.idnota, 
        n.nombre, 
        n.numeroserienota, 
        date_format(n.fecha,'%d-%m-%Y') as fechae, 
        c.descripcion, 
        n.serie_numero, 
        left(n.razon_social, 15) as razon_social , 
        n.numero_doc_ide, 
        n.tipo_moneda,  
        n.total_val_venta_og, 
        n.sum_igv, 
        n.importe_total, 
        n.estado, 
        n.codigo_nota,
        n.desc_motivo as observacion,
        n.tipo_doc_mod,
        e.numero_ruc,
        p.email,
        b.idboleta as idcomprobante,
         n.DetalleSunat
        from 
        notacd n inner join catalogo9 c on n.codtiponota=c.codigo inner join empresa e on 
       e.idempresa=n.idempresa inner join boleta b on n.idcomprobante=b.idboleta inner join persona p on  b.idcliente=p.idpersona
    where n.codigo_nota='08') as tbndebito group by idnota  order by idnota desc ";
    return ejecutarConsulta($sql);
  }

  public function cabecerancreditoFac($idnotac, $idempresa)
  {
    $sql = "select
         cliente, 
         domicilio, 
         numero_documento,
         femision, 
         nfactura, 
         femisionfac, 
         numerncd,
         motivo,
         observacion,
         codigo_nota,
         adicional,
         subtotal,
         igv,
         total,
         numero_ruc,
         estado,
         vendedorsitio,
         serie, 
         numeronota,
         tmoneda,
         tmonedafac

         from
         (
         select 
         p.razon_social as cliente, 
         p.domicilio_fiscal as domicilio, 
         p.numero_documento, 
         date_format(ncd.fecha, '%d-%m-%Y' ) as femision, 
         f.numeracion_08 as nfactura, 
         date_format(f.fecha_emision_01, '%d-%m-%Y') as femisionfac, 
         ncd.numeroserienota as numerncd,
         c9.descripcion as motivo,
         ncd.desc_motivo as observacion,
         ncd.codigo_nota,
         ncd.adicional,
         ncd.total_val_venta_og as subtotal,
         ncd.sum_igv as igv,
         ncd.importe_total as total,
         e.numero_ruc,
         ncd.estado,
         ncd.vendedorsitio,
         right(substring_index(ncd.numeroserienota,'-',1),4) as serie, 
         right(substring_index(ncd.numeroserienota,'-',-1),10) as numeronota,
         ncd.tipo_moneda as tmoneda,
         f.tipo_moneda_28 as tmonedafac
         from 
         notacd ncd inner join factura f on ncd.idcomprobante=f.idfactura inner join persona p on f.idcliente=p.idpersona inner join catalogo9 c9 on ncd.codtiponota=c9.codigo inner join empresa e on f.idempresa=e.idempresa
         where 
         ncd.idnota='$idnotac' and e.idempresa='$idempresa' and ncd.difComprobante='01'
         ) as tabla ";
    return ejecutarConsulta($sql);
  }

  public function cabecerancreditoBol($idnotac, $idempresa)
  {
    $sql = "
         select
         cliente, 
         domicilio,
         numero_documento, 
         femision, 
         nboleta, 
         femisionbol, 
         numerncd,
         motivo,
         observacion,
         codigo_nota,
         adicional,
         subtotal,
         igv,
         total,
         numero_ruc,
         estado,
         vendedorsitio,
         serie, 
         numeronota  
         from 
         (select 
         p.razon_social as cliente, 
         p.domicilio_fiscal as domicilio,
         p.numero_documento, 
         date_format(ncd.fecha, '%d-%m-%Y' ) as femision, 
         b.numeracion_07 as nboleta, 
         date_format(b.fecha_emision_01, '%d-%m-%Y') as femisionbol, 
         ncd.numeroserienota as numerncd,
         c9.descripcion as motivo,
         ncd.desc_motivo as observacion,
         ncd.codigo_nota,
         ncd.adicional,
         ncd.total_val_venta_og as subtotal,
         ncd.sum_igv as igv,
         ncd.importe_total as total,
         e.numero_ruc,
         ncd.estado,
         ncd.vendedorsitio,
         right(substring_index(ncd.numeroserienota,'-',1),4) as serie, 
         right(substring_index(ncd.numeroserienota,'-',-1),10) as numeronota  
         from 
         notacd ncd inner join boleta b on ncd.idcomprobante=b.idboleta inner join persona p on b.idcliente=p.idpersona inner join catalogo9 c9 on ncd.codtiponota=c9.codigo inner join empresa e on b.idempresa=e.idempresa and ncd.difComprobante='03'
         where 
         ncd.idnota='$idnotac' and e.idempresa='$idempresa'
         )
         as tabla";
    return ejecutarConsulta($sql);
  }

  public function detalleNotacredito($idnotac, $idempresa, $serienumero)
  {
    $sql = "select  
        articulo, 
        codigo, 
        cantidad, 
        valor_unitario, 
        subtotal2, 
        precio_venta, 
        valor_venta,
        subtotal,
        igv,
        total,
        observacion,
        descripitem
        
        from 
        (
        select 
        a.nombre as articulo, 
        a.codigo, 
        format(dfnc.cantidad,2) as cantidad, 
        dfnc.valor_unitario, 
        format((dfnc.cantidad * dfnc.valor_unitario),2) as subtotal2, 
        dfnc.precio_venta, 
        dfnc.valor_venta,
        ncd.total_val_venta_og as subtotal,
        ncd.sum_igv as igv,
        ncd.importe_total as total,
        ncd.desc_motivo as observacion,
        dfnc.descripitem
        
        from
        detalle_notacd_art dfnc inner join articulo a on dfnc.idarticulo=a.idarticulo inner join  notacd ncd on dfnc.idnotacd=ncd.idnota  inner join factura f on ncd.idcomprobante=f.idfactura inner join empresa e on f.idempresa=e.idempresa 
         where
          dfnc.idnotacd='$idnotac'  and f.numeracion_08='$serienumero' and e.idempresa='$idempresa' and ncd.difComprobante in('01')
       )
        as tabla";
    return ejecutarConsulta($sql);
  }

  public function detalleNotacreditoBol($idnotac, $idempresa, $serienumero)
  {
    $sql = "select  
        articulo, 
        codigo, 
        cantidad, 
        valor_unitario, 
        subtotal2, 
        precio_venta, 
        valor_venta,
        subtotal,
        igv,
        total,
        observacion
        from 
        (
        select 
        a.nombre as articulo, 
        a.codigo, 
        format(dfnc.cantidad,2) as cantidad, 
        dfnc.valor_unitario, 
        format((dfnc.cantidad * dfnc.valor_unitario),2) as subtotal2, 
        dfnc.precio_venta, 
        dfnc.valor_venta,
        ncd.total_val_venta_og as subtotal,
        ncd.sum_igv as igv,
        ncd.importe_total as total,
        ncd.desc_motivo as observacion 
        from
        detalle_notacd_art dfnc inner join articulo a on dfnc.idarticulo=a.idarticulo inner join  notacd ncd on dfnc.idnotacd=ncd.idnota  inner join boleta b on ncd.idcomprobante=b.idboleta inner join empresa e
        on b.idempresa=e.idempresa  where dfnc.idnotacd='$idnotac' and 
        ncd.difComprobante in('03') and e.idempresa='$idempresa' and b.numeracion_07='$serienumero'
        union all 
        select 
        a.descripcion as articulo, 
        a.codigo, 
        format(dfnc.cantidad,2) as cantidad, 
        dfnc.valor_unitario, 
        format((dfnc.cantidad * dfnc.valor_unitario),2) as subtotal2, 
        dfnc.precio_venta, 
        dfnc.valor_venta,
        ncd.total_val_venta_og as subtotal,
        ncd.sum_igv as igv,
        ncd.importe_total as total,
        ncd.desc_motivo as observacion from
        detalle_notacd_art dfnc inner join servicios_inmuebles a on dfnc.idarticulo=a.id inner join notacd ncd on dfnc.idnotacd=ncd.idnota inner join boletaservicio bs on ncd.idcomprobante=bs.idboleta inner join empresa e
        on bs.idempresa=e.idempresa  where dfnc.idnotacd='$idnotac' and 
        ncd.difComprobante in('05') and e.idempresa ='$idempresa'  and bs.numeracion_07='$serienumero' )
        as tabla";
    return ejecutarConsulta($sql);
  }




  public function cabecerandebitoFac($idnotadfac, $idempresa)
  {
    $sql = "select
         cliente, 
         domicilio,
         numero_documento, 
         femision, 
         nfactura, 
         femisionfac, 
         numerncd,
         motivo,
         observacion,
         codigo_nota,
         adicional,
         subtotal,
         igv,
         total,
         numero_ruc,
         estado,
         vendedorsitio,
         serie,
         numeronota 
         from 
         (select
         p.razon_social as cliente, 
         p.domicilio_fiscal as domicilio,
         p.numero_documento, 
         date_format(ncd.fecha, '%d-%m-%Y' ) as femision, 
         f.numeracion_08 as nfactura, 
         date_format(f.fecha_emision_01, '%d-%m-%Y') as femisionfac, 
         ncd.numeroserienota as numerncd,
         c10.descripcion as motivo,
         ncd.desc_motivo as observacion,
         ncd.codigo_nota,
         ncd.adicional,
         ncd.total_val_venta_og as subtotal,
         ncd.sum_igv as igv,
         ncd.importe_total as total,
         e.numero_ruc,
         ncd.estado,
         ncd.vendedorsitio,
         right(substring_index(ncd.numeroserienota,'-',1),4) as serie, 
         right(substring_index(ncd.numeroserienota,'-',-1),10) as numeronota
         from
         notacd ncd inner join factura f on ncd.idcomprobante=f.idfactura inner join persona p on f.idcliente=p.idpersona inner join catalogo10 c10 on ncd.codtiponota=c10.codigo inner join empresa e on f.idempresa=e.idempresa and ncd.difComprobante='01'
         where 
         ncd.idnota='$idnotadfac' and e.idempresa='$idempresa'
         union all
         select
         p.razon_social as cliente, 
         p.domicilio_fiscal as domicilio, 
         p.numero_documento,
         date_format(ncd.fecha, '%d-%m-%Y' ) as femision, 
         f.numeracion_08 as nfactura, 
         date_format(f.fecha_emision_01, '%d-%m-%Y') as femisionfac, 
         ncd.numeroserienota as numerncd,
         c10.descripcion as motivo,
         ncd.desc_motivo as observacion,
         ncd.codigo_nota,
         ncd.adicional,
         ncd.total_val_venta_og as subtotal,
         ncd.sum_igv as igv,
         ncd.importe_total as total,
         e.numero_ruc,
         ncd.estado,
         ncd.vendedorsitio,
         right(substring_index(ncd.numeroserienota,'-',1),4) as serie, 
         right(substring_index(ncd.numeroserienota,'-',-1),10) as numeronota
         from
         notacd ncd inner join facturaservicio f on ncd.idcomprobante=f.idfactura inner join persona p on f.idcliente=p.idpersona inner join catalogo10 c10 on ncd.codtiponota=c10.codigo inner join empresa e on f.idempresa=e.idempresa
         where 
         ncd.idnota='$idnotadfac' and e.idempresa='$idempresa' and ncd.difComprobante='04')
         as tabla";
    return ejecutarConsulta($sql);
  }

  public function cabecerandebitoBol($idnotadbol, $idempresa)
  {
    $sql = "select
           cliente, 
           domicilio,
           numero_documento, 
           femision, 
           nboleta, 
           femisionbol, 
           numerncd,
           motivo,
           codigo_nota,
           subtotal,
           igv,
           total,
           observacion,
           adicional,
           subtotalboleta,
           igvboleta,
           totalboleta,
           numero_ruc,
           estado,
           vendedorsitio,
           serie,
           numeronota   
           from
           (
           select
           p.razon_social as cliente, 
           p.domicilio_fiscal as domicilio,
           p.numero_documento, 
           date_format(ncd.fecha, '%d-%m-%Y' ) as femision, 
           b.numeracion_07 as nboleta, 
           date_format(b.fecha_emision_01, '%d-%m-%Y') as femisionbol, 
           ncd.numeroserienota as numerncd,
           c10.descripcion as motivo,
           ncd.codigo_nota,
           ncd.total_val_venta_og  as subtotal,
           ncd.sum_igv as igv,
           ncd.importe_total as total,
           ncd.desc_motivo as observacion,
           ncd.adicional,
           b.monto_15_2 as subtotalboleta,
           b.sumatoria_igv_18_1 as igvboleta,
           b.importe_total_23 as totalboleta,
           e.numero_ruc,
           ncd.estado,
           ncd.vendedorsitio,
           right(substring_index(ncd.numeroserienota,'-',1),4) as serie, 
         right(substring_index(ncd.numeroserienota,'-',-1),10) as numeronota
           from
           notacd ncd inner join boleta b on ncd.idcomprobante=b.idboleta inner join persona p on b.idcliente=p.idpersona inner join catalogo10 c10 on ncd.codtiponota=c10.codigo inner join empresa e on b.idempresa=e.idempresa and ncd.difComprobante='03'
           where 
           ncd.idnota='$idnotadbol' and e.idempresa='$idempresa'
           union all 
           select
           p.razon_social as cliente, 
           p.domicilio_fiscal as domicilio,
           p.numero_documento, 
           date_format(ncd.fecha, '%d-%m-%Y' ) as femision, 
           b.numeracion_07 as nboleta, 
           date_format(b.fecha_emision_01, '%d-%m-%Y') as femisionbol, 
           ncd.numeroserienota as numerncd,
           c10.descripcion as motivo,
           ncd.codigo_nota,
           ncd.total_val_venta_og  as subtotal,
           ncd.sum_igv as igv,
           ncd.importe_total as total,
           ncd.desc_motivo as observacion,
           ncd.adicional,
           b.monto_15_2 as subtotalboleta,
           b.sumatoria_igv_18_1 as igvboleta,
           b.importe_total_23 as totalboleta,
           e.numero_ruc,
           ncd.estado,
           ncd.vendedorsitio,
           right(substring_index(ncd.numeroserienota,'-',1),4) as serie, 
         right(substring_index(ncd.numeroserienota,'-',-1),10) as numeronota
           from 
           notacd ncd inner join boletaservicio b on ncd.idcomprobante=b.idboleta inner join persona p on b.idcliente=p.idpersona inner join catalogo10 c10 on ncd.codtiponota=c10.codigo inner join empresa e on b.idempresa=e.idempresa
           where 
           ncd.idnota='$idnotadbol' and e.idempresa='$idempresa' and ncd.difComprobante='05')
           as tabla";
    return ejecutarConsulta($sql);
  }

  public function detalleNotadebito($idnotac)
  {
    $sql = "select  
        a.nombre as articulo, 
        a.codigo, 
        format(dfnc.cantidad,2) as cantidad, 
        dfnc.valor_unitario, 
        format((dfnc.cantidad * dfnc.valor_unitario),2) as subtotal, 
        dfnc.precio_venta, 
        dfnc.valor_venta,
        ncd.total_val_venta_og as subtotal,
        ncd.sum_igv as igv,
        ncd.importe_total as total,
        ncd.desc_motivo as observacion
        from 
        detalle_notacd_art dfnc inner join articulo a on dfnc.idarticulo=a.idarticulo inner join  notacd ncd on dfnc.idnotacd=ncd.idnota where dfnc.idnotacd='$idnotac'";
    return ejecutarConsulta($sql);
  }

  public function ActualizarEstado($idnota, $st, $idcompro, $tipocompfb, $numerofacoriginal)
  {

    if ($tipocompfb == '01') {
      $sw = true;
      $sqlestado = "update notacd set estado='$st' where idnota='$idnota'";
      $actComp = "update factura set estado='0' where idfactura='$idcompro' and numeracion_08='$numerofacoriginal' ";
      $actCompFacSer = "update facturaservicio set estado='0' where idfactura='$idcompro' and numeracion_08='$numerofacoriginal'";
      ejecutarConsulta($sqlestado) or $sw = false;
      ejecutarConsulta($actComp) or $sw = false;
      ejecutarConsulta($actCompFacSer) or $sw = false;
    } else {
      $sw = true;
      $sqlestado = "update notacd set estado='$st' where idnota='$idnota'";
      $actComp = "update boleta set estado='0' where idboleta='$idcompro'  and numeracion_07='$numerofacoriginal'";
      $actCompBolSer = "update boletaservicio set estado='0' where idboleta='$idcompro' and numeracion_07='$numerofacoriginal'";
      ejecutarConsulta($sqlestado) or $sw = false;
      ejecutarConsulta($actComp) or $sw = false;
      ejecutarConsulta($actCompBolSer) or $sw = false;
    }




    return $sw;
  }


  public function ActualizarEstadoFirmado($idnota, $st)
  {

    $sw = true;
    $sqlestado = "update notacd set estado='$st' where idnota='$idnota'";
    ejecutarConsulta($sqlestado) or $sw = false;

    return $sw;
  }





  public function enviarcorreo($idnota)
  {

    //Inclusion de datos de configuracion del correo para envio.
    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->correo();
    $correo = $datos->fetch_object();

    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2();
    $Prutas = $Rrutas->fetch_object();
    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA

    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
      exit();
    }

    $sqlsendmail = "select 
        f.idfactura, 
        p.email,  
        p.nombres, 
        p.apellidos, 
        p.nombre_comercial, 
        e.numero_ruc,
        ncd.codigo_nota,
        ncd.numeroserienota
        from 
        factura f inner join persona p on 
        f.idcliente=p.idpersona inner join empresa e on 
        f.idempresa=e.idempresa inner join notacd ncd on f.idfactura=ncd.idcomprobante
        where 
        ncd.idnota='$idnota' ";

    $result = mysqli_query($connect, $sqlsendmail);

    $con = 0;

    while ($row = mysqli_fetch_assoc($result)) {
      for ($i = 0; $i <= count($result); $i++) {
        $correocliente = $row["email"];
      }

      //Agregar=====================================================
      // Ruta del directorio donde están los archivos
      $path = $rutafirma;
      $pathNotas = '../notasPDF/';
      // Arreglo con todos los nombres de los archivos
      $files = array_diff(scandir($path), array('.', '..'));
      $filesNotas = array_diff(scandir($pathNotas), array('.', '..'));
      //=============================================================
      $nota = $row['numero_ruc'] . "-" . $row['codigo_nota'] . "-" . $row['numeroserienota'];

      //Validar si existe el archivo firmado
      foreach ($files as $file) {
        // Divides en dos el nombre de tu archivo utilizando el . 

        $dataSt = explode(".", $file);
        // Nombre del archivo
        $fileName = $dataSt[0];
        $st = "1";
        // Extensión del archivo 
        $fileExtension = $dataSt[1];

        if ($nota == $fileName) {
          $archivoNota = $fileName;

          // Realizamos un break para que el ciclo se interrumpa
          break;
        }
      }
      //=========================================================================

      //Validar si existe el archivo en PDF
      foreach ($filesNotas as $fileNota) {
        // Divides en dos el nombre de tu archivo utilizando el . 
        $dataStF = explode(".", $fileNota);
        // Nombre del archivo
        $fileNameF = $dataStF[0];
        // Extensión del archivo 
        $fileExtensionF = $dataStF[1];

        if ($row['numeroserienota'] == $fileNameF) {
          $archivoNotaPDF = $fileNameF;
          // Realizamos un break para que el ciclo se interrumpa
          break;
        }
      }

      $url = $rutafirma . $archivoNota . '.xml';
      $fichero = file_get_contents($url);

      $urlNot = '../notasPDF/' . $archivoNotaPDF . '.pdf';
      $ficheroNot = file_get_contents($urlNot);

      // FUNCION PARA ENVIO DE CORREO CON LA FACTURA AL CLIENTE .
      require '../correo/PHPMailer/class.phpmailer.php';
      require '../correo/PHPMailer/class.smtp.php';
      $mail = new PHPMailer;
      $mail->isSMTP(); // Establecer el correo electrónico para utilizar SMTP
      $mail->Host = $correo->host; // Especificar el servidor de correo a utilizar 
      $mail->SMTPAuth = true; // Habilitar la autenticacion con SMTP
      $mail->Username = $correo->username; // Correo electronico saliente ejemplo: tucorreo@gmail.com
      //$clavehash=hash("SHA256",$correo->password);
      $mail->Password = $correo->password; // Tu contraseña de gmail
      $mail->SMTPSecure = $correo->smtpsecure; // Habilitar encriptacion, `ssl` es aceptada
      $mail->Port = $correo->port; // Puerto TCP  para conectarse 
      $mail->setFrom($correo->username, utf8_decode($correo->nombre)); //Introduzca la dirección de la que debe aparecer el correo electrónico. Puede utilizar cualquier dirección que el servidor SMTP acepte como válida. El segundo parámetro opcional para esta función es el nombre que se mostrará como el remitente en lugar de la dirección de correo electrónico en sí.
      $mail->addReplyTo($correo->username, utf8_decode($correo->nombre)); //Introduzca la dirección de la que debe responder. El segundo parámetro opcional para esta función es el nombre que se mostrará para responder
      $mail->addStringAttachment($fichero, $archivoNota . '.xml');
      $mail->addStringAttachment($ficheroNot, $archivoNotaPDF . '.pdf');
      $mail->addAddress($correocliente); // Agregar quien recibe el e-mail enviado
      //$mail->addAttachment();
      $message = file_get_contents('../correo/email_template.html');
      $message = str_replace('{{first_name}}', utf8_decode($correo->nombre), utf8_decode($correo->mensaje));
      $message = str_replace('{{message}}', utf8_decode($correo->mensaje), utf8_decode($correo->mensaje));
      $message = str_replace('{{customer_email}}', $correo->username, utf8_decode($correo->mensaje));
      $mail->isHTML(true); // Establecer el formato de correo electrónico en HTML

      $mail->Subject = $correo->username;
      $mail->msgHTML($message);
      //$mail->send();

      if (!$mail->send()) {
        //echo '<p style="color:red">No se pudo enviar el mensaje..';
        echo $mail->ErrorInfo;
        //echo "</p>";
      } else {
        echo 'Tu mensaje ha sido enviado';
      }
      // FUNCION PARA ENVIO DE CORREO CON LA FACTURA AL CLIENTE .
      $i = $i + 1;
      $con = $con + 1;
    }
  }


  public function generarxml($idnota, $idempresa)
  {
    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
      exit();
    }

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp($idempresa);
    $datose = $datos->fetch_object();

    $nombrecomercial = $datose->nombre_comercial;
    $codigoubigueo = $datose->codubigueo;
    $codigoestab = $datose->ubigueo;

    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutadata = $Prutas->rutadata; // ruta de la carpeta DATA
    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutadatalt = $Prutas->rutadatalt; // ruta de la carpeta DATAALTERNA
    $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta DATAALTERNA

    $tipodocmod = '';
    $codigonota = '';
    $query = '';
    $queryinicial = "select tipo_doc_mod, codigo_nota from notacd where idnota='$idnota' ";
    $resulti = mysqli_query($connect, $queryinicial);


    while ($row = mysqli_fetch_assoc($resulti)) {

      $tipodocmod = $row["tipo_doc_mod"]; //tipo de documento
      $codigonota = $row["codigo_nota"]; //tipo de documento


      if ($tipodocmod == '01') {

        $query = "select
     date_format(n.fecha, '%Y-%m-%d') as fecha, 
     right(substring_index(n.numeroserienota,'-',1),1) as serie,
     date_format(n.fecha, '%H:%i:%s') as hora,
     p.tipo_documento as  tipodocuCliente, 
     p.numero_documento, 
     p.razon_social, 
     n.tipo_moneda as moneda, 
     n.total_val_venta_og as subtotal, 
     n.sum_igv as igv, 
     n.importe_total as total, 
     n.codigo_nota as tipocomp, 
     n.numeroserienota as numerodoc, 
     n.estado, 
     f.codigo_tributo_22_3 as codigotrib,
     f.nombre_tributo_22_4  as nombretrib,
     f.codigo_internacional_22_5 as codigointtrib,
     f.total_operaciones_gravadas_codigo_18_1 as opera,
     f.numeracion_08 as refeiddoc,
     n.codtiponota as responsecode,
     n.motivonota as descmotivo,
     n.codigo_nota,
     n.tipo_doc_mod
     from 
     notacd n inner join factura f on n.idcomprobante=f.idfactura inner join persona p on f.idcliente=p.idpersona inner join empresa e on f.idempresa=e.idempresa where idnota='$idnota' and n.estado in('1','4') order by numerodoc";


        $querydetfb = "select
       f.tipo_documento_07 as tipocomp, 
       f.numeracion_08 as numerodoc,  
       dn.cantidad as cantidad, 
       a.codigo, 
       a.nombre as descripcion, 
       um.abre as um,
       replace(format(dn.valor_unitario,5),',','') as vui, 
       dn.igv as igvi, 
       dn.precio_venta as pvi, 
       dn.valor_venta as vvi,
       (dn.valor_venta * 0.18) as sutribitem,
       dn.nro_orden as nro_orden,

       dn.aigv,
       dn.codtrib,
       dn.nomtrib,
       dn.coditrib,
       a.codigosunat,
       f.tipo_moneda_28 as moneda,
       n.tipo_doc_mod

       from
       notacd n inner join detalle_notacd_art dn on n.idnota=dn.idnotacd  inner join factura f on n.idcomprobante=f.idfactura  inner join articulo a on dn.idarticulo=a.idarticulo inner join umedida um on a.unidad_medida=um.idunidad
          where n.idnota='$idnota' and n.estado in ('1','4') order by n.fecha";




      } else {

        $query = "select
     date_format(n.fecha, '%Y-%m-%d') as fecha, 
     right(substring_index(n.numeroserienota,'-',1),1) as serie,
     date_format(n.fecha, '%H:%i:%s') as hora,
     p.tipo_documento as  tipodocuCliente, 
     p.numero_documento, 
     p.razon_social, 
     b.tipo_moneda_24 as moneda, 
     n.total_val_venta_og as subtotal, 
     n.sum_igv as igv, 
     n.importe_total as total, 
     n.codigo_nota as tipocomp, 
     n.numeroserienota as numerodoc, 
     n.estado, 
     b.codigo_tributo_18_3 as codigotrib,
     b.nombre_tributo_18_4  as nombretrib,
     b.codigo_internacional_18_5 as codigointtrib,
     b.monto_15_2 as opera,
     b.numeracion_07 as refeiddoc,
     n.codtiponota as responsecode,
     n.motivonota as descmotivo,
     n.codigo_nota,
     n.tipo_doc_mod
     from 
     notacd n inner join boleta b on n.idcomprobante=b.idboleta inner join persona p on b.idcliente=p.idpersona inner join empresa e on b.idempresa=e.idempresa where idnota='$idnota' and n.estado in('1','4') order by numerodoc";


        $querydetfb = "select
       b.tipo_documento_06 as tipocomp, 
       b.numeracion_07 as numerodoc,  
       dn.cantidad as cantidad, 
       a.codigo, 
       a.nombre as descripcion, 
       um.abre as um,
       replace(format(dn.valor_unitario,5),',','') as vui, 
       dn.igv as igvi, 
       dn.precio_venta as pvi, 
       dn.valor_venta as vvi,
       (dn.valor_venta * 0.18) as sutribitem,
       dn.nro_orden as nro_orden,

       dn.aigv,
       dn.codtrib,
       dn.nomtrib,
       dn.coditrib,
       a.codigosunat,
       b.tipo_moneda_24 as moneda,
       n.tipo_doc_mod

       from
       notacd n inner join detalle_notacd_art dn on n.idnota=dn.idnotacd  inner join boleta b on n.idcomprobante=b.idboleta  inner join articulo a on dn.idarticulo=a.idarticulo  inner join umedida um on a.unidad_medida=um.idunidad
          where n.idnota='$idnota' and n.estado in ('1','4') order by n.fecha";


      } //Find e if

    } //Fin de while

    $resultfb = "";
    $result = mysqli_query($connect, $query);
    $resultfb = mysqli_query($connect, $querydetfb);



    //Parametros de salida
    $fecha = array();
    $hora = array();
    $serie = array();
    $tipodocu = array();
    $numdocu = array();
    $rasoc = array();
    $moneda = array();
    $codigotrib = array();
    $nombretrib = array();
    $codigointtrib = array();
    $subtotal = array();
    $igv = array();
    $total = array();
    $tdescu = array();
    $opera = array();
    $ubigueo = array();

    $refeiddoc = array();
    $responsecode = array();
    $descmotivo = array();
    $tipodocuorigen = array();



    $con = 0; //COntador de variable


    while ($row = mysqli_fetch_assoc($result)) {
      for ($i = 0; $i <= count($result); $i++) {
        $tipodocmod = $row["tipo_doc_mod"]; //tipo de documento
        //
        $fecha[$i] = $row["fecha"]; //Fecha emision
        $serie[$i] = $row["serie"];
        $tipodocu[$i] = $row["tipodocuCliente"]; //Tipo de documento de cliente ruc o dni

        $numdocu[$i] = $row["numero_documento"]; //NUmero de docuemnto de cliente
        $rasoc[$i] = $row["razon_social"]; //Nombre de cliente
        $moneda[$i] = $row["moneda"];
        $subtotal[$i] = $row["subtotal"];
        $igv[$i] = $row["igv"];
        $total[$i] = $row["total"];
        // $tdescu[$i]=$row["tdescuento"];
        $hora[$i] = $row["hora"];
        $tipocomp = $row["tipocomp"];
        $numerodoc = $row["numerodoc"];
        $ruc = $datose->numero_ruc;
        $ubigueo = "0000";
        $opera[$i] = $row["opera"];

        $codigotrib[$i] = $row["codigotrib"]; //codigo de tributo de la tabla catalo 5
        $nombretrib[$i] = $row["nombretrib"]; //NOmbre de tributo de la tabla catalo 5
        $codigointtrib[$i] = $row["codigointtrib"]; //Codigo internacional de la tabla catalo 5

        $refeiddoc[$i] = $row["refeiddoc"]; //Numero de documento de referencia
        $responsecode[$i] = $row["responsecode"]; //Codigo de motivo de nota 
        $descmotivo[$i] = $row["descmotivo"]; //Descripcion de motivo

        //  if ($tipodocmod=='01') {
        //   $resultfb = mysqli_query($connect, $querydetfac); 
        // }else{
        //   $resultfb = mysqli_query($connect, $querydetbol);             
        // }

        require_once "Letras.php";
        $V = new EnLetras();
        $con_letra = strtoupper($V->ValorEnLetras($total[$i], "NUEVOS SOLES"));


        //======================================== FORMATO XML ========================================================
        $domiciliofiscal = $datose->domicilio_fiscal;
        //Primera parte
        $facturaXML = '<?xml version="1.0" encoding="utf-8"?>
          <CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2"
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
                <cbc:ID>' . $numerodoc . '</cbc:ID>
                <cbc:IssueDate>' . $fecha[$i] . '</cbc:IssueDate>
                <cbc:IssueTime>' . $hora[$i] . '</cbc:IssueTime>
                <cbc:DocumentCurrencyCode>' . $moneda[$i] . '</cbc:DocumentCurrencyCode>

                <cac:DiscrepancyResponse>
                    <cbc:ReferenceID>' . $refeiddoc[$i] . '</cbc:ReferenceID>
                    <cbc:ResponseCode>' . $responsecode[$i] . '</cbc:ResponseCode>
                    <cbc:Description>' . $descmotivo[$i] . '</cbc:Description>
                </cac:DiscrepancyResponse>

                 <cac:BillingReference>
                    <cac:InvoiceDocumentReference>
                        <cbc:ID>' . $refeiddoc[$i] . '</cbc:ID>
                        <cbc:DocumentTypeCode>' . $tipodocmod . '</cbc:DocumentTypeCode>
                    </cac:InvoiceDocumentReference>
                </cac:BillingReference>

                <cac:Signature>
                    <cbc:ID>' . $ruc . '</cbc:ID>
                    <cbc:Note>SENCON</cbc:Note>
                    <cac:SignatoryParty>
                        <cac:PartyIdentification>
                            <cbc:ID>' . $ruc . '</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyName>
                            <cbc:Name><![CDATA[' . $nombrecomercial . ']]></cbc:Name>
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
                            <cbc:ID schemeID="6">' . $ruc . '</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyName>
                            <cbc:Name><![CDATA[' . $nombrecomercial . ']]></cbc:Name>
                        </cac:PartyName>
                        <cac:PartyLegalEntity>
                            <cbc:RegistrationName><![CDATA[' . $nombrecomercial . ']]></cbc:RegistrationName>
                            <cac:RegistrationAddress>
                                <cbc:ID>' . $codigoubigueo . '</cbc:ID>
                                <cbc:AddressTypeCode>' . $codigoestab . '</cbc:AddressTypeCode>
                            </cac:RegistrationAddress>
                        </cac:PartyLegalEntity>
                    </cac:Party>
                </cac:AccountingSupplierParty>

                <cac:AccountingCustomerParty>
                    <cac:Party>
                        <cac:PartyIdentification>
                            <cbc:ID schemeID="' . $tipodocu[$i] . '">' . $numdocu[$i] . '</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyLegalEntity>
                            <cbc:RegistrationName><![CDATA[' . $rasoc[$i] . ']]></cbc:RegistrationName>
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


        $facturaXML .= '<cac:TaxTotal>
                    <cbc:TaxAmount currencyID="' . $moneda[$i] . '">' . $igv[$i] . '</cbc:TaxAmount>
                        <cac:TaxSubtotal>
                        <cbc:TaxableAmount currencyID="' . $moneda[$i] . '">' . $subtotal[$i] . '</cbc:TaxableAmount>
                        <cbc:TaxAmount currencyID="' . $moneda[$i] . '">' . $igv[$i] . '</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cac:TaxScheme>
                                <cbc:ID>' . $codigotrib[$i] . '</cbc:ID>
                                <cbc:Name>' . $nombretrib[$i] . '</cbc:Name>
                                <cbc:TaxTypeCode>' . $codigointtrib[$i] . '</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>
                              </cac:TaxTotal>

                <cac:LegalMonetaryTotal>
                    <cbc:LineExtensionAmount currencyID="' . $moneda[$i] . '">' . $subtotal[$i] . '</cbc:LineExtensionAmount>
                    <cbc:PayableAmount currencyID="' . $moneda[$i] . '">' . $total[$i] . '</cbc:PayableAmount>
                </cac:LegalMonetaryTotal>';


      } //For cabecera
      $i = $i + 1;
      $con = $con + 1;
    } //While cabecera

    $codigo = array();
    $cantidad = array();
    $descripcion = array();
    $um = array();
    $vui = array();
    $igvi = array();
    $pvi = array();
    $vvi = array();
    $sutribitem = array();
    $aigv = array();
    $codtrib = array();
    $nomtrib = array();
    $coditrib = array();
    $codigosunat = array();
    $numorden = array();
    $tmon = array();

    while ($row = mysqli_fetch_assoc($resultfb)) {
      for ($i = 0; $i < count($resultfb); $i++) {
        $codigo[$i] = $row["codigo"];
        $cantidad[$i] = $row["cantidad"];
        $descripcion[$i] = $row["descripcion"];
        $vui[$i] = $row["vui"];
        $sutribitem[$i] = $row["sutribitem"];
        $igvi[$i] = $row["igvi"];
        $pvi[$i] = $row["pvi"];
        $vvi[$i] = $row["vvi"];
        $um[$i] = $row["um"];
        $tipocompf = $row["tipocomp"];
        $numerodocf = $row["numerodoc"];
        $ruc = $datose->numero_ruc;
        $numorden[$i] = $row["nro_orden"];

        $codtrib[$i] = $row["codtrib"];
        $nomtrib[$i] = $row["nomtrib"];
        $coditrib[$i] = $row["coditrib"];
        $codigosunat[$i] = $row["codigosunat"];


        $tmon[$i] = $row["moneda"];

        /* Número de orden del Ítem
           Cantidad y Unidad de medida por ítem
           Valor de venta del ítem  */

        $facturaXML .= '<cac:CreditNoteLine>
                    <cbc:ID>' . $numorden[$i] . '</cbc:ID>
                    <cbc:CreditedQuantity unitCode="' . $um[$i] . '">' . number_format($cantidad[$i], 2, '.', '') . '</cbc:CreditedQuantity>
                    <cbc:LineExtensionAmount currencyID="' . $tmon[$i] . '">' . number_format($vvi[$i], 2, '.', '') . '</cbc:LineExtensionAmount>
                    
                    <cac:PricingReference>
                        <cac:AlternativeConditionPrice>
                            <cbc:PriceAmount currencyID="' . $tmon[$i] . '">' . number_format($pvi[$i], 2, '.', '') . '</cbc:PriceAmount>
                            <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
                        </cac:AlternativeConditionPrice>
                    </cac:PricingReference>

                    <cac:TaxTotal>
                        <cbc:TaxAmount currencyID="' . $tmon[$i] . '">' . number_format($sutribitem[$i], 2, '.', '') . '</cbc:TaxAmount>                        
                        <cac:TaxSubtotal>
                            <cbc:TaxableAmount currencyID="' . $tmon[$i] . '">' . number_format($vvi[$i], 2, '.', '') . '</cbc:TaxableAmount>
                            <cbc:TaxAmount currencyID="' . $tmon[$i] . '">' . number_format($sutribitem[$i], 2, '.', '') . '</cbc:TaxAmount>
                            <cac:TaxCategory>
                                <cbc:Percent>18.00</cbc:Percent>
                                <cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode>
                                <cac:TaxScheme>
                                    <cbc:ID>1000</cbc:ID>
                                    <cbc:Name>IGV</cbc:Name>
                                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                                </cac:TaxScheme>
                            </cac:TaxCategory>
                        </cac:TaxSubtotal>
                    </cac:TaxTotal>

                    <cac:Item>
                        <cbc:Description><![CDATA[' . $descripcion[$i] . ']]></cbc:Description>
                    </cac:Item>

                    <cac:Price>
                        <cbc:PriceAmount currencyID="' . $tmon[$i] . '">' . number_format($vui[$i], 5, '.', '') . '</cbc:PriceAmount>
                    </cac:Price>
                </cac:CreditNoteLine>';

      } //Fin for
    } //Find e while 
    $facturaXML .= '</CreditNote>';
    //FIN DE CABECERA ===================================================================



    // Nos aseguramos de que la cadena que contiene el XML esté en UTF-8
    $facturaXML = mb_convert_encoding($facturaXML, "UTF-8");
    // Grabamos el XML en el servidor como un fichero plano, para
    // poder ser leido por otra aplicación.
    $gestor = fopen($rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml", 'w');
    fwrite($gestor, $facturaXML);
    fclose($gestor);

    $cabextxml = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";
    $cabxml = $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";
    $nomxml = $ruc . "-" . $tipocomp . "-" . $numerodoc;
    $nomxmlruta = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc;

    require_once("../greemter/Greenter.php");
    $invo = new Greenter();
    $out = $invo->getDatFac($cabextxml);

    $filenaz = $nomxml . ".zip";
    $zip = new ZipArchive();
    if ($zip->open($filenaz, ZIPARCHIVE::CREATE) === true) {
      //$zip->addEmptyDir("dummy");
      $zip->addFile($cabextxml, $cabxml);
      $zip->close();

      //if(!file_exists($rutaz)){mkdir($rutaz);}
      $imagen = file_get_contents($filenaz);
      $imageData = base64_encode($imagen);
      rename($cabextxml, $rutafirma . $cabxml);
      rename($filenaz, $rutaenvio . $filenaz);
    } else {
      $out = "Error al comprimir archivo";
    }

    $data[0] = "";
    $sxe = new SimpleXMLElement($cabextxml, null, true);
    $urn = $sxe->getNamespaces(true);
    $sxe->registerXPathNamespace('ds', $urn['ds']);
    $data = $sxe->xpath('//ds:DigestValue');

    $rpta = array('cabextxml' => $cabextxml, 'cabxml' => $cabxml, 'rutafirma' => $cabextxml);
    $sqlDetalle = "update notacd set DetalleSunat='XML firmado' , hashc='$data[0]', estado='4' where idnota='$idnota'";
    //$sqlDetalle="update notacd set DetalleSunat='XML firmado' where idnota='$idnota'";
    ejecutarConsulta($sqlDetalle);

    return $rpta;

  } //Fin de funcion





  public function enviarxmlSUNAT($idnota, $idempresa)
  {

    require_once "../modelos/Boleta.php";
    $boleta = new Boleta();
    $datos = $boleta->correo();
    $correo = $datos->fetch_object();

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
    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta FIRMA
    $rutarpta = $Prutas->rutarpta; // ruta de la carpeta FIRMA
    $rutaunzip = $Prutas->unziprpta; // ruta de la carpeta rpta xml

    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
      exit();
    }


    $tipodocmod = '';
    $query = '';
    $queryinicial = "select tipo_doc_mod from notacd where idnota='$idnota' ";
    $resulti = mysqli_query($connect, $queryinicial);


    while ($row = mysqli_fetch_assoc($resulti)) {

      $tipodocmod = $row["tipo_doc_mod"]; //tipo de documento
      if ($tipodocmod == '01') { //Si es factura

        $query = "select 
        n.idnota, 
        p.email,  
        p.nombres, 
        p.apellidos, 
        p.nombre_comercial, 
        e.numero_ruc,
        n.codigo_nota,
        n.numeroserienota 
        from 
        notacd n inner join factura f on n.idcomprobante=f.idfactura inner join  persona p on 
        f.idcliente=p.idpersona inner join empresa e on f.idempresa=e.idempresa 
        where 
        n.idnota='$idnota'";

      } else { // Si es boleta

        $query = "select 
        n.idnota, 
        p.email,  
        p.nombres, 
        p.apellidos, 
        p.nombre_comercial, 
        e.numero_ruc,
        n.codigo_nota,
        n.numeroserienota 
        from 
        notacd n inner join boleta b on n.idcomprobante=b.idboleta inner join  persona p on 
        b.idcliente=p.idpersona inner join empresa e on b.idempresa=e.idempresa 
        where 
        n.idnota='$idnota'";
      }
    }


    $result = mysqli_query($connect, $query);

    $con = 0;
    while ($row = mysqli_fetch_assoc($result)) {
      for ($i = 0; $i <= count($result); $i++) {
        $correocliente = $row["email"];
      }

      //Agregar=====================================================
      // Ruta del directorio donde están los archivos
      $path = $rutafirma;
      $files = array_diff(scandir($path), array('.', '..'));
      //=============================================================
      $factura = $row['numero_ruc'] . "-" . $row['codigo_nota'] . "-" . $row['numeroserienota'];

      //Validar si existe el archivo firmado
      foreach ($files as $file) {
        // Divides en dos el nombre de tu archivo utilizando el . 
        $dataSt = explode(".", $file);
        // Nombre del archivo
        $fileName = $dataSt[0];
        $st = "1";
        // Extensión del archivo 
        $fileExtension = $dataSt[1];
        if ($factura == $fileName) {
          $archivoFactura = $fileName;
          // Realizamos un break para que el ciclo se interrumpa
          break;
        }
      }
      //$url=$rutafirma.$archivoFactura.'.xml';
      $ZipFactura = $rutaenvio . $archivoFactura . '.zip';
      copy($ZipFactura, $archivoFactura . '.zip');
      $ZipFinal = $factura . '.zip';
      //echo $ZipFactura;

      $webservice = $datosc->rutaserviciosunat;
      $usuarioSol = $datosc->usuarioSol;
      $claveSol = $datosc->claveSol;
      $nruc = $datosc->numeroruc;

      //Llamada al WebService=======================================================================
      $service = $webservice;
      $headers = new CustomHeaders($nruc . $usuarioSol, $claveSol);
      $client = new SoapClient(
        $service,
        [
          'cache_wsdl' => WSDL_CACHE_NONE,
          'trace' => TRUE,
          'soap_version' => SOAP_1_1
        ]
      );

      try {
        $client->__setSoapHeaders([$headers]);
        $fcs = $client->__getFunctions();
        $params = array('fileName' => $ZipFinal, 'contentFile' => file_get_contents($ZipFinal));

        //Llamada al WebService=======================================================================
        $status = $client->sendBill($params); // Comando para enviar xml a SUNAT
        $conte = $client->__getLastResponse();
        $texto = trim(strip_tags($conte));


        $zip = new ZipArchive();
        if ($zip->open("R" . $ZipFinal, ZIPARCHIVE::CREATE) === true) {
          $zip->addEmptyDir("dummy");
          $zip->close();
        }


        $rpt = fopen("R" . $ZipFinal, 'w') or die("no se pudo crear archivo");
        fwrite($rpt, base64_decode($texto));
        fclose($rpt);
        rename("R" . $ZipFinal, $rutarpta . "R" . $ZipFinal);
        unlink($ZipFinal);
        // Llamada al WebService=======================================================================
      } catch (SoapFault $exception) {
        $exception = print_r($client->__getLastRequest());
      }

    } //Fin While

    $rutarptazip = $rutarpta . "R" . $ZipFinal;
    $zip = new ZipArchive;
    if ($zip->open($rutarptazip) === TRUE) {
      $zip->extractTo($rutaunzip);
      $zip->close();
    }
    $xmlFinal = $rutaunzip . 'R-' . $factura . '.xml';
    $data[0] = "";
    $rpta[0] = "";
    $sxe = new SimpleXMLElement($xmlFinal, null, true);
    $urn = $sxe->getNamespaces(true);
    $sxe->registerXPathNamespace('cac', $urn['cbc']);
    $data = $sxe->xpath('//cbc:Description');
    $rpta = $sxe->xpath('//cbc:ResponseCode');

    if ($rpta[0] == '0') {
      $msg = "Aceptada por SUNAT";
      $sqlCodigo = "update notacd set CodigoRptaSunat='$rpta[0]',  DetalleSunat='$data[0]', estado='5' where idnota='$idnota'";
      $sqlestadocompro = "";
    } else {
      $sqlCodigo = "update notacd set CodigoRptaSunat='$rpta[0]',  DetalleSunat='$data[0]', estado='4' where idnota='$idnota'";
    }

    ejecutarConsulta($sqlCodigo);

    return $data[0];

  }



  public function mostrarxml($idnota, $idempresa)
  {
    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
      exit();
    }

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp($idempresa);
    $datose = $datos->fetch_object();

    $nombrecomercial = $datose->nombre_comercial;

    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutadata = $Prutas->rutadata; // ruta de la carpeta DATA
    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutadatalt = $Prutas->rutadatalt; // ruta de la carpeta DATAALTERNA
    $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta rutaenvio
    $rutaunzipxml = $Prutas->unziprpta; // ruta de la carpeta ruta unziprpta

    $query = "select
     n.codigo_nota as tipocomp, 
     n.numeroserienota as numerodoc 
     from 
     notacd n where n.idnota='$idnota' and n.estado in('1','4','5') order by numerodoc";

    $result = mysqli_query($connect, $query);


    if ($result) {
      while ($row = mysqli_fetch_assoc($result)) {
        for ($i = 0; $i <= count($result); $i++) {
          $tipocomp = $row["tipocomp"];
          $numerodoc = $row["numerodoc"];
          $ruc = $datose->numero_ruc;
        }
      }
      $cabextxml = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";
      //$cabextxml="..//sfs//firma//20603504969-07-FF01-1.xml";
      $rpta = array('rutafirma' => $cabextxml);

    } else {

      $rpta = array('rutafirma' => 'Aún no se ha creado el archivo XML.');
    }


    return $rpta;
  }





  public function mostrarrpta($idnota)
  {
    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
      exit();
    }

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp('1');
    $datose = $datos->fetch_object();

    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2('1');
    $Prutas = $Rrutas->fetch_object();
    $rutarpta = $Prutas->rutarpta; // ruta de la carpeta DATA
    $rutaunzipxml = $Prutas->unziprpta; // ruta de la carpeta ruta unziprpta


    $query = "select
     n.codigo_nota as tipocomp, 
     n.numeroserienota as numerodoc 
     from 
     notacd n where n.idnota='$idnota' and n.estado in('4','5') order by numerodoc";

    $result = mysqli_query($connect, $query);

    $con = 0; //COntador de variable

    while ($row = mysqli_fetch_assoc($result)) {
      for ($i = 0; $i <= count($result); $i++) {
        $tipocomp = $row["tipocomp"];
        $numerodoc = $row["numerodoc"];
        $ruc = $datose->numero_ruc;
      }
    }

    $rutarptazip = $rutarpta . 'R' . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".zip";
    // $zip = new ZipArchive;
    // //en la función open se le pasa la ruta de nuestro archivo (alojada en carpeta temporal)
    // if ($zip->open($rutarptazip) === TRUE) 
    // {
    //   //función para extraer el ZIP, le pasamos la ruta donde queremos que nos descomprima
    //   $zip->extractTo($rutaunzipxml);
    //   $zip->close();
    // }
    $rutaxmlrpta = $rutaunzipxml . 'R-' . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";
    $rpta = array('rpta' => $rutarptazip, 'rutaxmlr' => $rutaxmlrpta);
    return $rpta;
  }





  public function generarxmlNd($idnota, $idempresa)
  {
    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');

    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
      exit();
    }

    require_once "../modelos/Factura.php";
    $factura = new Factura();
    $datos = $factura->datosemp($idempresa);
    $datose = $datos->fetch_object();

    $nombrecomercial = $datose->nombre_comercial;

    //Inclusion de la tabla RUTAS
    require_once "../modelos/Rutas.php";
    $rutas = new Rutas();
    $Rrutas = $rutas->mostrar2($idempresa);
    $Prutas = $Rrutas->fetch_object();
    $rutadata = $Prutas->rutadata; // ruta de la carpeta DATA
    $rutafirma = $Prutas->rutafirma; // ruta de la carpeta FIRMA
    $rutadatalt = $Prutas->rutadatalt; // ruta de la carpeta DATAALTERNA
    $rutaenvio = $Prutas->rutaenvio; // ruta de la carpeta DATAALTERNA

    $tipodocmod = '';
    $codigonota = '';
    $query = '';
    $queryinicial = "select tipo_doc_mod, codigo_nota from notacd where idnota='$idnota' ";
    $resulti = mysqli_query($connect, $queryinicial);


    while ($row = mysqli_fetch_assoc($resulti)) {

      $tipodocmod = $row["tipo_doc_mod"]; //tipo de documento
      $codigonota = $row["codigo_nota"]; //tipo de documento


      if ($tipodocmod == '01') {

        $query = "select
     date_format(n.fecha, '%Y-%m-%d') as fecha, 
     right(substring_index(n.numeroserienota,'-',1),1) as serie,
     date_format(n.fecha, '%H:%i:%s') as hora,
     p.tipo_documento as  tipodocuCliente, 
     p.numero_documento, 
     p.razon_social, 
     n.tipo_moneda as moneda, 
     n.total_val_venta_og as subtotal, 
     n.sum_igv as igv, 
     n.importe_total as total, 
     n.codigo_nota as tipocomp, 
     n.numeroserienota as numerodoc, 
     n.estado, 
     f.codigo_tributo_22_3 as codigotrib,
     f.nombre_tributo_22_4  as nombretrib,
     f.codigo_internacional_22_5 as codigointtrib,
     f.total_operaciones_gravadas_codigo_18_1 as opera,
     f.numeracion_08 as refeiddoc,
     n.codtiponota as responsecode,
     n.motivonota as descmotivo,
     n.codigo_nota,
     n.tipo_doc_mod
     from 
     notacd n inner join factura f on n.idcomprobante=f.idfactura inner join persona p on f.idcliente=p.idpersona inner join empresa e on f.idempresa=e.idempresa where idnota='$idnota' and n.estado in('1','4') order by numerodoc";


        $querydetfb = "select
       n.tipo_doc_mod as tipocomp, 
       n.serie_numero as numerodoc,  
       dn.cantidad as cantidad, 
       a.codigo, 
       a.nombre as descripcion, 
       a.unidad_medida as um,
       replace(format(dn.valor_unitario,5),',','') as vui, 
       dn.igv as igvi, 
       dn.precio_venta as pvi, 
       dn.valor_venta as vvi,
       (dn.valor_venta * 0.18) as sutribitem,
       dn.nro_orden as numorden,

       dn.aigv,
       dn.codtrib,
       dn.nomtrib,
       dn.coditrib,
       a.codigosunat,
       n.tipo_moneda as moneda,
       n.tipo_doc_mod

       from
       detalle_notacd_art dn inner join articulo a on dn.idarticulo=a.idarticulo inner join notacd n on dn.idnotacd=n.idnota inner join factura f on n.idcomprobante=f.idfactura
          where n.idnota='$idnota' and n.estado in ('1','4') order by n.fecha";

      } else {

        $query = "select
     date_format(n.fecha, '%Y-%m-%d') as fecha, 
     right(substring_index(n.numeroserienota,'-',1),1) as serie,
     date_format(n.fecha, '%H:%i:%s') as hora,
     p.tipo_documento as  tipodocuCliente, 
     p.numero_documento, 
     p.razon_social, 
     b.tipo_moneda_24 as moneda, 
     n.total_val_venta_og as subtotal, 
     n.sum_igv as igv, 
     n.importe_total as total, 
     n.codigo_nota as tipocomp, 
     n.numeroserienota as numerodoc, 
     n.estado, 
     b.codigo_tributo_18_3 as codigotrib,
     b.nombre_tributo_18_4  as nombretrib,
     b.codigo_internacional_18_5 as codigointtrib,
     b.monto_15_2 as opera,
     b.numeracion_07 as refeiddoc,
     n.codtiponota as responsecode,
     n.motivonota as descmotivo,
     n.codigo_nota,
     n.tipo_doc_mod
     from 
     notacd n inner join boleta b on n.idcomprobante=b.idboleta inner join persona p on b.idcliente=p.idpersona inner join empresa e on b.idempresa=e.idempresa where idnota='$idnota' and n.estado in('1','4') order by numerodoc";


        $querydetfb = "select
       b.tipo_documento_06 as tipocomp, 
       b.numeracion_07 as numerodoc,  
       dn.cantidad as cantidad, 
       a.codigo, 
       a.nombre as descripcion, 
       a.unidad_medida as um,
       replace(format(dn.valor_unitario,5),',','') as vui, 
       dn.igv as igvi, 
       dn.precio_venta as pvi, 
       dn.valor_venta as vvi,
       (dn.valor_venta * 0.18) as sutribitem,
       dn.nro_orden as numorden,

       dn.aigv,
       dn.codtrib,
       dn.nomtrib,
       dn.coditrib,
       a.codigosunat,
       b.tipo_moneda_24 as moneda,
       n.tipo_doc_mod

       from
       notacd n inner join detalle_notacd_art dn on n.idnota=dn.idnotacd  inner join boleta b on n.idcomprobante=b.idboleta  inner join articulo a on dn.idarticulo=a.idarticulo
          where n.idnota='$idnota' and n.estado in ('1','4') order by n.fecha";


      } //Find e if

    } //Fin de while

    $resultfb = "";
    $result = mysqli_query($connect, $query);
    $resultfb = mysqli_query($connect, $querydetfb);



    //Parametros de salida
    $fecha = array();
    $hora = array();
    $serie = array();
    $tipodocu = array();
    $numdocu = array();
    $rasoc = array();
    $moneda = array();
    $codigotrib = array();
    $nombretrib = array();
    $codigointtrib = array();
    $subtotal = array();
    $igv = array();
    $total = array();
    $tdescu = array();
    $opera = array();
    $ubigueo = array();

    $refeiddoc = array();
    $responsecode = array();
    $descmotivo = array();
    $tipodocuorigen = array();



    $con = 0; //COntador de variable


    while ($row = mysqli_fetch_assoc($result)) {
      for ($i = 0; $i <= count($result); $i++) {
        $tipodocmod = $row["tipo_doc_mod"]; //tipo de documento
        //
        $fecha[$i] = $row["fecha"]; //Fecha emision
        $serie[$i] = $row["serie"];
        $tipodocu[$i] = $row["tipodocuCliente"]; //Tipo de documento de cliente ruc o dni

        $numdocu[$i] = $row["numero_documento"]; //NUmero de docuemnto de cliente
        $rasoc[$i] = $row["razon_social"]; //Nombre de cliente
        $moneda[$i] = $row["moneda"];
        $subtotal[$i] = $row["subtotal"];
        $igv[$i] = $row["igv"];
        $total[$i] = $row["total"];
        //$tdescu[$i]=$row["tdescuento"];
        $hora[$i] = $row["hora"];
        $tipocomp = $row["tipocomp"];
        $numerodoc = $row["numerodoc"];
        $ruc = $datose->numero_ruc;
        $ubigueo = "0000";
        $opera[$i] = $row["opera"];

        $codigotrib[$i] = $row["codigotrib"]; //codigo de tributo de la tabla catalo 5
        $nombretrib[$i] = $row["nombretrib"]; //NOmbre de tributo de la tabla catalo 5
        $codigointtrib[$i] = $row["codigointtrib"]; //Codigo internacional de la tabla catalo 5

        $refeiddoc[$i] = $row["refeiddoc"]; //Numero de documento de referencia
        $responsecode[$i] = $row["responsecode"]; //Codigo de motivo de nota 
        $descmotivo[$i] = $row["descmotivo"]; //Descripcion de motivo

        //  if ($tipodocmod=='01') {
        //   $resultfb = mysqli_query($connect, $querydetfac); 
        // }else{
        //   $resultfb = mysqli_query($connect, $querydetbol);             
        // }

        require_once "Letras.php";
        $V = new EnLetras();
        $con_letra = strtoupper($V->ValorEnLetras($total[$i], "NUEVOS SOLES"));


        //======================================== FORMATO XML ========================================================
        $domiciliofiscal = $datose->domicilio_fiscal;
        //Primera parte
        $facturaXML = '<?xml version="1.0" encoding="utf-8"?>
           <DebitNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2"
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
                <cbc:ID>' . $numerodoc . '</cbc:ID>
                <cbc:IssueDate>' . $fecha[$i] . '</cbc:IssueDate>
                <cbc:IssueTime>' . $hora[$i] . '</cbc:IssueTime>
                <cbc:DocumentCurrencyCode>' . $moneda[$i] . '</cbc:DocumentCurrencyCode>

                <cac:DiscrepancyResponse>
                    <cbc:ReferenceID>' . $refeiddoc[$i] . '</cbc:ReferenceID>
                    <cbc:ResponseCode>' . $responsecode[$i] . '</cbc:ResponseCode>
                    <cbc:Description>' . $descmotivo[$i] . '</cbc:Description>
                </cac:DiscrepancyResponse>

                 <cac:BillingReference>
                    <cac:InvoiceDocumentReference>
                        <cbc:ID>' . $refeiddoc[$i] . '</cbc:ID>
                        <cbc:DocumentTypeCode>' . $tipodocmod . '</cbc:DocumentTypeCode>
                    </cac:InvoiceDocumentReference>
                </cac:BillingReference>

                <cac:Signature>
                    <cbc:ID>' . $ruc . '</cbc:ID>
                    <cbc:Note>SENCON</cbc:Note>
                    <cac:SignatoryParty>
                        <cac:PartyIdentification>
                            <cbc:ID>' . $ruc . '</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyName>
                            <cbc:Name><![CDATA[' . $nombrecomercial . ']]></cbc:Name>
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
                            <cbc:ID schemeID="6">' . $ruc . '</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyName>
                            <cbc:Name><![CDATA[' . $nombrecomercial . ']]></cbc:Name>
                        </cac:PartyName>
                        <cac:PartyLegalEntity>
                            <cbc:RegistrationName><![CDATA[' . $nombrecomercial . ']]></cbc:RegistrationName>
                            <cac:RegistrationAddress>
                                <cbc:ID>' . $ubigueo . '</cbc:ID>
                                <cbc:AddressTypeCode>' . $ubigueo . '</cbc:AddressTypeCode>
                            </cac:RegistrationAddress>
                        </cac:PartyLegalEntity>
                    </cac:Party>
                </cac:AccountingSupplierParty>

                <cac:AccountingCustomerParty>
                    <cac:Party>
                        <cac:PartyIdentification>
                            <cbc:ID schemeID="' . $tipodocu[$i] . '">' . $numdocu[$i] . '</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyLegalEntity>
                            <cbc:RegistrationName><![CDATA[' . $nombrecomercial . ']]></cbc:RegistrationName>
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


        $facturaXML .= '<cac:TaxTotal>
                    <cbc:TaxAmount currencyID="' . $moneda[$i] . '">' . $igv[$i] . '</cbc:TaxAmount>
                        <cac:TaxSubtotal>
                        <cbc:TaxableAmount currencyID="' . $moneda[$i] . '">' . $subtotal[$i] . '</cbc:TaxableAmount>
                        <cbc:TaxAmount currencyID="' . $moneda[$i] . '">' . $igv[$i] . '</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cac:TaxScheme>
                                <cbc:ID>' . $codigotrib[$i] . '</cbc:ID>
                                <cbc:Name>' . $nombretrib[$i] . '</cbc:Name>
                                <cbc:TaxTypeCode>' . $codigointtrib[$i] . '</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>
                              </cac:TaxTotal>

              <cac:RequestedMonetaryTotal>
                 <cbc:PayableAmount currencyID="' . $moneda[$i] . '">' . $total[$i] . '</cbc:PayableAmount>
              </cac:RequestedMonetaryTotal>';


      } //For cabecera
      $i = $i + 1;
      $con = $con + 1;
    } //While cabecera

    $codigo = array();
    $cantidad = array();
    $descripcion = array();
    $um = array();
    $vui = array();
    $igvi = array();
    $pvi = array();
    $vvi = array();
    $sutribitem = array();
    $aigv = array();
    $codtrib = array();
    $nomtrib = array();
    $coditrib = array();
    $codigosunat = array();
    $numorden = array();
    $tmon = array();

    while ($row = mysqli_fetch_assoc($resultfb)) {
      for ($i = 0; $i < count($resultfb); $i++) {
        $codigo[$i] = $row["codigo"];
        $cantidad[$i] = $row["cantidad"];
        $descripcion[$i] = $row["descripcion"];
        $vui[$i] = $row["vui"];
        $sutribitem[$i] = $row["sutribitem"];
        $igvi[$i] = $row["igvi"];
        $pvi[$i] = $row["pvi"];
        $vvi[$i] = $row["vvi"];
        $um[$i] = $row["um"];
        $tipocompf = $row["tipocomp"];
        $numerodocf = $row["numerodoc"];
        $ruc = $datose->numero_ruc;
        $numorden[$i] = $row["numorden"];

        $codtrib[$i] = $row["codtrib"];
        $nomtrib[$i] = $row["nomtrib"];
        $coditrib[$i] = $row["coditrib"];
        $codigosunat[$i] = $row["codigosunat"];


        $tmon[$i] = $row["moneda"];

        /* Número de orden del Ítem
           Cantidad y Unidad de medida por ítem
           Valor de venta del ítem  */

        $facturaXML .= '<cac:DebitNoteLine>
                    <cbc:ID>' . $numorden[$i] . '</cbc:ID>
                    <cbc:DebitedQuantity unitCode="' . $um[$i] . '">' . number_format($cantidad[$i], 2, '.', '') . '</cbc:DebitedQuantity>
                    <cbc:LineExtensionAmount currencyID="' . $tmon[$i] . '">' . number_format($vvi[$i], 2, '.', '') . '</cbc:LineExtensionAmount>
                    
                    <cac:PricingReference>
                        <cac:AlternativeConditionPrice>
                            <cbc:PriceAmount currencyID="' . $tmon[$i] . '">' . number_format($pvi[$i], 2, '.', '') . '</cbc:PriceAmount>
                            <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
                        </cac:AlternativeConditionPrice>
                    </cac:PricingReference>

                    <cac:TaxTotal>
                        <cbc:TaxAmount currencyID="' . $tmon[$i] . '">' . number_format($sutribitem[$i], 2, '.', '') . '</cbc:TaxAmount>                        
                        <cac:TaxSubtotal>
                            <cbc:TaxableAmount currencyID="' . $tmon[$i] . '">' . number_format($vvi[$i], 2, '.', '') . '</cbc:TaxableAmount>
                            <cbc:TaxAmount currencyID="' . $tmon[$i] . '">' . number_format($sutribitem[$i], 2, '.', '') . '</cbc:TaxAmount>
                            <cac:TaxCategory>
                                <cbc:Percent>18.00</cbc:Percent>
                                <cbc:TaxExemptionReasonCode>10</cbc:TaxExemptionReasonCode>
                                <cac:TaxScheme>
                                    <cbc:ID>1000</cbc:ID>
                                    <cbc:Name>IGV</cbc:Name>
                                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                                </cac:TaxScheme>
                            </cac:TaxCategory>
                        </cac:TaxSubtotal>
                    </cac:TaxTotal>

                    <cac:Item>
                        <cbc:Description><![CDATA[' . $descripcion[$i] . ']]></cbc:Description>
                    </cac:Item>

                    <cac:Price>
                        <cbc:PriceAmount currencyID="' . $tmon[$i] . '">' . number_format($vui[$i], 5, '.', '') . '</cbc:PriceAmount>
                    </cac:Price>
                </cac:DebitNoteLine>';

      } //Fin for
    } //Find e while 
    $facturaXML .= '</DebitNote>';
    //FIN DE CABECERA ===================================================================



    // Nos aseguramos de que la cadena que contiene el XML esté en UTF-8
    $facturaXML = mb_convert_encoding($facturaXML, "UTF-8");
    // Grabamos el XML en el servidor como un fichero plano, para
    // poder ser leido por otra aplicación.
    $gestor = fopen($rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml", 'w');
    fwrite($gestor, $facturaXML);
    fclose($gestor);

    $cabextxml = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";
    $cabxml = $ruc . "-" . $tipocomp . "-" . $numerodoc . ".xml";
    $nomxml = $ruc . "-" . $tipocomp . "-" . $numerodoc;
    $nomxmlruta = $rutafirma . $ruc . "-" . $tipocomp . "-" . $numerodoc;

    require_once("../greemter/Greenter.php");
    $invo = new Greenter();
    $out = $invo->getDatFac($cabextxml);

    $filenaz = $nomxml . ".zip";
    $zip = new ZipArchive();
    if ($zip->open($filenaz, ZIPARCHIVE::CREATE) === true) {
      //$zip->addEmptyDir("dummy");
      $zip->addFile($cabextxml, $cabxml);
      $zip->close();

      //if(!file_exists($rutaz)){mkdir($rutaz);}
      $imagen = file_get_contents($filenaz);
      $imageData = base64_encode($imagen);
      rename($cabextxml, $rutafirma . $cabxml);
      rename($filenaz, $rutaenvio . $filenaz);
    } else {
      $out = "Error al comprimir archivo";
    }

    $rpta = array('cabextxml' => $cabextxml, 'cabxml' => $cabxml, 'rutafirma' => $cabextxml);
    $sqlDetalle = "update notacd set DetalleSunat='XML firmado' where idnota='$idnota'";
    ejecutarConsulta($sqlDetalle);

    return $rpta;

  } //Fin de funcion


  public function bajanc($idnota, $fecha_baja, $com, $hora)
  {
    $sw = true;
    $connect = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    mysqli_query($connect, 'SET NAMES "' . DB_ENCODE . '"');
    //Si tenemos un posible error en la conexión lo mostramos
    if (mysqli_connect_errno()) {
      printf("Falló conexión a la base de datos: %s\n", mysqli_connect_error());
      exit();
    }

    //Regresar stock


    $query = "select 
    idnotacd, 
    idarticulo 
    from 
    detalle_notacd_art 
    where 
    idnotacd ='$idnota'";
    $resultado = mysqli_query($connect, $query);

    $Idnc = array();
    $Ida = array();
    $sw = true;
    $num_elementos = 0;

    while ($fila = mysqli_fetch_assoc($resultado)) {
      for ($i = 0; $i < count($resultado); $i++) {
        $Idnc[$i] = $fila["idnotacd"];
        $Ida[$i] = $fila["idarticulo"];

        $sql_update_articulo = "update
        notacd nc 
        inner join 
        detalle_notacd_art dnc 
        on nc.idnota=dnc.idnotacd
        inner join
        articulo a  
        on dnc.idarticulo = a.idarticulo
     set
         a.saldo_finu = a.saldo_finu - dnc.cantidad, 
        a.stock = a.stock - dnc.cantidad, 
        a.ventast = a.ventast + dnc.cantidad        
        where
        nc.idnota='$Idnc[$i]' and dnc.idarticulo='$Ida[$i]'";

      }
      ejecutarConsulta($sql_update_articulo) or $sw = false;

    }
    $num_elementos = $num_elementos + 1;
    //return $sw; 


    $sqlestado = "update notacd nc inner join factura f on nc.idcomprobante=f.idfactura  
         set 
         nc.estado='3', 
         nc.fecha_baja='$fecha_baja $hora', 
         nc.comentario_baja='$com', 
         nc.DetalleSunat='C/Baja',  
         nc.CodigoRptaSunat='3',
         f.estado='5'
         where 
         idnota='$idnota'";
    ejecutarConsulta($sqlestado) or $sw = false;
    //}
    //Fin de WHILE
//**************************************************************************
    return $sw;
  }

}



?>